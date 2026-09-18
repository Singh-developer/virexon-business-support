<?php $__env->startSection('content'); ?>
<?php
    $ws = $letter->workflowStatus();
    $canUpload = $letter->canUpload();
    $deadline = $letter->uploadDeadline();
    $deadlinePassed = $letter->uploadDeadlinePassed();
    $deadlineIso = $deadline->toIso8601String();
    $feePaid = $letter->processFeePaid();
    $feePending = $letter->processFeePending();
    $feeDue = $letter->processFeeDue();
    $feeAmount = $letter->processFeeAmount();
    $hasFee = $letter->hasProcessFee();
    $statusMeta = [
        'approved'          => ['label' => 'Approved',           'badge' => 'bg-green-50 text-green-700 border-green-200', 'icon' => 'fa-solid fa-check'],
        'under_review'      => ['label' => 'Under Review',       'badge' => 'bg-blue-50 text-blue-700 border-blue-200',     'icon' => 'fa-solid fa-clock'],
        'reupload_required' => ['label' => 'Re-upload Required', 'badge' => 'bg-orange-50 text-orange-700 border-orange-200','icon' => 'fa-solid fa-rotate-right'],
        'pending'           => ['label' => 'Pending',            'badge' => 'bg-slate-50 text-slate-600 border-slate-200',  'icon' => 'fa-regular fa-hourglass'],
    ];
    $meta = $statusMeta[$ws['key']];
?>
<div class="max-w-5xl mx-auto px-4 lg:px-6 py-8">

    <?php if(session('success')): ?>
    <div class="mb-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
        <i class="fa-solid fa-circle-check text-green-500 mr-2"></i><?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
        <i class="fa-solid fa-circle-xmark text-red-500 mr-2"></i><?php echo e(session('error')); ?>

    </div>
    <?php endif; ?>

    
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div>
            <a href="<?php echo e(route('agent.sanctions.index')); ?>" class="text-xs text-blue-600 hover:text-blue-800 inline-flex items-center gap-1 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Sanction Letters
            </a>
            <h1 class="text-2xl font-bold text-slate-900"><?php echo e($letter->sanction_letter_no); ?></h1>
            <p class="text-slate-500 text-sm mt-1"><?php echo e($letter->subject); ?></p>
        </div>
        <span class="text-xs font-bold <?php echo e($meta['badge']); ?> border px-2.5 py-1 rounded-full flex items-center gap-1">
            <i class="<?php echo e($meta['icon']); ?> text-[10px]"></i> <?php echo e($meta['label']); ?>

        </span>
    </div>

    
    <?php if($ws['key'] === 'reupload_required'): ?>
    <div class="mb-6 bg-orange-50 border border-orange-300 rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fa-solid fa-triangle-exclamation text-orange-500 mt-0.5"></i>
        <div>
            <div class="font-bold text-orange-800 text-sm">Action Required — Please Re-upload Your Signed PDF</div>
            <div class="text-orange-700 text-xs mt-1">
                The admin has reviewed your signed PDF and it was not accepted.
                <?php if($letter->review_comment): ?>
                <strong>Reason:</strong> <?php echo e($letter->review_comment); ?>

                <?php else: ?>
                Please download the letter below, sign it again and upload a new copy.
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php elseif($ws['key'] === 'under_review' || ($ws['key'] === 'pending' && ($letter->signed_pdf_upload_count ?? 0) > 0)): ?>
    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fa-solid fa-clock text-blue-500 mt-0.5"></i>
        <div>
            <div class="font-bold text-blue-900 text-sm">Signed PDF Uploaded — Pending Admin Review</div>
            <div class="text-blue-700 text-xs mt-1">
                Your signed PDF (uploaded <?php echo e($letter->signed_pdf_uploaded_at?->format('d M Y, h:i A')); ?>) is awaiting review.
                You will be notified once the admin approves it or requests a re-upload.
            </div>
        </div>
    </div>
    <?php elseif($ws['key'] === 'approved'): ?>
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fa-solid fa-circle-check text-green-500 mt-0.5"></i>
        <div>
            <div class="font-bold text-green-900 text-sm">Approved — Workflow Complete</div>
            <div class="text-green-700 text-xs mt-1">
                Your signed Sanction Letter <?php echo e($letter->sanction_letter_no); ?> has been approved by the admin.
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="mb-6 bg-slate-50 border border-slate-200 rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fa-regular fa-hourglass text-slate-400 mt-0.5"></i>
        <div>
            <div class="font-bold text-slate-800 text-sm">Pending Your Action</div>
            <div class="text-slate-500 text-xs mt-1">
                <?php if($hasFee && ! $feePaid): ?>
                Complete the processing fee payment, then download and sign the PDF below and upload the signed copy to complete the workflow.
                <?php else: ?>
                Download and sign the PDF below, then upload the signed copy to complete the workflow.
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-6">
        <div class="font-bold text-slate-800 text-sm mb-4">Workflow Status</div>
        <div class="flex flex-wrap items-center gap-1 text-xs">
            <span class="px-3 py-1.5 rounded-full font-semibold <?php echo e($letter->status === 'sent' && $letter->sent_at ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200'); ?>">
                📨 Letter Sent
            </span>
            <span class="text-slate-300">→</span>
            <span class="px-3 py-1.5 rounded-full font-semibold <?php echo e($feePaid ? 'bg-green-50 text-green-700 border border-green-200' : ($feePending ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-50 text-slate-400 border border-slate-200')); ?>">
                💰 Processing Fee <?php echo e($hasFee && ! $feePaid ? ($feePending ? '· Processing' : '· Pending') : ''); ?>

            </span>
            <span class="text-slate-300">→</span>
            <span class="px-3 py-1.5 rounded-full font-semibold <?php echo e($letter->downloaded_at ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200'); ?>">
                ⬇ Downloaded <?php echo e($letter->downloaded_at?->format('d M Y') ?? ''); ?>

            </span>
            <span class="text-slate-300">→</span>
            <span class="px-3 py-1.5 rounded-full font-semibold <?php echo e(($letter->signed_pdf_upload_count ?? 0) > 0 ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200'); ?>">
                ⬆ Signed PDF Uploaded
            </span>
            <span class="text-slate-300">→</span>
            <span class="px-3 py-1.5 rounded-full font-semibold <?php echo e($letter->reviewed_at && $ws['key'] !== 'pending' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200'); ?>">
                ⚖ Reviewed
            </span>
            <span class="text-slate-300">→</span>
            <span class="px-3 py-1.5 rounded-full font-semibold <?php echo e($ws['key'] === 'approved' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200'); ?>">
                ✅ <?php echo e($ws['key'] === 'approved' ? 'Approved' : 'Approval'); ?>

            </span>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm mb-6 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 flex-wrap gap-3">
            <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-regular fa-file-pdf text-blue-500"></i> Sanction Letter PDF
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('agent.sanctions.pdf', $letter)); ?>" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <i class="fa-regular fa-eye"></i> Open in new tab
                </a>
                <a href="<?php echo e(route('agent.sanctions.download', $letter)); ?>" class="text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-download"></i> Download PDF
                </a>
            </div>
        </div>
        <iframe src="<?php echo e(route('agent.sanctions.pdf', $letter)); ?>" class="w-full" style="height:560px;" title="Sanction Letter PDF"></iframe>
    </div>

    
    <div id="upload" class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-6 <?php echo e($letter->canUpload() ? '' : ''); ?>">
        <div class="flex items-center gap-2 mb-1">
            <i class="fa-solid fa-file-signature text-blue-500"></i>
            <div class="font-bold text-slate-800 text-sm">Upload Signed Sanction Letter</div>
        </div>

        <?php if($hasFee && ! $feePaid): ?>
        <div id="pay-fee" class="mb-4 rounded-xl border px-4 py-4 bg-amber-50 border-amber-200">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-money-check-dollar text-amber-500 mt-0.5"></i>
                <div class="flex-1">
                    <div class="text-xs font-bold text-amber-800">Processing Fee Required</div>
                    <div class="text-xs text-amber-700 mt-1">
                        A processing fee of <strong class="text-amber-800">₹ <?php echo e(number_format($feeAmount, 2)); ?></strong> is required before you can upload the signed PDF.
                    </div>
                    <div class="text-[11px] text-amber-700 italic mt-1">
                        Pay securely via Paytm. The upload unlocks immediately once the payment is confirmed.
                    </div>
                    <?php if($feePending): ?>
                    <div class="mt-2 text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-1.5 inline-block">
                        <i class="fa-solid fa-clock-rotate-left mr-1"></i> Your previous payment attempt has not been confirmed by Paytm yet. You may pay again below.
                    </div>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('agent.sanctions.pay-fee', $letter)); ?>" class="mt-3">
                        <?php echo csrf_field(); ?>
                        <button type="submit" id="pay-fee-btn"
                            class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2">
                            <i class="fa-solid fa-credit-card"></i> Pay Processing Fee ₹ <?php echo e(number_format($feeAmount, 2)); ?>

                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php elseif($letter->canUpload()): ?>
        <div class="mb-4 rounded-xl border px-4 py-3 bg-amber-50 border-amber-200">
            <div class="flex items-center gap-3">
                <i class="fa-regular fa-hourglass-half text-amber-500"></i>
                <div>
                    <div class="text-xs font-bold text-amber-800">Upload your signed PDF within 48 hours</div>
                    <div class="text-xs text-amber-700 countdown-row mt-0.5">
                        Time left: <strong class="countdown" data-deadline="<?php echo e($deadlineIso); ?>"><?php echo e($letter->uploadTimeRemaining()); ?></strong>
                        <span class="md:inline hidden">· Deadline: <?php echo e($deadline->format('d M Y, h:i A')); ?></span>
                    </div>
                    <div class="text-[11px] text-amber-700 italic mt-0.5">
                        After the deadline passes the upload will be disabled. If you miss it, contact the admin to get a new upload slot.
                    </div>
                </div>
            </div>
        </div>
        <p class="text-slate-500 text-xs mt-2 mb-5">
            After downloading, please print / physically or electronically sign the letter, then upload the signed copies (PDF, JPG, JPEG, WEBP, PNG — up to 5 files, 10MB each).
        </p>

        <form action="<?php echo e(route('agent.sanctions.upload', $letter)); ?>" method="POST" enctype="multipart/form-data" id="signed-upload-form" class="max-w-2xl">
            <?php echo csrf_field(); ?>
            <?php if($ws['key'] === 'reupload_required'): ?>
            <div class="mb-4 bg-orange-50 border border-orange-200 rounded-lg px-3 py-2 text-xs text-orange-700">
                ⚠️ This is a <strong>re-upload</strong>. The previously uploaded file will be replaced for review while keeping the upload history.
            </div>
            <?php endif; ?>

            <div id="signed-drop-zone" class="border-2 border-dashed border-blue-200 rounded-xl p-8 text-center cursor-pointer hover:bg-blue-50 transition mb-4 group"
                 onclick="document.getElementById('signed-file-input').click()">
                <i class="fa-solid fa-cloud-arrow-up text-3xl text-blue-400 mb-2 group-hover:text-blue-600"></i>
                <p class="text-sm font-semibold text-slate-700">Click or drag the signed copies here</p>
                <p class="text-xs text-slate-400 mt-1">PDF, JPG, JPEG, WEBP, PNG · up to 5 files · 10MB each</p>
                <input type="file" name="signed_pdf[]" id="signed-file-input" class="hidden" accept=".pdf,.jpg,.jpeg,.webp,.png" multiple required onchange="updateSignedFileName(this)">
            </div>

            <div id="signed-file-display" class="hidden mb-4 bg-green-50 border border-green-200 rounded-lg px-3 py-2 flex items-center gap-2 text-sm text-green-700">
                <i class="fa-solid fa-file-circle-check"></i>
                <span id="signed-file-text"></span>
            </div>

            <div id="deadline-expired-note" class="hidden mb-4 bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-xs text-red-700">
                <i class="fa-solid fa-lock mr-1"></i> The 48-hour upload window has expired. Upload is disabled — please contact the admin to get a new upload slot.
            </div>

            <?php $__errorArgs = ['signed_pdf'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg px-3 py-2 mb-3"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php
                $signedFileErrors = collect($errors->getMessages())
                    ->filter(fn($msgs, $key) => str_starts_with($key, 'signed_pdf.'))
                    ->flatten()->values();
            ?>
            <?php if($signedFileErrors->isNotEmpty()): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg px-3 py-2 mb-3"><?php echo e($signedFileErrors->first()); ?></div>
            <?php endif; ?>

            <button type="submit" id="signed-upload-btn"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload Signed Copy
            </button>
        </form>
        <?php elseif($deadlinePassed && $ws['key'] !== 'under_review' && $ws['key'] !== 'approved'): ?>
        <div class="mt-3 bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-xs text-red-700 inline-block">
            <i class="fa-solid fa-lock mr-1"></i> The 48-hour upload window has expired. Upload is disabled — please contact the admin to get a new upload slot.
        </div>
        <?php elseif(in_array($ws['key'], ['pending', 'under_review']) && $letter->signed_pdf_path): ?>
        <p class="text-xs text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 mt-3 inline-block">
            ⏳ Your signed PDF has been submitted and is waiting for admin review. Please wait for the admin's decision before uploading again.
        </p>
        <?php if($letter->uploads->isNotEmpty()): ?>
        <?php
            $firstUpload = $letter->uploads->first();
            $firstExt = strtolower(pathinfo($firstUpload->file_path ?? $firstUpload->original_name ?? '', PATHINFO_EXTENSION));
            $firstIsImage = in_array($firstExt, ['jpg', 'jpeg', 'png', 'webp']);
            $firstUrl = route('agent.sanctions.signed-file', [$letter, $firstUpload]);
        ?>
        <div id="agentSignedPreviewPanel" class="mt-4" style="scroll-margin-top:90px;">
            <div class="text-xs text-slate-500 font-semibold mb-2" id="agentSignedPreviewTitle">Signed file preview:</div>
            <div class="border border-slate-200 rounded-lg overflow-hidden bg-slate-50">
                <img id="agentSignedPreviewImg" src="<?php echo e($firstUrl); ?>" alt="Signed file preview" style="display:<?php echo e($firstIsImage ? 'block' : 'none'); ?>; width:100%; object-fit:contain; background:#fff;">
                <iframe id="agentSignedPreviewFrame" src="<?php echo e($firstUrl); ?>" style="display:<?php echo e($firstIsImage ? 'none' : 'block'); ?>; width:100%; height:480px; border:0; background:#fff;" title="Signed file preview"></iframe>
            </div>
            <div id="agentSignedPreviewCaption" class="text-[11px] text-slate-400 mt-1"><?php echo e($firstUpload->original_name ?? 'signed file'); ?></div>
        </div>
        <div class="mt-4">
            <div class="text-xs text-slate-500 font-semibold mb-2">Your uploaded signed files (<?php echo e($letter->uploads->count()); ?>):</div>
            <div class="grid gap-2">
                <?php $__currentLoopData = $letter->uploads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $upload): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-x-2 gap-y-1 flex-wrap bg-slate-50 border border-slate-200 rounded-lg px-3 py-2" style="min-width:0;">
                    <i class="fa-regular fa-file-lines text-blue-500" style="flex-shrink:0;"></i>
                    <span class="text-xs font-semibold text-slate-700 truncate min-w-0" style="flex:1 1 140px;" title="<?php echo e($upload->original_name); ?>"><?php echo e($upload->original_name ?? 'signed file'); ?></span>
                    <span class="text-[11px] text-slate-400" style="white-space:nowrap;"><?php echo e($upload->uploaded_at?->format('d M Y, h:i A') ?? $upload->created_at->format('d M Y, h:i A')); ?></span>
                    <?php if($upload->file_size): ?><span class="text-[11px] text-slate-400" style="white-space:nowrap;"><?php echo e(number_format($upload->file_size / 1024, 1)); ?> KB</span><?php endif; ?>
                    <a href="<?php echo e(route('agent.sanctions.signed-file', [$letter, $upload])); ?>" target="_blank" class="ml-auto text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 agent-file-view" style="flex-shrink:0; white-space:nowrap;"
                       data-url="<?php echo e(route('agent.sanctions.signed-file', [$letter, $upload])); ?>"
                       data-name="<?php echo e($upload->original_name ?? 'signed file'); ?>"
                       data-ext="<?php echo e(strtolower(pathinfo($upload->file_path ?? $upload->original_name ?? '', PATHINFO_EXTENSION))); ?>">
                        <i class="fa-regular fa-eye"></i> Preview
                    </a>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php else: ?>
        <div class="mt-4 flex items-center gap-3 flex-wrap">
            <div class="text-xs text-slate-500 font-semibold">Your latest signed PDF:</div>
            <a href="<?php echo e(route('agent.sanctions.signed-pdf', $letter)); ?>" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <i class="fa-regular fa-eye"></i> Preview signed copy
            </a>
        </div>
        <?php endif; ?>
        <?php elseif($ws['key'] === 'approved'): ?>
        <div class="mt-3 bg-green-50 border border-green-200 rounded-lg px-3 py-2 text-xs text-green-700 inline-block">
            <i class="fa-solid fa-circle-check mr-1"></i> This letter is approved. No further upload required.
        </div>
        <?php else: ?>
        <p class="text-xs text-slate-400 italic mt-3">Upload is not available for this letter right now.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    function updateSignedFileName(input) {
        const box = document.getElementById('signed-file-display');
        const txt = document.getElementById('signed-file-text');
        if (input.files.length > 0) {
            const names = Array.from(input.files).map(f => f.name).join(', ');
            txt.textContent = input.files.length + ' file(s): ' + names;
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }

    const signedDropZone = document.getElementById('signed-drop-zone');
    const signedFileInput = document.getElementById('signed-file-input');

    if (signedDropZone) {
        signedDropZone.addEventListener('dragover', e => {
            e.preventDefault();
            signedDropZone.classList.add('border-blue-500', 'bg-blue-50');
        });
        signedDropZone.addEventListener('dragleave', () => {
            signedDropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });
        signedDropZone.addEventListener('drop', e => {
            e.preventDefault();
            signedDropZone.classList.remove('border-blue-500', 'bg-blue-50');
            const files = e.dataTransfer.files;
            if (files.length) {
                // Multi-file upload: keep up to 5 dropped files.
                const dt = new DataTransfer();
                Array.from(files).slice(0, 5).forEach(f => dt.items.add(f));
                signedFileInput.files = dt.files;
                updateSignedFileName(signedFileInput);
            }
        });

        document.getElementById('signed-upload-form').addEventListener('submit', function() {
            const btn = document.getElementById('signed-upload-btn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
            btn.classList.add('opacity-60');
            btn.disabled = true;
        });
    }

    const countdownEls = document.querySelectorAll('.countdown[data-deadline]');
    countdownEls.forEach(function(el) {
        const deadline = new Date(el.getAttribute('data-deadline')).getTime();
        const form = document.getElementById('signed-upload-form');
        const btn = document.getElementById('signed-upload-btn');
        const expiredNote = document.getElementById('deadline-expired-note');
        const dropZone = document.getElementById('signed-drop-zone');

        function tick() {
            const diff = deadline - Date.now();
            if (diff <= 0) {
                el.textContent = 'Expired';
                const row = el.closest('.countdown-row');
                if (row) row.classList.replace('text-amber-700', 'text-red-700');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                    btn.innerHTML = '<i class="fa-solid fa-lock"></i> Upload Disabled';
                }
                if (expiredNote) expiredNote.classList.remove('hidden');
                if (dropZone) {
                    dropZone.classList.add('opacity-40', 'pointer-events-none');
                    dropZone.style.cursor = 'not-allowed';
                }
                if (form) {
                    const fileInput = form.querySelector('input[type=file]');
                    if (fileInput) fileInput.disabled = true;
                }
                return;
            }
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            el.textContent = (d > 0 ? d + 'd ' : '') +
                String(h).padStart(2, '0') + ':' +
                String(m).padStart(2, '0') + ':' +
                String(s).padStart(2, '0');
        }
        tick();
        setInterval(tick, 1000);
    });
</script>
<script>
    // Signed-file preview: popup on mobile, inline panel + scroll-to-top on desktop.
    (function () {
        var panel = document.getElementById('agentSignedPreviewPanel');
        var img = document.getElementById('agentSignedPreviewImg');
        var frame = document.getElementById('agentSignedPreviewFrame');
        var caption = document.getElementById('agentSignedPreviewCaption');
        if (!panel || !img || !frame) return;

        function isImageExt(ext) {
            return ['jpg', 'jpeg', 'png', 'webp'].indexOf(String(ext || '').toLowerCase()) !== -1;
        }

        document.querySelectorAll('.agent-file-view').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var url = link.getAttribute('data-url');
                var name = link.getAttribute('data-name') || 'Signed file';
                var ext = link.getAttribute('data-ext') || '';
                if (window.FilePreviewModal && FilePreviewModal.usePopup()) {
                    FilePreviewModal.open({ url: url, name: name, downloadUrl: url, ext: ext });
                    return;
                }
                if (isImageExt(ext)) {
                    img.src = url;
                    img.style.display = 'block';
                    frame.style.display = 'none';
                } else {
                    frame.src = url;
                    img.style.display = 'none';
                    frame.style.display = 'block';
                }
                if (caption) caption.textContent = name;
                try { panel.scrollIntoView({ block: 'start', behavior: 'smooth' }); }
                catch (err) { panel.scrollIntoView(); }
            });
        });
    })();
</script>
<?php echo $__env->make('partials.file-preview-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/agent/sanctions/show.blade.php ENDPATH**/ ?>