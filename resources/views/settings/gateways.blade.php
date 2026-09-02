@extends('layouts.app')

@section('content')

<div class="page-head">
    <div>
        <div class="eyebrow">PLATFORM SETTINGS</div>
        <h1>Payment Configuration</h1>
        <p>Manage global payment gateway settings and environment modes.</p>
    </div>
</div>

<div class="panel form-panel">

    <div class="panel-head">
        <div>
            <h3>Payment Mode</h3>
            <p>Controls whether payment gateways operate in Sandbox or Live mode.</p>
        </div>
        <span class="badge {{ $paymentMode === 'live' ? 'success' : 'neutral' }}">
            {{ strtoupper($paymentMode) }}
        </span>
    </div>

    <form method="POST" action="{{ route('settings.payment-mode.update') }}">
        @csrf
        <div class="form-grid">
            <label>
                Payment Mode
                <select name="payment_mode">
                    <option value="sandbox" @selected($paymentMode === 'sandbox')>Sandbox</option>
                    <option value="live" @selected($paymentMode === 'live')>Live</option>
                </select>
            </label>
        </div>

        <div class="notice">
            Only Admin and Super Admin users can change this setting.
            Agents do not have access to this configuration.
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Payment Mode</button>
        </div>
    </form>
</div>

@if($gateways->isNotEmpty())
<div class="panel form-panel" style="margin-top:20px;">
    <div class="panel-head">
        <div>
            <h3>Payment Gateways</h3>
            <p>Configure gateway credentials, statuses and environments. Leave credential fields blank to keep their current secure values.</p>
        </div>
    </div>
    
    <div style="padding: 0 24px 24px;">
        @forelse($gateways as $gateway)
        <div style="border:1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 6px;">
            <form method="POST" action="{{ route('settings.gateways.update', $gateway) }}">
                @csrf
                @method('PUT')
                
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
                    <strong style="font-size:16px;">{{ $gateway->name ?? 'Gateway #'.$gateway->id }}</strong>
                    
                    <div style="display:flex; gap:15px; align-items:center;">
                        <label style="margin:0;">
                            <input type="checkbox" name="status" value="1" @checked($gateway->status)> Active
                        </label>
                        
                        <select name="environment" style="width:auto; padding:4px;">
                            <option value="test" @selected($gateway->environment === 'test')>Test</option>
                            <option value="staging" @selected($gateway->environment === 'staging')>Staging</option>
                            <option value="production" @selected($gateway->environment === 'production')>Production</option>
                        </select>
                        
                        <button class="btn tiny primary">Save Changes</button>
                    </div>
                </div>

                <div class="form-grid">
                    <label>
                        API Key / Merchant ID
                        <input type="text" name="credentials[api_key]" placeholder="Leave blank to keep unchanged">
                    </label>
                    <label>
                        Secret Key
                        <input type="password" name="credentials[api_secret]" placeholder="Leave blank to keep unchanged">
                    </label>
                </div>
            </form>
        </div>
        @empty
        <p>No gateways configured yet.</p>
        @endforelse
    </div>
</div>
@endif

<div class="panel form-panel" style="margin-top:20px;">
    <div class="panel-head">
        <div>
            <h3>Add New Payment Gateway</h3>
            <p>Register a new payment provider.</p>
        </div>
    </div>
    <form method="POST" action="{{ route('settings.gateways.store') }}">
        @csrf
        <div class="form-grid">
            <label>
                Gateway Name (e.g. Stripe, Razorpay)
                <input type="text" name="name" required>
            </label>
            <label>
                Gateway Slug (e.g. stripe, razorpay)
                <input type="text" name="slug" required>
            </label>
        </div>
        <div class="form-actions">
            <button class="btn secondary">Add Gateway</button>
        </div>
    </form>
</div>

@endsection
