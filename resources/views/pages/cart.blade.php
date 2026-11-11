@extends('layouts.public')
@section('title', 'Shopping Cart')
@section('content')
@php $me = $me ?? []; @endphp
<div class="container section" id="cart-app" data-auth="{{ auth()->check() ? 1 : 0 }}" data-role="{{ auth()->user()->role ?? '' }}" data-login-url="{{ route('checkout') }}">
    <h1 style="font-size:30px;margin:0 0 20px">Shopping Cart</h1>
    @include('partials.form-errors')

    <div id="cart-empty" class="card center" style="padding:60px" hidden>
        <i class="bi bi-cart3" style="font-size:42px;color:var(--m)"></i>
        <h2>Your cart is empty</h2>
        <p class="muted">Pick some fresh products from local farmers.</p>
        <a class="btn" href="{{ route('products') }}">Shop now</a>
    </div>

    <div id="cart-full" class="layout-2" hidden>
        <div class="stack gap-20">
            <div class="card table-wrap" style="padding:0">
                <table>
                    <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th><span class="sr-only">Remove</span></th></tr></thead>
                    <tbody id="cart-rows"></tbody>
                </table>
            </div>

            <form id="checkout" class="card" method="POST" action="{{ route('orders.store') }}" novalidate>
                @csrf
                <input type="hidden" name="items" id="f-items">
                <h2 style="font-size:17px;margin-bottom:16px">Delivery Address</h2>
                <div class="form-row">
                    <div class="form-group"><label for="c-name">Full Name</label><div class="input-icon"><i class="bi bi-person"></i><input id="c-name" name="name" value="{{ old('name', $me['name'] ?? '') }}" required></div></div>
                    <div class="form-group"><label for="c-phone">Phone Number</label><div class="input-icon"><i class="bi bi-telephone"></i><input id="c-phone" name="phone" type="tel" value="{{ old('phone', $me['phone'] ?? '') }}" required></div></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="district">District</label>@include('partials.district-select', ['districts' => $districts, 'selected' => old('district', $me['district'] ?? ''), 'required' => true])</div>
                    <div class="form-group"><label for="c-address">Address</label><div class="input-icon"><i class="bi bi-geo-alt"></i><input id="c-address" name="address" value="{{ old('address', $me['address'] ?? '') }}" required></div></div>
                </div>
                <div class="form-group"><label for="c-note">Delivery Note (optional)</label><div class="input-icon"><i class="bi bi-chat-left-text"></i><input id="c-note" name="note" value="{{ old('note') }}" placeholder="e.g. call before arriving"></div></div>

                <h2 style="font-size:17px;margin:8px 0 12px">Payment Method</h2>
                @php
                    $bankConfigured = filled($paymentOptions['bank']['account_number']) && filled($paymentOptions['bank']['name']);
                    $configuredMethods = array_values(array_filter([
                        $codEnabled ? 'cod' : null,
                        filled($paymentOptions['bkash']) ? 'bkash' : null,
                        filled($paymentOptions['nagad']) ? 'nagad' : null,
                        $bankConfigured ? 'bank' : null,
                    ]));
                    $firstConfigured = $configuredMethods[0] ?? '';
                    $requestedPayment = old('payment', $firstConfigured);
                    $selectedPayment = in_array($requestedPayment, $configuredMethods, true) ? $requestedPayment : $firstConfigured;
                @endphp
                <div class="payment-methods" data-payment-methods>
                    @if ($codEnabled)
                        <label class="pay-option {{ $selectedPayment === 'cod' ? 'selected' : '' }}"><input type="radio" name="payment" value="cod" @checked($selectedPayment === 'cod')> <i class="bi bi-cash-stack"></i> <span><b>Cash on Delivery</b><br><span class="muted">Pay when the order arrives</span></span></label>
                    @endif
                    @foreach (['bkash' => ['bKash', 'wallet2'], 'nagad' => ['Nagad', 'wallet2'], 'bank' => ['Bank Transfer', 'bank']] as $method => [$label, $icon])
                        @php $methodConfigured = $method === 'bank' ? $bankConfigured : filled($paymentOptions[$method]); @endphp
                        <label class="pay-option {{ $selectedPayment === $method ? 'selected' : '' }} {{ $methodConfigured ? '' : 'pay-option-disabled' }}">
                            <input type="radio" name="payment" value="{{ $method }}" @checked($selectedPayment === $method) @disabled(!$methodConfigured)>
                            <i class="bi bi-{{ $icon }}"></i>
                            <span><b>{{ $label }}</b><br><span class="muted">{{ $methodConfigured ? 'Transfer to FarmDirect' : 'Receiving account not set up yet' }}</span></span>
                            @unless ($methodConfigured)<span class="badge">Unavailable</span>@endunless
                        </label>
                    @endforeach
                    @foreach (['bkash' => 'bKash', 'nagad' => 'Nagad'] as $method => $label)
                        @if (filled($paymentOptions[$method]))
                            <div class="payment-details" data-payment-details="{{ $method }}" @if ($selectedPayment !== $method) hidden @endif>
                                <p><strong>Send the total to {{ $paymentOptions[$method] }} using {{ $label }}.</strong> Then enter your sender number and transaction ID.</p>
                                <div class="form-row">
                                    <div class="form-group"><label for="payer-{{ $method }}">Your {{ $label }} number</label><input id="payer-{{ $method }}" name="payer_account" value="{{ old('payer_account') }}" maxlength="80" data-payment-required></div>
                                    <div class="form-group"><label for="reference-{{ $method }}">Transaction ID</label><input id="reference-{{ $method }}" name="payment_reference" value="{{ old('payment_reference') }}" maxlength="80" data-payment-required></div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @if ($bankConfigured)
                        <div class="payment-details" data-payment-details="bank" @if ($selectedPayment !== 'bank') hidden @endif>
                            <p><strong>{{ $paymentOptions['bank']['name'] }}</strong><br>Account name: {{ $paymentOptions['bank']['account_name'] }}<br>Account number: {{ $paymentOptions['bank']['account_number'] }}<br>Branch: {{ $paymentOptions['bank']['branch'] }}</p>
                            <div class="form-row">
                                <div class="form-group"><label for="payer-bank">Your account / sender name</label><input id="payer-bank" name="payer_account" value="{{ old('payer_account') }}" maxlength="80" data-payment-required></div>
                                <div class="form-group"><label for="reference-bank">Bank transfer reference</label><input id="reference-bank" name="payment_reference" value="{{ old('payment_reference') }}" maxlength="80" data-payment-required></div>
                            </div>
                        </div>
                    @endif
                    @if ($firstConfigured === '')<p class="muted">No payment method is currently available. Please contact support.</p>@endif
                </div>
            </form>
        </div>

        <aside class="card stack gap-12" style="position:sticky;top:90px">
            <h2 style="font-size:18px">Order Summary</h2>
            <div class="row"><span class="muted">Subtotal (<span data-count>0</span> items)</span><b data-sub>৳ 0</b></div>
            <div class="row"><span class="muted">Delivery Charge</span><b data-delivery>৳ 0</b></div>
            <p id="free-note" class="muted" style="margin:0;font-size:12px"></p>
            <hr class="divider" style="margin:0">
            <div class="row"><b>Total</b><b class="green" style="font-size:22px" data-total>৳ 0</b></div>
            <button type="button" class="btn btn-block" id="place-order" @disabled($firstConfigured === '')><i class="bi bi-lock"></i> Place Order · <span data-pay-amount>৳ 0</span></button>
            <a class="btn btn-outline btn-block" href="{{ route('products') }}">Continue Shopping</a>
            <p class="muted" style="margin:0;font-size:12px">Prices and stock are checked again when the order is placed.</p>
        </aside>
    </div>

</div>
@endsection
@push('scripts')
<script>window.FD_SHIPPING = {charge: {{ (int) ($shipping['charge'] ?? 40) }}, free: {{ (int) ($shipping['free'] ?? 1000) }}};</script>
<script src="{{ \App\Support\DemoData::v('js/cart-page.js') }}"></script>
@endpush
