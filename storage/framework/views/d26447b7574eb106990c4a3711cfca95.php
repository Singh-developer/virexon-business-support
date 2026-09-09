<?php $__env->startSection('content'); ?><div class="page-head">
    <div>
        <div class="eyebrow">OVERVIEW</div>
        <h1>Good day, <?php echo e(Str::before(auth()->user()->name,' ')); ?>!</h1>
        <p>Here’s what’s happening across your support and payment operations.</p>
    </div>
    <div class="actions"><?php if(auth()->user()->isAdmin()): ?><a class="btn secondary" href="<?php echo e(route('businesses.create')); ?>">+ New Business</a><?php endif; ?><a class="btn primary" href="<?php echo e(route('payments.create')); ?>">+ New Payment</a></div>
</div>
<div class="hero-banner">
    <div><span class="pill light">Business Support</span>
        <h2>Advance For Your Growth</h2>
        <p>Manage clients, cards and payment workflows from one secure workspace.</p>
    </div>
    <div class="hero-art">↗</div>
</div>
<div class="stats-grid"><?php $__currentLoopData = [['Total Businesses',$stats['businesses'],'blue'],['Active Businesses',$stats['active_businesses'],'green'],['Total Users / Agents',$stats['users'],'indigo'],['Active Virtual Cards',$stats['active_cards'],'orange'],['Total Card Limit','₹'.number_format($stats['card_limit'],2),'cyan'],['Total Amount Spent','₹'.number_format($stats['spent'],2),'violet'],['Available Limit','₹'.number_format($stats['remaining'],2),'teal'],['Today’s Payments',$stats['today_payments'],'pink']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="stat-card">
        <div class="stat-icon <?php echo e($s[2]); ?>">◈</div>
        <div>
            <div class="stat-label"><?php echo e($s[0]); ?></div>
            <div class="stat-value"><?php echo e($s[1]); ?></div>
        </div>
    </div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
<div class="section-grid">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Payment Overview</h3>
                <p>Daily activity snapshot</p>
            </div><span class="badge success">Live</span>
        </div>
        <div class="mini-chart"><?php for($i=0;$i<18;$i++): ?><span style="height:<?php echo e(28+($i%7)*9); ?>%"></span><?php endfor; ?></div>
        <div class="chart-legend"><span>● ₹1.6L volume</span><span>● 92% successful</span></div>
    </div>
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Payment Status</h3>
                <p>Today</p>
            </div>
        </div>
        <div class="status-summary">
            <div><strong><?php echo e($stats['successful_payments']); ?></strong><span>Successful</span></div>
            <div><strong><?php echo e($stats['pending_payments']); ?></strong><span>Pending</span></div>
            <div><strong><?php echo e($stats['failed_payments']); ?></strong><span>Failed</span></div>
        </div>
        <div class="progress"><span style="width:78%"></span></div><small>78% successful processing rate</small>
    </div>
</div>
<?php if(auth()->user()->isAdmin() && auth()->user()->unreadNotifications->isNotEmpty()): ?>
<div class="panel" style="margin-bottom: 16px;">
    <div class="panel-head">
        <div>
            <h3>Notifications</h3>
            <p>Recent alerts and registrations</p>
        </div>
        <form method="POST" action="<?php echo e(route('notifications.mark-read')); ?>" style="display:inline;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn secondary" style="font-size: 11px; padding: 4px 8px; cursor: pointer;">Mark all as read</button>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <tbody>
                <?php $__currentLoopData = auth()->user()->unreadNotifications->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="padding: 16px;">
                        <strong style="color: #2967d8; font-size: 11px;">NEW REGISTRATION</strong><br>
                        <span style="font-size: 13px;"><?php echo e($notification->data['message'] ?? 'New notification'); ?></span>
                    </td>
                    <td style="text-align: right; color: #8a97a9; font-size: 11px; padding: 16px;">
                        <?php echo e($notification->created_at->diffForHumans()); ?>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Recent Payments</h3>
            <p>Latest gateway activity</p>
        </div><a href="<?php echo e(route('payments.index')); ?>">View all →</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Business</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody><?php $__empty_1 = true; $__currentLoopData = $recentPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr>
                    <td class="mono"><?php echo e($p->reference); ?></td>
                    <td><?php echo e($p->business->name); ?></td>
                    <td>₹<?php echo e(number_format($p->amount,2)); ?></td>
                    <td><span class="badge neutral"><?php echo e(strtoupper($p->gateway)); ?></span></td>
                    <td><span class="badge <?php echo e($p->status->value); ?>"><?php echo e(ucfirst($p->status->value)); ?></span></td>
                    <td><?php echo e($p->created_at->diffForHumans()); ?></td>
                </tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr>
                    <td colspan="6" class="empty">No payments yet.</td>
                </tr><?php endif; ?></tbody>
        </table>
    </div>
</div><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/dashboard/index.blade.php ENDPATH**/ ?>