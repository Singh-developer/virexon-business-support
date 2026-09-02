@extends('layouts.app')

@section('content')

@php
$isAgent = auth()->user()->isAgent();
<<<<<<< HEAD
$card = $agent->virtualCard;

if (!$card) {
    // If agent has no card, we shouldn't try to render the card details
    echo "<div class='panel'><div class='panel-head'><h3>No Virtual Card</h3><p>This agent does not have a virtual card assigned yet.</p></div></div>";
}
@endphp

@if($card)
@php
=======
>>>>>>> 4f8931dc4f474c0b9c8ea80d3239f0d810bd25e2

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
            {{ $card->business->name }}
        </p>

    </div>

<<<<<<< HEAD
    <div class="flex items-center gap-3">
        <span class="badge {{ $agent->status==='active'?'success':'neutral' }}">
            {{ ucfirst($agent->status) }}
        </span>
        <form method="POST" action="{{ route('agents.toggle-status', $agent) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn tiny {{ $agent->status === 'active' ? 'secondary' : 'primary' }}" onclick="return confirm('Are you sure you want to {{ $agent->status === 'active' ? 'disable' : 'enable' }} login for this agent?');">
                {{ $agent->status === 'active' ? 'Disable Login' : 'Enable Login' }}
            </button>
        </form>
    </div>
=======
    <span class="badge success">
        {{ ucfirst($statusValue) }}
    </span>
>>>>>>> 4f8931dc4f474c0b9c8ea80d3239f0d810bd25e2

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
<<<<<<< HEAD
@endif
=======
>>>>>>> 4f8931dc4f474c0b9c8ea80d3239f0d810bd25e2

@endsection