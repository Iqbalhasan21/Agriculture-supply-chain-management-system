@extends('layouts.dashboard', ['role' => 'farmer'])
@section('title', 'My Products')
@section('content')
<div class="toolbar">
    <div class="input-icon" style="flex:1;max-width:320px"><i class="bi bi-search"></i><input type="search" data-live-search placeholder="Search my products..." aria-label="Search"></div>
    <a class="btn" href="{{ route('farmer.products.create') }}"><i class="bi bi-plus-lg"></i> Add Product</a>
</div>
@include('partials.dash.tabs', ['tabs' => ['All', 'Approved', 'Pending', 'Rejected']])
<div class="card">
    <div class="table-wrap">
        <table data-real>
            <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Approval</th><th>Stage</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach ($products as $p)
                <tr>
                    <td><div class="table-user">@if ($p['image'])<img class="thumb-sm" src="{{ asset('images/' . $p['image']) }}" alt="">@else<span class="thumb-sm">{{ $p['emoji'] }}</span>@endif<span>{{ $p['name'] }}</span></div></td>
                    <td>{{ $p['category'] }}</td>
                    <td>৳ {{ number_format($p['price']) }} / {{ $p['unit'] }}</td>
                    <td>
                        <form method="POST" action="{{ route('farmer.products.stock', $p['id']) }}" class="row gap-8" style="flex-wrap:nowrap;justify-content:flex-start">@csrf
                            <input type="number" name="stock" value="{{ $p['stock'] }}" min="0" style="width:84px;padding:6px 8px" aria-label="Stock of {{ $p['name'] }}">
                            <button class="btn btn-outline btn-sm" type="submit">Save</button>
                        </form>
                    </td>
                    <td>@include('partials.status-badge', ['status' => $p['status']])@if ($p['status'] === 'Rejected' && $p['reason'])<div class="muted" style="font-size:11px">{{ $p['reason'] }}</div>@endif</td>
                    <td>@include('partials.status-badge', ['status' => $p['stage']])</td>
                    <td class="actions">
                        <a href="{{ route('farmer.products.edit', $p['id']) }}"><i class="bi bi-pencil-square"></i> Edit</a>
                        <a href="{{ route('farmer.tracking', $p['id']) }}"><i class="bi bi-geo-alt"></i> Track</a>
                        <form method="POST" action="{{ route('farmer.products.delete', $p['id']) }}" style="display:inline" onsubmit="return confirm('Delete {{ $p['name'] }}?')">@csrf<button class="link-btn" style="color:var(--r)" type="submit"><i class="bi bi-trash"></i> Delete</button></form>
                    </td>
                </tr>
            @endforeach
            @if (! count($products))<tr><td colspan="7" class="center muted" style="padding:30px">No products yet. Add your first product.</td></tr>@endif
            </tbody>
        </table>
    </div>
</div>
@endsection
