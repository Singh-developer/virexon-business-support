@extends('layouts.app')
@section('content')
<div class="page-head">
    <div>
        <div class="eyebrow">PAYMENT FLOW</div>
        @if(auth()->user()->isAgent())
            <h1>Make a Payment</h1>
            <p>Pay your outstanding balance to reduce your card usage.</p>
        @else
            <h1>Initiate Payment</h1>
            <p>Card status and limits are validated server-side before gateway initiation.</p>
        @endif
    </div>
</div>

@if(auth()->user()->isAgent() && $agentCard)
<div class="stats-grid three" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-label">Outstanding Balance</div>
        <div class="stat-value" style="color: #E65100;">₹{{ number_format($agentCard->current_usage, 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Card Limit</div>
        <div class="stat-value">₹{{ number_format($agentCard->card_limit, 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Remaining Limit</div>
        <div class="stat-value" style="color: #27AE60;">₹{{ number_format($agentCard->remaining_limit, 2) }}</div>
    </div>
</div>
@endif

<div class="panel form-panel">
    <form method="POST" action="{{ route('payments.store') }}">
        @csrf

        <div class="form-grid">
            @if(auth()->user()->isAgent())
                <input type="hidden" name="business_id" value="{{ $agentCard?->business_id }}">
                <input type="hidden" name="card_id" value="{{ $agentCard?->id }}">
                <input type="hidden" name="payment_type" value="repayment">

                <label class="wide">
                    Your Card
                    <input type="text" value="{{ $agentCard?->reference }} · {{ $agentCard?->cardholder_name }}" readonly>
                </label>

                <label class="wide">
                    Outstanding Amount to Pay
                    <input
                        id="pay-amount"
                        name="amount"
                        type="number"
                        step="0.01"
                        min="10"
                        max="{{ $agentCard?->current_usage }}"
                        value="{{ $agentCard?->current_usage }}"
                        required
                    >
                    <small id="pay-error" style="color: #E65100; font-size: 11px; display: none;">Minimum payment amount is ₹10.00</small>
                    <small id="pay-hint" style="color: #8a97a9; font-size: 11px;">Maximum payable: ₹{{ number_format($agentCard?->current_usage ?? 0, 2) }}</small>
                </label>
            @else
                <input type="hidden" name="payment_type" value="spending">

                <label class="wide">
                    Business
                    <select id="business" name="business_id" required>
                        @foreach($businesses as $b)
                            <option value="{{ $b->id }}" data-cards='@json($b->cards)'>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Virtual Card
                    <select id="card" name="card_id" required></select>
                </label>

                <label>
                    Amount (INR)
                    <input name="amount" type="number" step="0.01" min="0.01" required>
                </label>
            @endif

            <label>
                Gateway
                <select name="gateway" required>
                    @forelse($activeGateways as $gw)
                        <option value="{{ $gw->slug }}">{{ $gw->name }}</option>
                    @empty
                        <option value="mock" selected>Mock Sandbox</option>
                    @endforelse
                    <option value="mock">Mock Sandbox (Testing)</option>
                </select>
            </label>
        </div>

        @if(auth()->user()->isAgent())
            <div class="notice">
                <strong>Repayment Flow</strong><br>
                Select a gateway and enter the amount to pay your outstanding balance.
                For live gateways, you'll be redirected to the payment provider's secure checkout page.
            </div>
        @else
            <div class="notice">
                Flow: validate card → limits → create payment → gateway checkout → server verification/webhook → transaction ledger.
            </div>
        @endif

        <div class="form-actions">
            <a class="btn secondary" href="{{ route('payments.index') }}">Cancel</a>
            <button class="btn primary" id="pay-btn">
                @if(auth()->user()->isAgent())
                    Pay ₹{{ number_format($agentCard?->current_usage ?? 0, 2) }}
                @else
                    Create Payment
                @endif
            </button>
        </div>
    </form>
</div>

@if(auth()->user()->isAgent())
<script>
(function() {
    var amountInput = document.getElementById('pay-amount');
    var payBtn = document.getElementById('pay-btn');
    var payError = document.getElementById('pay-error');
    var payHint = document.getElementById('pay-hint');
    var minAmount = 10;

    function updatePayBtn() {
        var val = parseFloat(amountInput.value) || 0;
        payBtn.textContent = 'Pay ₹' + val.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        if (val < minAmount) {
            payBtn.disabled = true;
            payBtn.style.opacity = '0.5';
            payBtn.style.cursor = 'not-allowed';
            payError.style.display = 'block';
            payHint.style.display = 'none';
        } else {
            payBtn.disabled = false;
            payBtn.style.opacity = '1';
            payBtn.style.cursor = 'pointer';
            payError.style.display = 'none';
            payHint.style.display = 'block';
        }
    }

    amountInput.addEventListener('input', updatePayBtn);
    updatePayBtn();
})();
</script>
@endif

@if(!auth()->user()->isAgent())
<script>
const b = document.querySelector('#business');
const c = document.querySelector('#card');

function sync() {
    const cards = JSON.parse(b.selectedOptions[0].dataset.cards || '[]');
    c.innerHTML = cards.map(x => `<option value="${x.id}">${x.reference} · ${x.cardholder_name} · ₹${Number(x.current_usage).toFixed(2)} used</option>`).join('');
}

b.addEventListener('change', sync);
sync();
</script>
@endif
@endsection
