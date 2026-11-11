@extends('layouts.public')
@section('title', 'Contact Us')
@section('content')
@include('partials.page-header', ['title' => 'Contact Us', 'subtitle' => "We'd love to hear from you"])
<div class="container section">
    <div class="grid grid-2 keep" style="grid-template-columns:420px 1fr;align-items:start">
        <div class="stack gap-12">
            @foreach ([['Address', 'House 12, Road 5, Dhaka, Bangladesh', 'geo-alt'], ['Phone', '+880 1XXX-XXXXXX', 'telephone'], ['Email', 'support@farmdirect.com', 'envelope'], ['Working Hours', 'Sat – Thu, 9:00 AM – 8:00 PM', 'clock']] as [$t, $d, $ic])
                <div class="card row" style="justify-content:flex-start;flex-wrap:nowrap;gap:14px;padding:18px"><span class="icon-circle"><i class="bi bi-{{ $ic }}"></i></span><div><div class="bold">{{ $t }}</div><div class="muted">{{ $d }}</div></div></div>
            @endforeach
        </div>
        <form class="card" method="POST" action="{{ route('contact.store') }}">
            @csrf
            @include('partials.form-errors')
            <h2 style="font-size:22px;margin-bottom:6px">Send us a message</h2>
            <p class="muted mb-16">Have a question? Fill the form and we'll reply soon.</p>
            <div class="form-row">
                <div class="form-group"><label for="name">Full Name</label><div class="input-icon"><i class="bi bi-person"></i><input id="name" name="name" value="{{ old('name') }}" required placeholder="Your name"></div></div>
                <div class="form-group"><label for="email">Email Address</label><div class="input-icon"><i class="bi bi-envelope"></i><input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder="you@example.com"></div></div>
            </div>
            <div class="form-group"><label for="subject">Subject</label><div class="input-icon"><i class="bi bi-chat-left-text"></i><input id="subject" name="subject" value="{{ old('subject') }}" required placeholder="How can we help?"></div></div>
            <div class="form-group"><label for="message">Message</label><textarea id="message" name="message" required placeholder="Write your message here...">{{ old('message') }}</textarea></div>
            <button class="btn btn-block" type="submit">Send Message</button>
        </form>
    </div>
</div>
@endsection
