<?php $__env->startSection('content'); ?>

<div class="page-head">
    <div>
        <div class="eyebrow">PLATFORM SETTINGS</div>
        <h1>Payment Configuration</h1>
        <p>Manage global payment gateway settings and environment modes.</p>
    </div>
</div>

<div class="panel form-panel">

    <div class="panel-head">
        <div>
            <h3>Payment Mode</h3>
            <p>Controls whether payment gateways operate in Sandbox or Live mode.</p>
        </div>
        <span class="badge <?php echo e($paymentMode === 'live' ? 'success' : 'neutral'); ?>">
            <?php echo e(strtoupper($paymentMode)); ?>

        </span>
    </div>

    <form method="POST" action="<?php echo e(route('settings.payment-mode.update')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-grid">
            <label>
                Payment Mode
                <select name="payment_mode">
                    <option value="sandbox" <?php if($paymentMode === 'sandbox'): echo 'selected'; endif; ?>>Sandbox</option>
                    <option value="live" <?php if($paymentMode === 'live'): echo 'selected'; endif; ?>>Live</option>
                </select>
            </label>
        </div>

        <div class="notice">
            Only Admin and Super Admin users can change this setting.
            Agents do not have access to this configuration.
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Payment Mode</button>
        </div>
    </form>
</div>

<?php if($gateways->isNotEmpty()): ?>
<div class="panel form-panel" style="margin-top:20px;">
    <div class="panel-head">
        <div>
            <h3>Payment Gateways</h3>
            <p>Configure gateway credentials, statuses and environments. Leave credential fields blank to keep their current secure values.</p>
        </div>
    </div>
    
    <div style="padding: 0 24px 24px;">
        <?php $__empty_1 = true; $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div style="border:1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 6px;">
            <form method="POST" action="<?php echo e(route('settings.gateways.update', $gateway)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px; flex-wrap: wrap; gap: 15px;">
                    <strong style="font-size:16px;"><?php echo e($gateway->name ?? 'Gateway #'.$gateway->id); ?></strong>
                    
                    <div style="display:flex; gap:15px; align-items:center; flex-wrap: wrap;">
                        <label style="margin:0;">
                            <input type="checkbox" name="status" value="1" <?php if($gateway->status): echo 'checked'; endif; ?>> Active
                        </label>
                        
                        <select name="environment" style="width:auto; padding:4px;">
                            <option value="sandbox" <?php if($gateway->environment === 'sandbox'): echo 'selected'; endif; ?>>Sandbox</option>
                            <option value="production" <?php if($gateway->environment === 'production'): echo 'selected'; endif; ?>>Production</option>
                        </select>
                        
                        <button type="submit" class="btn primary">Save Changes</button> 
                    </div>
                </div>

                <div class="form-grid">
                    <label>
                        Sandbox API Key / Merchant ID
                        <input type="text" name="credentials[sandbox_api_key]" value="<?php echo e($gateway->credentials['sandbox_api_key'] ?? ''); ?>" placeholder="Leave blank to keep unchanged">
                    </label>
                    <label>
                        Sandbox Secret Key
                        <input type="text" name="credentials[sandbox_api_secret]" value="<?php echo e($gateway->credentials['sandbox_api_secret'] ?? ''); ?>" placeholder="Leave blank to keep unchanged">
                    </label>
                    <label>
                        Production API Key / Merchant ID
                        <input type="text" name="credentials[production_api_key]" value="<?php echo e($gateway->credentials['production_api_key'] ?? ''); ?>" placeholder="Leave blank to keep unchanged">
                    </label>
                    <label>
                        Production Secret Key
                        <input type="text" name="credentials[production_api_secret]" value="<?php echo e($gateway->credentials['production_api_secret'] ?? ''); ?>" placeholder="Leave blank to keep unchanged">
                    </label>
                </div>
            </form>
            <form method="POST" action="<?php echo e(route('settings.gateways.destroy', $gateway)); ?>" style="margin-top: 15px; text-align: right;" onsubmit="return confirm('Are you sure you want to remove this gateway?');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn tiny secondary" style="color: #e53e3e; border-color: #e53e3e;">Remove Gateway</button>
            </form>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p>No gateways configured yet.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="panel form-panel" style="margin-top:20px;">
    <div class="panel-head">
        <div>
            <h3>Add New Payment Gateway</h3>
            <p>Register a new payment provider.</p>
        </div>
    </div>
    <form method="POST" action="<?php echo e(route('settings.gateways.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-grid">
            <label>
                Gateway Name (e.g. Stripe, Razorpay)
                <input type="text" name="name" required>
            </label>
            <label>
                Gateway Slug (e.g. stripe, razorpay)
                <input type="text" name="slug" required>
            </label>
        </div>
        <div class="form-actions">
            <button class="btn secondary">Add Gateway</button>
        </div>
    </form>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/settings/gateways.blade.php ENDPATH**/ ?>