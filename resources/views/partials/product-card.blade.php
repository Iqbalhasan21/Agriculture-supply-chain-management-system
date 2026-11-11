@php $mine = auth()->check() && ($p['district'] !== '') && ((auth()->user()->district->name ?? null) === $p['district']); @endphp
<div class="card product-card">
    <a href="{{ route('product.show', $p['id']) }}">
        <div class="ph {{ $p['image'] ? 'photo' : '' }}" style="background: {{ $p['color'] }}">
            @if ($p['image'])<img src="{{ asset('images/' . $p['image']) }}" alt="{{ $p['name'] }}">@else<span>{{ $p['emoji'] }}</span>@endif
        </div>
        <h3>{{ $p['name'] }}</h3>
        <div class="muted">{{ $p['unit'] }}</div>
    </a>
    <div class="loc"><i class="bi bi-geo-alt"></i> {{ $p['district'] }} @if ($mine)<span class="badge badge-green">Your district</span>@endif</div>
    <div class="row">
        <span class="price">৳ {{ number_format($p['price']) }}</span>
        <span class="muted">★ {{ $p['rating'] }}</span>
        @if ($p['stock'] > 0)
        <button type="button" class="cart-btn" data-add-cart data-id="{{ $p['id'] }}" data-name="{{ $p['name'] }}" data-price="{{ $p['price'] }}" data-unit="{{ $p['unit'] }}" data-image="{{ $p['image'] ? asset('images/' . $p['image']) : '' }}" data-emoji="{{ $p['emoji'] }}" data-district="{{ $p['district'] }}" data-stock="{{ $p['stock'] }}" aria-label="Add {{ $p['name'] }} to cart"><i class="bi bi-plus-lg"></i></button>
        @else<span class="badge badge-out-of-stock">Out of stock</span>@endif
    </div>
</div>
