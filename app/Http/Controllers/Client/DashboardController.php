<?php

namespace App\Http\Controllers\Client;

use App\Services\OrderService;
use App\Services\Shop;
use App\Support\DemoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\ProfilePhoto;
use App\Services\AccountVerification;

class DashboardController
{
    public function dashboard(Request $request)
    {
        return view('client.dashboard', [
            'orders' => OrderService::forUser($request->user()->id), 'categories' => Shop::categories(),
            'verificationStatus' => AccountVerification::status($request->user()->id),
        ]);
    }

    public function orders(Request $request)
    {
        return view('client.orders', ['orders' => OrderService::forUser($request->user()->id)]);
    }

    public function track(Request $request, ?string $no = null)
    {
        $userId = $request->user()->id;
        $no = $no ?: OrderService::latestActiveNo($userId);
        $order = $no ? OrderService::find($no, $userId) : null;
        if ($no && ! $order) {
            abort(404);
        }

        return view('client.track', ['order' => $order, 'steps' => OrderService::STEP_LABELS]);
    }

    public function cancel(Request $request, string $no)
    {
        $ok = OrderService::cancel($no, $request->user()->id);

        return back()->with('status', $ok ? 'Order ' . $no . ' was cancelled.' : 'This order can no longer be cancelled.');
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $district = $user->district_id ? DB::table('districts')->where('id', $user->district_id)->value('name') : '';

        return view('client.profile', [
            'me' => ['name' => $user->name, 'email' => $user->email, 'phone' => (string) $user->phone, 'address' => (string) $user->address, 'district' => (string) $district, 'upazila' => (string) $user->upazila, 'photo' => $user->photo, 'role' => 'Client'],
            'districts' => Shop::districts(),
            'verificationStatus' => AccountVerification::status($user->id),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['required', 'string', 'max:20'], 'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'district' => ['required', 'exists:districts,name'], 'upazila' => ['required', 'string', 'max:80'], 'address' => ['required', 'string', 'max:255'],
        ]);
        $d = DB::table('districts')->where('name', $data['district'])->first();
        $photo = ProfilePhoto::save($request->file('photo'));
        $phoneChanged = (string) $request->user()->phone !== (string) $data['phone'];
        if ($phoneChanged) {
            AccountVerification::invalidateAfterPhoneChange($request->user()->id, 'client');
        }
        $request->user()->update([
            'name' => $data['name'], 'email' => $data['email'] ?: null, 'phone' => $data['phone'], 'address' => $data['address'], 'upazila' => $data['upazila'],
            'division_id' => $d->division_id, 'district_id' => $d->id, 'latitude' => $d->latitude, 'longitude' => $d->longitude,
            ...($photo ? ['photo' => $photo] : []),
        ]);

        if ($phoneChanged) {
            return redirect()->route('account.verification')->with('status', 'Phone number changed. Submit verification again to confirm the new number.');
        }
        return back()->with('status', 'Your profile was updated.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'confirmed', 'min:8']]);
        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is not correct.']);
        }
        $user->update(['password' => $data['password']]);

        return back()->with('status', 'Your password was changed.');
    }

    /** Notifications are still demo data until the notification stage */
    public function notifications()
    {
        return view('client.notifications', ['items' => DemoData::notifications('client')]);
    }
}
