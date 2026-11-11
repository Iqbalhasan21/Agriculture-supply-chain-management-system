@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'Manager Dashboard')
@section('content')
@php
    $rows = array_map(fn ($o) => [$o['id'], $o['customer'], '৳ ' . number_format($o['total']), ['badge' => $o['status']]], $d['recent']);
@endphp
@include('partials.dash.stats', ['stats' => [['Approved Products', $d['products']], ['Total Orders', $d['orders']], ['Total Sales', '৳ ' . number_format($d['sales'])], ['Orders to Assign', $d['to_assign']]]])
@if ($d['waiting_approval'])
    <div class="card row" style="background:var(--gl);border-color:var(--g)"><span><i class="bi bi-clipboard-check"></i> <b>{{ $d['waiting_approval'] }}</b> {{ $d['waiting_approval'] === 1 ? 'product is' : 'products are' }} waiting for your approval.</span><a class="btn btn-sm" href="{{ route('manager.approvals') }}">Review now</a></div>
@endif
<div class="charts-row">
    <div class="card">
        <div class="row mb-16"><h3>Recent Orders</h3><a class="green bold" href="{{ route('manager.orders') }}">View All</a></div>
        @if (count($rows))@include('partials.dash.table', ['real' => true, 'cols' => ['Order ID', 'Customer', 'Total', 'Status'], 'rows' => $rows])@else<p class="muted">No orders yet.</p>@endif
    </div>
    <div class="card"><div class="row mb-16"><h3>Sales (last 10 days)</h3></div>
        @if (array_sum($d['series']) > 0)@include('partials.dash.line-chart', ['points' => $d['series'], 'labels' => $d['labels']])@else<p class="muted">The chart appears after the first order.</p>@endif
    </div>
</div>
<div class="card">
    <div class="row mb-16"><h3>Low Stock Alerts</h3><a class="green bold" href="{{ route('manager.inventory') }}">Open Inventory</a></div>
    @if (count($d['low']))
    <div class="grid grid-4">
        @foreach ($d['low'] as $p)
            <div class="card table-user" style="padding:12px">
                @if ($p['image'])<img class="thumb-sm" src="{{ asset('images/' . $p['image']) }}" alt="">@else<span class="thumb-sm">{{ $p['emoji'] }}</span>@endif
                <div><div class="bold">{{ $p['name'] }}</div><div style="color:var(--r)">{{ $p['stock'] }} left</div></div>
            </div>
        @endforeach
    </div>
    @else<p class="muted">All products are well stocked.</p>@endif
</div>
@endsection
