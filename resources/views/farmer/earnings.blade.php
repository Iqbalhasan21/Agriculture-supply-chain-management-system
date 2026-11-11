@extends('layouts.dashboard', ['role' => 'farmer'])
@section('title', 'Sales & Earnings')
@section('content')
@php
    $rows = array_map(fn ($p) => [$p['date'], $p['order'], $p['items'], '৳ ' . number_format($p['sales']), '৳ ' . number_format($p['fee']), '৳ ' . number_format($p['sales'] - $p['fee']), ['badge' => $p['status']]], $e['payments']);
@endphp
@include('partials.dash.stats', ['stats' => [['Total Sales', '৳ ' . number_format($e['sales'])], ['Platform Fee (' . $e['fee_percent'] . '%)', '৳ ' . number_format($e['fee'])], ['Net Earnings', '৳ ' . number_format($e['net'])], ['Pending Payout', '৳ ' . number_format($e['pending_payout'])]]])
<div class="card"><h3 class="mb-16">Monthly Sales (৳)</h3>@include('partials.dash.bars', ['data' => $e['months']])</div>
<div class="card"><h3 class="mb-16">Payments</h3>
    @if (count($rows))
        @include('partials.dash.table', ['real' => true, 'cols' => ['Date', 'Order', 'Items', 'Sales', 'Fee', 'Earning', 'Status'], 'rows' => $rows])
    @else
        <p class="muted">Payments appear here after your first order.</p>
    @endif
</div>
@endsection
