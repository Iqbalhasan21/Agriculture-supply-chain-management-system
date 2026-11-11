@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'Inventory Reports')
@section('content')
@php $rows = array_map(fn ($p) => [$p['name'], $p['farmer'], $p['stock'], $p['in_warehouses'], ['badge' => $p['status']]], $r['low_items']); @endphp
<div class="toolbar">
    <span class="muted">Report for {{ date('d M Y') }}</span>
    <div class="row gap-12"><button type="button" class="btn btn-outline" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button><a class="btn" href="{{ route('manager.reports.export') }}"><i class="bi bi-file-earmark-excel"></i> Download CSV</a></div>
</div>
@include('partials.dash.stats', ['stats' => [['Total Stock Value', '৳ ' . number_format($r['value'])], ['Total Products', $r['products']], ['Low Stock Items', $r['low']], ['Out of Stock', $r['out']]]])
<div class="charts-row">
    <div class="card"><h3 class="mb-16">Stock by Category (units)</h3>@include('partials.dash.bars', ['data' => $r['by_category']])</div>
    <div class="card"><h3 class="mb-16">Stock Status</h3>@include('partials.dash.donut', ['segments' => [['In Stock', max(1, $r['status']['In Stock']), '#1E7B34'], ['Low Stock', $r['status']['Low Stock'], '#F59E0B'], ['Out of Stock', $r['status']['Out of Stock'], '#DC2626']]])</div>
</div>
<div class="card"><h3 class="mb-16">Items that need attention</h3>
    @if (count($rows))@include('partials.dash.table', ['real' => true, 'cols' => ['Product', 'Farmer', 'Shop stock', 'In warehouses', 'Status'], 'rows' => $rows])@else<p class="muted">Nothing is low on stock.</p>@endif
</div>
@endsection
