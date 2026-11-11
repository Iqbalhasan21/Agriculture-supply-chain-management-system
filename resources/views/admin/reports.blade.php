@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'Generate Reports')
@section('content')
<div class="grid grid-2"><div class="card"><div class="muted">Orders in period</div><strong style="font-size:24px">{{ $summary->count }}</strong></div><div class="card"><div class="muted">Revenue in period</div><strong style="font-size:24px">৳ {{ number_format($summary->revenue) }}</strong></div></div>
<form class="card" method="GET" action="{{ route('admin.reports') }}">
    <h3 class="mb-16">Date range</h3><div class="form-row"><div class="form-group"><label for="from">From</label><input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}"></div><div class="form-group"><label for="to">To</label><input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}"></div></div>
    <button class="btn" type="submit">Apply filters</button> <a class="btn btn-outline" href="{{ route('admin.reports.export', $filters) }}">Export orders CSV</a>
</form>
<div class="grid grid-2"><div class="card"><h3 class="mb-16">Orders by status</h3><table><thead><tr><th>Status</th><th>Orders</th></tr></thead><tbody>@forelse ($statuses as $row)<tr><td>{{ ucfirst($row->status) }}</td><td>{{ $row->count }}</td></tr>@empty<tr><td colspan="2" class="muted">No order data in this period.</td></tr>@endforelse</tbody></table></div>
<div class="card"><h3 class="mb-16">Top products</h3><table><thead><tr><th>Product</th><th>Quantity</th><th>Sales</th></tr></thead><tbody>@forelse ($topProducts as $product)<tr><td>{{ $product->name }}</td><td>{{ $product->quantity }}</td><td>৳ {{ number_format($product->value) }}</td></tr>@empty<tr><td colspan="3" class="muted">No product sales in this period.</td></tr>@endforelse</tbody></table></div></div>
@endsection
