<div class="layout-side" style="grid-template-columns:300px 1fr">
    <div class="card stack gap-8" style="align-items:center;text-align:center">
        @if ($user['photo'])<img class="avatar avatar-lg" src="{{ asset('images/' . $user['photo']) }}" alt="{{ $user['name'] }}">@else<span class="avatar avatar-lg"></span>@endif
        <h3 style="font-size:18px">{{ $user['name'] }}</h3>
        <span class="muted">{{ $user['email'] }}</span>
        <span class="badge badge-green">{{ $user['role'] }}</span>
        <button class="btn btn-outline btn-sm demo-action" data-toast="Photo upload comes with the backend">Change Photo</button>
    </div>
    <div class="stack gap-20">
        <form class="card demo-form" data-persist data-toast="Profile saved" method="POST" action="#">
            @csrf
            <h3 class="mb-16">Personal Information</h3>
            <div class="form-row">
                <div class="form-group"><label>Full Name</label><div class="input-icon"><i class="bi bi-person"></i><input name="name" value="{{ $user['name'] }}"></div></div>
                <div class="form-group"><label>Phone Number</label><div class="input-icon"><i class="bi bi-telephone"></i><input name="phone" value="{{ $user['phone'] }}"></div></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Email Address</label><div class="input-icon"><i class="bi bi-envelope"></i><input name="email" type="email" value="{{ $user['email'] }}"></div></div>
                <div class="form-group"><label>Role</label><input value="{{ $user['role'] }}" disabled></div>
            </div>
            <div class="form-group"><label>Address</label><div class="input-icon"><i class="bi bi-geo-alt"></i><input name="address" value="{{ $user['address'] }}"></div></div>
            <button class="btn" type="submit">Save Changes</button>
        </form>
        <form class="card demo-form" data-toast="Password updated" method="POST" action="#">
            @csrf
            <h3 class="mb-16">Change Password</h3>
            <div class="form-group"><label>Current Password</label><div class="input-icon"><i class="bi bi-lock"></i><input name="current_password" type="password" required></div></div>
            <div class="form-row">
                <div class="form-group"><label>New Password</label><div class="input-icon"><i class="bi bi-lock"></i><input name="password" type="password" required></div></div>
                <div class="form-group"><label>Confirm New Password</label><div class="input-icon"><i class="bi bi-lock"></i><input name="password_confirmation" type="password" required></div></div>
            </div>
            <button class="btn" type="submit">Update Password</button>
        </form>
    </div>
</div>
