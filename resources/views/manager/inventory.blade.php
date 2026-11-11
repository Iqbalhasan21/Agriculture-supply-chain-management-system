@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'Manage Inventory')
@section('content')
<div class="toolbar"><div class="input-icon" style="flex:1;max-width:320px"><i class="bi bi-search"></i><input type="search" data-live-search placeholder="Search products..." aria-label="Search"></div></div>
@include('partials.dash.tabs', ['tabs' => ['All', 'In Stock', 'Low Stock', 'Out of Stock']])
<div class="card">
    <div class="table-wrap">
        <table data-real>
            <thead><tr><th>Product</th><th>Category</th><th>Farmer</th><th>Price</th><th>Stock (shop)</th><th>In warehouses</th><th>Status</th></tr></thead>
            <tbody>
            @foreach ($products as $p)
                <tr>
                    <td><div class="table-user">@if ($p['image'])<img class="thumb-sm" src="{{ asset('images/' . $p['image']) }}" alt="">@else<span class="thumb-sm">{{ $p['emoji'] }}</span>@endif<span>{{ $p['name'] }}</span></div></td>
                    <td>{{ $p['category'] }}</td><td>{{ $p['farmer'] }}</td><td>{{ $p['price'] }}</td>
                    <td><form method="POST" action="{{ route('manager.inventory.stock', $p['id']) }}" class="row gap-8" style="flex-wrap:nowrap;justify-content:flex-start">@csrf<input type="number" name="stock" min="0" value="{{ $p['stock'] }}" style="width:84px;padding:6px 8px" aria-label="Stock of {{ $p['name'] }}"><button class="btn btn-outline btn-sm" type="submit">Update</button></form></td>
                    <td>{{ $p['in_warehouses'] }}</td>
                    <td>@include('partials.status-badge', ['status' => $p['status']])</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<details class="card panel"><summary><i class="bi bi-plus-lg"></i> Add a product for a farmer</summary>
    <form method="POST" action="{{ route('manager.inventory.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group"><label for="a-farmer">Farmer</label><select id="a-farmer" name="farmer_id">@foreach ($farmers as $f)<option value="{{ $f['id'] }}">{{ $f['name'] }}</option>@endforeach</select></div>
            <div class="form-group"><label for="a-cat">Category</label><select id="a-cat" name="category">@foreach ($categories as $c)<option>{{ $c }}</option>@endforeach</select></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="a-name">Product Name</label><input id="a-name" name="name" required></div>
            <div class="form-group"><label for="a-unit">Unit</label><input id="a-unit" name="unit" required placeholder="e.g. 1 kg"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="a-price">Price (৳)</label><input id="a-price" name="price" type="number" min="1" required></div>
            <div class="form-group"><label for="a-stock">Stock</label><input id="a-stock" name="stock" type="number" min="0" required></div>
        </div>
        <button class="btn" type="submit">Add and approve</button>
    </form>
</details>
@endsection
