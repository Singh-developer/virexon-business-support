@extends('layouts.app')

@section('content')

<div class="page-head">

    <div>

        <div class="eyebrow">
            VIRTUAL CARD
        </div>

        <h1>
            Create Agent Card
        </h1>

        <p>
            Only Admin and Super Admin can create virtual cards.
            Each Agent can have one card.
        </p>

    </div>

</div>


<div class="panel form-panel">

    <form
        method="POST"
        action="{{ route('cards.store') }}"
    >

        @csrf


        <div class="form-grid">


            {{-- AGENT --}}

            <label class="wide">

                Agent

                <select
                    name="agent_id"
                    id="agent"
                    required
                >

                    @forelse($agents as $agent)

                        <option
                            value="{{ $agent->id }}"
                            data-business="{{ $agent->business_id }}"
                            data-name="{{ $agent->name }}"
                            data-limit="{{ $agent->detail?->max_limit ?? 500000 }}"
                        >

                            {{ $agent->name }}

                            ·

                            {{ $agent->email }}

                            ·

                            {{ $agent->business?->name }}

                        </option>

                    @empty

                        <option value="">
                            All active agents already have a card.
                        </option>

                    @endforelse

                </select>

            </label>


            {{-- BUSINESS --}}

            <input
                type="hidden"
                name="business_id"
                id="business_id"
                value="{{ old('business_id', $agents->first()?->business_id) }}"
            >


            {{-- CARD HOLDER --}}

            <label class="wide">

                Cardholder Name

                <input
                    name="cardholder_name"
                    id="cardholder_name"
                    value="{{ old('cardholder_name', $agents->first()?->name) }}"
                    readonly
                    required
                >

            </label>


            {{-- LIMITS --}}

            <label>

                Overall Limit

                <input
                    name="card_limit"
                    type="number"
                    step="0.01"
                    min="0.01"
                    value="{{ old('card_limit', $card->card_limit) }}"
                    required
                >

            </label>


            <label>

                Daily Limit

                <input
                    name="daily_limit"
                    type="number"
                    step="0.01"
                    min="0.01"
                    value="{{ old('daily_limit', $card->daily_limit) }}"
                    required
                >

            </label>


            <label>

                Monthly Limit

                <input
                    name="monthly_limit"
                    type="number"
                    step="0.01"
                    min="0.01"
                    value="{{ old('monthly_limit', $card->monthly_limit) }}"
                    required
                >

            </label>


            <label>

                Per Transaction

                <input
                    name="per_transaction_limit"
                    type="number"
                    step="0.01"
                    min="0.01"
                    value="{{ old('per_transaction_limit', $card->per_transaction_limit) }}"
                    required
                >

            </label>


            {{-- STATUS --}}

            <label>

                Status

                <select name="status">

                    <option value="active">
                        Active
                    </option>

                    <option value="blocked">
                        Blocked
                    </option>

                    <option value="suspended">
                        Suspended
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>

                </select>

            </label>

        </div>


        <div class="notice">

            <strong>
                Card generation
            </strong>

            <br>

            The sandbox provider generates the full card number,
            CVV, expiry and provider reference automatically.

            <br><br>

            The PAN is encrypted before storage.
            CVV is intentionally not stored permanently.

        </div>


        <div class="form-actions">

            <a
                class="btn secondary"
                href="{{ route('cards.index') }}"
            >
                Cancel
            </a>

            <button
                class="btn primary"
                {{ $agents->isEmpty() ? 'disabled' : '' }}
            >
                Create Virtual Card
            </button>

        </div>

    </form>

</div>


<script>

    const agentSelect =
        document.querySelector('#agent');

    const businessInput =
        document.querySelector('#business_id');

    const cardholderInput =
        document.querySelector('#cardholder_name');

    const cardLimitInput =
        document.querySelector('input[name="card_limit"]');

    const dailyLimitInput =
        document.querySelector('input[name="daily_limit"]');

    const monthlyLimitInput =
        document.querySelector('input[name="monthly_limit"]');

    const perTransactionInput =
        document.querySelector('input[name="per_transaction_limit"]');


    function syncAgent()
    {
        const option =
            agentSelect?.selectedOptions[0];

        if (! option) {
            return;
        }


        businessInput.value =
            option.dataset.business || '';


        cardholderInput.value =
            option.dataset.name || '';

        const limit = parseFloat(option.dataset.limit) || 500000;

        if (cardLimitInput && !cardLimitInput.value) {
            cardLimitInput.value = limit;
        }

        if (dailyLimitInput && !dailyLimitInput.value) {
            dailyLimitInput.value = limit;
        }

        if (monthlyLimitInput && !monthlyLimitInput.value) {
            monthlyLimitInput.value = limit * 10;
        }

        if (perTransactionInput && !perTransactionInput.value) {
            perTransactionInput.value = limit;
        }
    }


    agentSelect?.addEventListener(
        'change',
        syncAgent
    );


    syncAgent();

</script>

@endsection