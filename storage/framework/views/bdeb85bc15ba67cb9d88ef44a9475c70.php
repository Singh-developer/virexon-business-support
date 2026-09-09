<?php $__env->startSection('content'); ?>

<?php
    $detail    = $agent->detail;
    $refs      = $agent->referencePersons ?? collect();
    $appStatus = optional($detail)->application_status ?? 'pending';

    $statusColor = match($appStatus) {
        'approved' => 'success',
        'rejected' => 'failed',
        default    => 'pending',
    };
?>

<div class="page-head">
    <div>
        <div class="eyebrow">AGENT MANAGEMENT</div>
        <h1><?php echo e($agent->exists ? $agent->name : 'Create Agent'); ?></h1>
        <p><?php echo e($agent->exists ? 'Manage agent account, application status, and view submitted details.' : 'Create a new agent account. Virtual card is created separately.'); ?></p>
    </div>
    <?php if($agent->exists): ?>
    <span class="badge <?php echo e($statusColor); ?>"><?php echo e(ucfirst($appStatus)); ?></span>
    <?php endif; ?>
</div>

<style>
    .agent-detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0; border: 1px solid #f1f5f9; border-radius: 0 0 10px 10px; overflow: hidden; }
    .agent-detail-grid .detail-item { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; }
    .agent-detail-grid .detail-item:nth-child(odd) { border-right: 1px solid #f1f5f9; }
    .detail-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 4px; }
    .detail-value { font-size: 13px; font-weight: 600; color: #1e293b; }
    .detail-value.empty { color: #cbd5e1; font-style: italic; font-weight: 400; }
    .agent-section-head { grid-column: 1 / -1; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 16px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #64748b; }
    @media(max-width:600px){ .agent-detail-grid { grid-template-columns: 1fr; } .agent-detail-grid .detail-item { border-right: none !important; } }
    .settings-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .settings-card:last-child { margin-bottom: 0; }
    .settings-card-title { font-weight: 800; font-size: 12px; text-transform: uppercase; color: #475569; margin-bottom: 16px; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px; }
    .info-banner { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; margin-top: 16px; font-size: 12px; color: #1e40af; font-weight: 600; }
</style>

<?php
    function agentDv($val) { return filled($val) ? e($val) : '<span class="empty">—</span>'; }
?>



<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Account Details</h3>
            <p>Login credentials and account assignment.</p>
        </div>
    </div>

    <form method="POST" action="<?php echo e($agent->exists ? route('agents.update', $agent) : route('agents.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($agent->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        <div class="form-grid">

            <label>
                Full Name
                <input name="name" value="<?php echo e(old('name', $agent->name)); ?>" style="color:#1e293b" required>
            </label>

            <label>
                Official Email
                <input name="email" type="email" value="<?php echo e(old('email', $agent->email)); ?>" style="color:#1e293b" required>
            </label>

            <label>
                Phone
                <input name="phone" value="<?php echo e(old('phone', $agent->phone)); ?>" style="color:#1e293b">
            </label>

            <label>
                Business
                <select name="business_id" style="color:#1e293b" required>
                    <?php $__currentLoopData = $businesses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $business): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($business->id); ?>" <?php if(old('business_id', $agent->business_id) == $business->id): echo 'selected'; endif; ?>>
                            <?php echo e($business->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>
                Login Status
                <select name="status" style="color:#1e293b">
                    <option value="active"   <?php if(old('status', $agent->status ?? 'active') === 'active'): echo 'selected'; endif; ?>>Active</option>
                    <option value="inactive" <?php if(old('status', $agent->status) === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                </select>
            </label>

            <label>
                Password <?php if($agent->exists): ?><small>Leave blank to keep current password.</small><?php endif; ?>
                <div style="position: relative;">
                    <input name="password" id="agent-password" type="password" <?php echo e($agent->exists ? '' : 'required'); ?> autocomplete="new-password" style="color:#1e293b; width: 100%; padding-right: 40px; box-sizing: border-box;">
                    <button type="button" onclick="togglePassword('agent-password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; font-size: 1.2rem; padding: 0;">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
            </label>

            <?php if(!$agent->exists): ?>
            <label>
                Password Confirmation
                <div style="position: relative;">
                    <input name="password_confirmation" id="agent-password-confirm" type="password" required autocomplete="new-password" style="color:#1e293b; width: 100%; padding-right: 40px; box-sizing: border-box;">
                    <button type="button" onclick="togglePassword('agent-password-confirm', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; font-size: 1.2rem; padding: 0;">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
            </label>
            <?php endif; ?>
            
            <script>
                function togglePassword(inputId, btn) {
                    const input = document.getElementById(inputId);
                    if (input.type === 'password') {
                        input.type = 'text';
                        btn.querySelector('.eye-icon').innerText = '🙈';
                    } else {
                        input.type = 'password';
                        btn.querySelector('.eye-icon').innerText = '👁️';
                    }
                }
            </script>

        </div>

        <div class="notice">
            One-agent-one-card rule: an Agent account is assigned exactly one virtual card. The database enforces this with a unique constraint on <code>virtual_cards.agent_id</code>.
        </div>

        <div class="form-actions">
            <a class="btn secondary" href="<?php echo e(route('agents.index')); ?>">Cancel</a>
            <button class="btn primary"><?php echo e($agent->exists ? 'Update Agent' : 'Create Agent'); ?></button>
        </div>
    </form>
</div>

<?php if($agent->exists): ?>


<?php
    $detail = $agent->detail;
?>
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Limit & Commission Settings</h3>
            <p>Set the maximum transaction limit and commission rate for this agent. These settings control how much the agent can transact and earn.</p>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('agents.update-limit-commission', $agent)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
        
        
        <div class="settings-card">
            <div class="settings-card-title">
                📊 Transaction Limit
            </div>
            
            <div class="form-grid">
                <label>
                    Maximum Transaction Limit (₹)
                    <input type="number" step="0.01" name="max_limit" 
                           value="<?php echo e(old('max_limit', $detail->max_limit ?? 500000)); ?>" 
                           min="0" max="999999999999.99"
                           style="color:#1e293b"
                           placeholder="e.g. 500000 for 5 Lakh">
                    <small style="color: #64748b; font-weight: 400;">Default maximum is ₹5,00,000 (5 Lakh)</small>
                </label>
            </div>
        </div>

        
        <div class="settings-card">
            <div class="settings-card-title">
                💵 Commission Settings
            </div>
            <div class="form-grid">
                <label>
                    Commission Type
                    <select name="commission_type" id="commission_type" style="color:#1e293b">
                        <option value="percentage" <?php if(old('commission_type', $detail->commission_type ?? 'percentage') === 'percentage'): echo 'selected'; endif; ?>>Percentage (%)</option>
                        <option value="fixed" <?php if(old('commission_type', $detail->commission_type ?? 'percentage') === 'fixed'): echo 'selected'; endif; ?>>Fixed Amount (₹)</option>
                    </select>
                </label>
                <label id="commission_rate_label">
                    Commission Rate (%)
                    <input type="number" step="0.0001" name="commission_rate" id="commission_rate" value="<?php echo e(old('commission_rate', $detail->commission_rate ?? 0)); ?>" min="0" max="100" style="color:#1e293b" placeholder="e.g. 2.5">
                </label>
                <label id="commission_fixed_label" style="display: none;">
                    Fixed Commission (₹)
                    <input type="number" step="0.01" name="commission_fixed" id="commission_fixed" value="<?php echo e(old('commission_fixed', $detail->commission_fixed ?? 0)); ?>" min="0" style="color:#1e293b" placeholder="e.g. 100">
                </label>
            </div>
            <div class="info-banner">
                <div style="font-weight: 700; margin-bottom: 6px;">Commission Preview (on ₹10,000 transaction)</div>
                <?php
                    $sampleAmount = 10000;
                    $preview = 0;
                    if (($detail->commission_type ?? 'percentage') === 'fixed') {
                        $preview = $detail->commission_fixed ?? 0;
                    } else {
                        $preview = $sampleAmount * (($detail->commission_rate ?? 0) / 100);
                    }
                ?>
                <div style="margin-top: 4px;">Estimated Commission: <strong style="color: #059669;">₹<?php echo e(number_format($preview, 2)); ?></strong></div>
            </div>
        </div>

        <div class="form-actions">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="color: #64748b; font-size: 12px;">Changes apply immediately to the agent's account.</span>
                <div style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                    <?php if($agent->virtualCard): ?>
                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 6px 10px;">
                        🃏 Card: <?php echo e($agent->virtualCard->last4 ?: $agent->virtualCard->reference); ?>

                    </span>
                    <?php endif; ?>
                    <select name="action" style="border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:12px; font-weight:600; color:#1e293b; background:#fff;">
                        <option value="save">Save Only</option>
                        <?php if(!$agent->virtualCard): ?>
                        <option value="save_and_card">Save & Create Virtual Card</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn primary">Save Limit & Commission Settings</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const commissionType = document.getElementById('commission_type');
    const rateLabel = document.getElementById('commission_rate_label');
    const fixedLabel = document.getElementById('commission_fixed_label');
    function toggleCommissionFields() {
        if (commissionType.value === 'fixed') {
            rateLabel.style.display = 'none';
            fixedLabel.style.display = 'block';
        } else {
            rateLabel.style.display = 'block';
            fixedLabel.style.display = 'none';
        }
    }
    commissionType.addEventListener('change', toggleCommissionFields);
    toggleCommissionFields();
});
</script>


<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Application Status</h3>
            <p>Approve or reject this agent's application. Setting <strong>"Form Received"</strong> will prompt the agent to upload their documents.</p>
        </div>
        <span class="badge <?php echo e($statusColor); ?>"><?php echo e(ucfirst($appStatus)); ?></span>
    </div>

    <form method="POST" action="<?php echo e(route('agents.application-status', $agent)); ?>" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <?php echo csrf_field(); ?>
        <select name="application_status" style="border:1px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:13px; font-weight:600; min-width:200px; color:#1e293b; background:#fff;">
            <option value="pending"       <?php if($appStatus === 'pending'): echo 'selected'; endif; ?>\>⏳ Pending</option>
            <option value="form_received" <?php if($appStatus === 'form_received'): echo 'selected'; endif; ?>>📋 Form Received — Request Docs</option>
            <option value="approved"      <?php if($appStatus === 'approved'): echo 'selected'; endif; ?>>✅ Approved</option>
            <option value="rejected"      <?php if($appStatus === 'rejected'): echo 'selected'; endif; ?>>❌ Rejected</option>
        </select>
        <button type="submit" class="btn primary" onclick="return confirm('Update this agent\'s application status?')">
            Save Status
        </button>
        <a href="<?php echo e(route('admin.documents.index', $agent->id)); ?>" class="btn secondary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            📁 Review Documents
        </a>
    </form>
</div>

<?php if($appStatus === 'approved'): ?>

<?php
    $activeAdvance = $agent->advances()->where('status', 'active')->first();
    $totalCommission = $agent->commissions()->sum('net_amount');
    $totalDeductions = $agent->commissions()->sum('advance_deduction');
?>
<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Advance & Commission</h3>
            <p>Issue an advance to the agent or process their commission payouts.</p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">Net Commissions Paid</div>
            <div style="font-size:16px;font-weight:700;color:#10b981;">₹<?php echo e(number_format($totalCommission, 2)); ?></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:20px;border-top:1px solid #f1f5f9; padding-top: 20px;">
        
        
        <div class="settings-card" style="margin-bottom:0;">
            <div class="settings-card-title">Issue Advance</div>
            
            <?php if($activeAdvance): ?>
                <div style="background:#fff;border:1px solid #bfdbfe;border-radius:8px;padding:12px;margin-bottom:12px;">
                    <div style="font-size:12px;color:#1e40af;font-weight:700;">Active Advance</div>
                    <div style="font-size:18px;font-weight:800;color:#1e293b;margin:4px 0;">₹<?php echo e(number_format($activeAdvance->outstanding_amount, 2)); ?> <span style="font-size:12px;font-weight:600;color:#64748b;">outstanding</span></div>
                    <div style="font-size:12px;color:#64748b;">Original: ₹<?php echo e(number_format($activeAdvance->total_amount, 2)); ?></div>
                    
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid #e2e8f0;font-size:12px;">
                        Repayment Method: 
                        <?php if($activeAdvance->repayment_type === 'unselected'): ?>
                            <strong style="color:#ef4444;">Not selected yet</strong>
                        <?php elseif($activeAdvance->repayment_type === 'one_time'): ?>
                            <strong>One-Time Deduction</strong>
                        <?php elseif($activeAdvance->repayment_type === 'emi'): ?>
                            <strong>EMI (₹<?php echo e(number_format($activeAdvance->emi_amount, 2)); ?> per payout)</strong>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <form action="<?php echo e(route('admin.advances.store', $agent->id)); ?>" method="POST" style="margin:0;">
                    <?php echo csrf_field(); ?>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">Advance Amount (₹)</label>
                            <input type="number" step="0.01" name="amount" required style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;">
                        </div>
                        <button type="submit" class="btn primary" style="width:100%;justify-content:center;">Issue Advance</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        
        <div class="settings-card" style="margin-bottom:0;">
            <div class="settings-card-title">Pay Commission</div>
            
            <?php if($activeAdvance && $activeAdvance->repayment_type === 'unselected'): ?>
                <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:12px;color:#991b1b;font-size:13px;">
                    <strong>Wait!</strong> The agent has an active advance but hasn't selected a repayment method. Commission cannot be processed until they choose how to repay it.
                </div>
            <?php else: ?>
                <?php
                    $commType = $detail->commission_type ?? 'percentage';
                    $commRate = $detail->commission_rate ?? 0;
                    $commFixed = $detail->commission_fixed ?? 0;
                ?>
                <div style="background:#fff;border:1px solid #d1fae5;border-radius:8px;padding:10px;margin-bottom:12px;font-size:11px;color:#065f46;">
                    <strong>Agent Commission:</strong>
                    <?php if($commType === 'fixed'): ?>
                        Fixed ₹<?php echo e(number_format($commFixed, 2)); ?> / transaction
                    <?php else: ?>
                        <?php echo e(number_format($commRate, 4)); ?>% of transaction
                    <?php endif; ?>
                </div>
                <form action="<?php echo e(route('admin.commissions.store', $agent->id)); ?>" method="POST" style="margin:0;">
                    <?php echo csrf_field(); ?>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">Transaction Amount (₹)</label>
                            <input type="number" step="0.01" name="transaction_amount" placeholder="Enter transaction amount..." style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;">
                            <small style="color:#64748b;font-size:10px;">Commission auto-calculated from agent's rate.</small>
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">Or Override Gross Commission (₹)</label>
                            <input type="number" step="0.01" name="gross_amount" placeholder="Override amount..." style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;">
                        </div>
                        <div>
                            <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block;">Description / Note</label>
                            <input type="text" name="description" placeholder="e.g. October Sales" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:14px;">
                        </div>
                        <?php if($activeAdvance): ?>
                        <div style="background:#eff6ff;color:#1e40af;font-size:11px;padding:8px;border-radius:6px;">
                            ℹ️ Advance deduction (<?php echo e($activeAdvance->repayment_type === 'emi' ? 'EMI' : 'Full'); ?>) will be auto-calculated and subtracted.
                        </div>
                        <?php endif; ?>
                        <button type="submit" class="btn primary" style="width:100%;justify-content:center;background:#10b981;border-color:#10b981;">Process Payout</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>



<div class="panel">
    <div class="panel-head">
        <div>
            <h3>Agent Application Details</h3>
            <p>Information submitted by the agent during registration. Read-only.</p>
        </div>
    </div>

    <div class="agent-detail-grid">

        
        <div class="agent-section-head">Personal Information</div>
        <div class="detail-item"><div class="detail-label">Full Name</div><div class="detail-value"><?php echo agentDv($agent->name); ?></div></div>
        <div class="detail-item"><div class="detail-label">Official Email</div><div class="detail-value"><?php echo agentDv($agent->email); ?></div></div>
        <div class="detail-item"><div class="detail-label">Personal Email</div><div class="detail-value"><?php echo agentDv(optional($detail)->personal_email); ?></div></div>
        <div class="detail-item"><div class="detail-label">Mobile</div><div class="detail-value"><?php echo agentDv(optional($detail)->mobile); ?></div></div>
        <div class="detail-item"><div class="detail-label">Guardian / Father Name</div><div class="detail-value"><?php echo agentDv(optional($detail)->guardian_name ?? optional($detail)->father_name); ?></div></div>
        <div class="detail-item"><div class="detail-label">Agent ID Number</div><div class="detail-value"><?php echo agentDv(optional($detail)->agent_id_number); ?></div></div>
        <div class="detail-item"><div class="detail-label">Date of Birth</div><div class="detail-value"><?php echo agentDv(optional($detail)->date_of_birth); ?></div></div>
        <div class="detail-item"><div class="detail-label">Gender</div><div class="detail-value"><?php echo agentDv(optional($detail)->gender ? ucfirst(optional($detail)->gender) : null); ?></div></div>
        <div class="detail-item"><div class="detail-label">PAN Number</div><div class="detail-value"><?php echo agentDv(optional($detail)->pan_number); ?></div></div>
        <div class="detail-item"><div class="detail-label">Marital Status</div><div class="detail-value"><?php echo agentDv(optional($detail)->is_married === 1 ? 'Married' : (optional($detail)->is_married === 0 ? 'Single' : null)); ?></div></div>

        
        <div class="agent-section-head">Address</div>
        <div class="detail-item" style="grid-column:1/-1">
            <div class="detail-label">Current Address</div>
            <div class="detail-value">
                <?php echo agentDv(optional($detail)->current_address); ?>

                <?php if(optional($detail)->address_line_2): ?>, <?php echo e(optional($detail)->address_line_2); ?><?php endif; ?>
            </div>
        </div>
        <div class="detail-item"><div class="detail-label">City</div><div class="detail-value"><?php echo agentDv(optional($detail)->current_city); ?></div></div>
        <div class="detail-item"><div class="detail-label">State</div><div class="detail-value"><?php echo agentDv(optional($detail)->current_state); ?></div></div>
        <div class="detail-item"><div class="detail-label">Pincode</div><div class="detail-value"><?php echo agentDv(optional($detail)->current_pincode); ?></div></div>

        
        <div class="agent-section-head">Bank Details</div>
        <div class="detail-item"><div class="detail-label">Account Holder</div><div class="detail-value"><?php echo agentDv(optional($detail)->account_name); ?></div></div>
        <div class="detail-item"><div class="detail-label">Bank Name</div><div class="detail-value"><?php echo agentDv(optional($detail)->bank_name); ?></div></div>
        <div class="detail-item"><div class="detail-label">Account Number</div><div class="detail-value"><?php echo agentDv(optional($detail)->account_number); ?></div></div>
        <div class="detail-item"><div class="detail-label">IFSC / Routing No.</div><div class="detail-value"><?php echo agentDv(optional($detail)->routing_number); ?></div></div>
        <div class="detail-item"><div class="detail-label">Account Type</div><div class="detail-value"><?php echo agentDv(optional($detail)->account_type); ?></div></div>
        <div class="detail-item"><div class="detail-label">Branch</div><div class="detail-value"><?php echo agentDv(optional($detail)->branch_name); ?></div></div>

        
        <div class="agent-section-head">Advance Details</div>
        <div class="detail-item">
            <div class="detail-label">Loan Amount Requested</div>
            <div class="detail-value">
                <?php echo e(optional($detail)->loan_amount ? '₹' . number_format(optional($detail)->loan_amount, 2) : '—'); ?>

            </div>
        </div>
        <div class="detail-item"><div class="detail-label">Purpose of Advance</div><div class="detail-value"><?php echo agentDv(optional($detail)->purpose_of_advance); ?></div></div>

    </div>

    
    <?php if($refs->count()): ?>
    <div style="border-top:1px solid #f1f5f9; margin-top:0;">
        <div style="background:#f8fafc; padding:10px 16px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#64748b;">
            References (<?php echo e($refs->count()); ?>)
        </div>
        <div style="padding:14px 16px; display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:12px;">
            <?php $__currentLoopData = $refs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $ref): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="border:1px solid #e2e8f0; border-radius:10px; padding:14px;">
                <div style="font-size:10px; font-weight:800; color:#94a3b8; text-transform:uppercase; margin-bottom:6px;">Reference <?php echo e($i + 1); ?></div>
                <div style="font-size:14px; font-weight:700; color:#1e293b; margin-bottom:4px;"><?php echo e($ref->person_name); ?></div>
                <div style="font-size:12px; color:#64748b;">📞 <?php echo e($ref->mobile); ?></div>
                <div style="font-size:11px; color:#94a3b8; margin-top:4px;">ID: <?php echo e($ref->company_agent_id); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php else: ?>
    <div style="padding:14px 16px; color:#94a3b8; font-size:12px; border-top:1px solid #f1f5f9;">
        No references submitted yet.
    </div>
    <?php endif; ?>
</div>

<?php endif; ?> 

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/agents/form.blade.php ENDPATH**/ ?>