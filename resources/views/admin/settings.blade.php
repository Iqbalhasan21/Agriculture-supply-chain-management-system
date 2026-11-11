@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'System Settings')
@section('content')
<form class="stack gap-20" method="POST" action="{{ route('admin.settings.save') }}">
    @csrf
    <div class="card">
        <h3 class="mb-16">General</h3>
        <div class="form-row">
            <div class="form-group"><label for="site_name">Site Name</label><input id="site_name" name="site_name" value="{{ old('site_name', $settings->get('site_name', 'FarmDirect')) }}" required maxlength="80"></div>
            <div class="form-group"><label for="support_email">Support Email</label><input id="support_email" name="support_email" type="email" value="{{ old('support_email', $settings->get('support_email', '')) }}"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="support_phone">Support Phone</label><input id="support_phone" name="support_phone" value="{{ old('support_phone', $settings->get('support_phone', '')) }}" maxlength="30"></div>
            <div class="form-group"><label>Currency</label><input value="৳ (BDT)" disabled></div>
        </div>
    </div>
    <div class="card">
        <h3 class="mb-16">Payment Accounts</h3>
        <p class="muted">Enter the accounts customers should transfer payments to. Payments stay pending until an admin verifies the transaction reference.</p>
        <div class="form-row">
            <div class="form-group"><label for="bkash_account">bKash account / merchant number</label><input id="bkash_account" name="bkash_account" value="{{ old('bkash_account', $settings->get('bkash_account', '')) }}" maxlength="40" placeholder="01XXXXXXXXX"></div>
            <div class="form-group"><label for="nagad_account">Nagad account / merchant number</label><input id="nagad_account" name="nagad_account" value="{{ old('nagad_account', $settings->get('nagad_account', '')) }}" maxlength="40" placeholder="01XXXXXXXXX"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="bank_name">Bank name</label><input id="bank_name" name="bank_name" value="{{ old('bank_name', $settings->get('bank_name', '')) }}" maxlength="120"></div>
            <div class="form-group"><label for="bank_account_name">Bank account name</label><input id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name', $settings->get('bank_account_name', '')) }}" maxlength="120"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label for="bank_account_number">Bank account number</label><input id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $settings->get('bank_account_number', '')) }}" maxlength="80"></div>
            <div class="form-group"><label for="bank_branch">Branch</label><input id="bank_branch" name="bank_branch" value="{{ old('bank_branch', $settings->get('bank_branch', '')) }}" maxlength="120"></div>
        </div>
    </div>
    <div class="card">
        <h3 class="mb-16">Delivery</h3>
        <div class="form-row">
            <div class="form-group"><label for="delivery_charge">Delivery Charge (৳)</label><input id="delivery_charge" name="delivery_charge" type="number" min="0" value="{{ old('delivery_charge', $settings->get('delivery_charge', 40)) }}" required></div>
            <div class="form-group"><label for="free_delivery_above">Free Delivery Above (৳)</label><input id="free_delivery_above" name="free_delivery_above" type="number" min="0" value="{{ old('free_delivery_above', $settings->get('free_delivery_above', 1000)) }}" required></div>
        </div>
    </div>
    <div class="card"><h3>System</h3>
        <div class="setting-row"><div><b>Allow new registrations</b><div class="muted">Client and farmer signup forms are available.</div></div><label class="switch"><input type="checkbox" name="allow_registration" value="1" @checked(old('allow_registration', $settings->get('allow_registration', '1')) === '1') aria-label="Allow new registrations"><span></span></label></div>
        <div class="setting-row"><div><b>Cash on Delivery</b><div class="muted">The only currently supported payment method.</div></div><label class="switch"><input type="checkbox" name="cod_enabled" value="1" @checked(old('cod_enabled', $settings->get('cod_enabled', '1')) === '1') aria-label="Enable Cash on Delivery"><span></span></label></div>
        <p class="muted">bKash, Nagad and bank transfer appear at checkout after you save their receiving account details. Admins verify submitted references from Transactions.</p>
    </div>
    <div><button class="btn" type="submit">Save Settings</button></div>
</form>
@endsection
