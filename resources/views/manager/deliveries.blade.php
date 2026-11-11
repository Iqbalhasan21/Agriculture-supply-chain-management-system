@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'Assign Deliveries')
@section('content')
@include('partials.dash.stats', ['stats' => [['Awaiting Assignment', count($d['waiting'])], ['In Transit', $d['in_transit']], ['Delivered Today', $d['delivered_today']], ['Delivery Team', count($d['team'])]]])
<div class="charts-row">
    <div class="card">
        <h3 class="mb-16">Orders Waiting for a Delivery Man</h3>
        @if (count($d['waiting']))
        <div class="table-wrap"><table data-real>
            <thead><tr><th>Order ID</th><th>Customer</th><th>District</th><th>Total</th><th>Assign to</th></tr></thead>
            <tbody>
            @foreach ($d['waiting'] as $o)
                <tr><td><a class="green bold" href="{{ route('manager.orders.show', $o['no']) }}">{{ $o['id'] }}</a></td><td>{{ $o['customer'] }}</td><td>{{ $o['district'] }}</td><td>৳ {{ number_format($o['total']) }}</td>
                    <td><form method="POST" action="{{ route('manager.deliveries.assign') }}" class="row gap-8" style="flex-wrap:nowrap">@csrf<input type="hidden" name="order_no" value="{{ $o['no'] }}">
                        <select name="delivery_man_id" aria-label="Delivery man for {{ $o['id'] }}" style="padding:6px 8px">@foreach ($d['team'] as $m)<option value="{{ $m['id'] }}">{{ $m['name'] }}</option>@endforeach</select>
                        <button class="btn btn-sm" type="submit">Assign</button></form></td></tr>
            @endforeach
            </tbody></table></div>
        @else<p class="muted">Every order has a delivery man. Nice work!</p>@endif
    </div>
    <div class="card stack gap-12">
        <h3>Delivery Team</h3>
        @foreach ($d['team'] as $m)
            <div class="row" style="border:1px solid var(--b);border-radius:10px;padding:12px;flex-wrap:nowrap">
                <div class="table-user"><span class="avatar"></span><div><div class="bold">{{ $m['name'] }}</div><div class="muted" style="font-size:12px">{{ $m['phone'] ?: 'No phone' }} · {{ $m['active'] }} active</div></div></div>
                @include('partials.status-badge', ['status' => $m['busy'] ? 'Busy' : 'Available'])
            </div>
        @endforeach
        <details class="panel"><summary class="green"><i class="bi bi-plus-lg"></i> Add Delivery Man</summary>
            <form method="POST" action="{{ route('manager.deliveries.men') }}">@csrf
                <div class="form-group"><label for="m-name">Name</label><input id="m-name" name="name" required></div>
                <div class="form-group"><label for="m-phone">Phone</label><input id="m-phone" name="phone" type="tel"></div>
                <button class="btn btn-sm" type="submit">Add</button>
            </form>
        </details>
    </div>
</div>
@endsection
