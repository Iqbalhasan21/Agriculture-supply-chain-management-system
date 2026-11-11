@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'Manage Warehouse')
@section('content')
<div class="grid grid-3">
    @foreach ($warehouses as $w)
        <div class="card stack gap-8">
            <div class="row"><h3>{{ $w['name'] }}</h3><b style="color:{{ $w['pct'] > 85 ? 'var(--r)' : 'var(--g)' }}">{{ $w['pct'] }}% full</b></div>
            <span class="muted"><i class="bi bi-geo-alt"></i> {{ $w['district'] ?: 'No district' }}</span>
            <div class="bar wh-bar"><i style="width:{{ $w['pct'] }}%;background:{{ $w['pct'] > 85 ? 'var(--r)' : 'var(--g)' }}"></i></div>
            <b>{{ number_format($w['used']) }} / {{ number_format($w['capacity']) }} units · {{ $w['lines'] }} products</b>
        </div>
    @endforeach
</div>
<div class="grid grid-3">
    <details class="card panel"><summary><i class="bi bi-box-arrow-in-down"></i> Receive stock</summary>
        <form method="POST" action="{{ route('manager.warehouse.receive') }}">@csrf
            <div class="form-group"><label for="r-w">Warehouse</label><select id="r-w" name="warehouse_id">@foreach ($warehouses as $w)<option value="{{ $w['id'] }}">{{ $w['name'] }}</option>@endforeach</select></div>
            <div class="form-group"><label for="r-p">Product</label><select id="r-p" name="product_id">@foreach ($products as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }} ({{ $p['farmer'] }})</option>@endforeach</select></div>
            <div class="form-group"><label for="r-q">Quantity</label><input id="r-q" name="quantity" type="number" min="1" required></div>
            <button class="btn btn-sm" type="submit">Receive</button>
        </form>
    </details>
    <details class="card panel"><summary><i class="bi bi-arrow-left-right"></i> Move stock</summary>
        <form method="POST" action="{{ route('manager.warehouse.transfer') }}">@csrf
            <div class="form-row">
                <div class="form-group"><label for="t-f">From</label><select id="t-f" name="from_id">@foreach ($warehouses as $w)<option value="{{ $w['id'] }}">{{ $w['name'] }}</option>@endforeach</select></div>
                <div class="form-group"><label for="t-t">To</label><select id="t-t" name="to_id">@foreach ($warehouses as $w)<option value="{{ $w['id'] }}" @selected($loop->iteration === 2)>{{ $w['name'] }}</option>@endforeach</select></div>
            </div>
            <div class="form-group"><label for="t-p">Product</label><select id="t-p" name="product_id">@foreach ($products as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>@endforeach</select></div>
            <div class="form-group"><label for="t-q">Quantity</label><input id="t-q" name="quantity" type="number" min="1" required></div>
            <button class="btn btn-sm" type="submit">Move</button>
        </form>
    </details>
    <details class="card panel"><summary><i class="bi bi-plus-lg"></i> Add warehouse</summary>
        <form method="POST" action="{{ route('manager.warehouse.store') }}">@csrf
            <div class="form-group"><label for="w-n">Name</label><input id="w-n" name="name" required placeholder="Warehouse D"></div>
            <div class="form-group"><label for="w-d">District</label>@include('partials.district-select', ['districts' => $districts, 'required' => true])</div>
            <div class="form-group"><label for="w-c">Capacity (units)</label><input id="w-c" name="capacity" type="number" min="1" value="1000" required></div>
            <button class="btn btn-sm" type="submit">Add</button>
        </form>
    </details>
</div>
<div class="toolbar"><div class="input-icon" style="flex:1;max-width:320px"><i class="bi bi-search"></i><input type="search" data-live-search placeholder="Search warehouse stock..." aria-label="Search"></div></div>
<div class="card">
    <h3 class="mb-16">Stock by Warehouse</h3>
    <div class="table-wrap"><table data-real>
        <thead><tr><th>Product</th><th>Category</th><th>Warehouse</th><th>Quantity</th><th>Status</th></tr></thead>
        <tbody>
        @foreach ($stock as $s)
            <tr><td><div class="table-user">@if ($s['image'])<img class="thumb-sm" src="{{ asset('images/' . $s['image']) }}" alt="">@else<span class="thumb-sm">{{ $s['emoji'] }}</span>@endif<span>{{ $s['product'] }}</span></div></td><td>{{ $s['category'] }}</td><td>{{ $s['warehouse'] }}</td><td>{{ $s['qty'] }}</td><td>@include('partials.status-badge', ['status' => $s['status']])</td></tr>
        @endforeach
        </tbody></table></div>
    <p class="muted" style="margin-bottom:0">Stock leaves a warehouse when its order is marked as shipped. Receiving stock also adds it to the shop stock.</p>
</div>
@endsection
