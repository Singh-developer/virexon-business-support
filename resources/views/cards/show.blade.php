@extends('layouts.app')@section('content')<div class="page-head">
    <div>
        <div class="eyebrow">CARD DETAILS</div>
        <h1>{{ $card->reference }}</h1>
        <p>{{ $card->business->name }} · {{ $card->cardholder_name }}</p>
    </div><span class="badge success">{{ ucfirst($card->status->value) }}</span>
</div>
<div class="card-visual">
    <div class="chip"></div>
    <div class="card-number">•••• •••• •••• {{ str_pad((string)$card->id,4,'0',STR_PAD_LEFT) }}</div>
    <div class="card-bottom"><span>{{ strtoupper($card->cardholder_name) }}</span><span>EXP {{ $card->expiry_date->format('m/y') }}</span></div>
</div>
<div class="stats-grid three">
    <div class="stat-card">
        <div class="stat-label">Overall Limit</div>
        <div class="stat-value">₹{{ number_format($card->card_limit,2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Current Usage</div>
        <div class="stat-value">₹{{ number_format($card->current_usage,2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Remaining</div>
        <div class="stat-value">₹{{ number_format($card->remaining_limit,2) }}</div>
    </div>
</div>
<div class="panel">
    <div class="panel-head">
        <h3>Limit Policy</h3>
    </div>
    <div class="limits">
        <div><span>Daily</span><strong>₹{{ number_format($card->daily_limit,2) }}</strong></div>
        <div><span>Monthly</span><strong>₹{{ number_format($card->monthly_limit,2) }}</strong></div>
        <div><span>Per transaction</span><strong>₹{{ number_format($card->per_transaction_limit,2) }}</strong></div>
    </div>
</div>@endsection