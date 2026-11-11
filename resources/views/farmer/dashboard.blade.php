@extends('layouts.dashboard', ['role' => 'farmer'])
@section('title', 'Farmer Dashboard')
@section('content')
@php
    $rows = array_map(fn ($o) => [$o['id'], $o['customer'], $o['items'], '৳ ' . number_format($o['amount']), ['badge' => $o['status']]], $d['recent']);
@endphp
@if ($verificationStatus !== 'verified')
    <div class="verification-dashboard-notice"><div><strong>Verify your account before adding products</strong><p>You can view your dashboard and existing products while verification is pending.</p></div><a class="btn btn-sm" href="{{ route('account.verification') }}">Verify Account</a></div>
@endif
@include('partials.dash.stats', ['stats' => [['My Products', $d['products']], ['Total Orders', $d['orders']], ['Total Sales', '৳ ' . number_format($d['sales'])], ['Net Earnings', '৳ ' . number_format($d['net'])]]])
<div class="charts-row">
    <div class="card">
        <div class="row mb-16"><h3>Recent Orders</h3><a class="green bold" href="{{ route('farmer.orders') }}">View All</a></div>
        @if (count($rows))
            @include('partials.dash.table', ['real' => true, 'cols' => ['Order ID', 'Customer', 'My Items', 'Amount', 'Status'], 'rows' => $rows])
        @else
            <p class="muted">No orders for your products yet.</p>
        @endif
    </div>
    <div class="card"><div class="row mb-16"><h3>Sales (last 10 days)</h3></div>
        @if (array_sum($d['series']) > 0)
            @include('partials.dash.line-chart', ['points' => $d['series'], 'labels' => $d['labels']])
        @else
            <p class="muted">Your sales chart appears after your first order.</p>
        @endif
    </div>
</div>
<div class="card">
    <div class="row mb-16"><h3>My Products</h3><a class="green bold" href="{{ route('farmer.products') }}">Manage Products</a></div>
    @if (count($d['product_cards']))
    <div class="grid grid-5">
        @foreach ($d['product_cards'] as $p)
            <div class="card stack gap-8" style="padding:12px">
                <div class="table-user">@if ($p['image'])<img class="thumb-sm" src="{{ asset('images/' . $p['image']) }}" alt="">@else<span class="thumb-sm">{{ $p['emoji'] }}</span>@endif<b>{{ $p['name'] }}</b></div>
                @include('partials.status-badge', ['status' => $p['status']])
                <span class="muted">Stock: {{ $p['stock'] }}</span>
            </div>
        @endforeach
    </div>
    @else
        <p class="muted">You have not added a product yet. <a class="green bold" href="{{ route('farmer.products.create') }}">Add your first product</a></p>
    @endif
</div>
<div class="card">
    <h3 class="mb-16">Quick Actions</h3>
    <div class="grid grid-5">
        <a class="btn btn-outline" href="{{ route('farmer.products.create') }}"><i class="bi bi-plus-circle"></i> Add Product</a>
        <a class="btn btn-outline" href="{{ route('farmer.orders') }}"><i class="bi bi-bag-check"></i> My Orders</a>
        <a class="btn btn-outline" href="{{ route('farmer.earnings') }}"><i class="bi bi-cash-coin"></i> Earnings</a>
        <a class="btn btn-outline" href="{{ route('farmer.location') }}"><i class="bi bi-geo-alt"></i> Farm Location</a>
        <a class="btn" href="{{ route('messages.index') }}"><i class="bi bi-headset"></i> Contact Support</a>
    </div>
</div>
@endsection
