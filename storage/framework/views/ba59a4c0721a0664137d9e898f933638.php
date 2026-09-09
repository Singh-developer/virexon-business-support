<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div>
        <div class="eyebrow">CARD DETAILS</div>
        <h1><?php echo e($card->reference); ?></h1>
        <p><?php echo e($card->business->name); ?> · <?php echo e($card->cardholder_name); ?></p>
    </div>
    <span class="badge <?php echo e($card->status->value); ?>"><?php echo e(ucfirst($card->status->value)); ?></span>
</div>
<div class="card-visual" id="card-visual" style="cursor: pointer; position: relative;" title="Click to reveal card details">
    <div class="chip"></div>
    <div class="card-number" id="card-number"><?php echo e($card->maskedPan()); ?></div>
    <div class="card-bottom">
        <span><?php echo e(strtoupper($card->cardholder_name)); ?></span>
        <div style="display: flex; align-items: center; gap: 14px;">
            <span id="card-cvv-badge" style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-size: 10px; letter-spacing: 1px; cursor: pointer;" title="Click to reveal CVV">CVV: •••</span>
            <span>EXP <?php echo e($card->expiry_date->format('m/y')); ?></span>
        </div>
    </div>
    <div id="card-toggle-hint" style="position: absolute; top: 12px; right: 16px; font-size: 9px; letter-spacing: 1px; opacity: 0.6; text-transform: uppercase;">tap to reveal</div>
</div>
<div class="stats-grid three">
    <div class="stat-card">
        <div class="stat-label">Overall Limit</div>
        <div class="stat-value">₹<?php echo e(number_format($card->card_limit,2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Current Usage</div>
        <div class="stat-value">₹<?php echo e(number_format($card->current_usage,2)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Remaining</div>
        <div class="stat-value">₹<?php echo e(number_format($card->remaining_limit,2)); ?></div>
    </div>
</div>
<div class="panel">
    <div class="panel-head">
        <h3>Limit Policy</h3>
    </div>
    <div class="limits">
        <div><span>Daily</span><strong>₹<?php echo e(number_format($card->daily_limit,2)); ?></strong></div>
        <div><span>Monthly</span><strong>₹<?php echo e(number_format($card->monthly_limit,2)); ?></strong></div>
        <div><span>Per transaction</span><strong>₹<?php echo e(number_format($card->per_transaction_limit,2)); ?></strong></div>
    </div>
</div>
<script>
(function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var revealPanUrl = '<?php echo e(route("cards.reveal-pan", $card)); ?>';
    var revealCvvUrl = '<?php echo e(route("cards.reveal-cvv", $card)); ?>';
    var maskedPan = <?php echo json_encode($card->maskedPan(), 15, 512) ?>;

    var cardVisual = document.getElementById('card-visual');
    var cardNumberEl = document.getElementById('card-number');
    var cvvBadge = document.getElementById('card-cvv-badge');
    var hint = document.getElementById('card-toggle-hint');
    var panRevealed = false;
    var cvvRevealed = false;

    function togglePan(e) {
        if (e) e.stopPropagation();
        if (panRevealed) {
            cardNumberEl.textContent = maskedPan;
            hint.textContent = 'tap to reveal';
            panRevealed = false;
            return;
        }
        hint.textContent = 'loading...';
        fetch(revealPanUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            cardNumberEl.textContent = data.formatted || data.pan;
            hint.textContent = 'tap to hide';
            panRevealed = true;
        })
        .catch(function() {
            hint.textContent = 'error - retry';
            setTimeout(function() { hint.textContent = 'tap to reveal'; }, 2000);
        });
    }

    function toggleCvv(e) {
        e.stopPropagation();
        if (cvvRevealed) {
            cvvBadge.textContent = 'CVV: \u2022\u2022\u2022';
            cvvRevealed = false;
            return;
        }
        cvvBadge.textContent = 'CVV: ...';
        fetch(revealCvvUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            cvvBadge.textContent = 'CVV: ' + data.cvv;
            cvvRevealed = true;
        })
        .catch(function() {
            cvvBadge.textContent = 'CVV: error';
            setTimeout(function() { cvvBadge.textContent = 'CVV: \u2022\u2022\u2022'; }, 2000);
        });
    }

    cardVisual.addEventListener('click', togglePan);
    cvvBadge.addEventListener('click', toggleCvv);
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/cards/show.blade.php ENDPATH**/ ?>