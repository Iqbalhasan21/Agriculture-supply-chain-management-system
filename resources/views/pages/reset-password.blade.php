@extends('layouts.public')
@section('title', 'Choose a New Password')
@section('content')
<div class="container section"><div class="card" style="max-width:520px;margin:0 auto">
    <h1 style="font-size:26px">Choose a new password</h1>
    @include('partials.form-errors')
    <form method="POST" action="{{ route('password.update') }}">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-group"><label for="email">Email Address</label><input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email"></div>
        <div class="form-group"><label for="password">New Password</label><input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"></div>
        <div class="form-group"><label for="password_confirmation">Confirm New Password</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"></div>
        <button class="btn btn-block" type="submit">Reset Password</button>
    </form>
</div></div>
@endsection