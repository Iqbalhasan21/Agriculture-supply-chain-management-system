@extends('layouts.dashboard', ['role' => 'client'])
@section('title', 'My Orders')
@section('content')
<div class="tabs">
    @foreach (['All', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $i => $t)
        <a href="{{ route('client.orders', $i ? ['status' => $t] : []) }}" class="tab {{ (request('status', 'All') === $t) ? 'active' : '' }}">{{ $t }}</a>
    @endforeach
</div>
@php
    $status = request('status');
    $rows = $status ? array_values(array_filter($orders, fn ($o) => $o['status'] === $status)) : $orders;
@endphp
<div class="card">
    @if (count($rows)) @include('client._orders-table', ['orders' => $rows]) @else <p class="center muted">No {{ strtolower($status) }} orders.</p> @endif
</div>
@endsection
