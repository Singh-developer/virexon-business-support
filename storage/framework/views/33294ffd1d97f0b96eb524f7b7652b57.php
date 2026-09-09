<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div>
        <div class="eyebrow">PAYMENT FLOW</div>
        <?php if(auth()->user()->isAgent()): ?>
            <h1>Make a Payment</h1>
            <p>Pay your outstanding balance to reduce your card usage.</p>
        <?php else: ?>
            <h1>Initiate Payment</h1>
            <p>Card status and limits are validated server-side before gateway initiation.</p>
        <?php endif; ?>
    </div>
</div>

<?php if(auth()->user()->isAgent() && $agentCard): ?>
<div class="stats-grid three" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-label">Outstanding Balance</div>
        <div class="stat-value" style="color: #E65100;">₹<?php echo e(number_format($agentCard->current_usage, 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Card Limit</div>
        <div class="stat-value">₹<?php echo e(number_format($agentCard->card_limit, 2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Remaining Limit</div>
        <div class="stat-value" style="color: #27AE60;">₹<?php echo e(number_format($agentCard->remaining_limit, 2)); ?></div>
    </div>
</div>
<?php endif; ?>

<div class="panel form-panel">
    <form method="POST" action="<?php echo e(route('payments.store')); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-grid">
            <?php if(auth()->user()->isAgent()): ?>
                <input type="hidden" name="business_id" value="<?php echo e($agentCard?->business_id); ?>">
                <input type="hidden" name="card_id" value="<?php echo e($agentCard?->id); ?>">
                <input type="hidden" name="payment_type" value="repayment">

                <label class="wide">
                    Your Card
                    <input type="text" value="<?php echo e($agentCard?->reference); ?> · <?php echo e($agentCard?->cardholder_name); ?>" readonly>
                </label>

                <label class="wide">
                    Outstanding Amount to Pay
                    <input
                        id="pay-amount"
                        name="amount"
                        type="number"
                        step="0.01"
                        min="10"
                        max="<?php echo e($agentCard?->current_usage); ?>"
                        value="<?php echo e($agentCard?->current_usage); ?>"
                        required
                    >
                    <small id="pay-error" style="color: #E65100; font-size: 11px; display: none;">Minimum payment amount is ₹10.00</small>
                    <small id="pay-hint" style="color: #8a97a9; font-size: 11px;">Maximum payable: ₹<?php echo e(number_format($agentCard?->current_usage ?? 0, 2)); ?></small>
                </label>
            <?php else: ?>
                <input type="hidden" name="payment_type" value="spending">

                <label class="wide">
                    Business
                    <select id="business" name="business_id" required>
                        <?php $__currentLoopData = $businesses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->id); ?>" data-cards='<?php echo json_encode($b->cards, 15, 512) ?>'><?php echo e($b->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
            <?php endif; ?>

            <?php if(auth()->user()->isAgent()): ?>
                <input type="hidden" name="gateway" value="mock">
                <label>
                    Gateway
                    <input type="text" value="Mock Sandbox (Internal)" readonly>
                    <small>Agent repayments use the internal sandbox. Admin can initiate real gateway payments.</small>
                </label>
            <?php else: ?>
                <label>
                    Gateway
                    <select name="gateway" required>
                        <?php $__empty_1 = true; $__currentLoopData = $activeGateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <option value="<?php echo e($gw->slug); ?>" <?php if($loop->first): echo 'selected'; endif; ?>><?php echo e($gw->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <option value="mock" selected>Mock Sandbox</option>
                        <?php endif; ?>
                    </select>
                </label>
            <?php endif; ?>
        </div>

        <?php if(auth()->user()->isAgent()): ?>
            <div class="notice">
                <strong>Repayment Flow</strong><br>
                Your payment will be processed through the selected gateway.
                Once successful, your outstanding balance will be reduced accordingly.
            </div>
        <?php else: ?>
            <div class="notice">
                Flow: validate card → limits → create payment → gateway checkout → server verification/webhook → transaction ledger.
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <a class="btn secondary" href="<?php echo e(route('payments.index')); ?>">Cancel</a>
            <button class="btn primary" id="pay-btn">
                <?php if(auth()->user()->isAgent()): ?>
                    Pay ₹<?php echo e(number_format($agentCard?->current_usage ?? 0, 2)); ?>

                <?php else: ?>
                    Create Payment
                <?php endif; ?>
            </button>
        </div>
    </form>
</div>

<?php if(auth()->user()->isAgent()): ?>
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
<?php endif; ?>

<?php if(!auth()->user()->isAgent()): ?>
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
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/payments/form.blade.php ENDPATH**/ ?>