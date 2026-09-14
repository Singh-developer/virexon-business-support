
<form method="GET" action="<?php echo e(url()->current()); ?>" style="display:flex;justify-content:flex-end;margin:0 0 10px;">
    <?php $__currentLoopData = request()->except(['per_page', 'page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(is_string($value) || is_numeric($value)): ?>
            <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php echo $__env->make('partials.per-page-fields', ['perPage' => $perPage ?? 20, 'options' => $options ?? [20, 40, 100]], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</form>
<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/partials/per-page.blade.php ENDPATH**/ ?>