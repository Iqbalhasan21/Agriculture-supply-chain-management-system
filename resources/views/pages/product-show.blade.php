@extends('layouts.public')
@section('title', $p['name'])
@section('content')
<div class="container section">
    <p class="muted">Home › {{ $p['category'] }} › {{ $p['name'] }}</p>
    <div class="grid grid-2 keep mt-16" style="gap:48px;align-items:start">
        <div>
            <div class="ph {{ $p['image'] ? 'photo' : '' }}" style="background:{{ $p['color'] }};font-size:120px;aspect-ratio:4/3;border-radius:16px">
                @if ($p['image'])<img src="{{ asset('images/' . $p['image']) }}" alt="{{ $p['name'] }}">@else{{ $p['emoji'] }}@endif
            </div>
            @if (count($p['gallery']))
                <div class="thumbs">@foreach ($p['gallery'] as $g)<img src="{{ asset('images/' . $g) }}" alt="{{ $p['name'] }} photo {{ $loop->iteration }}">@endforeach</div>
            @endif
        </div>
        <div class="stack gap-12">
            <h1 style="font-size:30px">{{ $p['name'] }}</h1>
            <div><span class="stars">★★★★★</span> <span class="muted">{{ $p['rating'] }} ({{ $p['reviews'] }} Reviews)</span></div>
            <div><span class="price" style="font-size:32px">৳ {{ number_format($p['price']) }}</span> <span class="muted">/ {{ $p['unit'] }}</span></div>
            <p class="muted" style="margin:0">{{ $p['description'] ?: 'Farm fresh, naturally grown without harmful chemicals. Perfect for cooking and everyday meals.' }}</p>
            <div class="stack gap-8">
                <div><span class="muted">Category:</span> <b>{{ $p['category'] }}</b></div>
                <div><span class="muted">Farmer:</span> <a class="green bold" href="{{ route('farmer.show', $p['farmer_id']) }}">{{ $p['farmer'] }}</a></div>
                <div><span class="muted">Farm location:</span> <b><i class="bi bi-geo-alt"></i> {{ $p['district'] }}, Bangladesh</b></div>
                <div><span class="muted">Availability:</span> @if ($p['stock'] > 0)<b class="green">In Stock ({{ $p['stock'] }})</b>@else<b style="color:var(--r)">Out of stock</b>@endif</div>
            </div>
            @if ($p['stock'] > 0)
            <div class="row" style="justify-content:flex-start;gap:16px">
                <span class="muted">Quantity:</span>
                <div class="qty"><button type="button" data-qty="-1" aria-label="Decrease">−</button><input id="detail-qty" type="text" value="1" aria-label="Quantity" readonly><button type="button" data-qty="1" aria-label="Increase">+</button></div>
            </div>
            @php $cartAttrs = 'data-add-cart data-qty-input="#detail-qty" data-cart-url="' . route('cart') . '" data-id="' . $p['id'] . '" data-name="' . e($p['name']) . '" data-price="' . $p['price'] . '" data-unit="' . e($p['unit']) . '" data-image="' . ($p['image'] ? asset('images/' . $p['image']) : '') . '" data-emoji="' . $p['emoji'] . '" data-district="' . e($p['district']) . '" data-stock="' . $p['stock'] . '"'; @endphp
            <button type="button" class="btn" {!! $cartAttrs !!}><i class="bi bi-cart-plus"></i> Add to Cart</button>
            <button type="button" class="btn btn-outline" {!! $cartAttrs !!} data-buy-now>Buy Now</button>
            @endif
        </div>
    </div>

    <div class="card row mt-24" style="padding:18px">
        <div class="row gap-16" style="justify-content:flex-start">
            <img class="avatar avatar-md" src="{{ asset('images/' . $p['farmer_photo']) }}" alt="{{ $p['farmer'] }}">
            <div><div class="bold">{{ $p['farmer'] }}</div><div class="green">✓ Verified Farmer · {{ $p['district'] }}</div></div>
        </div>
        <a class="btn btn-outline" href="{{ route('farmer.show', $p['farmer_id']) }}">View Profile</a>
    </div>

    <div class="grid grid-2 keep mt-24">
        <div class="card"><h3>Product Details</h3><p class="muted">These products are 100% fresh and hand picked from our farm. We do not use any harmful chemicals.</p></div>
        @php $totalReviews = array_sum($breakdown); @endphp
        <div class="card">
            <div class="row">
                <h3>Reviews ({{ $p['reviews'] }})</h3>
                @if ($canReview)<a class="green bold" href="{{ route('review.create', ['product' => $p['id']]) }}">Write a review</a>@endif
            </div>
            <div class="row gap-16 mt-16" style="justify-content:flex-start;flex-wrap:nowrap">
                <div><div style="font-size:42px;font-weight:700">{{ number_format($p['rating'], 1) }}</div><div class="stars">★★★★★</div></div>
                <div class="stack gap-8" style="flex:1">
                    @foreach ($breakdown as $s => $n)
                        <div class="row" style="flex-wrap:nowrap"><span class="muted" style="width:28px">{{ $s }} ★</span><div class="bar" style="flex:1"><i style="width:{{ $totalReviews ? round($n / $totalReviews * 100) : 0 }}%"></i></div><span class="muted" style="width:28px">{{ $n }}</span></div>
                    @endforeach
                </div>
            </div>
            <hr class="divider">
            @forelse ($reviews as $r)
                <div class="mb-16"><div class="row"><b>{{ $r['name'] }}</b><span class="muted">{{ $r['date'] }}</span></div><div class="stars">{{ str_repeat('★', $r['rating']) }}{{ str_repeat('☆', 5 - $r['rating']) }}</div>@if ($r['title'])<b>{{ $r['title'] }}</b>@endif<p style="margin:4px 0 0" class="muted">{{ $r['body'] }}</p></div>
            @empty
                <p class="muted">No reviews yet. Buy this product and write the first one after it is delivered.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
