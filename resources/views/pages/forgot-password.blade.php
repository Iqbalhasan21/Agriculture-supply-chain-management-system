@extends('layouts.public')
@section('title', 'Reset Password')
@section('content')
<div class="container section"><div class="card" style="max-width:520px;margin:0 auto">
    <h1 style="font-size:26px">Reset your password</h1>
    <p class="muted">Enter the email address saved on your account and we will send a reset link.</p>
    @include('partials.form-errors')
    <form method="POST" action="{{ route('password.email') }}">@csrf
        <div class="form-group"><label for="email">Email Address</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"></div>
        <button class="btn btn-block" type="submit">Send Reset Link</button>
    </form>
    <p class="muted mt-16">Accounts created without an email address cannot use email recovery. <a class="green bold" href="{{ route('contact') }}">Contact support</a> for identity verification.</p>
</div></div>
@endsection