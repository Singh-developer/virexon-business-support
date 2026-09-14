<?php $__env->startSection('content'); ?>

<style>
    /* Guarantee input text is always visible on this page */
    .profile-form input,
    .profile-form select,
    .profile-form textarea {
        color: #1e293b !important;
    }
    .profile-form input[readonly],
    .profile-form select[disabled],
    .profile-form input.locked {
        background-color: #f1f5f9 !important;
        cursor: not-allowed !important;
        opacity: 0.7;
    }
    .section-divider {
        grid-column: 1 / -1;
        border-top: 1px solid #e2e8f0;
        padding-top: 1rem;
        margin-top: 0.5rem;
    }
    .section-divider h3 {
        font-size: 0.875rem;
        font-weight: 700;
        color: #475569;
        margin: 0 0 0.25rem;
    }
    .section-divider p {
        font-size: 0.75rem;
        color: #94a3b8;
        margin: 0;
    }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">ACCOUNT SETTINGS</div>
        <h1>My Profile</h1>
        <p>View and update your account information.</p>
    </div>
</div>

<div class="panel form-panel">

    <?php if($isAgent): ?>
        <div class="mb-4 px-3 py-2 rounded-lg <?php echo e($isApproved ? 'bg-green-50 border border-green-200' : 'bg-orange-50 border border-orange-200'); ?>" style="font-size:12px; font-weight:600;">
            <?php if($isApproved): ?>
                <span style="color:#15803d;">✓ Application Approved — Some fields are locked and cannot be edited.</span>
            <?php else: ?>
                <span style="color:#c2410c;">⏳ Application Pending — You can view and edit all your details below.</span>
            <?php endif; ?>
        </div>

        
        
        
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('settings.profile.update')); ?>" class="profile-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="form-grid">

            
            <div class="section-divider">
                <h3>Account Information</h3>
                <p>Basic login details for your account</p>
            </div>

            <label>
                Full Name
                <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" style="color:#1e293b" required>
            </label>

            <label>
                Login Email (Official)
                <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" style="color:#1e293b" required>
            </label>

            <label>
                Phone
                <input type="text" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>" style="color:#1e293b">
            </label>

            <label>
                Role
                <input type="text" value="<?php echo e($user->role?->name); ?>" style="color:#1e293b" readonly>
            </label>

            <?php if($isAgent): ?>

            
            <div class="section-divider">
                <h3>Personal Details</h3>
                <p>
                    <?php if($isApproved): ?>
                        Locked after approval. Contact admin to make changes.
                    <?php else: ?>
                        Editable until admin approves your application.
                    <?php endif; ?>
                </p>
            </div>

            <label>
                Personal Email
                <input type="email" name="personal_email"
                    value="<?php echo e(old('personal_email', $detail?->personal_email)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter personal email">
            </label>

            <label>
                Mobile Number
                <input type="text" name="mobile"
                    value="<?php echo e(old('mobile', $detail?->mobile)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter mobile number">
            </label>

            <label>
                Guardian / Father Name
                <input type="text" name="guardian_name"
                    value="<?php echo e(old('guardian_name', $detail?->guardian_name ?? $detail?->father_name)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter guardian/father name"
                    <?php if($isApproved): ?> readonly class="locked" <?php endif; ?>>
            </label>

            <label>
                Agent ID Number
                <input type="text" name="agent_id_number"
                    value="<?php echo e(old('agent_id_number', $detail?->agent_id_number)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter agent ID"
                    <?php if($isApproved): ?> readonly class="locked" <?php endif; ?>>
            </label>

            <label>
                Date of Birth
                <input type="date" name="date_of_birth"
                    value="<?php echo e(old('date_of_birth', $detail?->date_of_birth)); ?>"
                    style="color:#1e293b"
                    <?php if($isApproved): ?> readonly class="locked" <?php endif; ?>>
            </label>

            <label>
                Gender
                <select name="gender" style="color:#1e293b" <?php if($isApproved): ?> disabled class="locked" <?php endif; ?>>
                    <option value="">Select Gender</option>
                    <option value="male"   <?php if(old('gender', $detail?->gender) === 'male'): echo 'selected'; endif; ?>>Male</option>
                    <option value="female" <?php if(old('gender', $detail?->gender) === 'female'): echo 'selected'; endif; ?>>Female</option>
                    <option value="other"  <?php if(old('gender', $detail?->gender) === 'other'): echo 'selected'; endif; ?>>Other</option>
                </select>
                <?php if($isApproved): ?>
                    <input type="hidden" name="gender" value="<?php echo e($detail?->gender); ?>">
                <?php endif; ?>
            </label>

            <label>
                PAN Number
                <input type="text" name="pan_number"
                    value="<?php echo e(old('pan_number', $detail?->pan_number)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter PAN number"
                    <?php if($isApproved): ?> readonly class="locked" <?php endif; ?>>
            </label>

            <label>
                Marital Status
                <select name="is_married" style="color:#1e293b" <?php if($isApproved): ?> disabled class="locked" <?php endif; ?>>
                    <option value="0" <?php if(old('is_married', $detail?->is_married) == '0'): echo 'selected'; endif; ?>>Single</option>
                    <option value="1" <?php if(old('is_married', $detail?->is_married) == '1'): echo 'selected'; endif; ?>>Married</option>
                </select>
                <?php if($isApproved): ?>
                    <input type="hidden" name="is_married" value="<?php echo e($detail?->is_married); ?>">
                <?php endif; ?>
            </label>

            
            <div class="section-divider">
                <h3>Address Details</h3>
                <p>Always editable — keep your address up to date.</p>
            </div>

            <label class="wide">
                Current Address
                <input type="text" name="current_address"
                    value="<?php echo e(old('current_address', $detail?->current_address)); ?>"
                    style="color:#1e293b"
                    placeholder="House No., Building, Street">
            </label>

            <label class="wide">
                Address Line 2
                <input type="text" name="address_line_2"
                    value="<?php echo e(old('address_line_2', $detail?->address_line_2)); ?>"
                    style="color:#1e293b"
                    placeholder="Area / Landmark (optional)">
            </label>

            <label>
                City
                <input type="text" name="current_city"
                    value="<?php echo e(old('current_city', $detail?->current_city)); ?>"
                    style="color:#1e293b"
                    placeholder="City / District">
            </label>

            <label>
                State
                <input type="text" name="current_state"
                    value="<?php echo e(old('current_state', $detail?->current_state)); ?>"
                    style="color:#1e293b"
                    placeholder="State">
            </label>

            <label>
                Pincode
                <input type="text" name="current_pincode"
                    value="<?php echo e(old('current_pincode', $detail?->current_pincode)); ?>"
                    style="color:#1e293b"
                    placeholder="Pin code">
            </label>

            
            <div class="section-divider">
                <h3>Bank Details</h3>
                <p>Always editable — used for reimbursements.</p>
            </div>

            <label>
                Account Holder Name
                <input type="text" name="account_name"
                    value="<?php echo e(old('account_name', $detail?->account_name)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter account holder name">
            </label>

            <label>
                Bank Name
                <input type="text" name="bank_name"
                    value="<?php echo e(old('bank_name', $detail?->bank_name)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter bank name">
            </label>

            <label>
                Account Number
                <input type="text" name="account_number"
                    value="<?php echo e(old('account_number', $detail?->account_number)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter account number">
            </label>

            <label>
                IFSC / Routing Number
                <input type="text" name="routing_number"
                    value="<?php echo e(old('routing_number', $detail?->routing_number)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter IFSC code">
            </label>

            <label>
                Account Type
                <input type="text" name="account_type"
                    value="<?php echo e(old('account_type', $detail?->account_type)); ?>"
                    style="color:#1e293b"
                    placeholder="Savings / Current">
            </label>

            <label>
                Branch Name
                <input type="text" name="branch_name"
                    value="<?php echo e(old('branch_name', $detail?->branch_name)); ?>"
                    style="color:#1e293b"
                    placeholder="Enter branch name">
            </label>

            <?php endif; ?> 

        </div>

        <div class="form-actions">
            <button type="submit" class="btn primary">
                Save Changes
            </button>
        </div>

    </form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/settings/profile.blade.php ENDPATH**/ ?>