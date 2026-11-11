<?php

namespace App\Http\Controllers\Farmer;

use App\Services\FarmerService;
use App\Services\FarmDirectNotifier;
use App\Services\OrderException;
use App\Services\Shop;
use App\Services\ProfilePhoto;
use App\Services\AccountVerification;
use App\Support\DemoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class FarmerController
{
    /** The logged-in farmer's record id: only approved farmers get this far */
    private function fid(Request $request): int
    {
        return FarmerService::farmerId($request->user()->id) ?? abort(403, 'Your farmer account is not approved yet.');
    }

    private function productRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], 'category' => ['required', 'exists:categories,name'], 'unit' => ['required', 'string', 'max:50'],
            'price' => ['required', 'integer', 'min:1', 'max:1000000'], 'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:2000'], 'harvest_date' => ['nullable', 'date', 'before_or_equal:today'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /** Save an uploaded product photo in public/images/uploads and return the path used by the views */
    private function savePhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }
        $file = $request->file('photo');
        $name = 'p' . bin2hex(random_bytes(8)) . '.' . $file->extension();
        $file->move(public_path('images/uploads'), $name);

        return 'uploads/' . $name;
    }

    public function dashboard(Request $request)
    {
        return view('farmer.dashboard', [
            'd' => FarmerService::dashboard($this->fid($request)),
            'verificationStatus' => AccountVerification::status($request->user()->id),
        ]);
    }

    // ---- products ----
    public function products(Request $request)
    {
        return view('farmer.products', ['products' => FarmerService::products($this->fid($request))]);
    }

    public function create(Request $request)
    {
        $this->fid($request);

        return view('farmer.product-form', ['product' => null, 'categories' => Shop::categories(), 'farm' => FarmerService::farm($request->user()->id)]);
    }

    public function store(Request $request)
    {
        $fid = $this->fid($request);
        $data = $request->validate($this->productRules());

        $productId = FarmerService::createProduct($fid, $request->user()->id, $data, $this->savePhoto($request));
        FarmDirectNotifier::role('manager', 'Product awaiting approval', $data['name'] . ' was submitted for review.', '/manager/approvals');

        return redirect()->route('farmer.products')->with('status', 'Product submitted. A manager will review it before it appears in the shop.');
    }

    public function edit(Request $request, int $id)
    {
        $product = FarmerService::product($this->fid($request), $id) ?? abort(404);

        return view('farmer.product-form', ['product' => $product, 'categories' => Shop::categories(), 'farm' => FarmerService::farm($request->user()->id)]);
    }

    public function update(Request $request, int $id)
    {
        $fid = $this->fid($request);
        $data = $request->validate($this->productRules());

        try {
            $resubmitted = FarmerService::updateProduct($fid, $request->user()->id, $id, $data, $this->savePhoto($request));
        } catch (OrderException $e) {
            abort(404);
        }

        return redirect()->route('farmer.products')->with('status', $resubmitted ? 'Saved. Because key details changed, a manager will approve the product again.' : 'Product updated.');
    }

    public function stock(Request $request, int $id)
    {
        $fid = $this->fid($request);
        $data = $request->validate(['stock' => ['required', 'integer', 'min:0', 'max:1000000']]);

        try {
            FarmerService::updateStock($fid, $id, (int) $data['stock']);
        } catch (OrderException $e) {
            abort(404);
        }

        return back()->with('status', 'Stock updated.');
    }

    public function destroy(Request $request, int $id)
    {
        try {
            FarmerService::deleteProduct($this->fid($request), $id);
        } catch (OrderException $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return back()->with('status', 'Product deleted.');
    }

    // ---- orders, tracking, earnings ----
    public function orders(Request $request)
    {
        return view('farmer.orders', ['orders' => FarmerService::orders($this->fid($request))]);
    }

    public function tracking(Request $request, ?int $id = null)
    {
        $fid = $this->fid($request);
        $products = FarmerService::products($fid);
        $id = $id ?: ($products[0]['id'] ?? null);
        $journey = $id ? (FarmerService::journey($fid, $id) ?? abort(404)) : null;

        return view('farmer.tracking', ['products' => $products, 'journey' => $journey]);
    }

    public function earnings(Request $request)
    {
        return view('farmer.earnings', ['e' => FarmerService::earnings($this->fid($request))]);
    }

    // ---- farm location ----
    public function location(Request $request)
    {
        $this->fid($request);

        return view('farmer.location', ['farm' => FarmerService::farm($request->user()->id), 'districts' => Shop::districts()]);
    }

    public function saveLocation(Request $request)
    {
        $this->fid($request);
        // Only points inside Bangladesh are accepted
        $data = $request->validate([
            'district' => ['required', 'exists:districts,name'], 'upazila' => ['required', 'string', 'max:80'],
            'latitude' => ['required', 'numeric', 'between:20.5,26.8'], 'longitude' => ['required', 'numeric', 'between:88,92.8'],
        ]);
        FarmerService::saveLocation($request->user()->id, $data);

        return back()->with('status', 'Farm location saved. Clients in your district will see your products first.');
    }

    // ---- profile ----
    public function profile(Request $request)
    {
        $farm = FarmerService::farm($request->user()->id);

        return view('farmer.profile', ['farm' => $farm, 'verificationStatus' => AccountVerification::status($request->user()->id)]);
    }

    public function saveProfile(Request $request)
    {
        $this->fid($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['required', 'string', 'max:20'], 'address' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'farm_name' => ['required', 'string', 'max:255'], 'upazila' => ['required', 'string', 'max:80'], 'about' => ['nullable', 'string', 'max:2000'],
        ]);
        $photo = ProfilePhoto::save($request->file('photo'));
        $phoneChanged = (string) $request->user()->phone !== (string) $data['phone'];
        FarmerService::saveProfile($request->user()->id, $data);
        if ($phoneChanged) {
            AccountVerification::invalidateAfterPhoneChange($request->user()->id, 'farmer');
            return redirect()->route('account.verification')->with('status', 'Phone number changed. Submit verification again to confirm the new number.');
        }
        if ($photo) {
            $request->user()->update(['photo' => $photo]);
        }

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

        return back()->with('status', 'Your password was changed.');
    }

    /** Notifications are still demo data until the notification stage */
    public function notifications()
    {
        return view('farmer.notifications', ['items' => DemoData::notifications('farmer')]);
    }
}
