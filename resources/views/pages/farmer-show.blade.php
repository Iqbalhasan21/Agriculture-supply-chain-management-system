@extends('layouts.public')
@section('title', $farmer['name'])
@push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
@section('content')
@php $loc = collect($districts)->firstWhere('name', $farmer['district']); @endphp
<div class="container section">
    <p class="muted">Home › Farmers › {{ $farmer['name'] }}</p>
    <div class="card mt-16" style="padding:0;overflow:hidden">
        <div style="height:150px;background:#BFDDB2"></div>
        <div class="row" style="padding:20px 32px;flex-wrap:wrap">
            <div class="row gap-16" style="justify-content:flex-start;flex-wrap:nowrap">
                <img class="avatar avatar-lg" src="{{ asset('images/' . $farmer['photo']) }}" alt="{{ $farmer['name'] }}" style="margin-top:-60px;border:4px solid #fff">
                <div><h1 style="font-size:26px">{{ $farmer['name'] }}</h1><div class="green bold">✓ Verified Farmer</div><div class="muted"><i class="bi bi-geo-alt"></i> {{ $farmer['district'] }}, Bangladesh · ★ {{ $farmer['rating'] }} ({{ $farmer['reviews'] }} Reviews)</div></div>
            </div>
            <div class="row gap-12">
                @auth
                    @if (auth()->user()->role === 'client')
                        <form method="POST" action="{{ route('farmer.follow', $farmer['id']) }}">@csrf<button class="btn {{ $isFollowing ? 'btn-outline' : '' }}" type="submit">{{ $isFollowing ? 'Unfollow' : 'Follow' }}</button></form>
                    @endif
                @else
                    <a class="btn" href="{{ route('login') }}">Login to follow</a>
                @endauth
                @auth
                    @if (auth()->user()->role === 'client')
                        <form method="POST" action="{{ route('messages.farmer', $farmer['id']) }}">@csrf<button class="btn btn-outline" type="submit"><i class="bi bi-chat-dots"></i> Message Farmer</button></form>
                    @endif
                @else
                    <a class="btn btn-outline" href="{{ route('login') }}">Login to message</a>
                @endauth
            </div>
        </div>
    </div>
    <div class="grid grid-2 keep mt-16">
        <div class="card"><h3>About {{ $farmer['name'] }}</h3><p class="muted">{{ $farmer['about'] }}</p></div>
        <div class="card" style="padding:0;overflow:hidden"><div id="district-map" style="height:180px;border:0;border-radius:0" aria-label="Farm location map"></div></div>
    </div>
    <h2 class="section-title mt-24">Products by {{ $farmer['name'] }}</h2>
    @if (count($items))
        <div class="grid grid-5">@foreach ($items as $p) @include('partials.product-card', ['p' => $p]) @endforeach</div>
    @else
        <div class="card center">No products listed yet.</div>
    @endif
</div>
@if (auth()->check() && auth()->user()->role === 'client')
    <div class="container section" style="padding-top:0"><a class="muted" href="{{ route('messages.index') }}"><i class="bi bi-inbox"></i> View your messages</a></div>
@endif
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('district-map').setView([{{ $loc['lat'] ?? 23.685 }}, {{ $loc['lng'] ?? 90.356 }}], 9);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'}).addTo(map);
    L.marker([{{ $loc['lat'] ?? 23.685 }}, {{ $loc['lng'] ?? 90.356 }}]).addTo(map).bindPopup('{{ $farmer['name'] }} – {{ $farmer['district'] }}');
</script>
@endpush
