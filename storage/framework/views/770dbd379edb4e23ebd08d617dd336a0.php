<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div>
        <div class="eyebrow">PAYMENT MANAGEMENT</div>
        <h1>Payments</h1>
        <p>Gateway-aware payment operations with status, verification and auditability.</p>
    </div>
    <a class="btn primary" href="<?php echo e(route('payments.create')); ?>">+ New Payment</a>
</div>
<div class="panel">
    <form class="toolbar">
        <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Payment reference or gateway ID">
        <select name="status">
            <option value="">All statuses</option>
            <?php $__currentLoopData = ['created','pending','processing','successful','failed','cancelled','refunded']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php if(request('status') === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select name="gateway">
            <option value="">All gateways</option>
            <option value="mock">Mock</option>
            <option value="razorpay">Razorpay</option>
            <option value="paytm">Paytm</option>
        </select>
        <select name="payment_type">
            <option value="">All types</option>
            <option value="spending" <?php if(request('payment_type') === 'spending'): echo 'selected'; endif; ?>">Spending</option>
            <option value="repayment" <?php if(request('payment_type') === 'repayment'): echo 'selected'; endif; ?>">Repayment</option>
        </select>
        <button class="btn secondary">Filter</button>
    </form>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Payment</th>
                    <?php if(auth()->user()->isAdmin()): ?>
                        <th>Agent</th>
                    <?php endif; ?>
                    <th>Business</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><a class="link mono" href="<?php echo e(route('payments.show', $p)); ?>"><?php echo e($p->reference); ?></a></td>
                        <?php if(auth()->user()->isAdmin()): ?>
                            <td><?php echo e($p->user?->name ?? '—'); ?></td>
                        <?php endif; ?>
                        <td><?php echo e($p->business->name); ?></td>
                        <td>
                            <?php if($p->isRepayment()): ?>
                                <span class="badge" style="background: #E8F8F5; color: #27AE60;">Repayment</span>
                            <?php else: ?>
                                <span class="badge" style="background: #FEF5E7; color: #E65100;">Spending</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: 600; color: <?php echo e($p->isRepayment() ? '#27AE60' : '#1565C0'); ?>">
                            <?php if($p->isRepayment()): ?>−<?php endif; ?> ₹<?php echo e(number_format($p->amount, 2)); ?>

                        </td>
                        <td><span class="badge neutral"><?php echo e(strtoupper($p->gateway)); ?></span></td>
                        <td><span class="badge <?php echo e($p->status->value); ?>"><?php echo e(ucfirst($p->status->value)); ?></span></td>
                        <td><?php echo e($p->created_at->format('d M Y H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e(auth()->user()->isAdmin() ? 8 : 7); ?>" class="empty">No payments yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination"><?php echo e($payments->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/payments/index.blade.php ENDPATH**/ ?>