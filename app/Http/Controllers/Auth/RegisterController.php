<?php

namespace App\Http\Controllers\Auth;

use App\Models\Category;
use App\Models\District;
use App\Models\Farmer;
use App\Models\User;
use App\Services\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterController
{
    public function createClient()
    {
        abort_unless(Shop::setting('allow_registration', '1') === '1', 403, 'Registration is currently unavailable.');

        return view('pages.signup', [
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'categories' => Category::where('is_active', true)->orderBy('name')->pluck('name')->all(),
            'accountType' => in_array(request()->query('type'), ['client', 'farmer', 'manager', 'admin'], true) ? request()->query('type') : 'client',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Shop::setting('allow_registration', '1') === '1', 403, 'Registration is currently unavailable.');
        $choice = $request->validate(['account_type' => ['required', 'in:client,farmer,manager,admin']]);
        return match ($choice['account_type']) {
            'farmer' => $this->storeFarmer($request),
            'manager', 'admin' => $this->storeStaffRequest($request, $choice['account_type']),
            default => $this->storeClient($request),
        };
    }

    private function storeStaffRequest(Request $request, string $role): RedirectResponse
    {
        abort_unless(Shop::setting('allow_registration', '1') === '1', 403, 'Registration is currently unavailable.');
        $this->prepareNid($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'nid_number' => ['required', 'regex:/^(?:[0-9]{10}|[0-9]{13}|[0-9]{17})$/'],
            'nid_hash' => ['required', 'unique:users,nid_hash'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'district' => ['required', 'exists:districts,name'],
            'upazila' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);
        $district = District::where('name', $data['district'])->firstOrFail();
        User::create([
            'name' => $data['name'], 'email' => $data['email'] ?? null, 'nid_hash' => $data['nid_hash'],
            'password' => $data['password'], 'phone' => $data['phone'], 'address' => $data['address'],
            'upazila' => $data['upazila'], 'role' => $role, 'is_active' => false,
            'division_id' => $district->division_id, 'district_id' => $district->id,
            'latitude' => $district->latitude, 'longitude' => $district->longitude,
        ]);
        \App\Services\FarmDirectNotifier::role('admin', ucfirst($role) . ' account request', $data['name'] . ' requested a ' . $role . ' account.', '/admin/users');

        return redirect()->route('login')->with('status', ucfirst($role) . ' account request submitted. An administrator must approve it before you can log in.');
    }

    public function storeClient(Request $request): RedirectResponse
    {
        abort_unless(Shop::setting('allow_registration', '1') === '1', 403, 'Registration is currently unavailable.');
        $this->prepareNid($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'nid_number' => ['required', 'regex:/^(?:[0-9]{10}|[0-9]{13}|[0-9]{17})$/'],
            'nid_hash' => ['required', 'unique:users,nid_hash'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'district' => ['required', 'exists:districts,name'],
            'upazila' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $district = District::where('name', $data['district'])->firstOrFail();

        $user = User::create([
            'name' => $data['name'], 'email' => $data['email'] ?? null, 'nid_hash' => $data['nid_hash'], 'password' => $data['password'], 'phone' => $data['phone'], 'is_active' => true,
            'address' => $data['address'], 'upazila' => $data['upazila'], 'role' => 'client', 'division_id' => $district->division_id, 'district_id' => $district->id,
            'latitude' => $district->latitude, 'longitude' => $district->longitude,
        ]);
        DB::table('user_verifications')->insert(['user_id' => $user->id, 'status' => 'not_submitted', 'created_at' => now(), 'updated_at' => now()]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('client.dashboard')->with('status', 'Your account is ready. Verify your account before placing an order.');
    }

    public function createFarmer()
    {
        abort_unless(Shop::setting('allow_registration', '1') === '1', 403, 'Registration is currently unavailable.');

        return view('pages.register-farmer', [
            'districts' => District::orderBy('name')->get(['id', 'name']),
            'categories' => Category::where('is_active', true)->orderBy('name')->pluck('name')->all(),
        ]);
    }

    public function storeFarmer(Request $request): RedirectResponse
    {
        abort_unless(Shop::setting('allow_registration', '1') === '1', 403, 'Registration is currently unavailable.');
        $this->prepareNid($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'nid_number' => ['required', 'regex:/^(?:[0-9]{10}|[0-9]{13}|[0-9]{17})$/'],
            'nid_hash' => ['required', 'unique:users,nid_hash'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'farm' => ['required', 'string', 'max:255'],
            'district' => ['required', 'exists:districts,name'],
            'upazila' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $district = District::where('name', $data['district'])->firstOrFail();

        // The account can sign in only to complete identity verification; its dashboard remains gated.
        $user = DB::transaction(function () use ($data, $district) {
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'] ?? null, 'nid_hash' => $data['nid_hash'], 'password' => $data['password'], 'phone' => $data['phone'],
                'address' => $data['address'], 'upazila' => $data['upazila'], 'role' => 'farmer', 'is_active' => true, 'division_id' => $district->division_id,
                'district_id' => $district->id, 'latitude' => $district->latitude, 'longitude' => $district->longitude,
            ]);

            Farmer::create([
                'user_id' => $user->id, 'farm_name' => $data['farm'], 'division_id' => $district->division_id, 'district_id' => $district->id,
                'upazila' => $data['upazila'], 'address' => $data['address'], 'latitude' => $district->latitude, 'longitude' => $district->longitude,
                'status' => 'pending',
            ]);
            DB::table('user_verifications')->insert(['user_id' => $user->id, 'status' => 'not_submitted', 'created_at' => now(), 'updated_at' => now()]);
            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('farmer.dashboard')->with('status', 'Your account is ready. Verify your account before adding products.');
    }

    private function prepareNid(Request $request): void
    {
        $normalized = User::normalizeNid((string) $request->input('nid_number'));
        $request->merge(['nid_number' => $normalized, 'nid_hash' => User::nidHashFor($normalized)]);
    }
}
