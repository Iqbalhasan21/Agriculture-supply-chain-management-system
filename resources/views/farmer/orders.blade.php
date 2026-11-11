@extends('layouts.dashboard', ['role' => 'farmer'])
@section('title', 'Orders of My Products')
@section('content')
@php
    $rows = array_map(fn ($o) => [$o['id'], $o['customer'], $o['district'], $o['items'], '৳ ' . number_format($o['amount']), $o['date'], ['badge' => $o['status']]], $orders);
@endphp
@include('partials.dash.tabs', ['tabs' => ['All', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled']])
<div class="card">
    @if (count($rows))
        @include('partials.dash.table', ['real' => true, 'cols' => ['Order ID', 'Customer', 'District', 'My Items', 'Amount', 'Date', 'Status'], 'rows' => $rows])
    @else
        <p class="center muted" style="padding:30px">No orders for your products yet.</p>
    @endif
</div>
<p class="muted">The amount counts only your own products in each order. Delivery and payment are handled by FarmDirect.</p>
@endsection
