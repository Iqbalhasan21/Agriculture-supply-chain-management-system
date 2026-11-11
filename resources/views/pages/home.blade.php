@extends('layouts.public')
@section('title', 'Fresh From Farmers')
@section('content')
<section class="hero">
    <div class="container">
        <div>
            <h1>Fresh From Farmers,<br><span class="green">Direct to Your Door</span></h1>
            <p>Support local farmers and get the freshest products delivered to your home.</p>
            <div class="row gap-12" style="justify-content:flex-start">
                <a class="btn" href="{{ route('products') }}">Shop Now</a>
                <a class="btn btn-outline" href="{{ route('signup', ['type' => 'farmer']) }}">Become a Farmer</a>
            </div>
        </div>
        <img class="hero-photo" src="{{ asset('images/hero.jpg') }}" alt="Farmer carrying rice seedlings across a green field">
    </div>
</section>

<div class="container section">
    <div class="card strip">
        @foreach ([['Fresh & Organic', '100% Natural', 'flower1'], ['Support Farmers', 'Empower Local', 'people'], ['Safe Delivery', 'Fast & Reliable', 'truck'], ['Best Price', 'Save More', 'tag']] as [$t, $s, $ic])
            <div class="feature"><span class="ic"><i class="bi bi-{{ $ic }}"></i></span><div><div class="bold">{{ $t }}</div><div class="muted">{{ $s }}</div></div></div>
        @endforeach
    </div>
</div>

<div class="container section">
    <h2 class="section-title">Shop by Category</h2>
    <div class="grid grid-6" style="grid-template-columns:repeat(auto-fit,minmax(120px,1fr))">
        @php $colors = ['#F4D9D4', '#FBE7C2', '#EFE3C8', '#E4EEF5', '#DDE7EC', '#F6E2C4']; $emojis = ['🥬', '🍎', '🌾', '🥛', '🐟', '🥚']; @endphp
        @foreach ($categories as $i => $c)
            <a href="{{ route('products', ['category' => $c]) }}" class="stack center gap-8" style="align-items:center">
                @php $ci = \App\Services\Shop::categoryImage($c); @endphp
                @if ($ci)<img class="cat-img" src="{{ asset('images/' . $ci) }}" alt="">@else<div class="ph ph-round" style="background:{{ $colors[$i % 6] }};width:84px">{{ $emojis[$i % 6] }}</div>@endif
                <span class="bold">{{ $c }}</span>
            </a>
        @endforeach
    </div>
</div>

<div class="container section">
    <div class="row mb-16"><h2 class="section-title" style="margin:0">Featured Products</h2><a class="green bold" href="{{ route('products') }}">View All</a></div>
    <div class="grid grid-6">
        @foreach ($products as $p) @include('partials.product-card', ['p' => $p]) @endforeach
    </div>
</div>

<div class="container section">
    <div class="card row" style="padding:28px">
        <div>
            <h2>Buy from farmers in your own district</h2>
            <p class="muted" style="margin:6px 0 0">Pick a district on the map and see which farmers grow near you.</p>
        </div>
        <a class="btn" href="{{ route('districts') }}">Shop by District</a>
    </div>
</div>

@include('partials.cta-farmer')

<section class="container section" id="faq">
    <div class="faq-heading">
        <span class="faq-eyebrow"><i class="bi bi-question-circle"></i> NEED A HAND?</span>
        <h2>Questions? We’re here to help.</h2>
        <p>Quick answers to help you shop fresh and connect with local farmers.</p>
    </div>
    <div class="faq-layout">
        <div class="faq-list">
            @foreach ([
                ['basket2', 'How do I place an order?', 'Browse fresh products, add your picks to the cart, then sign in as a client to complete checkout. You can follow your order from your dashboard.'],
                ['chat-dots', 'Can I speak directly with a farmer?', 'Yes. Visit a farmer’s profile and tap Message Farmer. Your conversation stays in your Messages inbox so you can come back any time.'],
                ['headset', 'How do I get help with an order?', 'Sign in and open Messages to start a support chat. A FarmDirect manager or admin will reply in the same conversation.'],
                ['shop', 'How can I sell my farm products?', 'Choose Become a Farmer and submit your farm details. Our team will review your application and let you know when your shop is ready.'],
            ] as [$icon, $question, $answer])
                <details class="faq-item">
                    <summary><span class="faq-question-icon"><i class="bi bi-{{ $icon }}"></i></span><span>{{ $question }}</span><i class="bi bi-plus-lg faq-toggle"></i></summary>
                    <div class="faq-answer"><p>{{ $answer }}</p></div>
                </details>
            @endforeach
        </div>
        <aside class="faq-contact-card">
            <span class="faq-contact-icon"><i class="bi bi-chat-heart"></i></span>
            <span class="faq-eyebrow">WE’RE JUST A MESSAGE AWAY</span>
            <h3>Still have a question?</h3>
            <p>{{ auth()->check() && auth()->user()->role === 'farmer' ? 'Talk with our support team and get help with your shop, products or orders.' : 'Talk with our friendly support team and get help with your order.' }}</p>
            @auth
                @if (in_array(auth()->user()->role, ['client', 'farmer'], true))
                    <a class="btn" href="{{ route('messages.index') }}">Message Support <i class="bi bi-arrow-up-right"></i></a>
                @else
                    <a class="btn" href="{{ route('messages.index') }}">Open Messages <i class="bi bi-arrow-up-right"></i></a>
                @endif
            @else
                <a class="btn" href="{{ route('login') }}">Sign in to chat <i class="bi bi-arrow-up-right"></i></a>
            @endauth
            <div class="faq-contact-note"><i class="bi bi-clock"></i><span>We usually reply as soon as possible during support hours.</span></div>
        </aside>
    </div>
</section>
@endsection
