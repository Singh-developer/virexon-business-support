<?php $__env->startSection('content'); ?><div class="page-head">
    <div>
        <div class="eyebrow">AGENT MANAGEMENT</div>
        <h1></h1>
        <h1><?php echo e($agent->exists ? 'Update Agent' : 'Create Agent'); ?></h1>
        <p><?php echo e($agent->exists ? 'Admins can update all agent details and the assigned business.' : 'Admin creates the Agent account first. A virtual card is created separately by an authorized Admin.'); ?></p>
    </div>
</div>
<div class="panel form-panel">
    <form method="POST" action="<?php echo e($agent->exists ? route('agents.update',$agent) : route('agents.store')); ?>"><?php echo csrf_field(); ?> <?php if($agent->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><div class="form-grid"><label>Full Name<input name="name" value="<?php echo e(old('name',$agent->name)); ?>" required></label><label>Email<input name="email" type="email" value="<?php echo e(old('email',$agent->email)); ?>" required></label><label>Phone<input name="phone" value="<?php echo e(old('phone',$agent->phone)); ?>"></label><label>Business<select name="business_id" required><?php $__currentLoopData = $businesses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $business): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($business->id); ?>" <?php if(old('business_id',$agent->business_id)==$business->id): echo 'selected'; endif; ?>><?php echo e($business->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><label>Status<select name="status">
                    <option value="active" <?php if(old('status',$agent->status ?: 'active')==='active'): echo 'selected'; endif; ?>>Active</option>
                    <option value="inactive" <?php if(old('status',$agent->status)==='inactive'): echo 'selected'; endif; ?>>Inactive</option>
                </select></label><label>Password <?php if($agent->exists): ?><small>Leave blank to keep current password.</small><?php endif; ?><input name="password" type="password" <?php echo e($agent->exists ? '' : 'required'); ?> autocomplete="new-password"></label><label>Password Confirmation<input name="password_confirmation" type="password" <?php echo e($agent->exists ? '' : 'required'); ?> autocomplete="new-password"></label></div>
        <div class="notice">One-agent-one-card rule: an Agent account is assigned exactly one virtual card. The database enforces this with a unique constraint on <code>virtual_cards.agent_id</code>.</div>
        <div class="form-actions"><a class="btn secondary" href="<?php echo e(route('agents.index')); ?>">Cancel</a><button class="btn primary"><?php echo e($agent->exists ? 'Update Agent' : 'Create Agent + Card'); ?></button></div>
    </form>
</div><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/agents/form.blade.php ENDPATH**/ ?>