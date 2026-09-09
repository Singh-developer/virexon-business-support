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
                
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px; flex-wrap: wrap; gap: 15px;">
                    <strong style="font-size:16px;">{{ $gateway->name ?? 'Gateway #'.$gateway->id }}</strong>
                    
                    <div style="display:flex; gap:15px; align-items:center; flex-wrap: wrap;">
                        <label style="margin:0;">
                            <input type="checkbox" name="status" value="1" @checked($gateway->status)> Active
                        </label>
                        
                        <select name="environment" style="width:auto; padding:4px;">
                            <option value="sandbox" @selected($gateway->environment === 'sandbox')>Sandbox</option>
                            <option value="production" @selected($gateway->environment === 'production')>Production</option>
                        </select>
                        
                        <button type="submit" class="btn primary">Save Changes</button> {{-- tiny --}}
                    </div>
                </div>

                <div class="form-grid">
                    <label>
                        Sandbox API Key / Merchant ID
                        <input type="text" name="credentials[sandbox_api_key]" value="{{ $gateway->credentials['sandbox_api_key'] ?? '' }}" placeholder="Leave blank to keep unchanged">
                    </label>
                    <label>
                        Sandbox Secret Key
                        <input type="text" name="credentials[sandbox_api_secret]" value="{{ $gateway->credentials['sandbox_api_secret'] ?? '' }}" placeholder="Leave blank to keep unchanged">
                    </label>
                    <label>
                        Sandbox Website Name
                        <input type="text" name="credentials[sandbox_website]" value="{{ $gateway->credentials['sandbox_website'] ?? '' }}" placeholder="e.g. WEBSTAGING (Paytm) or website name">
                    </label>
                    <label>
                        Production API Key / Merchant ID
                        <input type="text" name="credentials[production_api_key]" value="{{ $gateway->credentials['production_api_key'] ?? '' }}" placeholder="Leave blank to keep unchanged">
                    </label>
                    <label>
                        Production Secret Key
                        <input type="text" name="credentials[production_api_secret]" value="{{ $gateway->credentials['production_api_secret'] ?? '' }}" placeholder="Leave blank to keep unchanged">
                    </label>
                    <label>
                        Production Website Name
                        <input type="text" name="credentials[production_website]" value="{{ $gateway->credentials['production_website'] ?? '' }}" placeholder="e.g. DEFAULT (Paytm) or website name">
                        <small style="color: #8a97a9;">Paytm: Find this in your Paytm Dashboard under Merchant Settings. Common values: DEFAULT, WEBSTAGING.</small>
                    </label>
                </div>
            </form>
            <form method="POST" action="{{ route('settings.gateways.destroy', $gateway) }}" style="margin-top: 15px; text-align: right;" onsubmit="return confirm('Are you sure you want to remove this gateway?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn tiny secondary" style="color: #e53e3e; border-color: #e53e3e;">Remove Gateway</button>
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
