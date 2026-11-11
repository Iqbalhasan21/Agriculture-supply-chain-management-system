@extends('layouts.public')
@section('title', 'Order Placed')
@section('content')
<div class="container section" style="max-width:700px">
    <div class="card center stack gap-16" style="padding:44px;align-items:center">
        <span class="avatar" style="width:84px;height:84px;background:var(--g);color:#fff;display:grid;place-items:center;font-size:40px"><i class="bi bi-check-lg"></i></span>
        <h1 style="font-size:26px">Order Placed Successfully!</h1>
        <p class="muted" style="margin:0">Thank you for shopping with FarmDirect.<br>We will notify you when your order is on its way.</p>
        <div class="stack gap-8" style="width:100%;background:var(--bg);padding:18px;border-radius:10px;text-align:left">
            <div class="row"><span class="muted">Order ID</span><b>#{{ $order['no'] }}</b></div>
            <div class="row"><span class="muted">Items</span><b>{{ collect($order['items'])->map(fn ($i) => $i['name'] . ' ×' . $i['qty'])->implode(', ') }}</b></div>
            <div class="row"><span class="muted">Payment</span><b>{{ ['cod' => 'Cash on Delivery', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'bank' => 'Bank Transfer', 'card' => 'Card'][$order['payment_method']] ?? $order['payment_method'] }}{{ $order['payment_status'] === 'paid' ? ' – paid' : ($order['payment_status'] === 'pending' ? ' – waiting for payment verification' : '') }}</b></div>
            <div class="row"><span class="muted">Deliver to</span><b>{{ $order['address'] }}</b></div>
            <div class="row"><span class="muted">Subtotal</span><b>৳ {{ number_format($order['subtotal']) }}</b></div>
            <div class="row"><span class="muted">Delivery</span><b>{{ $order['delivery_charge'] ? '৳ ' . number_format($order['delivery_charge']) : 'Free' }}</b></div>
            <div class="row"><span class="muted">Total</span><b class="green">৳ {{ number_format($order['total']) }}</b></div>
        </div>
        <div class="row gap-12">
            <a class="btn" href="{{ route('client.track', $order['no']) }}">Track Order</a>
            <a class="btn btn-outline" href="{{ route('products') }}">Continue Shopping</a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>try { localStorage.removeItem('fd_cart'); } catch (e) {}</script>
@endpush
