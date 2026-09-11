<?php $__env->startSection('content'); ?>
<style>
.al-grid{display:flex;flex-direction:column;gap:24px;align-items:stretch;}
.al-form label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px}
.al-form input,.al-form select,.al-form textarea{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;color:#1e293b;background:#fff;font-family:inherit;transition:border-color .15s,box-shadow .15s}
.al-form input:focus,.al-form select:focus,.al-form textarea:focus{outline:none;border-color:#1557d6;box-shadow:0 0 0 3px rgba(21,87,214,.12)}
.al-form textarea{resize:vertical;min-height:100px;line-height:1.55}
.al-form .field{margin-bottom:14px}
.al-form .row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.al-form .hint{color:#64748b;font-size:12px;margin-top:4px}
.al-form .recipient-info{background:linear-gradient(135deg,#f0f7ff,#ecfdf5);border:1px solid #bfdbfe;border-radius:8px;padding:12px 14px;font-size:13px;color:#1e40af;margin-bottom:14px}
.preview-wrap{background:#475569;padding:18px;border-radius:10px;height:calc(100vh - 180px);display:flex;flex-direction:column;overflow:hidden}
.preview-toolbar{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:10px 16px;margin-bottom:12px;display:flex;gap:10px;align-items:center;justify-content:space-between;flex-shrink:0}
.indicator{display:inline-flex;align-items:center;gap:8px;font-size:13px;color:#64748b}
.indicator .dot{width:8px;height:8px;border-radius:50%;background:#10b981}
.indicator.updating .dot{background:#f59e0b;animation:pulse 1s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
.action-bar{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.action-bar .btn{padding:11px 22px;font-size:14px}
.al-section-title{font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:#64748b;margin:16px 0 8px;font-weight:700}
/* Dynamic fields */
.df-row{display:grid;grid-template-columns:1fr 1.5fr 36px;gap:8px;align-items:center;margin-bottom:6px}
.df-row input{padding:8px 10px;font-size:13px;border:1px solid #cbd5e1;border-radius:7px;width:100%}
.df-remove{width:34px;height:34px;border-radius:7px;border:1px solid #fca5a5;background:#fef2f2;color:#dc2626;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.df-remove:hover{background:#fee2e2}
.df-add-btn{padding:7px 14px;background:#eff6ff;border:1px solid #bfdbfe;color:#1557d6;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;margin-top:4px}
.df-add-btn:hover{background:#dbeafe}
/* Signature upload */
.sig-upload-wrap{display:flex;gap:14px;align-items:flex-start;margin-top:4px}
.sig-upload-box{border:2px dashed #cbd5e1;border-radius:10px;padding:16px;text-align:center;cursor:pointer;flex:1;transition:border-color .2s,background .2s}
.sig-upload-box:hover,.sig-upload-box.dragover{border-color:#1557d6;background:#eff6ff}
.sig-upload-box input{display:none}
.sig-upload-icon{font-size:28px;color:#94a3b8;margin-bottom:6px}
.sig-upload-text{font-size:12px;color:#64748b}
.sig-upload-text strong{color:#1557d6}
.sig-preview{margin-top:10px}
.sig-preview img{max-height:70px;border:1px solid #e2e8f0;border-radius:6px}
.sig-hint{font-size:11px;color:#94a3b8;margin-top:4px;text-align:center}
iframe#previewFrame{border-radius:4px;border:0;flex:1;min-height:0;background:#fff;width:100%}
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">ASSERTION LETTERS</div>
        <h1>Compose New Letter</h1>
        <p style="color:#64748b;font-size:14px;margin-top:5px">Select an agent, fill the form, and see the actual PDF preview on the right — updated in real time.</p>
    </div>
    <a class="btn secondary" href="<?php echo e(route('assertions.index')); ?>">← All Letters</a>
</div>

<div class="al-grid">
    <div class="al-form">
        <form id="assertionForm" method="POST" action="<?php echo e(route('assertions.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="dynamicFieldsJson" name="dynamic_fields" value="">

            <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px;">
                
                <!-- Column 1: Recipient & Content -->
                <div>
                    <div class="al-section-title">1. Recipient</div>
                    <div class="field">
                        <label for="user_id">Select Agent</label>
                        <select id="user_id" name="user_id" required>
                            <option value="">— Choose an agent —</option>
                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($agent->id); ?>"
                                data-email="<?php echo e($agent->email); ?>"
                                data-pemail="<?php echo e($agent->detail?->personal_email ?? ''); ?>"
                                data-business="<?php echo e($agent->business?->name ?? ''); ?>"
                                <?php echo e(($selectedAgent?->id ?? old('user_id')) == $agent->id ? 'selected' : ''); ?>>
                                <?php echo e($agent->name); ?> — <?php echo e($agent->email); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div id="recipientInfo" class="recipient-info" style="display:none">
                        <div><strong>Will be sent to:</strong> <span id="rcpEmail">—</span></div>
                        <div style="margin-top:4px"><strong>Business:</strong> <span id="rcpBusiness">—</span></div>
                    </div>

                    <div class="al-section-title">2. Letter Content</div>
                    <div class="field">
                        <label for="title">Document Title</label>
                        <input id="title" name="title" type="text" required maxlength="200" value="<?php echo e(old('title', 'Sanction Letter A/F')); ?>">
                    </div>
                    <div class="field">
                        <label for="subject">Email Subject</label>
                        <input id="subject" name="subject" type="text" required maxlength="255" value="<?php echo e(old('subject', 'Your Advance Fund Sanction Letter from Virexon')); ?>">
                    </div>
                    <div class="field">
                        <label for="greeting">Greeting / Salutation</label>
                        <input id="greeting" name="greeting" type="text" required value="<?php echo e(old('greeting', 'Dear Sir/Madam,')); ?>">
                    </div>
                </div>

                <!-- Column 2: Body, Notes & Dynamic Fields -->
                <div>
                    <div class="field">
                        <label for="body">Body of the Letter</label>
                        <textarea id="body" name="body" required rows="6" placeholder="Write the letter body here. Use [[field_name]] for dynamic values."><?php echo e(old('body', 'With reference to your application/request for financial support and subject to the terms and conditions of the applicable [[bold:Agent Agreement / Advance Fund Agreement]], we are pleased to inform you that the Company has approved the following Advance Fund in your favour:')); ?></textarea>
                        <div class="hint">
                            Use <code>[[field_name]]</code> syntax for dynamic fields. e.g. <code>[[agent_name]]</code>
                            <br><br>
                            <strong>Available Dynamic Keys:</strong>
                            <div style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px;margin-top:6px;font-size:12px;line-height:1.8;columns:2;">
                                <code>[[agent_name]]</code> — Agent's full name<br>
                                <code>[[agent_email]]</code> — Agent's email<br>
                                <code>[[agent_id]]</code> — Agent ID number<br>
                                <code>[[agent_address]]</code> — Agent's address<br>
                                <code>[[business_name]]</code> — Business name<br>
                                <code>[[letter_no]]</code> — Sanction letter number<br>
                                <code>[[approved_amount]]</code> — Approved fund amount<br>
                                <code>[[sanction_date]]</code> — Sanction date<br>
                                <code>[[document_title]]</code> — Document title<br>
                                <code>[[email_subject]]</code> — Email subject<br>
                                <code>[[signature_name]]</code> — Signatory name<br>
                                <code>[[signature_designation]]</code> — Signatory designation<br>
                                <code>[[signature_company]]</code> — Signatory company
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label for="notes">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Appears after Terms & Conditions..."><?php echo e(old('notes', '')); ?></textarea>
                    </div>

                    
                    
                </div>

                <!-- Column 3: Closing & Signatory -->
                <div>
                    <div class="al-section-title">4. Closing &amp; Signatory</div>
                    <div class="field">
                        <label for="closing">Closing Line</label>
                        <input id="closing" name="closing" type="text" required value="<?php echo e(old('closing', 'Yours sincerely,')); ?>">
                    </div>
                    
                    <div class="field">
                        <label for="signature_name">Name</label>
                        <input id="signature_name" name="signature_name" type="text" required maxlength="180" value="<?php echo e(old('signature_name', auth()->user()->name)); ?>">
                    </div>
                    <div class="field">
                        <label for="signature_designation">Designation</label>
                        <input id="signature_designation" name="signature_designation" type="text" maxlength="180" value="<?php echo e(old('signature_designation', 'Director')); ?>">
                    </div>
                    <div class="field">
                        <label for="signature_company">Company</label>
                        <input id="signature_company" name="signature_company" type="text" maxlength="180" value="<?php echo e(old('signature_company', 'Virexon (Easy Online Marketing)')); ?>">
                    </div>
                    
                    <div class="field" style="margin-top:14px">
                        <label>Upload Signature Image</label>
                        <div class="sig-upload-wrap">
                            <label class="sig-upload-box" id="sigUploadBox">
                                <input type="file" name="signature_image" id="sigImage" accept="image/png,image/jpeg,image/svg+xml">
                                <div class="sig-upload-icon">✍️</div>
                                <div class="sig-upload-text">Click or drag to upload<br><strong>PNG, JPG, SVG</strong></div>
                            </label>
                            <div style="flex:1" id="sigPreviewContainer">
                                <?php if(!empty($existingSignature)): ?>
                                <div id="sigPreview" style="display:block">
                                    <img src="<?php echo e(asset('storage/' . $existingSignature)); ?>" alt="Signature preview">
                                    <div style="font-size:11px;color:#10b981;margin-top:4px">✓ Previous signature will be used</div>
                                </div>
                                <input type="hidden" name="existing_signature_image" id="existingSigInput" value="<?php echo e($existingSignature); ?>">
                                <?php else: ?>
                                <div id="sigPreview" style="display:none"></div>
                                <?php endif; ?>
                                <div class="sig-hint">Signature will appear in the PDF</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-bar" style="justify-content: flex-end; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px;">
                <button type="button" id="downloadBtn" class="btn secondary">⬇ Download PDF</button>
                <button type="submit" class="btn primary">✉ Generate &amp; Email to Agent</button>
            </div>
        </form>
    </div>

    <!-- PDF Live Preview spanning full width below the form -->
    <div style="margin-top: 20px;">
        <div class="preview-toolbar">
            <div class="indicator" id="previewIndicator"><span class="dot"></span> Live PDF Preview</div>
            <div style="font-size:12px;color:#64748b" id="previewStatus">Waiting...</div>
        </div>
        <div class="preview-wrap" style="height: 800px;">
            <iframe id="previewFrame" src="about:blank" style="width:100%;height:100%;border:0;border-radius:4px;background:#f8fafc"></iframe>
        </div>
    </div>
</div>

<script>
(function(){
var form = document.getElementById('assertionForm');
var iframe = document.getElementById('previewFrame');
var indicator = document.getElementById('previewIndicator');
var statusEl = document.getElementById('previewStatus');
var userSelect = document.getElementById('user_id');
var recipientInfo = document.getElementById('recipientInfo');
var rcpEmail = document.getElementById('rcpEmail');
var rcpBusiness = document.getElementById('rcpBusiness');
var downloadBtn = document.getElementById('downloadBtn');
var sigImage = document.getElementById('sigImage');
var sigPreview = document.getElementById('sigPreview');
var sigUploadBox = document.getElementById('sigUploadBox');
var dynamicFieldsJson = document.getElementById('dynamicFieldsJson');

var timer = null;
var dynamicFields = [];

// Dynamic fields management
function renderDfRows(){
    var list = document.getElementById('dfList');
    if(!list) return;
    list.innerHTML = '';
    dynamicFields.forEach(function(f, i){
        var row = document.createElement('div');
        row.className = 'df-row';
        row.innerHTML = '<input type="text" placeholder="e.g. joining_date" value="'+esc(f.key)+'" data-idx="'+i+'" class="df-key">' +
            '<input type="text" placeholder="Value (e.g. 15 September 2026)" value="'+esc(f.value)+'" data-idx="'+i+'" class="df-val">' +
            '<button type="button" class="df-remove" data-idx="'+i+'" title="Remove">&#x2715;</button>';
        list.appendChild(row);
    });
    syncDfJson();
}

function syncDfJson(){
    if(dynamicFieldsJson) dynamicFieldsJson.value = JSON.stringify(dynamicFields);
}

function esc(s){if(s==null)return'';return String(s).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");}

var addDfBtnEl = document.getElementById('addDfBtn');
var dfListEl = document.getElementById('dfList');

if(addDfBtnEl) addDfBtnEl.addEventListener('click', function(){
    dynamicFields.push({key:'', value:''});
    renderDfRows();
});

if(dfListEl) dfListEl.addEventListener('input', function(e){
    var idx = parseInt(e.target.dataset.idx);
    if(e.target.classList.contains('df-key')) dynamicFields[idx].key = e.target.value;
    if(e.target.classList.contains('df-val')) dynamicFields[idx].value = e.target.value;
    syncDfJson();
});

if(dfListEl) dfListEl.addEventListener('click', function(e){
    var btn = e.target.closest('.df-remove');
    if(!btn) return;
    var idx = parseInt(btn.dataset.idx);
    dynamicFields.splice(idx, 1);
    renderDfRows();
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
        sigPreview.innerHTML = '<img src="'+e.target.result+'" alt="Signature preview">';
    };
    reader.readAsDataURL(file);
});

// Drag over highlight
sigUploadBox.addEventListener('dragover', function(e){ e.preventDefault(); this.classList.add('dragover'); });
sigUploadBox.addEventListener('dragleave', function(){ this.classList.remove('dragover'); });
sigUploadBox.addEventListener('drop', function(e){
    e.preventDefault(); this.classList.remove('dragover');
    if(e.dataTransfer.files.length) { sigImage.files = e.dataTransfer.files; sigImage.dispatchEvent(new Event('change')); }
});

// Recipient info
function updateRecipient(){
    var opt = userSelect.options[userSelect.selectedIndex];
    if(!opt || !opt.value){ recipientInfo.style.display='none'; return; }
    var pemail = opt.getAttribute('data-pemail')||'';
    rcpEmail.textContent = (pemail.length ? pemail : opt.getAttribute('data-email')) || '—';
    rcpBusiness.textContent = opt.getAttribute('data-business') || '—';
    recipientInfo.style.display = 'block';
}

// Gather form data as FormData
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
    indicator.classList.add('updating');
    statusEl.textContent = 'Generating PDF...';
    var formData = gatherFormData();
    fetch("<?php echo e(route('assertions.preview')); ?>", {
        method:'POST',
        headers:{'X-CSRF-TOKEN':document.querySelector('input[name=_token]').value,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body: formData
    }).then(function(r){ return r.json(); })
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

['input','change'].forEach(function(e){ form.addEventListener(e, debounceRefresh, true); });
userSelect.addEventListener('change', function(){ updateRecipient(); refreshPreview(); });
updateRecipient();
if(userSelect.value) refreshPreview();

downloadBtn.addEventListener('click', function(){
    if(!userSelect.value){ alert('Please select an agent first.'); return; }
    var orig = form.action;
    form.action = "<?php echo e(route('assertions.download-pdf')); ?>";
    form.target = '_blank';
    form.submit();
    setTimeout(function(){ form.action = orig; form.target = '_self'; }, 100);
});
})();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/assertions/create.blade.php ENDPATH**/ ?>