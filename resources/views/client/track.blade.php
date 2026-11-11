@extends('layouts.dashboard', ['role' => 'client'])
@section('title', 'Track Order')
@section('content')
@if (! $order)
    <div class="card center" style="padding:50px"><h2>No orders to track yet</h2><p class="muted">When you place an order, you can follow it here.</p><a class="btn mt-16" href="{{ route('products') }}">Start shopping</a></div>
@else
<div class="card row">
    <div><h2 style="font-size:20px">Order #{{ $order['no'] }}</h2><p class="muted" style="margin:4px 0 0">Placed on {{ $order['date'] }} · Total ৳ {{ number_format($order['total']) }} · {{ ['cod' => 'Cash on Delivery', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'bank' => 'Bank Transfer', 'card' => 'Card'][$order['payment_method']] ?? $order['payment_method'] }} ({{ $order['payment_status'] }})</p></div>
    @include('partials.status-badge', ['status' => $order['status']])
</div>
<div class="card">
    <h3 class="mb-16">Order Progress</h3>
    @if ($order['key'] === 'cancelled')
        <p class="muted" style="margin:0">This order was cancelled.</p>
    @else
    <div class="stepper">
        @foreach ($steps as $i => $label)
            <div class="step {{ $i < $order['step'] ? 'done' : ($i === $order['step'] ? ($order['step'] === 3 ? 'done' : 'current') : '') }}"><span class="dot">{{ $i < $order['step'] || $order['step'] === 3 ? '✓' : '' }}</span><b>{{ $label }}</b></div>
        @endforeach
    </div>
    @endif
    @if (count($order['events']))
        <hr class="divider">
        @foreach ($order['events'] as $e)<div class="row" style="justify-content:flex-start;gap:12px"><b>{{ $e['stage'] }}</b><span class="muted">{{ $e['note'] }} · {{ $e['at'] }}</span></div>@endforeach
    @endif
</div>
<div class="grid grid-2 keep">
    <div class="card stack gap-12">
        <h3>Items in this order</h3>
        @foreach ($order['items'] as $i)
            <div class="row gap-12">
                <div class="row gap-12" style="flex-wrap:nowrap">
                    @if ($i['image'])<img class="thumb-sm" style="width:52px;height:52px" src="{{ asset('images/' . $i['image']) }}" alt="">@else<span class="thumb-sm" style="width:52px;height:52px">{{ $i['emoji'] }}</span>@endif
                    <div><div class="bold">{{ $i['name'] }} ×{{ $i['qty'] }}</div><div class="muted">From {{ $i['farmer'] }}, {{ $i['district'] }}</div>
                        @if ($order['key'] === 'delivered')<a class="green bold" href="{{ route('review.create', ['product' => $i['product_id']]) }}"><i class="bi bi-star"></i> Write a review</a>@endif
                    </div>
                </div>
                <b class="green">৳ {{ number_format($i['price'] * $i['qty']) }}</b>
            </div>
        @endforeach
        <hr class="divider" style="margin:4px 0">
        <div class="row"><span class="muted">Delivery</span><b>{{ $order['delivery_charge'] ? '৳ ' . number_format($order['delivery_charge']) : 'Free' }}</b></div>
    </div>
    <div class="card stack gap-12">
        <h3>Delivery Details</h3>
        @if ($order['delivery'])
            <div class="row gap-12" style="justify-content:flex-start;background:var(--bg);padding:12px;border-radius:10px"><span class="avatar avatar-md"></span><div><div class="bold">{{ $order['delivery']['man'] }}</div><div class="muted">Delivery Man · {{ $order['delivery']['phone'] }}</div></div></div>
        @else
            <p class="muted" style="margin:0">A delivery man will be assigned soon.</p>
        @endif
        <div class="muted">Delivery Address</div><div><b>{{ $order['name'] }}</b>, {{ $order['phone'] }}<br>{{ $order['address'] }}</div>
        @if ($order['key'] === 'pending')
            <form method="POST" action="{{ route('client.orders.cancel', $order['no']) }}" onsubmit="return confirm('Cancel this order?')">@csrf<button class="btn btn-danger" type="submit"><i class="bi bi-x-circle"></i> Cancel Order</button></form>
        @endif
    </div>
</div>
@endif
@endsection
