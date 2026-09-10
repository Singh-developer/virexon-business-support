@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <div class="eyebrow">PAYTM PAYMENT</div>
        <h1>Pay via Paytm</h1>
        <p>Enter the amount you wish to pay using Paytm.</p>
    </div>
</div>

@if(session('error'))
<div style="background: #FEE2E2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
    {{ session('error') }}
</div>
@endif

@if(session('success'))
<div style="background: #DCFCE7; border: 1px solid #BBF7D0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
    {{ session('success') }}
</div>
@endif

@if(session('warning'))
<div style="background: #FEF3C7; border: 1px solid #FDE68A; color: #92400E; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
    {{ session('warning') }}
</div>
@endif

<div class="panel form-panel">
    <form method="POST" action="{{ route('paytm.pay') }}">
        @csrf
        <div class="form-grid">
            <label class="wide">
                Amount (INR)
                <input type="number" name="amount" step="0.01" min="1" max="5000000" required placeholder="Enter amount">
            </label>
        </div>
        <div class="form-actions">
            <a class="btn secondary" href="{{ route('payments.index') }}">Cancel</a>
            <button class="btn primary" type="submit">Pay via Paytm</button>
        </div>
    </form>
</div>
@endsection
