@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'My Profile')
@section('content')
<div class="stack gap-20">
	<form class="card" method="POST" enctype="multipart/form-data" action="{{ route('admin.profile.save') }}">@csrf
		<h3 class="mb-16">Personal Information</h3>
		<div class="admin-profile-picture">@include('partials.profile-picture', ['photo' => $user->photo, 'name' => $user->name, 'photoInputId' => 'admin-profile-photo'])</div>
		@include('partials.profile-photo-upload', ['photoInputId' => 'admin-profile-photo'])
		<div class="form-row"><div class="form-group"><label for="name">Full Name</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required></div><div class="form-group"><label for="phone">Phone</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}"></div></div>
		<div class="form-row"><div class="form-group"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"></div><div class="form-group"><label>Role</label><input value="Administrator" disabled></div></div>
		<div class="form-group"><label for="address">Address</label><input id="address" name="address" value="{{ old('address', $user->address) }}" maxlength="255"></div>
		<button class="btn" type="submit">Save Profile</button>
	</form>
	<form class="card" method="POST" action="{{ route('admin.password.save') }}">@csrf
		<h3 class="mb-16">Change Password</h3>
		<div class="form-group"><label for="current_password">Current Password</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password"></div>
		<div class="form-row"><div class="form-group"><label for="password">New Password</label><input id="password" name="password" type="password" required autocomplete="new-password"></div><div class="form-group"><label for="password_confirmation">Confirm New Password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div></div>
		<button class="btn" type="submit">Update Password</button>
	</form>
</div>
@endsection
