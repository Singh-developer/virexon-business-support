
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div>
        <div class="eyebrow">SANCTION LETTER</div>
        <h1><?php echo e($letter->sanction_letter_no); ?></h1>
        <p style="color:#64748b;font-size:14px;margin-top:5px">Details of the sent sanction letter.</p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="<?php echo e(route('sanctions.download', $letter)); ?>" class="btn primary">Download PDF</a>
        <a href="<?php echo e(route('sanctions.index')); ?>" class="btn secondary">← Back</a>
    </div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
    <div class="panel" style="padding:20px">
        <div style="font-size:11px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.08em;margin-bottom:12px">Recipient</div>
        <div style="font-size:16px;font-weight:700;color:#062b66"><?php echo e($letter->user->name); ?></div>
        <div style="color:#64748b;font-size:13px;margin-top:4px"><?php echo e($letter->user->email); ?></div>
        <?php if($letter->user->detail?->mobile): ?><div style="color:#64748b;font-size:13px">📞 <?php echo e($letter->user->detail->mobile); ?></div><?php endif; ?>
        <?php if($letter->business): ?><div style="color:#1557d6;font-size:13px;margin-top:6px;font-weight:600"><?php echo e($letter->business->name); ?></div><?php endif; ?>
    </div>
    <div class="panel" style="padding:20px">
        <div style="font-size:11px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.08em;margin-bottom:12px">Status &amp; Timing</div>
        <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:#dcfce7;color:#16a34a">✓ Sent</div>
        <div style="color:#64748b;font-size:13px;margin-top:10px">Sent: <?php echo e($letter->sent_at?->format('d M Y, h:i A')); ?></div>
        <div style="color:#64748b;font-size:13px">Created: <?php echo e($letter->created_at->format('d M Y, h:i A')); ?></div>
    </div>
</div>
<div class="panel" style="padding:24px">
    <div style="font-size:11px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.08em;margin-bottom:16px">Letter Preview</div>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;max-height:600px;overflow:auto">
        <div style="font-size:22px;font-weight:900;color:#062b66;letter-spacing:2px;margin-bottom:4px">AGENT BUSINESS SUPPORT</div>
        <div style="font-size:10px;color:#1557d6;letter-spacing:3px;font-weight:700;margin-bottom:20px">PARTNERING YOUR GROWTH</div>
        <div style="font-size:18px;font-weight:800;color:#062b66;text-align:center;text-transform:uppercase;letter-spacing:2px;margin-bottom:8px"><?php echo $resolvedTitle; ?></div>
        <div style="text-align:center;font-size:11px;color:#64748b;margin-bottom:20px">Date: <?php echo e($letter->sent_at?->format('d F Y') ?? now()->format('d F Y')); ?></div>
        <div style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px">
            <div><strong>Agent:</strong> <?php echo e($letter->user->name); ?></div>
            <div><strong>Email:</strong> <?php echo e($letter->user->email); ?></div>
            <?php if($letter->business): ?><div><strong>Business:</strong> <?php echo e($letter->business->name); ?></div><?php endif; ?>
        </div>
        <div style="font-weight:700;color:#062b66;margin-bottom:12px"><?php echo $resolvedGreeting; ?></div>
        <div style="white-space:pre-wrap;margin-bottom:20px;line-height:1.7"><?php echo $resolvedBody; ?></div>
        <div style="margin-top:20px"><?php echo $resolvedClosing; ?></div>
        <div style="margin-top:30px;border-top:1px solid #e2e8f0;padding-top:12px">
            <div style="font-weight:800;color:#062b66"><?php echo e($letter->signature_name); ?></div>
            <?php if($letter->signature_designation): ?><div style="color:#1557d6;font-weight:600;font-size:12px"><?php echo e($letter->signature_designation); ?></div><?php endif; ?>
            <?php if($letter->signature_company): ?><div style="color:#64748b;font-size:11px"><?php echo e($letter->signature_company); ?></div><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/sanctions/show.blade.php ENDPATH**/ ?>