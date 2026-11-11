@extends('layouts.public')
@section('title', 'Our Farmers')
@section('content')
@include('partials.page-header', ['title' => 'Our Farmers', 'subtitle' => 'Meet the local farmers who grow your fresh food'])
@php
    $selected = request('district');
    $list = $selected ? array_values(array_filter($farmers, fn ($f) => $f['district'] === $selected)) : $farmers;
@endphp
<div class="container section">
    <form method="GET" action="{{ route('farmers') }}" class="row mb-16">
        <div class="row" style="flex-wrap:nowrap">
            <select name="district" style="width:240px" aria-label="Filter by district">
                <option value="">All districts</option>
                @foreach (collect($farmers)->pluck('district')->unique() as $d)
                    <option value="{{ $d }}" @selected($selected === $d)>{{ $d }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit">Filter</button>
        </div>
        <a class="btn btn-outline" href="{{ route('districts') }}">View on map</a>
    </form>
    @if (count($list))
    <div class="grid grid-4">
        @foreach ($list as $f)
            @php $count = count(array_filter($products, fn ($p) => $p['farmer_id'] === $f['id'])); @endphp
            <div class="card stack gap-8" style="align-items:center;text-align:center">
                <img class="avatar avatar-lg" src="{{ asset('images/' . $f['photo']) }}" alt="{{ $f['name'] }}">
                <h3>{{ $f['name'] }}</h3>
                <span class="green bold">✓ Verified Farmer</span>
                <span class="muted"><i class="bi bi-geo-alt"></i> {{ $f['district'] }}, Bangladesh</span>
                <div class="row gap-16" style="justify-content:center"><span><b class="green">★ {{ $f['rating'] }}</b><br><span class="muted">{{ $f['reviews'] }} reviews</span></span><span><b>{{ $count }}</b><br><span class="muted">products</span></span></div>
                <a class="btn btn-outline btn-block" href="{{ route('farmer.show', $f['id']) }}">View Profile</a>
            </div>
        @endforeach
    </div>
    @else
        <div class="card center">No farmers in {{ $selected }} yet.</div>
    @endif
</div>
@include('partials.cta-farmer')
@endsection
