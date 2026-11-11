@extends('layouts.dashboard', ['role' => 'farmer'])
@section('title', 'My Profile')
@section('content')
<div class="layout-side" style="grid-template-columns:300px 1fr">
    <div class="card stack gap-8" style="align-items:center;text-align:center">
        @include('partials.profile-picture', ['photo' => $farm['photo'], 'name' => $farm['name'], 'photoInputId' => 'farmer-profile-photo'])
        <h3 style="font-size:18px">{{ $farm['name'] }}</h3><span class="muted">{{ $farm['email'] }}</span>
        <span class="badge badge-green">Farmer</span>
        @if ($verificationStatus !== 'verified')<span class="badge badge-pending">{{ ucfirst(str_replace('_', ' ', $verificationStatus)) }}</span>@else<span class="badge badge-green">Verified</span>@endif
        <a class="btn btn-outline btn-sm" href="{{ route('account.verification') }}"><i class="bi bi-shield-check"></i> Account Verification</a>
        <span class="muted"><i class="bi bi-geo-alt"></i> {{ $farm['district'] ?? 'Location not set' }}</span>
        <a class="btn btn-outline btn-sm" href="{{ route('farmer.show', $farm['id']) }}">View public profile</a>
    </div>
    <div class="stack gap-20">
        <form class="card" method="POST" enctype="multipart/form-data" action="{{ route('farmer.profile.save') }}">
            @csrf
            <h3 class="mb-16">Farmer Information</h3>
            @include('partials.profile-photo-upload', ['photoInputId' => 'farmer-profile-photo'])
            <div class="form-row">
                <div class="form-group"><label for="p-name">Full Name</label><div class="input-icon"><i class="bi bi-person"></i><input id="p-name" name="name" value="{{ old('name', $farm['name']) }}" required></div></div>
                <div class="form-group"><label for="p-phone">Phone Number</label><div class="input-icon"><i class="bi bi-telephone"></i><input id="p-phone" name="phone" value="{{ old('phone', $farm['phone']) }}" required></div></div>
            </div>
            <div class="form-group"><label for="p-email">Email Address</label><div class="input-icon"><i class="bi bi-envelope"></i><input id="p-email" name="email" type="email" value="{{ old('email', $farm['email']) }}"></div></div>
            <div class="form-group"><label for="p-farm">Farm Name</label><div class="input-icon"><i class="bi bi-flower2"></i><input id="p-farm" name="farm_name" value="{{ old('farm_name', $farm['farm_name']) }}" required></div></div>
            <div class="form-group"><label for="p-address">Farm Address</label><input id="p-address" name="address" value="{{ old('address', $farm['address'] ?? '') }}" maxlength="255"></div>
            <div class="form-group"><label for="p-upazila">Upazila</label><input id="p-upazila" name="upazila" value="{{ old('upazila', $farm['upazila']) }}" required maxlength="80"></div>
            <div class="form-group"><label for="p-about">About your farm (shown on your public profile)</label><textarea id="p-about" name="about">{{ old('about', $farm['about']) }}</textarea></div>
            <button class="btn" type="submit">Save Changes</button>
        </form>
        <form class="card" method="POST" action="{{ route('farmer.password.save') }}">
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
