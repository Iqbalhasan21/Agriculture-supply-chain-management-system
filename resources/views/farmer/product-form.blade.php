@extends('layouts.dashboard', ['role' => 'farmer'])
@section('title', $product ? 'Edit Product' : 'Add Product')
@section('content')
@php $v = fn ($k, $d = '') => old($k, $product[$k] ?? $d); @endphp
<div class="card row" style="background:var(--gl);border-color:var(--g)">
    <span><i class="bi bi-geo-alt"></i> Your farm location is <b>{{ $farm['district'] ?? 'not set' }}</b>. Clients in your district will see this product first.</span>
    <a class="green bold" href="{{ route('farmer.location') }}">Change location</a>
</div>
<form class="card" method="POST" enctype="multipart/form-data" action="{{ $product ? route('farmer.products.update', $product['id']) : route('farmer.products.store') }}">
    @csrf
    <h3 class="mb-16">Product Details</h3>
    @if ($product && $product['status'] === 'approved')
        <p class="muted" style="margin-top:0">This product is live. Changing the name, category, unit or photo sends it to a manager for approval again. Price and stock changes apply at once.</p>
    @endif
    <div class="form-row">
        <div class="form-group"><label for="name">Product Name</label><input id="name" name="name" value="{{ $v('name') }}" required placeholder="e.g. Fresh Cabbage"></div>
        <div class="form-group"><label for="category">Category</label><select id="category" name="category">@foreach ($categories as $c)<option @selected($v('category') === $c)>{{ $c }}</option>@endforeach</select></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label for="price">Price (৳)</label><input id="price" name="price" type="number" min="1" value="{{ $v('price') }}" required></div>
        <div class="form-group"><label for="unit">Unit</label><input id="unit" name="unit" value="{{ $v('unit') }}" required placeholder="e.g. 1 kg, 1 dozen"></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label for="stock">Available Stock</label><input id="stock" name="stock" type="number" min="0" value="{{ $v('stock') }}" required></div>
        <div class="form-group"><label for="harvest_date">Harvest Date</label><input id="harvest_date" name="harvest_date" type="date" max="{{ date('Y-m-d') }}" value="{{ $v('harvest_date') ? substr($v('harvest_date'), 0, 10) : '' }}"></div>
    </div>
    <div class="form-group"><label for="description">Description</label><textarea id="description" name="description" placeholder="Describe how the product was grown...">{{ $v('description') }}</textarea></div>
    <div class="form-group"><label for="photo">Product Photo (JPG, PNG or WebP, up to 4 MB)</label>
        @if ($product && $product['image'])<div class="mb-16"><img class="thumb-sm" style="width:72px;height:72px" src="{{ asset('images/' . $product['image']) }}" alt="Current photo"></div>@endif
        <div class="file-drop"><input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp"></div>
    </div>
    <p class="muted">A manager reviews every new product before it appears in the shop.</p>
    <div class="row gap-12" style="justify-content:flex-start"><button class="btn" type="submit">{{ $product ? 'Save Changes' : 'Submit for Approval' }}</button><a class="btn btn-outline" href="{{ route('farmer.products') }}">Cancel</a></div>
</form>
@endsection
