@extends('layouts.public')
@section('title', 'About Us')
@section('content')
<section class="hero">
    <div class="container">
        <div>
            <h1 style="font-size:44px">About FarmDirect</h1>
            <p>We connect local farmers directly with customers, so everyone gets fresh food at a fair price.</p>
            <p style="font-size:15px;margin-top:0">Farmers list their products, managers handle stock, warehouses and deliveries, and clients order fresh produce from home.</p>
        </div>
        <img class="hero-photo" style="aspect-ratio:560/340" src="{{ asset('images/about.jpg') }}" alt="Farmers planting rice in a green field">
    </div>
</section>
<div class="container section">
    <div class="card row" style="padding:26px 36px">
        @foreach ([['342', 'Farmers'], ['1,256', 'Users'], ['1,024', 'Products'], ['2,560', 'Orders']] as [$v, $l])
            <div class="center"><div class="green" style="font-size:32px;font-weight:700">{{ $v }}</div><div class="muted">{{ $l }}</div></div>
        @endforeach
    </div>
</div>
<div class="container section">
    <h2 class="section-title">What Drives Us</h2>
    <div class="grid grid-3">
        @foreach ([['Our Mission', 'Empower local farmers with a direct market and a fair price for their hard work.'], ['Our Vision', 'A transparent supply chain where fresh, safe food reaches every home quickly and reliably.'], ['Our Values', 'Freshness, honesty, fair prices and support for local communities in everything we do.']] as [$t, $d])
            <div class="card stack gap-8"><h3>{{ $t }}</h3><p class="muted" style="margin:0">{{ $d }}</p></div>
        @endforeach
    </div>
</div>
<div class="container section">
    <h2 class="section-title">How It Works</h2>
    <div class="grid grid-4">
        @foreach ([['Farmers List Products', 'Farmers add fresh products with price, stock and their farm location.'], ['Manager Approves', 'Managers approve products and manage warehouse stock.'], ['Clients Order', 'Customers browse, pick their district, add to cart and place orders.'], ['Fast Delivery', 'Orders are assigned for delivery and tracked live.']] as $i => [$t, $d])
            <div class="card stack gap-8"><span class="avatar" style="background:var(--g);color:#fff;display:grid;place-items:center;font-weight:700">{{ $i + 1 }}</span><h3>{{ $t }}</h3><p class="muted" style="margin:0">{{ $d }}</p></div>
        @endforeach
    </div>
</div>
<div class="container section">
    <div class="cta"><div><h2>Ready to get fresh from the farm?</h2><p>Shop now and support local farmers.</p></div><a class="btn btn-white" href="{{ route('products') }}">Shop Now</a></div>
</div>
@endsection
