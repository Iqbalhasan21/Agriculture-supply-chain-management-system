@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'Roles & Permissions')
@section('content')
@php
    // permission => [Admin, Manager, Farmer, Client]
    $perms = [
        ['Manage Users', 1, 0, 0, 0], ['Manage Roles & Permissions', 1, 0, 0, 0], ['Manage Product Categories', 1, 0, 0, 0],
        ['Monitor Transactions', 1, 0, 0, 0], ['Generate Reports', 1, 1, 0, 0], ['Backup & Restore Database', 1, 0, 0, 0], ['System Settings', 1, 0, 0, 0],
        ['Manage Inventory & Update Stock', 0, 1, 0, 0], ['Manage Orders', 0, 1, 0, 0], ['Assign Deliveries', 0, 1, 0, 0],
        ['Manage Warehouse', 0, 1, 0, 0], ['Approve Products', 0, 1, 0, 0],
        ['Manage My Products & Stock', 0, 0, 1, 0], ['View Orders of My Products', 0, 0, 1, 0], ['Set Farm Location', 0, 0, 1, 0], ['Track My Product Status', 0, 0, 1, 0],
        ['Browse, Search & Add to Cart', 0, 0, 0, 1], ['Place Order & Track Order', 0, 0, 0, 1], ['Order History & Submit Reviews', 0, 0, 0, 1],
        ['Login, Profile, Password, Dashboard, Notifications', 1, 1, 1, 1],
    ];
@endphp
<div class="card">
    <div class="row mb-16"><h3>Role Access Control</h3><span class="badge badge-active">Enforced by routes</span></div>
    <div class="table-wrap">
        <table class="matrix">
            <thead><tr><th>Permission</th><th>Admin</th><th>Manager</th><th>Farmer</th><th>Client</th></tr></thead>
            <tbody>
            @foreach ($perms as $p)
                <tr>
                    <td>{{ $p[0] }}</td>
                    @foreach (['admin', 'manager', 'farmer', 'client'] as $i => $r)
                        <td><input type="checkbox" disabled @checked($p[$i + 1]) aria-label="{{ $p[0] }} for {{ $r }}"></td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<p class="muted">Role changes are managed from Users. Fine-grained editable permissions are not configured in this application.</p>
@endsection
