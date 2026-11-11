@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'Data Export')
@section('content')
<div class="card"><h3>Export application data</h3><p class="muted">Download a JSON export of users, farmers, categories, products, orders, items, and payments. Passwords and NID hashes are excluded.</p><a class="btn" href="{{ route('admin.backup.export') }}"><i class="bi bi-download"></i> Download JSON export</a></div>
<div class="note">This export is not a full database backup and cannot restore the application. Use your hosting provider's encrypted database backup and recovery process for disaster recovery.</div>
@endsection
