<?php $__env->startSection('content'); ?><div class="page-head">
    <div>
        <div class="eyebrow">CARD DETAILS</div>
        <h1><?php echo e($card->reference); ?></h1>
        <p><?php echo e($card->business->name); ?> · <?php echo e($card->cardholder_name); ?></p>
    </div><span class="badge success"><?php echo e(ucfirst($card->status->value)); ?></span>
</div>
<div class="card-visual">
    <div class="chip"></div>
    <div class="card-number">•••• •••• •••• <?php echo e(str_pad((string)$card->id,4,'0',STR_PAD_LEFT)); ?></div>
    <div class="card-bottom"><span><?php echo e(strtoupper($card->cardholder_name)); ?></span><span>EXP <?php echo e($card->expiry_date->format('m/y')); ?></span></div>
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
</div><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/cards/show.blade.php ENDPATH**/ ?>