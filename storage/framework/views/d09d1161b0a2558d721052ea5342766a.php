<?php $__env->startSection('content'); ?>
<style>
/* Stunning Pro Design Variables & Base */
:root {
    --primary: #1a56db;
    --primary-hover: #1e40af;
    --bg-light: #f8fafc;
    --card-bg: #ffffff;
    --border: #e2e8f0;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --focus-ring: rgba(26, 86, 219, 0.2);
}
.page-head { margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; }
.page-head h1 { font-size: 24px; font-weight: 800; color: var(--text-main); margin-bottom: 4px; }
.page-head .eyebrow { font-size: 12px; font-weight: 700; color: var(--primary); letter-spacing: 0.1em; text-transform: uppercase; }

/* Grid & Cards */
.form-container { display: flex; flex-direction: column; gap: 24px; margin-bottom: 24px; }
.section-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; }
.card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); }
.card-title { font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid var(--bg-light); padding-bottom: 12px; }

/* Form Controls */
.field { margin-bottom: 16px; }
.field label { display: block; font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 8px; }
.field input, .field select, .field textarea { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; color: var(--text-main); background: #fff; transition: all 0.2s ease; }
.field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--focus-ring); }
.field input[readonly], .field input:disabled { background: var(--bg-light); color: var(--text-muted); cursor: not-allowed; }
.field textarea { resize: vertical; min-height: 80px; }
.field .hint { color: var(--text-muted); font-size: 12px; margin-top: 6px; line-height: 1.4; }

.row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

/* Signature Upload */
.sig-upload-box { border: 2px dashed #cbd5e1; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.2s; background: #f8fafc; }
.sig-upload-box:hover { border-color: var(--primary); background: #eff6ff; }
.sig-upload-box input { display: none; }
.sig-upload-icon { font-size: 24px; margin-bottom: 8px; }

/* Action Bar */
.action-bar { background: var(--card-bg); padding: 16px 24px; border-radius: 12px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: flex-end; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
.btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; border: none; }
.btn.primary { background: var(--primary); color: white; }
.btn.primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
.btn.secondary { background: white; color: var(--text-main); border: 1px solid var(--border); }
.btn.secondary:hover { background: var(--bg-light); }

/* PDF Preview */
.preview-container { margin-top: 24px; }
.preview-toolbar { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; background: white; border: 1px solid var(--border); border-radius: 12px 12px 0 0; }
.preview-wrap { height: 800px; background: radial-gradient(circle at 50% -20%, #334155, #0f172a); padding: 24px; border-radius: 0 0 12px 12px; }
iframe#previewFrame { width: 100%; height: 100%; border: none; border-radius: 8px; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }

.indicator { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--text-main); }
.indicator .dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; }
.indicator.updating .dot { background: #f59e0b; animation: pulse 1s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

/* Responsive adjustments */
@media(max-width: 768px) {
    .row-2, .row-3 { grid-template-columns: 1fr; gap: 12px; }
    .action-bar { flex-direction: column; align-items: stretch; }
    .action-bar label { margin-bottom: 12px; }
}
</style>

<?php
    $prefillName = $selectedAgent?->name ?? '';
    $prefillAgentId = $selectedAgent?->detail?->agent_id_number ?? '';
    $prefillAddress = '';
    if ($selectedAgent?->detail?->current_address) {
        $prefillAddress = $selectedAgent->detail->current_address;
        if ($selectedAgent->detail->current_city) $prefillAddress .= ', ' . $selectedAgent->detail->current_city;
        if ($selectedAgent->detail->current_state) $prefillAddress .= ', ' . $selectedAgent->detail->current_state;
        if ($selectedAgent->detail->current_pincode) $prefillAddress .= ' - ' . $selectedAgent->detail->current_pincode;
    }
?>

<div class="page-head">
    <div>
        <div class="eyebrow">SANCTION LETTERS</div>
        <h1>Compose New Letter</h1>
        <p style="color:var(--text-muted); font-size:14px; margin-top:4px;">Fill out the details below to generate and send a Sanction Letter to an agent.</p>
    </div>
    <a class="btn secondary" href="<?php echo e(route('sanctions.index')); ?>">← All Letters</a>
</div>

<form id="sanctionForm" method="POST" action="<?php echo e(route('sanctions.store')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <input type="hidden" id="dynamicFieldsJson" name="dynamic_fields" value="">

    <div class="form-container">
        
        <!-- ROW 1: Agent Info & Sanction Details -->
        <div class="section-grid">
            <!-- Agent Info Card -->
            <div class="card">
                <div class="card-title">1. Agent Information</div>
                <div class="field">
                    <label for="user_id">Select Agent</label>
                    <select id="user_id" name="user_id" required>
                        <option value="">— Choose an agent —</option>
                        <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($agent->id); ?>"
                            data-name="<?php echo e($agent->name); ?>"
                            data-agent-id="<?php echo e($agent->detail?->agent_id_number ?? ''); ?>"
                            data-loan-amount="<?php echo e($agent->detail?->loan_amount ?? ''); ?>"
                            data-address="<?php echo e(($agent->detail?->current_address ?? '') . ($agent->detail?->current_city ? ', ' . $agent->detail->current_city : '') . ($agent->detail?->current_state ? ', ' . $agent->detail->current_state : '') . ($agent->detail?->current_pincode ? ' - ' . $agent->detail->current_pincode : '')); ?>"
                            data-email="<?php echo e($agent->email); ?>"
                            data-pemail="<?php echo e($agent->detail?->personal_email ?? ''); ?>"
                            data-business="<?php echo e($agent->business?->name ?? ''); ?>"
                            <?php echo e(($selectedAgent?->id ?? old('user_id')) == $agent->id ? 'selected' : ''); ?>>
                            <?php echo e($agent->name); ?> — <?php echo e($agent->email); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div class="row-2">
                    <div class="field">
                        <label for="agent_name">Agent Name</label>
                        <input id="agent_name" name="agent_name" type="text" value="<?php echo e(old('agent_name', $prefillName)); ?>" readonly placeholder="Auto-filled">
                    </div>
                    <div class="field">
                        <label for="agent_id_display">Agent ID</label>
                        <input id="agent_id_display" type="text" value="<?php echo e(old('agent_id_display', $prefillAgentId)); ?>" readonly placeholder="Auto-filled">
                    </div>
                </div>

                <div class="row-2">
                    <div class="field">
                        <label for="guardian_relation">Guardian Relation</label>
                        <select id="guardian_relation" name="guardian_relation">
                            <option value="S/o" <?php echo e(old('guardian_relation', 'S/o') === 'S/o' ? 'selected' : ''); ?>>S/o (Son of)</option>
                            <option value="D/o" <?php echo e(old('guardian_relation') === 'D/o' ? 'selected' : ''); ?>>D/o (Daughter of)</option>
                            <option value="W/o" <?php echo e(old('guardian_relation') === 'W/o' ? 'selected' : ''); ?>>W/o (Wife of)</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="sanction_date">Sanction Date</label>
                        <input id="sanction_date" name="sanction_date" type="date" required value="<?php echo e(old('sanction_date', date('Y-m-d'))); ?>">
                    </div>
                </div>

                <div class="field">
                    <label for="letter_address">Recipient Address</label>
                    <textarea id="letter_address" name="letter_address" required rows="3" placeholder="Auto-filled — editable for letter"><?php echo e(old('letter_address', $prefillAddress)); ?></textarea>
                    <div class="hint">Changes here only affect the PDF, not the agent's actual profile.</div>
                </div>
            </div>

            <!-- Sanction Details Card -->
            <div class="card">
                <div class="card-title">2. Sanction Financials</div>
                <div class="field">
                    <label for="sanction_number_display">Sanction Letter No.</label>
                    <input id="sanction_number_display" type="text" value="<?php echo e($nextSanctionNumber); ?>" readonly>
                    <div class="hint">Reference No: <code><?php echo e($nextReferenceNumber); ?></code></div>
                </div>
                <div class="row-2">
                    <div class="field">
                        <label for="approved_amount">Approved Amount (₹)</label>
                        <input id="approved_amount" name="approved_amount" type="number" step="0.01" min="0" required value="<?php echo e(old('approved_amount', optional($selectedAgent?->detail)->loan_amount ?? '')); ?>" placeholder="0.00">
                    </div>
                    <div class="field">
                        <label for="disbursement_mode">Disbursement Mode</label>
                        <select id="disbursement_mode" name="disbursement_mode" required>
                            <option value="">— Choose mode —</option>
                            <?php $__currentLoopData = $disbursementModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($mode); ?>" <?php echo e(old('disbursement_mode') === $mode ? 'selected' : ''); ?>><?php echo e($mode); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                
                <div class="row-2">
                    <div class="field">
                        <label for="tenure">Tenure (Months)</label>
                        <input id="tenure" name="tenure" type="number" min="1" step="1" required value="<?php echo e(old('tenure')); ?>" placeholder="e.g. 12">
                    </div>
                    <div class="field">
                        <label for="process_fee">Process Fee (₹)</label>
                        <input id="process_fee" name="process_fee" type="number" step="0.01" min="0" value="<?php echo e(old('process_fee')); ?>" placeholder="0.00">
                    </div>
                </div>

                <div class="field">
                    <label for="monthly_principal_settlement">Monthly Principal Settlement (₹)</label>
                    <input id="monthly_principal_settlement" name="monthly_principal_settlement" type="number" step="0.01" min="0" required value="<?php echo e(old('monthly_principal_settlement')); ?>" placeholder="0.00">
                </div>
                <div class="field">
                    <label for="monthly_portal_service_charges">Monthly Portal &amp; Service Charges (₹)</label>
                    <input id="monthly_portal_service_charges" name="monthly_portal_service_charges" type="number" step="0.01" min="0" required value="<?php echo e(old('monthly_portal_service_charges')); ?>" placeholder="0.00">
                </div>
                <div class="field">
                    <label for="total_monthly_settlement">Total Monthly Settlement (₹)</label>
                    <input id="total_monthly_settlement" type="text" value="" readonly style="background: #e0f2fe; color: #0369a1; font-weight: bold; border-color: #bae6fd;">
                </div>
            </div>
        </div>

        <!-- ROW 2: Letter Content & Signatory -->
        <div class="section-grid">
            <!-- Content Card -->
            <div class="card">
                <div class="card-title">3. Letter Content</div>
                <div class="row-2">
                    <div class="field">
                        <label for="title">Document Title</label>
                        <input id="title" name="title" type="text" required maxlength="200" value="<?php echo e(old('title', 'Sanction Letter A/F')); ?>">
                    </div>
                    <div class="field">
                        <label for="subject">Email Subject</label>
                        <input id="subject" name="subject" type="text" required maxlength="255" value="<?php echo e(old('subject', 'Your Advance Fund Sanction Letter from Virexon')); ?>">
                    </div>
                </div>
                <div class="row-2">
                    <div class="field">
                        <label for="greeting">Greeting / Salutation</label>
                        <input id="greeting" name="greeting" type="text" required value="<?php echo e(old('greeting', 'Dear Sir/Madam,')); ?>">
                    </div>
                    <div class="field">
                        <label for="closing">Closing Line</label>
                        <input id="closing" name="closing" type="text" required value="<?php echo e(old('closing', 'Yours sincerely,')); ?>">
                    </div>
                </div>
                <div class="field">
                    <label for="body">Body of the Letter</label>
                    <textarea id="body" name="body" required rows="4"><?php echo e(old('body', 'With reference to your application/request for financial support and subject to the terms and conditions of the applicable [[bold:Agent Agreement / Advance Fund Agreement]], we are pleased to inform you that the Company has approved the following Advance Fund in your favour:')); ?></textarea>
                    <div class="hint">Use <code>[[field_name]]</code> syntax (e.g., <code>[[agent_name]]</code>, <code>[[approved_amount]]</code>).</div>
                </div>
                <div class="field">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Optional notes to appear in the PDF"><?php echo e(old('notes', '')); ?></textarea>
                </div>
            </div>

            <!-- Signatory Card -->
            <div class="card">
                <div class="card-title">4. Signature / Stamp</div>
                <div class="field">
                    <label>Upload Signature Image</label>
                    <div style="display:flex; gap:16px;">
                        <label class="sig-upload-box" id="sigUploadBox" style="flex:1;">
                            <input type="file" name="signature_image" id="sigImage" accept="image/png,image/jpeg,image/svg+xml">
                            <div class="sig-upload-icon">✍️</div>
                            <div style="width:54px;height:54px;margin:4px auto 10px;border:2px solid rgba(225,29,72,.4);border-radius:12px;background:rgba(254,242,242,.6);display:flex;align-items:center;justify-content:center;transform:rotate(-6deg);box-shadow:0 1px 3px rgba(0,0,0,.15);"><span style="font-size:9px;font-weight:700;letter-spacing:.5px;line-height:1.2;color:rgba(185,28,28,.8);text-align:center;">SAMPLE<br>STAMP</span></div>
                            <div style="font-size:12px; color:var(--text-muted);">Click to upload PNG/JPG</div>
                        </label>
                        <div style="flex:1;">
                            <?php if(!empty($existingSignature)): ?>
                            <div id="sigPreview">
                                <img src="<?php echo e(asset('storage/' . $existingSignature)); ?>" alt="Signature" style="max-height:80px; border:1px solid #e2e8f0; border-radius:8px;">
                            </div>
                            <input type="hidden" name="existing_signature_image" id="existingSigInput" value="<?php echo e($existingSignature); ?>">
                            <?php else: ?>
                            <div id="sigPreview" style="display:none;"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; color:var(--text-main); margin-right:auto;">
                <input type="checkbox" name="send_email" id="send_email" value="1" <?php echo e(old('send_email') ? 'checked' : ''); ?> style="width:18px; height:18px; accent-color:var(--primary);">
                Send PDF to Agent via Email
            </label>
            <button type="button" id="downloadBtn" class="btn secondary">⬇ Download PDF</button>
            <button type="submit" class="btn primary">✉ Generate &amp; Save</button>
        </div>

    </div>
</form>

<!-- Full Width PDF Preview -->
<div class="preview-container">
    <div class="preview-toolbar">
        <div class="indicator" id="previewIndicator"><span class="dot"></span> Live PDF Preview</div>
        <div style="font-size:12px;color:var(--text-muted);" id="previewStatus">Waiting...</div>
    </div>
    <div class="preview-wrap">
        <iframe id="previewFrame" src="about:blank"></iframe>
    </div>
</div>

<script>
(function(){
var form = document.getElementById('sanctionForm');
var iframe = document.getElementById('previewFrame');
var indicator = document.getElementById('previewIndicator');
var statusEl = document.getElementById('previewStatus');
var userSelect = document.getElementById('user_id');
var downloadBtn = document.getElementById('downloadBtn');
var sigImage = document.getElementById('sigImage');
var sigPreview = document.getElementById('sigPreview');
var sigUploadBox = document.getElementById('sigUploadBox');
var agentNameEl = document.getElementById('agent_name');
var agentIdDisplayEl = document.getElementById('agent_id_display');
var letterAddressEl = document.getElementById('letter_address');
var approvedAmountEl = document.getElementById('approved_amount');

var timer = null;
var dynamicFields = [];

// Prevent accidental page reloads: Enter inside an input must not submit the form,
// otherwise the page POSTs to sanctions.store and wipes everything the user typed.
form.addEventListener('keydown', function(e){
    if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'checkbox' && e.target.type !== 'radio') {
        e.preventDefault();
    }
});

// Signature image preview
sigImage.addEventListener('change', function(){
    var file = this.files[0];
    if(!file) return;
    var existingSigInput = document.getElementById('existingSigInput');
    if(existingSigInput) existingSigInput.value = '';
    var reader = new FileReader();
    reader.onload = function(e){
        sigPreview.style.display = 'block';
        sigPreview.innerHTML = '<img src="'+e.target.result+'" alt="Signature preview" style="max-height:80px; border:1px solid #e2e8f0; border-radius:8px;">';
    };
    reader.readAsDataURL(file);
});

sigUploadBox.addEventListener('dragover', function(e){ e.preventDefault(); this.style.borderColor = '#1a56db'; });
sigUploadBox.addEventListener('dragleave', function(){ this.style.borderColor = '#cbd5e1'; });
sigUploadBox.addEventListener('drop', function(e){
    e.preventDefault(); this.style.borderColor = '#cbd5e1';
    if(e.dataTransfer.files.length) { sigImage.files = e.dataTransfer.files; sigImage.dispatchEvent(new Event('change')); }
});

function updateAgentInfo(){
    var opt = userSelect.options[userSelect.selectedIndex];
                    if(!opt || !opt.value){
        agentNameEl.value = '';
        agentIdDisplayEl.value = '';
        letterAddressEl.value = '';
        approvedAmountEl.value = '';
        return;
    }
    agentNameEl.value = opt.getAttribute('data-name') || '';
    agentIdDisplayEl.value = opt.getAttribute('data-agent-id') || '';
    if(!letterAddressEl.value || letterAddressEl.dataset.touched !== '1'){
        letterAddressEl.value = opt.getAttribute('data-address') || '';
        letterAddressEl.dataset.touched = '0';
    }
    if(!approvedAmountEl.value || approvedAmountEl.dataset.touched !== '1'){
        approvedAmountEl.value = opt.getAttribute('data-loan-amount') || '';
        approvedAmountEl.dataset.touched = '0';
    }
}

function recalcTotal(){
    var principal = parseFloat(document.getElementById('monthly_principal_settlement').value) || 0;
    var charges  = parseFloat(document.getElementById('monthly_portal_service_charges').value) || 0;
    var totalEl  = document.getElementById('total_monthly_settlement');
    totalEl.value = (principal + charges).toFixed(2);
}

function gatherFormData(){
    var fd = new FormData(form);
    fd.delete('dynamic_fields');
    fd.set('dynamic_fields', JSON.stringify(dynamicFields));
    return fd;
}

function refreshPreview(){
    if(!userSelect.value){
        iframe.src = 'about:blank';
        statusEl.textContent = 'Select an agent first';
        return;
    }
    if(!document.getElementById('approved_amount').value ||
       !document.getElementById('disbursement_mode').value ||
       !document.getElementById('tenure').value ||
       !document.getElementById('monthly_principal_settlement').value ||
       !document.getElementById('monthly_portal_service_charges').value ||
       !document.getElementById('letter_address').value){
        statusEl.textContent = 'Complete the sanction details to preview';
        return;
    }
    indicator.classList.add('updating');
    statusEl.textContent = 'Generating PDF...';
    var formData = gatherFormData();
    fetch("<?php echo e(route('sanctions.preview')); ?>", {
        method:'POST',
        headers:{'X-CSRF-TOKEN':document.querySelector('input[name=_token]').value,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body: formData
    }).then(function(r){
        if(!r.ok) return r.json().then(function(e){ throw new Error(e.message || 'Validation failed'); });
        return r.json();
    })
    .then(function(data){
        if(data.pdf){ iframe.src = data.pdf; statusEl.textContent = 'Preview ready'; }
    }).catch(function(err){
        console.error('Preview error', err);
        statusEl.textContent = 'Preview error';
    }).finally(function(){
        indicator.classList.remove('updating');
    });
}

function debounceRefresh(){
    clearTimeout(timer);
    timer = setTimeout(refreshPreview, 500);
}

letterAddressEl.addEventListener('input', function(){ letterAddressEl.dataset.touched = '1'; });

['input','change'].forEach(function(e){ form.addEventListener(e, debounceRefresh, true); });
document.getElementById('monthly_principal_settlement').addEventListener('input', recalcTotal);
document.getElementById('monthly_portal_service_charges').addEventListener('input', recalcTotal);
userSelect.addEventListener('change', function(){ updateAgentInfo(); recalcTotal(); refreshPreview(); });

updateAgentInfo();
recalcTotal();
if(userSelect.value) refreshPreview();

downloadBtn.addEventListener('click', function(){
    if(!userSelect.value){ alert('Please select an agent first.'); return; }
    if(!document.getElementById('approved_amount').value ||
       !document.getElementById('disbursement_mode').value ||
       !document.getElementById('tenure').value ||
       !document.getElementById('monthly_principal_settlement').value ||
       !document.getElementById('monthly_portal_service_charges').value ||
       !document.getElementById('letter_address').value){
        alert('Please complete the sanction details before downloading.');
        return;
    }
    var orig = form.action;
    form.action = "<?php echo e(route('sanctions.download-pdf')); ?>";
    form.target = '_blank';
    form.submit();
    setTimeout(function(){ form.action = orig; form.target = '_self'; }, 100);
});
})();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/sanctions/create.blade.php ENDPATH**/ ?>