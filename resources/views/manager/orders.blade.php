@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'Manage Orders')
@section('content')
<div class="toolbar"><div class="input-icon" style="flex:1;max-width:320px"><i class="bi bi-search"></i><input type="search" data-live-search placeholder="Search orders or customers..." aria-label="Search"></div></div>
@include('partials.dash.tabs', ['tabs' => ['All', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled']])
<div class="card">
    <div class="table-wrap">
        <table data-real>
            <thead><tr><th>Order ID</th><th>Customer</th><th>District</th><th>Items</th><th>Total</th><th>Delivery Man</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach ($orders as $o)
                <tr>
                    <td><a class="green bold" href="{{ route('manager.orders.show', $o['no']) }}">{{ $o['id'] }}</a></td>
                    <td>{{ $o['customer'] }}</td><td>{{ $o['district'] }}</td><td>{{ $o['items'] }}</td><td>৳ {{ number_format($o['total']) }}</td><td>{{ $o['delivery'] }}</td><td>{{ $o['date'] }}</td>
                    <td>@include('partials.status-badge', ['status' => $o['status']])</td>
                    <td class="actions"><a href="{{ route('manager.orders.show', $o['no']) }}"><i class="bi bi-eye"></i> View</a>@if ($o['delivery'] === 'Not assigned' && in_array($o['key'], ['pending', 'processing']))<a href="{{ route('manager.deliveries') }}"><i class="bi bi-person-plus"></i> Assign</a>@endif</td>
                </tr>
            @endforeach
            @if (! count($orders))<tr><td colspan="9" class="center muted" style="padding:30px">No orders yet.</td></tr>@endif
            </tbody>
        </table>
    </div>
</div>
@endsection
