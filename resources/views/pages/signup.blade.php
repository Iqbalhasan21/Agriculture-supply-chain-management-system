@extends('layouts.public')
@section('title', 'Sign Up')
@section('content')
<div class="container">
    <div class="auth">
        <div class="auth-panel">
            <h1>Join FarmDirect</h1>
            <p>One place to request a Client, Farmer, Manager or Admin account.</p>
            @foreach ([['Fresh & organic', 'Directly from verified farmers.'], ['Buy near you', 'See farmers in your own district first.'], ['Track your order', 'Know where your food is.']] as [$t, $d])
                <div class="benefit"><span class="tick"><i class="bi bi-check-lg"></i></span><div><b>{{ $t }}</b><small>{{ $d }}</small></div></div>
            @endforeach
        </div>
        <form class="card" method="POST" action="{{ route('signup.store') }}" style="padding:34px" data-signup-form>
            @csrf
            <h2 style="font-size:26px;margin-bottom:6px">Create your account</h2>
            <p class="muted mb-16">Fields marked with <span class="required-indicator" aria-hidden="true"><i class="bi bi-star-fill"></i></span> are required.</p>
            @include('partials.form-errors')
            <div class="form-group">
                <label for="account_type">I want to</label>
                <select id="account_type" name="account_type" data-account-type-select required>
                    <option value="client" @selected(old('account_type', $accountType) === 'client')>Buy products as a Client</option>
                    <option value="farmer" @selected(old('account_type', $accountType) === 'farmer')>Sell products as a Farmer</option>
                    <option value="manager" @selected(old('account_type', $accountType) === 'manager')>Request a Manager account</option>
                    <option value="admin" @selected(old('account_type', $accountType) === 'admin')>Request an Admin account</option>
                </select>
            </div>
            <p class="signup-approval-note" data-staff-only hidden>Manager and Admin accounts stay inactive until an existing administrator approves the request.</p>
            <div class="form-row">
                <div class="form-group"><label for="name">Full Name</label><div class="input-icon"><i class="bi bi-person"></i><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name"></div></div>
                <div class="form-group"><label for="phone">Phone Number</label><div class="input-icon"><i class="bi bi-telephone"></i><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required placeholder="+880 1XXX-XXXXXX" autocomplete="tel"></div></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="nid_number">NID Number</label><div class="input-icon"><i class="bi bi-person-vcard"></i><input id="nid_number" name="nid_number" inputmode="numeric" value="{{ old('nid_number') }}" required autocomplete="off"></div><small>Required for account verification (10, 13 or 17 digits).</small></div>
                <div class="form-group"><label for="email">Email Address (optional)</label><div class="input-icon"><i class="bi bi-envelope"></i><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email"></div></div>
            </div>
            <div class="form-group" data-farmer-only hidden><label for="farm">Farm Name</label><div class="input-icon"><i class="bi bi-flower2"></i><input id="farm" name="farm" value="{{ old('farm') }}" required disabled placeholder="e.g. Green Valley Farm"></div></div>
            <div class="form-row">
                <div class="form-group"><label for="district">District</label>@include('partials.district-select', ['districts' => $districts, 'selected' => old('district'), 'required' => true])</div>
                <div class="form-group"><label for="upazila">Upazila</label><div class="input-icon"><i class="bi bi-geo-alt"></i><input id="upazila" name="upazila" value="{{ old('upazila') }}" required maxlength="80"></div></div>
            </div>
            <div class="form-group"><label for="address">Address</label><div class="input-icon"><i class="bi bi-geo-alt"></i><input id="address" name="address" value="{{ old('address') }}" required placeholder="Village, road, area"></div></div>
            <div class="form-row">
                <div class="form-group"><label for="password">Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"></div></div>
                <div class="form-group"><label for="password_confirmation">Confirm Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div></div>
            </div>
            <button class="btn btn-block" type="submit">Sign Up</button>
            <p class="center muted mt-16">Already have an account? <a class="green bold" href="{{ route('login') }}">Login</a></p>
        </form>
    </div>
</div>
@endsection
