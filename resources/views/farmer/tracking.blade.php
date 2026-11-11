@extends('layouts.dashboard', ['role' => 'farmer'])
@section('title', 'Product Tracking')
@section('content')
@if (! $journey)
    <div class="card center" style="padding:50px"><h2>No products to track yet</h2><p class="muted">Add a product and follow its journey from your farm to the customer.</p><a class="btn mt-16" href="{{ route('farmer.products.create') }}">Add Product</a></div>
@else
@php $p = $journey['product']; @endphp
<div class="layout-2" style="grid-template-columns:1fr 360px">
    <div class="card">
        <div class="row mb-16">
            <div class="table-user">@if ($p['image'])<img class="thumb-sm" src="{{ asset('images/' . $p['image']) }}" alt="">@else<span class="thumb-sm">{{ $p['emoji'] ?? '📦' }}</span>@endif<div><h3>{{ $p['name'] }}</h3><span class="muted">@if ($journey['order'])Latest order #{{ $journey['order']['no'] }} · {{ $journey['order']['qty'] }} ordered @else No orders yet @endif</span></div></div>
            @include('partials.status-badge', ['status' => ucfirst($p['status'])])
        </div>
        @if ($journey['rejected'])<div class="alert alert-error">Rejected{{ $p['rejection_reason'] ? ': ' . $p['rejection_reason'] : '' }}. Edit the product to send it again.</div>@endif
        <div class="timeline">
            @foreach ($journey['steps'] as $s)
                <div class="t-step {{ $s['state'] }}">
                    <span class="t-dot">{{ $s['state'] === 'done' ? '✓' : '' }}</span>
                    <div><div class="bold">{{ $s['label'] }}</div>@if ($s['note'] || $s['at'])<div class="muted">{{ $s['note'] }} {{ $s['at'] ? '· ' . $s['at'] : '' }}</div>@endif</div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card stack gap-12" style="align-self:start">
        <h3>My Products</h3>
        @foreach ($products as $x)
            <a class="row" href="{{ route('farmer.tracking', $x['id']) }}" style="flex-wrap:nowrap;border:1px solid {{ $x['id'] === $p['id'] ? 'var(--g)' : 'var(--b)' }};border-radius:10px;padding:10px;background:{{ $x['id'] === $p['id'] ? 'var(--gl)' : 'var(--w)' }}">
                <div class="table-user">@if ($x['image'])<img class="thumb-sm" src="{{ asset('images/' . $x['image']) }}" alt="">@else<span class="thumb-sm">{{ $x['emoji'] }}</span>@endif<b>{{ $x['name'] }}</b></div>
                @include('partials.status-badge', ['status' => $x['stage']])
            </a>
        @endforeach
    </div>
</div>
@endif
@endsection
