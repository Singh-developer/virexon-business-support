<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div>
        <div class="eyebrow">PAYMENT DETAIL</div>
        <h1 class="mono"><?php echo e($payment->reference); ?></h1>
        <p><?php echo e($payment->business->name); ?> · <?php echo e(strtoupper($payment->gateway)); ?></p>
    </div>
    <div style="display: flex; gap: 8px; align-items: center;">
        <span class="badge <?php echo e($payment->status->value); ?>"><?php echo e(ucfirst($payment->status->value)); ?></span>
        <?php if($payment->isRepayment()): ?>
            <span class="badge" style="background: #EBF5FB; color: #1565C0;">Repayment</span>
        <?php else: ?>
            <span class="badge" style="background: #FEF5E7; color: #E65100;">Spending</span>
        <?php endif; ?>
    </div>
</div>

<?php if(session('error')): ?>
<div style="background: #FEE2E2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
    <?php echo e(session('error')); ?>

</div>
<?php endif; ?>

<?php if($payment->status->value === 'failed' && $payment->gateway_response && isset($payment->gateway_response['error'])): ?>
<div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
    <strong>Gateway Error:</strong> <?php echo e($payment->gateway_response['error']); ?>

</div>
<?php endif; ?>

<div class="stats-grid three">
    <div class="stat-card">
        <div class="stat-label">Amount</div>
        <div class="stat-value" style="color: <?php echo e($payment->isRepayment() ? '#27AE60' : '#1565C0'); ?>">
            <?php if($payment->isRepayment()): ?>−<?php endif; ?> ₹<?php echo e(number_format($payment->amount, 2)); ?>

        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Gateway Order</div>
        <div class="stat-value small mono"><?php echo e($payment->gateway_order_id ?: '—'); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Card</div>
        <div class="stat-value small"><?php echo e($payment->card?->reference ?: '—'); ?></div>
    </div>
</div>

<?php if(auth()->user()->isAdmin()): ?>
<div class="panel" style="margin-bottom: 16px;">
    <div class="panel-head">
        <h3>Agent Details</h3>
    </div>
    <div style="padding: 16px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
        <div>
            <div style="font-size: 11px; color: #8a97a9; margin-bottom: 4px;">Agent Name</div>
            <div style="font-weight: 600; font-size: 14px;"><?php echo e($payment->user?->name ?? '—'); ?></div>
        </div>
        <div>
            <div style="font-size: 11px; color: #8a97a9; margin-bottom: 4px;">Agent Email</div>
            <div style="font-weight: 600; font-size: 14px;"><?php echo e($payment->user?->email ?? '—'); ?></div>
        </div>
        <div>
            <div style="font-size: 11px; color: #8a97a9; margin-bottom: 4px;">Payment Type</div>
            <div style="font-weight: 600; font-size: 14px; color: <?php echo e($payment->isRepayment() ? '#27AE60' : '#E65100'); ?>">
                <?php echo e($payment->isRepayment() ? 'Repayment (reduces outstanding)' : 'Spending (increases usage)'); ?>

            </div>
        </div>
        <div>
            <div style="font-size: 11px; color: #8a97a9; margin-bottom: 4px;">Card Usage Before</div>
            <div style="font-weight: 600; font-size: 14px;">₹<?php echo e(number_format(($payment->card?->current_usage ?? 0) + ($payment->isRepayment() ? $payment->amount : -$payment->amount), 2)); ?></div>
        </div>
        <div>
            <div style="font-size: 11px; color: #8a97a9; margin-bottom: 4px;">Card Usage After</div>
            <div style="font-weight: 600; font-size: 14px;">₹<?php echo e(number_format($payment->card?->current_usage ?? 0, 2)); ?></div>
        </div>
        <div>
            <div style="font-size: 11px; color: #8a97a9; margin-bottom: 4px;">Completed At</div>
            <div style="font-weight: 600; font-size: 14px;"><?php echo e($payment->completed_at ? $payment->completed_at->format('d M Y H:i') : '—'); ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <h3>Payment Timeline</h3>
    </div>
    <div class="timeline">
        <div class="done">Created <span><?php echo e($payment->created_at->format('d M Y H:i')); ?></span></div>
        <div class="<?php echo e(in_array($payment->status->value, ['pending', 'processing', 'successful']) ? 'done' : ''); ?>">Gateway Processing</div>
        <div class="<?php echo e($payment->status->value === 'successful' ? 'done' : ''); ?>">Server Verified</div>
        <div class="<?php echo e($payment->status->value === 'successful' ? 'done' : ''); ?>">Ledger Updated</div>
    </div>

    <?php if($payment->gateway === 'mock' && $payment->status->value !== 'successful'): ?>
        <form method="POST" action="<?php echo e(route('payments.mock-complete', $payment)); ?>" style="display: inline;">
            <?php echo csrf_field(); ?>
            <button class="btn primary" style="margin-top: 12px;">
                <?php if($payment->isRepayment()): ?>
                    Complete Mock Repayment
                <?php else: ?>
                    Complete Mock Payment
                <?php endif; ?>
            </button>
        </form>
    <?php endif; ?>

    <?php if($payment->status->value === 'failed'): ?>
        <a href="<?php echo e(route('payments.create')); ?>" class="btn secondary" style="margin-top: 12px; margin-left: 8px;">
            Make Another Payment
        </a>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/payments/show.blade.php ENDPATH**/ ?>