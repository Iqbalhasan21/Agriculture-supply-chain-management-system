<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\ProfilePhoto;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class AdminController
{
    public function dashboard()
    {
        $stats = [
            'users' => DB::table('users')->count(),
            'farmers' => DB::table('farmers')->where('status', 'approved')->count(),
            'products' => DB::table('products')->where('status', 'approved')->count(),
            'orders' => DB::table('orders')->count(),
            'revenue' => (int) DB::table('orders')->whereNotIn('status', ['cancelled'])->sum('total'),
        ];
        $recent = DB::table('orders')->orderByDesc('id')->limit(8)->get(['order_no', 'name', 'total', 'status', 'created_at']);

        return view('admin.dashboard', compact('stats', 'recent'));
    }

    public function users()
    {
        $users = User::query()->addSelect(['verification_status' => DB::table('user_verifications')->select('status')->whereColumn('user_verifications.user_id', 'users.id')->limit(1)])->orderByDesc('id')->paginate(30);
        $farmerRequests = DB::table('farmers as f')->join('users as u', 'u.id', '=', 'f.user_id')
            ->leftJoin('districts as d', 'd.id', '=', 'f.district_id')->where('f.status', 'pending')->orderBy('f.id')
            ->get(['f.id', 'f.farm_name', 'f.upazila', 'f.address', 'u.name', 'u.email', 'u.phone', 'd.name as district']);
        $verifications = DB::table('user_verifications as v')->join('users as u', 'u.id', '=', 'v.user_id')
            ->where('v.status', 'pending')->orderBy('v.submitted_at')
            ->get(['v.user_id', 'v.id_front_path', 'v.id_back_path', 'v.face_path', 'v.nid_encrypted', 'v.submitted_at', 'u.name', 'u.email', 'u.phone', 'u.role'])
            ->map(function ($verification) {
                try {
                    $verification->nid_number = $verification->nid_encrypted ? Crypt::decryptString($verification->nid_encrypted) : null;
                } catch (\Throwable) {
                    $verification->nid_number = null;
                }
                return $verification;
            });

        return view('admin.users', compact('users', 'farmerRequests', 'verifications'));
    }

    public function approveVerification(Request $request, int $id)
    {
        $request->validate([
            'phone_verified' => ['accepted'],
            'nid_verified' => ['accepted'],
            'face_verified' => ['accepted'],
        ]);
        $result = DB::transaction(function () use ($request, $id) {
            $verification = DB::table('user_verifications')->where('user_id', $id)->lockForUpdate()->first() ?? abort(404);
            abort_unless($verification->status === 'pending', 409, 'This verification request is no longer pending.');
            foreach ([$verification->id_front_path, $verification->id_back_path, $verification->face_path] as $path) {
                abort_unless($path && Storage::disk('local')->exists($path), 422, 'A required verification image is missing. Ask the user to submit again.');
            }
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
            $nid = $verification->nid_encrypted ? Crypt::decryptString($verification->nid_encrypted) : '';
            abort_unless($user->nid_hash && hash_equals($user->nid_hash, User::nidHashFor($nid)), 422, 'The submitted NID does not match this account.');
            $now = now();
            DB::table('user_verifications')->where('user_id', $id)->update([
                'status' => 'verified', 'phone_verified_at' => $now, 'nid_verified_at' => $now, 'face_verified_at' => $now,
                'id_front_path' => null, 'id_back_path' => null, 'face_path' => null, 'nid_encrypted' => null,
                'reviewed_by' => $request->user()->id, 'reviewed_at' => $now, 'review_note' => null, 'updated_at' => $now,
            ]);
            DB::table('users')->where('id', $id)->update(['is_active' => true, 'updated_at' => $now]);
            if ($user->role === 'farmer') {
                DB::table('farmers')->where('user_id', $id)->update([
                    'status' => 'approved', 'is_verified' => true, 'review_note' => null, 'updated_at' => $now,
                ]);
            }

            return ['user_id' => $id, 'role' => $user->role];
        });
        Storage::disk('local')->deleteDirectory('verification/' . $id);
        \App\Services\FarmDirectNotifier::user($result['user_id'], 'Account verified', 'Your phone, NID and face photo were verified. Your FarmDirect account is active.', '/' . $result['role'] . '/dashboard');

        return back()->with('status', 'Phone, NID and face checks recorded. The account is verified. Review images and temporary NID data were deleted.');
    }

    public function rejectVerification(Request $request, int $id)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $path = DB::transaction(function () use ($request, $id, $data) {
            $verification = DB::table('user_verifications')->where('user_id', $id)->lockForUpdate()->first() ?? abort(404);
            abort_unless($verification->status === 'pending', 409, 'This verification request is no longer pending.');
            DB::table('user_verifications')->where('user_id', $id)->update([
                'status' => 'rejected', 'id_front_path' => null, 'id_back_path' => null, 'face_path' => null,
                'nid_encrypted' => null, 'phone_verified_at' => null, 'nid_verified_at' => null, 'face_verified_at' => null,
                'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'review_note' => trim($data['reason']), 'updated_at' => now(),
            ]);
            return 'verification/' . $id;
        });
        Storage::disk('local')->deleteDirectory($path);
        \App\Services\FarmDirectNotifier::user($id, 'Verification needs another submission', 'Please sign in and resubmit your verification documents: ' . trim($data['reason']), '/account/verification');

        return back()->with('status', 'Verification declined. The user can sign in and submit new documents. Old files were deleted.');
    }

    public function messages(Request $request)
    {
        $unreadOnly = $request->boolean('unread');
        $query = DB::table('contact_messages')->orderByDesc('id');
        if ($unreadOnly) {
            $query->where('is_read', false);
        }
        $messages = $query->paginate(20)->withQueryString();

        return view('admin.messages', compact('messages', 'unreadOnly'));
    }

    public function markMessageRead(int $id)
    {
        DB::table('contact_messages')->where('id', $id)->exists() || abort(404);
        DB::table('contact_messages')->where('id', $id)->update(['is_read' => true, 'updated_at' => now()]);

        return back()->with('status', 'Message marked as read.');
    }

    public function replyToMessage(Request $request, int $id)
    {
        $data = $request->validate(['admin_reply' => ['required', 'string', 'max:5000']]);
        $message = DB::table('contact_messages')->where('id', $id)->first() ?? abort(404);
        DB::table('contact_messages')->where('id', $id)->update([
            'admin_reply' => trim($data['admin_reply']), 'replied_by' => $request->user()->id,
            'replied_at' => now(), 'is_read' => true, 'updated_at' => now(),
        ]);

        return back()->with('status', 'Reply saved. Use the email link to send it to ' . $message->email . '.');
    }

    public function approveFarmer(int $id)
    {
        DB::transaction(function () use ($id) {
            $farmer = DB::table('farmers')->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            if ($farmer->status !== 'pending') {
                abort(409, 'This farmer request has already been reviewed.');
            }
            $now = now();
            DB::table('farmers')->where('id', $id)->update([
                'status' => 'approved', 'is_verified' => true, 'review_note' => null, 'updated_at' => $now,
            ]);
            DB::table('users')->where('id', $farmer->user_id)->update(['is_active' => true, 'updated_at' => $now]);
            \App\Services\FarmDirectNotifier::user((int) $farmer->user_id, 'Farmer account approved', 'Your farmer account is approved and ready to use.', '/farmer/dashboard');
        });

        return back()->with('status', 'Farmer approved. Their account is now active.');
    }

    public function rejectFarmer(Request $request, int $id)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        DB::transaction(function () use ($id, $data) {
            $farmer = DB::table('farmers')->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            if ($farmer->status !== 'pending') {
                abort(409, 'This farmer request has already been reviewed.');
            }
            $now = now();
            DB::table('farmers')->where('id', $id)->update([
                'status' => 'rejected', 'review_note' => trim($data['reason']), 'updated_at' => $now,
            ]);
            DB::table('users')->where('id', $farmer->user_id)->update(['is_active' => false, 'updated_at' => $now]);
            \App\Services\FarmDirectNotifier::user((int) $farmer->user_id, 'Farmer request reviewed', 'Your request was not approved: ' . trim($data['reason']), '/farmer/profile');
        });

        return back()->with('status', 'Farmer request rejected.');
    }

    public function toggleUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }
        if ($user->role === 'admin' && $user->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['user' => 'The last active admin account cannot be deactivated.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active ? 'Account activated.' : 'Account deactivated.');
    }

    public function updateRole(Request $request, int $id)
    {
        $data = $request->validate(['role' => ['required', 'in:admin,manager,farmer,client']]);
        $user = User::findOrFail($id);
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }
        if ($user->role === 'admin' && $data['role'] !== 'admin' && $user->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['role' => 'The last active admin account cannot be demoted.']);
        }

        $user->update(['role' => $data['role']]);
        if (in_array($data['role'], ['client', 'farmer'], true)) {
            DB::table('user_verifications')->insertOrIgnore([
                'user_id' => $user->id, 'status' => 'not_submitted', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return back()->with('status', 'User role updated.');
    }

    public function categories()
    {
        $categories = DB::table('categories')->orderBy('name')->get()->map(function ($category) {
            $category->products_count = DB::table('products')->where('category_id', $category->id)->count();
            return $category;
        });

        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:60', 'unique:categories,name']]);
        $slug = Str::slug($data['name']);
        if ($slug === '') {
            return back()->withErrors(['name' => 'Enter a category name containing letters or numbers.'])->withInput();
        }
        if (DB::table('categories')->where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'A category with this URL name already exists.'])->withInput();
        }

        DB::table('categories')->insert(['name' => $data['name'], 'slug' => $slug, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('status', 'Category created.');
    }

    public function updateCategory(Request $request, int $id)
    {
        $category = DB::table('categories')->where('id', $id)->first() ?? abort(404);
        $data = $request->validate(['name' => ['required', 'string', 'max:60', 'unique:categories,name,' . $id]]);
        $slug = Str::slug($data['name']);
        if ($slug === '' || DB::table('categories')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
            return back()->withErrors(['name' => 'A category with this URL name already exists.'])->withInput();
        }

        DB::table('categories')->where('id', $id)->update(['name' => $data['name'], 'slug' => $slug, 'updated_at' => now()]);

        return back()->with('status', 'Category updated.');
    }

    public function toggleCategory(int $id)
    {
        $category = DB::table('categories')->where('id', $id)->first() ?? abort(404);
        DB::table('categories')->where('id', $id)->update(['is_active' => ! $category->is_active, 'updated_at' => now()]);

        return back()->with('status', 'Category visibility updated.');
    }

    public function transactions(Request $request)
    {
        $status = $request->query('status');
        abort_if($status && ! in_array($status, ['completed', 'pending', 'failed', 'refunded'], true), 404);
        $query = DB::table('payments as p')->join('orders as o', 'o.id', '=', 'p.order_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')->orderByDesc('p.id')
            ->select(['p.id', 'o.id as order_id', 'o.order_no', 'o.user_id', 'u.name', 'p.method', 'p.amount', 'p.status', 'p.reference', 'p.payer_account', 'p.created_at']);
        if ($status) {
            $query->where('p.status', $status);
        }
        $payments = $query->paginate(30)->withQueryString();
        $totals = DB::table('payments')->selectRaw('status, COUNT(*) as count, SUM(amount) as amount')->groupBy('status')->get()->keyBy('status');

        return view('admin.transactions', compact('payments', 'totals', 'status'));
    }

    public function approvePayment(Request $request, int $id)
    {
        $customer = DB::transaction(function () use ($request, $id) {
            $payment = DB::table('payments')->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            abort_if($payment->method === 'cod', 422, 'Cash on Delivery is confirmed when the order is delivered.');
            abort_if($payment->status !== 'pending', 409, 'This payment has already been reviewed.');
            $order = DB::table('orders')->where('id', $payment->order_id)->lockForUpdate()->first() ?? abort(404);
            abort_if($order->status === 'cancelled', 409, 'Cancelled orders cannot be paid.');
            DB::table('payments')->where('id', $id)->update([
                'status' => 'completed', 'verified_by' => $request->user()->id, 'verified_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('orders')->where('id', $order->id)->update(['payment_status' => 'paid', 'updated_at' => now()]);

            return ['id' => (int) $order->user_id, 'no' => $order->order_no];
        });
        \App\Services\FarmDirectNotifier::user($customer['id'], 'Payment confirmed', 'Your payment for order ' . $customer['no'] . ' was verified.', '/client/orders');

        return back()->with('status', 'Payment verified and order marked as paid.');
    }

    public function rejectPayment(Request $request, int $id)
    {
        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:255']])['reason'] ?? null;
        $customer = DB::transaction(function () use ($request, $id, $reason) {
            $payment = DB::table('payments')->where('id', $id)->lockForUpdate()->first() ?? abort(404);
            abort_if($payment->method === 'cod', 422, 'Cash on Delivery cannot be rejected as a transfer.');
            abort_if($payment->status !== 'pending', 409, 'This payment has already been reviewed.');
            $order = DB::table('orders')->where('id', $payment->order_id)->lockForUpdate()->first() ?? abort(404);
            DB::table('payments')->where('id', $id)->update([
                'status' => 'failed', 'verified_by' => $request->user()->id, 'verified_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('orders')->where('id', $order->id)->update(['payment_status' => 'unpaid', 'updated_at' => now()]);

            return ['id' => (int) $order->user_id, 'no' => $order->order_no];
        });
        \App\Services\FarmDirectNotifier::user($customer['id'], 'Payment needs attention', 'Your payment for order ' . $customer['no'] . ' could not be verified.' . ($reason ? ' ' . $reason : ''), '/client/orders');

        return back()->with('status', 'Payment marked for follow-up and the customer was notified.');
    }

    public function exportTransactions()
    {
        $payments = DB::table('payments as p')->join('orders as o', 'o.id', '=', 'p.order_id')->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->orderByDesc('p.id')->get(['p.id', 'o.order_no', 'u.name as customer', 'p.method', 'p.amount', 'p.status', 'p.payer_account', 'p.reference', 'p.created_at']);

        return response()->streamDownload(function () use ($payments) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Payment ID', 'Order', 'Customer', 'Method', 'Amount', 'Status', 'Sender Account', 'Reference', 'Created']);
            foreach ($payments as $payment) {
                fputcsv($out, (array) $payment);
            }
            fclose($out);
        }, 'farmdirect-payments-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function reports(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $query = DB::table('orders')->whereNotIn('status', ['cancelled']);
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
        $summary = (clone $query)->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as revenue')->first();
        $statuses = (clone $query)->selectRaw('status, COUNT(*) as count')->groupBy('status')->get();
        $topProducts = DB::table('order_items as i')->join('orders as o', 'o.id', '=', 'i.order_id')
            ->whereNotIn('o.status', ['cancelled'])
            ->when(! empty($filters['from']), fn ($q) => $q->whereDate('o.created_at', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn ($q) => $q->whereDate('o.created_at', '<=', $filters['to']))
            ->selectRaw('i.name, SUM(i.quantity) as quantity, SUM(i.unit_price * i.quantity) as value')
            ->groupBy('i.name')->orderByDesc('quantity')->limit(10)->get();

        return view('admin.reports', ['summary' => $summary, 'statuses' => $statuses, 'topProducts' => $topProducts, 'filters' => $filters]);
    }

    public function exportReport(Request $request)
    {
        $filters = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $query = DB::table('orders as o')->leftJoin('districts as d', 'd.id', '=', 'o.district_id')->orderByDesc('o.id');
        if (! empty($filters['from'])) {
            $query->whereDate('o.created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('o.created_at', '<=', $filters['to']);
        }
        $orders = $query->get(['o.order_no', 'o.name', 'd.name as district', 'o.subtotal', 'o.delivery_charge', 'o.total', 'o.status', 'o.payment_status', 'o.created_at']);

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Order', 'Customer', 'District', 'Subtotal', 'Delivery', 'Total', 'Status', 'Payment', 'Created']);
            foreach ($orders as $order) {
                fputcsv($out, (array) $order);
            }
            fclose($out);
        }, 'farmdirect-orders-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function backup()
    {
        return view('admin.backup', ['lastExport' => DB::table('settings')->where('key', 'last_data_export_at')->value('value')]);
    }

    public function exportData()
    {
        $data = [
            'exported_at' => now()->toIso8601String(),
            'users' => DB::table('users')->get(['id', 'name', 'email', 'phone', 'address', 'role', 'division_id', 'district_id', 'upazila', 'is_active', 'created_at']),
            'farmers' => DB::table('farmers')->get(),
            'categories' => DB::table('categories')->get(),
            'products' => DB::table('products')->get(),
            'orders' => DB::table('orders')->get(),
            'order_items' => DB::table('order_items')->get(),
            'payments' => DB::table('payments')->get(),
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }, 'farmdirect-data-' . now()->format('Y-m-d-His') . '.json', ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function settings()
    {
        $settings = DB::table('settings')->pluck('value', 'key');

        return view('admin.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:80'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'delivery_charge' => ['required', 'integer', 'min:0', 'max:100000'],
            'free_delivery_above' => ['required', 'integer', 'min:0', 'max:10000000'],
            'bkash_account' => ['nullable', 'string', 'max:40'],
            'nagad_account' => ['nullable', 'string', 'max:40'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_account_name' => ['nullable', 'string', 'max:120'],
            'bank_account_number' => ['nullable', 'string', 'max:80'],
            'bank_branch' => ['nullable', 'string', 'max:120'],
        ]);
        $data['allow_registration'] = $request->boolean('allow_registration') ? '1' : '0';
        $data['cod_enabled'] = $request->boolean('cod_enabled') ? '1' : '0';
        $now = now();
        foreach ($data as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => (string) $value, 'updated_at' => $now, 'created_at' => $now]);
        }

        return back()->with('status', 'Settings saved.');
    }

    public function profile(Request $request)
    {
        return view('admin.profile', ['user' => $request->user()]);
    }

    public function saveProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'], 'address' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $photo = ProfilePhoto::save($request->file('photo'));
        unset($data['photo']);
        $data['email'] = $data['email'] ?: null;
        if ($photo) {
            $data['photo'] = $photo;
        }
        $request->user()->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function savePassword(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'confirmed', 'min:8']]);
        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is not correct.']);
        }
        $user->update(['password' => $data['password']]);

        return back()->with('status', 'Password updated.');
    }
}
