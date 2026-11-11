@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'Order #' . $order['no'])
@section('content')
<div class="card row">
    <div><h2 style="font-size:20px">Order #{{ $order['no'] }}</h2><p class="muted" style="margin:4px 0 0">Placed {{ $order['date'] }} · {{ ['cod' => 'Cash on Delivery', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'card' => 'Card'][$order['payment_method']] ?? $order['payment_method'] }} ({{ $order['payment_status'] }})</p></div>
    @include('partials.status-badge', ['status' => $order['status']])
</div>
<div class="grid grid-2 keep">
    <div class="card stack gap-12">
        <h3>Items</h3>
        @foreach ($order['items'] as $i)
            <div class="row"><div><div class="bold">{{ $i['name'] }} ×{{ $i['qty'] }}</div><div class="muted">From {{ $i['farmer'] }}, {{ $i['district'] }}</div></div><b>৳ {{ number_format($i['price'] * $i['qty']) }}</b></div>
        @endforeach
        <hr class="divider" style="margin:4px 0">
        <div class="row"><span class="muted">Subtotal</span><b>৳ {{ number_format($order['subtotal']) }}</b></div>
        <div class="row"><span class="muted">Delivery</span><b>{{ $order['delivery_charge'] ? '৳ ' . number_format($order['delivery_charge']) : 'Free' }}</b></div>
        <div class="row"><b>Total</b><b class="green">৳ {{ number_format($order['total']) }}</b></div>
    </div>
    <div class="stack gap-20">
        <div class="card stack gap-8">
            <h3>Customer</h3>
            <div><b>{{ $order['customer'] }}</b> · {{ $order['phone'] }}</div>
            <div class="muted"><i class="bi bi-geo-alt"></i> {{ $order['address'] }}, {{ $order['district'] }}</div>
            @if ($order['note'])<div class="muted">Note: {{ $order['note'] }}</div>@endif
        </div>
        <div class="card stack gap-12">
            <h3>Delivery</h3>
            @if ($order['delivery'])<div><b>{{ $order['delivery']['man'] }}</b> · {{ $order['delivery']['phone'] }} <span class="badge">{{ str_replace('_', ' ', $order['delivery']['status']) }}</span></div>@else<p class="muted" style="margin:0">No delivery man yet.</p>@endif
            @if (in_array($order['key'], ['pending', 'processing']))
                <form method="POST" action="{{ route('manager.deliveries.assign') }}" class="row gap-8" style="flex-wrap:nowrap">@csrf<input type="hidden" name="order_no" value="{{ $order['no'] }}">
                    <select name="delivery_man_id" aria-label="Delivery man">@foreach ($men as $m)<option value="{{ $m['id'] }}">{{ $m['name'] }}{{ $m['busy'] ? ' (' . $m['active'] . ' active)' : '' }}</option>@endforeach</select>
                    <button class="btn btn-outline" type="submit">{{ $order['delivery'] ? 'Change' : 'Assign' }}</button>
                </form>
            @endif
        </div>
        @if (count($order['next']))
        <div class="card stack gap-12">
            <h3>Update status</h3>
            <div class="row gap-12" style="justify-content:flex-start">
                @foreach ($order['next'] as $n)
                    <form method="POST" action="{{ route('manager.orders.status', $order['no']) }}" @if ($n === 'cancelled') onsubmit="return confirm('Cancel this order? The stock goes back to the shop.')" @endif>@csrf<input type="hidden" name="status" value="{{ $n }}"><button class="btn {{ $n === 'cancelled' ? 'btn-danger' : '' }}" type="submit">{{ ['processing' => 'Start processing', 'shipped' => 'Mark as shipped', 'delivered' => 'Mark as delivered', 'cancelled' => 'Cancel order'][$n] }}</button></form>
                @endforeach
            </div>
            @if ($order['key'] === 'processing')<p class="muted" style="margin:0;font-size:12px">A delivery man must be assigned before the order can ship.</p>@endif
        </div>
        @endif
    </div>
</div>
@if (count($order['events']))
<div class="card"><h3 class="mb-16">History</h3>@foreach ($order['events'] as $e)<div class="row" style="justify-content:flex-start;gap:12px"><b>{{ $e['stage'] }}</b><span class="muted">{{ $e['note'] }} · {{ $e['at'] }}</span></div>@endforeach</div>
@endif
@endsection
