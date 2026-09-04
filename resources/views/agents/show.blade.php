@extends('layouts.app')

@section('content')

@php
$isAgent = auth()->user()->isAgent();
$card    = $agent->virtualCard;
$detail  = $agent->detail;
$refs    = $agent->referencePersons ?? collect();
$appStatus = optional($detail)->application_status ?? 'pending';

$statusColor = match($appStatus) {
    'approved' => 'success',
    'rejected' => 'failed',
    default    => 'pending',
};
@endphp

@if($card)
@php
$statusValue = $card->status instanceof \BackedEnum
? $card->status->value
: $card->status;
@endphp

<div class="page-head">

    <div>

        <div class="eyebrow">
            {{ $isAgent ? 'MY VIRTUAL CARD' : 'CARD DETAILS' }}
        </div>

        <h1>
            {{ $card->reference }}
        </h1>

        <p>
            {{ $card->cardholder_name }}
            ·
            {{ $card->business->name ?? 'No Business' }}
        </p>

    </div>

    <div class="flex items-center gap-3">
        <span class="badge {{ $agent->status==='active'?'success':'neutral' }}">
            {{ ucfirst($agent->status) }}
        </span>
        
        @if(auth()->user()->isAdmin())
        <a href="{{ route('agents.edit', $agent) }}" class="btn tiny secondary">
            Edit Agent
        </a>
        @endif

        <form method="POST" action="{{ route('agents.toggle-status', $agent) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn tiny {{ $agent->status === 'active' ? 'secondary' : 'primary' }}" onclick="return confirm('Are you sure you want to {{ $agent->status === 'active' ? 'disable' : 'enable' }} login for this agent?');">
                {{ $agent->status === 'active' ? 'Disable Login' : 'Enable Login' }}
            </button>
        </form>
    </div>
</div>

@if(session('card_created') && session('generated_cvv'))

<div class="flash success">

    <strong>Virtual card created successfully.</strong>

    <div style="margin-top:10px;">
        Sandbox CVV:
        <strong class="mono">
            {{ session('generated_cvv') }}
        </strong>
    </div>

    <small>
        This CVV is shown only at creation time and is not stored
        permanently by the application.
    </small>

</div>

@endif


{{-- ================================================================
     CARD VISUAL
================================================================ --}}

<div class="card-visual">

    <div class="card-top">

        <div class="card-brand">
            VIRTUAL
        </div>

        <div class="card-type">
            CARD
        </div>

    </div>

    <div
        id="card-pan-display"
        class="card-number">
        {{ $card->maskedPan() }}
    </div>

    <div class="card-bottom">

        <div>

            <div class="card-label">
                CARD HOLDER
            </div>

            <div class="card-holder">
                {{ strtoupper($card->cardholder_name) }}
            </div>

        </div>

        <div>

            <div class="card-label">
                EXPIRES
            </div>

            <div class="card-expiry">

                {{ $card->expiry_date->format('m/y') }}

            </div>

        </div>

    </div>

</div>


{{-- ================================================================
     PAN CONTROLS
================================================================ --}}

<div class="panel">

    <div class="panel-head">

        <div>

            <h3>
                Card Information
            </h3>

            <p>
                Sandbox card data only.
            </p>

        </div>

    </div>


    <div class="card-information-grid">

        <div class="info-item">

            <span class="info-label">
                Card Holder
            </span>

            <strong>
                {{ $card->cardholder_name }}
            </strong>

        </div>


        <div class="info-item">

            <span class="info-label">
                Expiry
            </span>

            <strong>
                {{ $card->expiry_date->format('m / Y') }}
            </strong>

        </div>


        <div class="info-item">

            <span class="info-label">
                Card Number
            </span>

            <strong
                id="card-pan-text"
                class="mono">
                {{ $card->maskedPan() }}
            </strong>

        </div>


        <div class="info-item">

            <span class="info-label">
                CVV
            </span>

            <strong class="mono">
                •••
            </strong>

            <small>
                CVV is provided by the issuer/provider and is not
                permanently stored by this application.
            </small>

        </div>

    </div>


    <div class="form-actions">

        <button
            type="button"
            id="reveal-pan-button"
            class="btn primary">
            Show Full Card Number
        </button>

        <button
            type="button"
            id="copy-pan-button"
            class="btn secondary"
            style="display:none;">
            Copy Card Number
        </button>

    </div>

</div>


{{-- ================================================================
     LIMITS
================================================================ --}}

<div class="stats-grid three">

    <div class="stat-card">

        <div class="stat-label">
            Overall Limit
        </div>

        <div class="stat-value">
            ₹{{ number_format($card->card_limit, 2) }}
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-label">
            Current Usage
        </div>

        <div class="stat-value">
            ₹{{ number_format($card->current_usage, 2) }}
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-label">
            Remaining
        </div>

        <div class="stat-value">
            ₹{{ number_format($card->remaining_limit, 2) }}
        </div>

    </div>

</div>


@if(! $isAgent)

{{-- ============================================================
         ADMIN ONLY
    ============================================================= --}}

<div class="panel">

    <div class="panel-head">

        <div>

            <h3>
                Limit Policy
            </h3>

            <p>
                Card spending configuration.
            </p>

        </div>

    </div>


    <div class="limits">

        <div>

            <span>
                Daily
            </span>

            <strong>
                ₹{{ number_format($card->daily_limit, 2) }}
            </strong>

        </div>


        <div>

            <span>
                Monthly
            </span>

            <strong>
                ₹{{ number_format($card->monthly_limit, 2) }}
            </strong>

        </div>


        <div>

            <span>
                Per Transaction
            </span>

            <strong>
                ₹{{ number_format($card->per_transaction_limit, 2) }}
            </strong>

        </div>

    </div>

</div>

@endif


<script>
    const revealButton =
        document.getElementById('reveal-pan-button');

    const copyButton =
        document.getElementById('copy-pan-button');

    const panDisplay =
        document.getElementById('card-pan-display');

    const panText =
        document.getElementById('card-pan-text');


    let fullPan = null;


    revealButton?.addEventListener('click', async function() {

        revealButton.disabled = true;

        revealButton.innerText =
            'Loading...';

        try {

            const response = await fetch(
                @json(route('cards.reveal-pan', $card)), {
                    method: 'POST',

                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token()),

                        'Accept': 'application/json',
                    },
                }
            );


            if (!response.ok) {

                throw new Error(
                    'Unable to reveal card number.'
                );

            }


            const data =
                await response.json();


            fullPan =
                data.formatted;


            panDisplay.innerText =
                data.formatted;


            panText.innerText =
                data.formatted;


            revealButton.innerText =
                'Card Number Visible';


            copyButton.style.display =
                'inline-flex';


        } catch (error) {

            console.error(error);

            alert(
                'Unable to reveal the card number.'
            );

            revealButton.disabled =
                false;

            revealButton.innerText =
                'Show Full Card Number';
        }

    });


    copyButton?.addEventListener('click', async function() {

        if (!fullPan) {
            return;
        }

        try {

            await navigator.clipboard.writeText(
                fullPan
            );

            copyButton.innerText =
                'Copied';

            setTimeout(() => {

                copyButton.innerText =
                    'Copy Card Number';

            }, 1800);

        } catch (error) {

            alert(
                'Unable to copy card number.'
            );

        }

    });
</script>
@endif

{{-- ====================================================================
     NO CARD NOTICE
==================================================================== --}}
@if(!$card)
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>No Virtual Card</h3>
            <p>This agent does not have a virtual card assigned yet.</p>
        </div>
        <a href="{{ route('cards.create') }}" class="btn tiny primary">Create Card</a>
    </div>
</div>
@endif

{{-- ====================================================================
     APPLICATION STATUS (Admin Only)
==================================================================== --}}
@if(!$isAgent)
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Application Status</h3>
            <p>Approve or reject this agent's application. This controls what the agent can edit in their profile.</p>
        </div>
        <span class="badge {{ $statusColor }}">{{ ucfirst($appStatus) }}</span>
    </div>

    <form method="POST" action="{{ route('agents.application-status', $agent) }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        @csrf
        <select name="application_status" style="border:1px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:13px; font-weight:600; min-width:160px; color:#1e293b; background:#fff;">
            <option value="pending"  @selected($appStatus === 'pending')>⏳ Pending</option>
            <option value="approved" @selected($appStatus === 'approved')>✅ Approved</option>
            <option value="rejected" @selected($appStatus === 'rejected')>❌ Rejected</option>
        </select>
        <button type="submit" class="btn primary" onclick="return confirm('Update application status?')">
            Save Status
        </button>
    </form>
</div>

{{-- ====================================================================
     FULL AGENT DETAILS (Admin read-only view)
==================================================================== --}}
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Agent Application Details</h3>
            <p>Information submitted by the agent during registration.</p>
        </div>
    </div>

    <style>
        .agent-detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0; }
        .agent-detail-grid .detail-item { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; }
        .agent-detail-grid .detail-item:nth-child(odd) { border-right: 1px solid #f1f5f9; }
        .agent-detail-grid .detail-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 4px; }
        .agent-detail-grid .detail-value { font-size: 13px; font-weight: 600; color: #1e293b; }
        .agent-detail-grid .detail-value.empty { color: #cbd5e1; font-style: italic; font-weight: 400; }
        .agent-section-head { grid-column: 1 / -1; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 16px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #64748b; }
        @media(max-width:600px){ .agent-detail-grid { grid-template-columns: 1fr; } .agent-detail-grid .detail-item { border-right: none !important; } }
    </style>

    @php
    function dv($val) { return filled($val) ? e($val) : '<span class="empty">—</span>'; }
    @endphp

    <div class="agent-detail-grid">
        {{-- Personal Info --}}
        <div class="agent-section-head">Personal Information</div>
        <div class="detail-item"><div class="detail-label">Full Name</div><div class="detail-value">{!! dv($agent->name) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Official Email</div><div class="detail-value">{!! dv($agent->email) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Personal Email</div><div class="detail-value">{!! dv(optional($detail)->personal_email) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Mobile</div><div class="detail-value">{!! dv(optional($detail)->mobile) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Guardian / Father Name</div><div class="detail-value">{!! dv(optional($detail)->guardian_name ?? optional($detail)->father_name) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Agent ID Number</div><div class="detail-value">{!! dv(optional($detail)->agent_id_number) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Date of Birth</div><div class="detail-value">{!! dv(optional($detail)->date_of_birth) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Gender</div><div class="detail-value">{!! dv(optional($detail)->gender ? ucfirst(optional($detail)->gender) : null) !!}</div></div>
        <div class="detail-item"><div class="detail-label">PAN Number</div><div class="detail-value">{!! dv(optional($detail)->pan_number) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Marital Status</div><div class="detail-value">{!! dv(optional($detail)->is_married ? 'Married' : (optional($detail)->is_married === 0 ? 'Single' : null)) !!}</div></div>

        {{-- Address --}}
        <div class="agent-section-head">Address Details</div>
        <div class="detail-item" style="grid-column:1/-1"><div class="detail-label">Current Address</div><div class="detail-value">{!! dv(optional($detail)->current_address) !!}@if(optional($detail)->address_line_2), {{ optional($detail)->address_line_2 }}@endif</div></div>
        <div class="detail-item"><div class="detail-label">City</div><div class="detail-value">{!! dv(optional($detail)->current_city) !!}</div></div>
        <div class="detail-item"><div class="detail-label">State</div><div class="detail-value">{!! dv(optional($detail)->current_state) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Pincode</div><div class="detail-value">{!! dv(optional($detail)->current_pincode) !!}</div></div>

        {{-- Bank --}}
        <div class="agent-section-head">Bank Details</div>
        <div class="detail-item"><div class="detail-label">Account Holder</div><div class="detail-value">{!! dv(optional($detail)->account_name) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Bank Name</div><div class="detail-value">{!! dv(optional($detail)->bank_name) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Account Number</div><div class="detail-value">{!! dv(optional($detail)->account_number) !!}</div></div>
        <div class="detail-item"><div class="detail-label">IFSC / Routing</div><div class="detail-value">{!! dv(optional($detail)->routing_number) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Account Type</div><div class="detail-value">{!! dv(optional($detail)->account_type) !!}</div></div>
        <div class="detail-item"><div class="detail-label">Branch</div><div class="detail-value">{!! dv(optional($detail)->branch_name) !!}</div></div>

        {{-- Advance --}}
        <div class="agent-section-head">Advance Details</div>
        <div class="detail-item"><div class="detail-label">Loan Amount Requested</div><div class="detail-value">{{ optional($detail)->loan_amount ? '₹'.number_format(optional($detail)->loan_amount, 2) : '—' }}</div></div>
        <div class="detail-item"><div class="detail-label">Purpose of Advance</div><div class="detail-value">{!! dv(optional($detail)->purpose_of_advance) !!}</div></div>
    </div>

    {{-- References --}}
    @if($refs->count())
    <div style="margin-top:0; border-top:1px solid #f1f5f9;">
        <div class="agent-section-head" style="background:#f8fafc; padding:10px 16px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#64748b;">References ({{ $refs->count() }})</div>
        <div style="padding:12px 16px; display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:12px;">
            @foreach($refs as $i => $ref)
            <div style="border:1px solid #e2e8f0; border-radius:10px; padding:14px;">
                <div style="font-size:10px; font-weight:800; color:#94a3b8; text-transform:uppercase; margin-bottom:8px;">Reference {{ $i + 1 }}</div>
                <div style="font-size:14px; font-weight:700; color:#1e293b; margin-bottom:4px;">{{ $ref->person_name }}</div>
                <div style="font-size:12px; color:#64748b;">📞 {{ $ref->mobile }}</div>
                <div style="font-size:11px; color:#94a3b8; margin-top:4px;">ID: {{ $ref->company_agent_id }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div style="padding:16px; color:#94a3b8; font-size:12px; border-top:1px solid #f1f5f9;">No references submitted yet.</div>
    @endif
</div>
@endif

@endsection