@extends('layouts.public')
@section('title', 'Login')
@section('content')
<div class="container">
    <div class="auth">
        <div class="auth-panel">
            <h1>Welcome to FarmDirect</h1>
            <p>Login as Client, Farmer, Manager or Admin to open your dashboard.</p>
            @foreach ([['Fresh from the farm', 'Order directly from local farmers.'], ['Manage with ease', 'Inventory, orders and deliveries in one place.'], ['Secure access', 'Role based access for every user.']] as [$t, $d])
                <div class="benefit"><span class="tick"><i class="bi bi-check-lg"></i></span><div><b>{{ $t }}</b><small>{{ $d }}</small></div></div>
            @endforeach
        </div>
        <form class="card" method="POST" action="{{ route('login.attempt') }}" style="padding:36px">
            @csrf
            <h2 style="font-size:26px;margin-bottom:6px">Welcome back</h2>
            <p class="muted mb-16">Login to continue to your account.</p>
            @include('partials.form-errors')
            <div class="role-tabs" style="grid-template-columns:repeat(4,1fr)">
                @foreach (['Client', 'Farmer', 'Manager', 'Admin'] as $role)
                    <label><input type="radio" name="role" value="{{ strtolower($role) }}" @checked(old('role', 'client') === strtolower($role))><span>{{ $role }}</span></label>
                @endforeach
            </div>
            <div class="form-group"><label for="identifier">NID Number or Email</label><div class="input-icon"><i class="bi bi-person-vcard"></i><input id="identifier" name="identifier" value="{{ old('identifier') }}" required autofocus autocomplete="username"></div></div>
            <div class="form-group"><label for="password">Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="password" name="password" type="password" required placeholder="••••••••"></div></div>
            <div class="row form-group"><label class="check"><input type="checkbox" name="remember"> Remember me</label><a class="green bold" href="{{ route('password.request') }}">Forgot Password?</a></div>
            <button class="btn btn-block" type="submit">Login</button>
            <p class="center muted mt-16">Don't have an account? <a class="green bold" href="{{ route('signup') }}">Sign Up</a></p>
        </form>
    </div>
</div>
@endsection
