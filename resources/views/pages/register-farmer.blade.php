@extends('layouts.public')
@section('title', 'Register as Farmer')
@section('content')
<div class="container">
    <div class="auth">
        <div class="auth-panel">
            <h1>Become a FarmDirect Farmer</h1>
            <p>Sell your products directly to customers and get a fair price for your hard work.</p>
            @foreach ([['Reach more customers', 'Clients in your own district find you first.'], ['Fair & fast payment', 'Earn the full value of your harvest.'], ['Easy product management', 'Managers handle stock, orders and delivery.']] as [$t, $d])
                <div class="benefit"><span class="tick"><i class="bi bi-check-lg"></i></span><div><b>{{ $t }}</b><small>{{ $d }}</small></div></div>
            @endforeach
        </div>
        <form class="card" method="POST" action="{{ route('register.farmer.store') }}">
            @csrf
            <h2 style="font-size:24px;margin-bottom:6px">Create your farmer account</h2>
            <p class="muted mb-16">Your account will be reviewed and approved by our team.</p>
            @include('partials.form-errors')
            <div class="form-row">
                <div class="form-group"><label for="name">Full Name</label><div class="input-icon"><i class="bi bi-person"></i><input id="name" name="name" value="{{ old('name') }}" required></div></div>
                <div class="form-group"><label for="phone">Phone Number</label><div class="input-icon"><i class="bi bi-telephone"></i><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required placeholder="+880 1XXX-XXXXXX"></div></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="nid_number">NID Number</label><div class="input-icon"><i class="bi bi-person-vcard"></i><input id="nid_number" name="nid_number" inputmode="numeric" value="{{ old('nid_number') }}" required></div></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="email">Email Address (optional)</label><div class="input-icon"><i class="bi bi-envelope"></i><input id="email" name="email" type="email" value="{{ old('email') }}"></div></div>
                <div class="form-group"><label for="farm">Farm Name</label><div class="input-icon"><i class="bi bi-flower2"></i><input id="farm" name="farm" value="{{ old('farm') }}" required placeholder="e.g. Green Valley Farm"></div></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="district">District (farm location)</label>@include('partials.district-select', ['districts' => $districts, 'selected' => old('district'), 'required' => true])</div>
                <div class="form-group"><label for="upazila">Upazila</label><div class="input-icon"><i class="bi bi-geo-alt"></i><input id="upazila" name="upazila" value="{{ old('upazila') }}" required></div></div>
            </div>
            <div class="form-group"><label for="address">Village / Address</label><div class="input-icon"><i class="bi bi-geo-alt"></i><input id="address" name="address" value="{{ old('address') }}" required></div></div>
            <div class="form-group"><label for="type">What do you grow or sell?</label>
                <select id="type" name="type">@foreach ($categories as $c)<option @selected(old('type') === $c)>{{ $c }}</option>@endforeach</select>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="password">Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="password" name="password" type="password" required minlength="8"></div></div>
                <div class="form-group"><label for="password_confirmation">Confirm Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="password_confirmation" name="password_confirmation" type="password" required></div></div>
            </div>
            <label class="check form-group"><input type="checkbox" required> I agree to the Terms & Conditions and Privacy Policy</label>
            <button class="btn btn-block" type="submit">Register as Farmer</button>
            <p class="center muted mt-16">Already have an account? <a class="green bold" href="{{ route('login') }}">Login</a></p>
        </form>
    </div>
</div>
@endsection
