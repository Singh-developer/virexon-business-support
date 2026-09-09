<?php $__env->startSection('content'); ?>
<style>
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
    .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .status-badge.sent { background: #dcfce7; color: #16a34a; }
    .status-badge.draft { background: #fef3c7; color: #d97706; }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">ASSERTION LETTERS</div>
        <h1>Assertion Letters</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">View all sent assertion letters, download PDFs or send new ones to agents.</p>
    </div>
    <a class="btn primary" href="<?php echo e(route('assertions.create')); ?>">+ New Letter</a>
</div>

<div class="table-wrap">
    <table class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Agent</th>
                <th>Subject</th>
                <th>Business</th>
                <th>Status</th>
                <th>Sent At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $letters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $letter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td>#<?php echo e($letter->id); ?></td>
                <td>
                    <strong><?php echo e($letter->user->name); ?></strong><br>
                    <small style="color:#64748b;"><?php echo e($letter->user->email); ?></small>
                </td>
                <td><?php echo e($letter->subject); ?></td>
                <td><?php echo e($letter->business?->name ?? '—'); ?></td>
                <td>
                    <span class="status-badge <?php echo e($letter->status); ?>">
                        <?php if($letter->status === 'sent'): ?> ✓ Sent <?php else: ?> Draft <?php endif; ?>
                    </span>
                </td>
                <td><?php echo e($letter->sent_at?->format('d M Y, h:i A') ?? '—'); ?></td>
                <td>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <a href="<?php echo e(route('assertions.show', $letter)); ?>" class="btn secondary tiny">View</a>
                        <a href="<?php echo e(route('assertions.download', $letter)); ?>" class="btn secondary tiny">PDF</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" style="text-align:center; padding: 30px; color:#64748b;">
                    No assertion letters found. <a href="<?php echo e(route('assertions.create')); ?>">Create the first one.</a>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if($letters->hasPages()): ?>
    <div style="margin-top:15px; display:flex; justify-content:center;">
        <?php echo e($letters->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/assertions/index.blade.php ENDPATH**/ ?>