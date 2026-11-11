@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'Admin Dashboard')
@section('content')
<div class="grid grid-5">
    @foreach ([['Users', $stats['users']], ['Approved Farmers', $stats['farmers']], ['Approved Products', $stats['products']], ['Orders', $stats['orders']], ['Revenue', '৳ ' . number_format($stats['revenue'])]] as [$label, $value])
        <div class="card"><div class="muted">{{ $label }}</div><strong style="font-size:24px">{{ $value }}</strong></div>
    @endforeach
</div>
<div class="card">
    <div class="row mb-16"><h3>Recent Orders</h3><a class="green bold" href="{{ route('admin.transactions') }}">View Payments</a></div>
    <div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Created</th></tr></thead><tbody>
        @forelse ($recent as $order)
            <tr><td>{{ $order->order_no }}</td><td>{{ $order->name }}</td><td>৳ {{ number_format($order->total) }}</td><td>{{ ucfirst($order->status) }}</td><td>{{ $order->created_at }}</td></tr>
        @empty
            <tr><td colspan="5" class="muted">No orders yet.</td></tr>
        @endforelse
    </tbody></table></div>
</div>
@endsection
