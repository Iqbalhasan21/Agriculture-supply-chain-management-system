@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'Manage Users')
@section('content')
<div class="card verification-admin-card">
    <div class="row mb-16"><h3>Identity Verification</h3><span class="badge badge-pending">{{ $verifications->count() }} pending</span></div>
    <p class="muted">Call the saved phone number, compare the NID photos with the submitted NID number, and compare the face photo with the ID. The app does not perform SMS or automated face matching.</p>
    @forelse ($verifications as $verification)
        <div class="verification-review-row">
            <div class="verification-review-heading">
                <div><strong>{{ $verification->name }}</strong> <span class="badge">{{ ucfirst($verification->role) }}</span>
                    <div class="muted">{{ $verification->phone }} · {{ $verification->email ?: 'No email' }}</div>
                    <div class="muted">Submitted NID: <strong>{{ $verification->nid_number ?: 'Unable to decrypt; reject and request resubmission' }}</strong> · {{ \Carbon\Carbon::parse($verification->submitted_at)->diffForHumans() }}</div>
                </div>
                <div class="row gap-8">
                    <a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="{{ route('admin.users.verification.document', [$verification->user_id, 'id-front']) }}">NID Front</a>
                    <a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="{{ route('admin.users.verification.document', [$verification->user_id, 'id-back']) }}">NID Back</a>
                    <a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="{{ route('admin.users.verification.document', [$verification->user_id, 'face']) }}">Face Photo</a>
                </div>
            </div>
            <div class="verification-review-actions">
                <form method="POST" action="{{ route('admin.users.verification.approve', $verification->user_id) }}" class="verification-checklist">
                    @csrf
                    <label><input type="checkbox" name="phone_verified" value="1" required> I called and confirmed this phone number</label>
                    <label><input type="checkbox" name="nid_verified" value="1" required> NID photos match the submitted number</label>
                    <label><input type="checkbox" name="face_verified" value="1" required> Face photo matches the NID photo</label>
                    <button class="btn btn-sm" type="submit">Verify Account</button>
                </form>
                <form method="POST" action="{{ route('admin.users.verification.reject', $verification->user_id) }}" class="verification-reject-form">
                    @csrf<input name="reason" maxlength="500" required placeholder="Reason and what to resubmit" aria-label="Reason for rejection">
                    <button class="btn btn-outline btn-sm" type="submit">Request Resubmission</button>
                </form>
            </div>
        </div>
    @empty
        <p class="muted">No identity verification requests are waiting for review.</p>
    @endforelse
</div>
<div class="card">
    <div class="row mb-16"><h3>Farmer Requests</h3><span class="badge badge-pending">{{ $farmerRequests->count() }} pending</span></div>
    @forelse ($farmerRequests as $farmer)
        <div class="row" style="padding:14px 0;border-top:1px solid var(--line);align-items:flex-start">
            <div>
                <strong>{{ $farmer->name }}</strong> · {{ $farmer->farm_name }}
                <div class="muted">{{ $farmer->district }}, {{ $farmer->upazila }} · {{ $farmer->phone }} · {{ $farmer->email ?: 'No email' }}</div>
                <div class="muted">{{ $farmer->address }}</div>
            </div>
            <div class="row gap-8">
                <form method="POST" action="{{ route('admin.farmers.approve', $farmer->id) }}">@csrf<button class="btn btn-sm" type="submit">Approve</button></form>
                <form method="POST" action="{{ route('admin.farmers.reject', $farmer->id) }}" class="row gap-8">
                    @csrf<input name="reason" required maxlength="500" placeholder="Reason for rejection" aria-label="Reason for rejection">
                    <button class="btn btn-outline btn-sm" type="submit">Reject</button>
                </form>
            </div>
        </div>
    @empty
        <p class="muted">No farmer requests are waiting for review.</p>
    @endforelse
</div>
<div class="card">
    <h3 class="mb-16">All Users</h3>
    <div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @foreach ($users as $user)
            <tr>
                @php($identityPending = in_array($user->role, ['client', 'farmer'], true) && $user->verification_status !== 'verified')
                <td>{{ $user->name }}</td><td>{{ $user->email ?: 'Not provided' }}</td><td>{{ ucfirst($user->role) }}</td><td>{{ $user->phone ?: '—' }}</td>
                <td><span class="badge badge-{{ $identityPending ? 'pending' : ($user->is_active ? 'active' : 'inactive') }}">{{ $identityPending ? ucfirst(str_replace('_', ' ', $user->verification_status ?: 'unverified')) : ($user->is_active ? 'Active' : 'Inactive') }}</span></td>
                <td><div class="row gap-8">
                    <form method="POST" action="{{ route('admin.users.role', $user->id) }}">@csrf
                        <select name="role" aria-label="Role for {{ $user->name }}">@foreach (['client', 'farmer', 'manager', 'admin'] as $role)<option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>@endforeach</select>
                        <button class="btn btn-outline btn-sm" type="submit">Save role</button>
                    </form>
                    @if ($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.status', $user->id) }}">@csrf<button class="btn btn-outline btn-sm" type="submit">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                    @endif
                </div></td>
            </tr>
        @endforeach
    </tbody></table></div>
    {{ $users->links() }}
</div>
@endsection
