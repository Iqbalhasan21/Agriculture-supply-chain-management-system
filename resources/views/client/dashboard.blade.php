@extends('layouts.dashboard', ['role' => 'client'])
@section('title', 'Client Dashboard')
@section('content')
@php $order = collect($orders); @endphp
@if ($verificationStatus !== 'verified')
    <div class="verification-dashboard-notice"><div><strong>Account verification needed to place an order</strong><p>Browse products as usual; verify your account before checkout.</p></div><a class="btn btn-sm" href="{{ route('account.verification') }}">Verify Account</a></div>
@endif
<div class="stats">
    <div class="card stat"><div class="label">Total Orders</div><div class="value">{{ count($orders) }}</div></div>
    <div class="card stat"><div class="label">Pending Orders</div><div class="value">{{ $order->where('status', 'Pending')->count() }}</div></div>
    <div class="card stat"><div class="label">Delivered Orders</div><div class="value">{{ $order->where('status', 'Delivered')->count() }}</div></div>
    <div class="card stat"><div class="label">Total Spent</div><div class="value">৳ {{ number_format($order->where('status', '!=', 'Cancelled')->sum('total')) }}</div></div>
</div>
<div class="layout-2" style="grid-template-columns:1fr 380px">
    <div class="card">
        <div class="row mb-16"><h3>Recent Orders</h3><a class="green bold" href="{{ route('client.orders') }}">View All</a></div>
        @include('client._orders-table', ['orders' => $orders])
    </div>
    <div class="card">
        <h3 class="mb-16">Top Categories</h3>
        <div class="stack gap-12">
            @foreach ([['Vegetables', 40], ['Fruits', 25], ['Dairy', 20], ['Others', 15]] as [$n, $v])
                <div><div class="row"><span>{{ $n }}</span><span class="muted">{{ $v }}%</span></div><div class="bar mt-8"><i style="width:{{ $v }}%"></i></div></div>
            @endforeach
        </div>
    </div>
</div>
<div class="card">
    <h3 class="mb-16">Quick Actions</h3>
    <div class="grid grid-5">
        <a class="btn btn-outline" href="{{ route('products') }}">Browse Products</a>
        <a class="btn btn-outline" href="{{ route('cart') }}">My Cart</a>
        <a class="btn btn-outline" href="{{ route('client.track') }}">Track Order</a>
        <a class="btn btn-outline" href="{{ route('client.orders') }}">Order History</a>
        <a class="btn" href="{{ route('messages.index') }}"><i class="bi bi-headset"></i> Contact Support</a>
    </div>
</div>
@endsection
