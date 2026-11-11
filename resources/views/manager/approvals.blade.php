@extends('layouts.dashboard', ['role' => 'manager'])
@section('title', 'Approve Products')
@section('content')
<div class="toolbar"><div class="input-icon" style="flex:1;max-width:320px"><i class="bi bi-search"></i><input type="search" data-live-search placeholder="Search products or farmers..." aria-label="Search"></div></div>
@include('partials.dash.tabs', ['tabs' => ['Pending', 'Approved', 'Rejected', 'All']])
<div class="card">
    <div class="table-wrap">
        <table data-real>
            <thead><tr><th>Product</th><th>Farmer</th><th>Category</th><th>Price</th><th>Stock</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach ($products as $p)
                <tr @if ($p['status'] !== 'Pending') hidden @endif>
                    <td><div class="table-user">@if ($p['image'])<img class="thumb-sm" src="{{ asset('images/' . $p['image']) }}" alt="">@else<span class="thumb-sm">{{ $p['emoji'] }}</span>@endif<span>{{ $p['name'] }}</span></div></td>
                    <td>{{ $p['farmer'] }}</td><td>{{ $p['category'] }}</td><td>{{ $p['price'] }}</td><td>{{ $p['stock'] }}</td><td>{{ $p['date'] }}</td>
                    <td>@include('partials.status-badge', ['status' => $p['status']])@if ($p['reason'])<div class="muted" style="font-size:11px">{{ $p['reason'] }}</div>@endif</td>
                    <td class="actions">
                        @if ($p['status'] !== 'Approved')
                            <form method="POST" action="{{ route('manager.approvals.approve', $p['id']) }}" style="display:inline">@csrf<button class="link-btn" type="submit"><i class="bi bi-check-lg"></i> Approve</button></form>
                        @endif
                        @if ($p['status'] === 'Pending')
                            <details class="inline"><summary style="color:var(--r)"><i class="bi bi-x-lg"></i> Reject</summary>
                                <form method="POST" action="{{ route('manager.approvals.reject', $p['id']) }}">@csrf
                                    <label for="r{{ $p['id'] }}" style="font-size:12px;font-weight:600">Reason for the farmer</label>
                                    <input id="r{{ $p['id'] }}" name="reason" required maxlength="255" placeholder="e.g. Photo is unclear">
                                    <button class="btn btn-danger btn-sm" style="margin-top:8px" type="submit">Reject product</button>
                                </form>
                            </details>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
