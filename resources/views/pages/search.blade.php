@extends('layouts.public')
@section('title', 'Search results')
@section('content')
<div class="container section">
    <h1 style="font-size:26px">Search results for “{{ $q }}”</h1>
    <p class="muted mb-16">{{ count($items) }} {{ count($items) === 1 ? 'product' : 'products' }} found</p>
    @if (count($items))
        <div class="grid grid-4">@foreach ($items as $p) @include('partials.product-card', ['p' => $p]) @endforeach</div>
    @else
        <div class="card center" style="padding:60px">
            <h2>No products found</h2>
            <p class="muted">We could not find anything for “{{ $q }}”. Check the spelling or try a different keyword.</p>
            <a class="btn mt-16" href="{{ route('products') }}">Browse All Products</a>
        </div>
    @endif
</div>
@endsection
