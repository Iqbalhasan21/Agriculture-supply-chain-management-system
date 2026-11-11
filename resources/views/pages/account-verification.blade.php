@extends('layouts.public')
@section('title', 'Account Verification')
@section('content')
<div class="container section">
    <div class="auth" style="max-width:1000px;margin:auto">
        <div class="auth-panel">
            <span class="badge badge-pending"><i class="bi bi-shield-lock"></i> Verification required</span>
            <h1 class="mt-16">Verify your account</h1>
            <p>To activate your {{ ucfirst(auth()->user()->role) }} account, submit your NID photos and a clear face photo. Our Admin team will call your saved phone number, check the NID, and compare your face photo with the ID.</p>
            <div class="benefit"><span class="tick"><i class="bi bi-telephone"></i></span><div><b>Phone check</b><small>An Admin will call the number on your account. SMS OTP is not available because no SMS provider is connected.</small></div></div>
            <div class="benefit"><span class="tick"><i class="bi bi-person-vcard"></i></span><div><b>NID check</b><small>We compare both sides of your ID with the NID number you entered at signup.</small></div></div>
            <div class="benefit"><span class="tick"><i class="bi bi-person-bounding-box"></i></span><div><b>Face check</b><small>An Admin manually compares your face photo with your ID photo. This is not automated facial recognition.</small></div></div>
        </div>
        <div class="card" style="padding:30px">
            @include('partials.form-errors')
            @if ($verification->status === 'verified')
                <div class="verification-result verification-result-success"><i class="bi bi-shield-check"></i><h2>Account verified</h2><p>You can now use your FarmDirect account.</p><a class="btn mt-16" href="{{ route(auth()->user()->homeRoute()) }}">Go to dashboard</a></div>
            @elseif ($verification->status === 'pending')
                <form method="POST" action="{{ route('account.verification.withdraw') }}" class="center mt-16">@csrf<button class="link-btn" type="submit">Withdraw consent and delete my submitted documents</button></form>
                <div class="verification-result"><i class="bi bi-hourglass-split"></i><h2>Documents under review</h2><p>Your phone, NID and face photo are waiting for Admin review. You can keep this page open or check again later.</p><p class="muted">Submitted {{ \Carbon\Carbon::parse($verification->submitted_at)->diffForHumans() }}</p></div>
            @else
                @if ($verification->review_note && $verification->status !== 'rejected')
                    <div class="alert alert-error">{{ $verification->review_note }}</div>
                @endif
                @if ($verification->status === 'rejected' && $verification->review_note)
                    <div class="alert alert-error"><strong>More information needed</strong><br>{{ $verification->review_note }}</div>
                @endif
                <h2 style="font-size:23px;margin-bottom:6px">Identity documents</h2>
                <p class="muted mb-16">Use clear, readable JPG, PNG or WebP images, up to 5 MB each.</p>
                <form method="POST" action="{{ route('account.verification.submit') }}" enctype="multipart/form-data" class="stack gap-16">
                    @csrf
                    <div class="form-group"><label for="verify-phone">Phone number to verify</label><input id="verify-phone" name="phone" type="tel" value="{{ old('phone', auth()->user()->phone) }}" required autocomplete="tel"><small>An Admin will call this number to confirm it.</small></div>
                    <div class="form-group"><label for="verify-nid">NID number</label><input id="verify-nid" name="nid_number" inputmode="numeric" value="{{ old('nid_number') }}" required autocomplete="off"><small>Use the NID number from your signup. Bengali and English digits are accepted.</small></div>
                    <div class="form-group"><label for="id-front">NID front photo</label><input id="id-front" name="id_front" type="file" accept="image/jpeg,image/png,image/webp" required></div>
                    <div class="form-group"><label for="id-back">NID back photo</label><input id="id-back" name="id_back" type="file" accept="image/jpeg,image/png,image/webp" required></div>
                    <div class="form-group"><label for="face-photo">Face photo</label><input id="face-photo" name="face_photo" type="file" accept="image/jpeg,image/png,image/webp" capture="user" required><small>Take a recent, well-lit photo with your face clearly visible.</small></div>
                    <label class="verification-consent"><input type="checkbox" name="consent" value="1" required><span>I specifically consent to FarmDirect storing and reviewing these NID and face photos for account verification. The files and temporary NID value are deleted after the Admin decision; the verification result and review record remain.</span></label>
                    <button class="btn" type="submit"><i class="bi bi-shield-check"></i> Submit for verification</button>
                </form>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="mt-16">@csrf<button class="btn btn-outline" type="submit">Log out</button></form>
        </div>
    </div>
</div>
@endsection
