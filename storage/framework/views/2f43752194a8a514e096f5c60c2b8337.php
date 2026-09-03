

<?php $__env->startSection('content'); ?>

<div class="page-head">

    <div>

        <div class="eyebrow">
            ACCOUNT SETTINGS
        </div>

        <h1>
            My Settings
        </h1>

        <p>
            Update your own account information.
        </p>

    </div>

</div>

<div class="panel form-panel">

    <form
        method="POST"
        action="<?php echo e(route('settings.profile.update')); ?>"
    >

        <?php echo csrf_field(); ?>

        <?php echo method_field('PUT'); ?>

        <div class="form-grid">

            <label>

                Name

                <input
                    type="text"
                    name="name"
                    value="<?php echo e(old('name', $user->name)); ?>"
                    required
                >

            </label>


            <label>

                Email

                <input
                    type="email"
                    name="email"
                    value="<?php echo e(old('email', $user->email)); ?>"
                    required
                >

            </label>


            <label>

                Phone

                <input
                    type="text"
                    name="phone"
                    value="<?php echo e(old('phone', $user->phone)); ?>"
                >

            </label>


            <label>

                Role

                <input
                    type="text"
                    value="<?php echo e($user->role?->name); ?>"
                    readonly
                >

            </label>

        </div>

        <div class="notice">

            You can edit your own profile information.
            Platform payment configuration is available only to Admin users.

        </div>

        <div class="form-actions">

            <button class="btn primary">
                Save Changes
            </button>

        </div>

    </form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views\settings\profile.blade.php ENDPATH**/ ?>