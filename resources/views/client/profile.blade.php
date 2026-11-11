@extends('layouts.dashboard', ['role' => 'client'])
@section('title', 'My Profile')
@section('content')
<div class="layout-side" style="grid-template-columns:300px 1fr">
    <div class="card stack gap-8" style="align-items:center;text-align:center">
        @include('partials.profile-picture', ['photo' => $me['photo'], 'name' => $me['name'], 'photoInputId' => 'client-profile-photo'])
        <h3 style="font-size:18px">{{ $me['name'] }}</h3><span class="muted">{{ $me['email'] }}</span><span class="badge badge-green">Client</span>
        @if ($verificationStatus !== 'verified')<span class="badge badge-pending">{{ ucfirst(str_replace('_', ' ', $verificationStatus)) }}</span>@else<span class="badge badge-green">Verified</span>@endif
        <a class="btn btn-outline btn-sm" href="{{ route('account.verification') }}"><i class="bi bi-shield-check"></i> Account Verification</a>
    </div>
    <div class="stack gap-20">
        <form class="card" method="POST" enctype="multipart/form-data" action="{{ route('client.profile.update') }}">
            @csrf
            <h3 class="mb-16">Personal Information</h3>
            @include('partials.profile-photo-upload', ['photoInputId' => 'client-profile-photo'])
            <div class="form-row">
                <div class="form-group"><label for="p-name">Full Name</label><div class="input-icon"><i class="bi bi-person"></i><input id="p-name" name="name" value="{{ old('name', $me['name']) }}" required></div></div>
                <div class="form-group"><label for="p-phone">Phone Number</label><div class="input-icon"><i class="bi bi-telephone"></i><input id="p-phone" name="phone" value="{{ old('phone', $me['phone']) }}" required></div></div>
            </div>
            <div class="form-group"><label for="p-email">Email Address</label><div class="input-icon"><i class="bi bi-envelope"></i><input id="p-email" name="email" type="email" value="{{ old('email', $me['email']) }}"></div></div>
            <div class="form-row">
                <div class="form-group"><label for="district">District</label>@include('partials.district-select', ['districts' => $districts, 'selected' => old('district', $me['district']), 'required' => true])</div>
                <div class="form-group"><label for="p-upazila">Upazila</label><input id="p-upazila" name="upazila" value="{{ old('upazila', $me['upazila']) }}" required maxlength="80"></div>
            </div>
            <div class="form-group"><label for="p-address">Delivery Address</label><div class="input-icon"><i class="bi bi-geo-alt"></i><input id="p-address" name="address" value="{{ old('address', $me['address']) }}" required></div></div>
            <button class="btn" type="submit">Save Changes</button>
        </form>
        <form class="card" method="POST" action="{{ route('client.password.update') }}">
            @csrf
            <h3 class="mb-16">Change Password</h3>
            <div class="form-group"><label for="cp">Current Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="cp" name="current_password" type="password" required></div></div>
            <div class="form-row">
                <div class="form-group"><label for="np">New Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="np" name="password" type="password" minlength="8" required></div></div>
                <div class="form-group"><label for="npc">Confirm New Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="npc" name="password_confirmation" type="password" required></div></div>
            </div>
            <button class="btn" type="submit">Update Password</button>
        </form>
    </div>
</div>
@endsection
