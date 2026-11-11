@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'My Profile')
@section('content')
<div class="layout-side" style="grid-template-columns:300px 1fr">
    <div class="card stack gap-8" style="align-items:center;text-align:center">
        @include('partials.profile-picture', ['photo' => $me['photo'], 'name' => $me['name'], 'photoInputId' => 'manager-profile-photo'])
        <h3 style="font-size:18px">{{ $me['name'] }}</h3><span class="muted">{{ $me['email'] }}</span><span class="badge badge-green">Manager</span>
    </div>
    <div class="stack gap-20">
        <form class="card" method="POST" enctype="multipart/form-data" action="{{ route('manager.profile.save') }}">@csrf
            <h3 class="mb-16">Personal Information</h3>
            @include('partials.profile-photo-upload', ['photoInputId' => 'manager-profile-photo'])
            <div class="form-row">
                <div class="form-group"><label for="p-name">Full Name</label><div class="input-icon"><i class="bi bi-person"></i><input id="p-name" name="name" value="{{ old('name', $me['name']) }}" required></div></div>
                <div class="form-group"><label for="p-phone">Phone Number</label><div class="input-icon"><i class="bi bi-telephone"></i><input id="p-phone" name="phone" value="{{ old('phone', $me['phone']) }}" required></div></div>
            </div>
            <div class="form-group"><label for="p-email">Email Address</label><div class="input-icon"><i class="bi bi-envelope"></i><input id="p-email" name="email" type="email" value="{{ old('email', $me['email']) }}"></div></div>
            <div class="form-group"><label for="p-address">Address</label><input id="p-address" name="address" value="{{ old('address', $me['address'] ?? '') }}" maxlength="255"></div>
            <button class="btn" type="submit">Save Changes</button>
        </form>
        <form class="card" method="POST" action="{{ route('manager.password.save') }}">@csrf
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
