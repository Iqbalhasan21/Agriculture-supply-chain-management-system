@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'Monitor Transactions')
@section('content')
<div class="note"><strong>Manual payment review</strong><p>Compare the sender number and transaction reference with the bKash, Nagad or bank account statement before selecting Verify.</p></div>
<div class="grid grid-4">
    @foreach (['completed' => 'Completed', 'pending' => 'Pending', 'failed' => 'Failed', 'refunded' => 'Refunded'] as $key => $label)
        <a class="card" href="{{ route('admin.transactions', ['status' => $key]) }}"><div class="muted">{{ $label }}</div><strong>{{ $totals[$key]->count ?? 0 }}</strong><div>৳ {{ number_format($totals[$key]->amount ?? 0) }}</div></a>
    @endforeach
</div>
<div class="toolbar"><div class="tabs">@foreach (['All' => null, 'Completed' => 'completed', 'Pending' => 'pending', 'Failed' => 'failed', 'Refunded' => 'refunded'] as $label => $key)<a class="tab {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.transactions', array_filter(['status' => $key])) }}">{{ $label }}</a>@endforeach</div><a class="btn btn-outline" href="{{ route('admin.transactions.export') }}"><i class="bi bi-download"></i> Export CSV</a></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Payment</th><th>Order</th><th>Customer</th><th>Method</th><th>Amount</th><th>Status</th><th>Sender</th><th>Transaction Reference</th><th>Date</th><th>Review</th></tr></thead><tbody>
    @forelse ($payments as $payment)
        <tr>
            <td>#{{ $payment->id }}</td><td>{{ $payment->order_no }}</td><td>{{ $payment->name }}</td><td>{{ strtoupper($payment->method) }}</td><td>৳ {{ number_format($payment->amount) }}</td>
            <td><span class="badge {{ $payment->status === 'completed' ? 'badge-approved' : ($payment->status === 'pending' ? 'badge-pending' : 'badge-cancelled') }}">{{ ucfirst($payment->status) }}</span></td>
            <td>{{ $payment->payer_account ?: '—' }}</td><td>{{ $payment->reference ?: '—' }}</td><td>{{ $payment->created_at }}</td>
            <td>@if ($payment->status === 'pending' && $payment->method !== 'cod')
                <div class="row gap-8" style="justify-content:flex-start;flex-wrap:nowrap">
                    <form method="POST" action="{{ route('admin.transactions.approve', $payment->id) }}">@csrf<button class="btn btn-sm" type="submit">Verify</button></form>
                    <form method="POST" action="{{ route('admin.transactions.reject', $payment->id) }}">@csrf<button class="btn btn-outline btn-sm" type="submit">Reject</button></form>
                </div>
            @else<span class="muted">—</span>@endif</td>
        </tr>
    @empty
        <tr><td colspan="10" class="muted">No payments match this filter.</td></tr>
    @endforelse
</tbody></table></div>{{ $payments->links() }}</div>
@endsection
