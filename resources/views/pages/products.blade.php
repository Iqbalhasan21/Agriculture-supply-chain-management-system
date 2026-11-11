@extends('layouts.public')
@section('title', 'All Products')
@section('content')
<div class="container section">
    <p class="muted">Home › Products</p>
    <div class="row mb-16">
        <h1 style="font-size:28px">All Products</h1>
        <form method="GET" action="{{ route('products') }}" class="row" style="flex-wrap:nowrap">
            <input type="search" name="q" value="{{ $q }}" placeholder="Search products..." style="width:260px">
            <button class="btn" type="submit">Search</button>
        </form>
    </div>
    <div class="layout-side">
        <form method="GET" action="{{ route('products') }}" class="card stack gap-12">
            <h3>Categories</h3>
            <div>
                <a href="{{ route('products') }}" class="pill {{ !$category ? 'active' : '' }}">All</a>
                @foreach ($categories as $c)
                    <a href="{{ route('products', ['category' => $c]) }}" class="pill {{ $category === $c ? 'active' : '' }}">{{ $c }}</a>
                @endforeach
            </div>
            <hr class="divider" style="margin:0">
            <h3>District</h3>
            @include('partials.district-select', ['districts' => $districts, 'selected' => $district, 'placeholder' => 'All districts'])
            <label class="price-filter-label" for="max-price-range">Maximum price <strong>৳ <output data-max-price-output>{{ number_format($max) }}</output></strong></label>
            <input id="max-price-range" class="price-filter-range" type="range" name="max" min="10" max="{{ $priceCeiling }}" step="10" value="{{ min($max, $priceCeiling) }}" data-max-price-range>
            <div class="price-filter-limits"><span>৳10</span><span>৳{{ number_format($priceCeiling) }}+</span></div>
            <h3>Sort</h3>
            <select name="sort">
                <option value="">Your district first</option>
                <option value="low" @selected($sort === 'low')>Price: Low to High</option>
                <option value="high" @selected($sort === 'high')>Price: High to Low</option>
            </select>
            @if ($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            @if ($q)<input type="hidden" name="q" value="{{ $q }}">@endif
            <button class="btn btn-block" type="submit">Apply</button>
        </form>
        <div>
            @if (count($items))
                <div class="grid grid-3">@foreach ($items as $p) @include('partials.product-card', ['p' => $p]) @endforeach</div>
            @else
                <div class="card center"><h3>No products found</h3><p class="muted">Try another district or category.</p><a class="btn mt-16" href="{{ route('products') }}">Show all products</a></div>
            @endif
        </div>
    </div>
</div>
@endsection
