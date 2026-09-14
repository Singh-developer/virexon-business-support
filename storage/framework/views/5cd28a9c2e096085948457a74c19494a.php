
<?php
    $perPageCurrent = $perPage ?? 20;
    $perPageOptions = $options ?? [20, 40, 100];
?>
<label style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#64748b;white-space:nowrap;margin:0;">
    Show
    <select name="per_page" onchange="this.form.submit()" style="border:1px solid #d9e0e8;border-radius:8px;padding:8px 10px;font-size:12px;font-weight:700;color:#1e293b;background:#fff;cursor:pointer;">
        <?php $__currentLoopData = $perPageOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($option); ?>" <?php if((int) $perPageCurrent === (int) $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    entries
</label>
<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/partials/per-page-fields.blade.php ENDPATH**/ ?>