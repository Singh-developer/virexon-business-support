@extends('layouts.app')

@section('content')
@php
    $ws = $letter->workflowStatus();
    $statusMeta = [
        'approved'          => ['label' => 'Approved',           'badge' => 'bg-green-50 text-green-700 border-green-200', 'icon' => 'fa-solid fa-check'],
        'under_review'      => ['label' => 'Under Review',       'badge' => 'bg-blue-50 text-blue-700 border-blue-200',     'icon' => 'fa-solid fa-clock'],
        'reupload_required' => ['label' => 'Re-upload Required', 'badge' => 'bg-orange-50 text-orange-700 border-orange-200','icon' => 'fa-solid fa-rotate-right'],
        'pending'           => ['label' => 'Pending',            'badge' => 'bg-slate-50 text-slate-600 border-slate-200',  'icon' => 'fa-regular fa-hourglass'],
    ];
    $meta = $statusMeta[$ws['key']];
@endphp
<div class="max-w-5xl mx-auto px-4 lg:px-6 py-8">

    @if(session('success'))
    <div class="mb-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
        <i class="fa-solid fa-circle-check text-green-500 mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
        <i class="fa-solid fa-circle-xmark text-red-500 mr-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div>
            <a href="{{ route('agent.sanctions.index') }}" class="text-xs text-blue-600 hover:text-blue-800 inline-flex items-center gap-1 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Sanction Letters
            </a>
            <h1 class="text-2xl font-bold text-slate-900">{{ $letter->sanction_letter_no }}</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $letter->subject }}</p>
        </div>
        <span class="text-xs font-bold {{ $meta['badge'] }} border px-2.5 py-1 rounded-full flex items-center gap-1">
            <i class="{{ $meta['icon'] }} text-[10px]"></i> {{ $meta['label'] }}
        </span>
    </div>

    {{-- Action required alert --}}
    @if($ws['key'] === 'reupload_required')
    <div class="mb-6 bg-orange-50 border border-orange-300 rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fa-solid fa-triangle-exclamation text-orange-500 mt-0.5"></i>
        <div>
            <div class="font-bold text-orange-800 text-sm">Action Required — Please Re-upload Your Signed PDF</div>
            <div class="text-orange-700 text-xs mt-1">
                The admin has reviewed your signed PDF and it was not accepted.
                @if($letter->review_comment)
                <strong>Reason:</strong> {{ $letter->review_comment }}
                @else
                Please download the letter below, sign it again and upload a new copy.
                @endif
            </div>
        </div>
    </div>
    @elseif($ws['key'] === 'under_review')
    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fa-solid fa-clock text-blue-500 mt-0.5"></i>
        <div>
            <div class="font-bold text-blue-900 text-sm">Signed PDF Uploaded — Pending Admin Review</div>
            <div class="text-blue-700 text-xs mt-1">
                Your signed PDF (uploaded {{ $letter->signed_pdf_uploaded_at?->format('d M Y, h:i A') }}) is awaiting review.
                You will be notified once the admin approves it or requests a re-upload.
            </div>
        </div>
    </div>
    @elseif($ws['key'] === 'approved')
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fa-solid fa-circle-check text-green-500 mt-0.5"></i>
        <div>
            <div class="font-bold text-green-900 text-sm">Approved — Workflow Complete</div>
            <div class="text-green-700 text-xs mt-1">
                Your signed Sanction Letter {{ $letter->sanction_letter_no }} has been approved by the admin.
            </div>
        </div>
    </div>
    @else
    <div class="mb-6 bg-slate-50 border border-slate-200 rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fa-regular fa-hourglass text-slate-400 mt-0.5"></i>
        <div>
            <div class="font-bold text-slate-800 text-sm">Pending Your Action</div>
            <div class="text-slate-500 text-xs mt-1">
                Download and sign the PDF below, then upload the signed copy to complete the workflow.
            </div>
        </div>
    </div>
    @endif

    {{-- Workflow steps --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-6">
        <div class="font-bold text-slate-800 text-sm mb-4">Workflow Status</div>
        <div class="flex flex-wrap items-center gap-1 text-xs">
            <span class="px-3 py-1.5 rounded-full font-semibold {{ $letter->status === 'sent' && $letter->sent_at ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                📨 Letter Sent
            </span>
            <span class="text-slate-300">→</span>
            <span class="px-3 py-1.5 rounded-full font-semibold {{ $letter->downloaded_at ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                ⬇ Downloaded {{ $letter->downloaded_at?->format('d M Y') ?? '' }}
            </span>
            <span class="text-slate-300">→</span>
            <span class="px-3 py-1.5 rounded-full font-semibold {{ ($letter->signed_pdf_upload_count ?? 0) > 0 ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                ⬆ Signed PDF Uploaded
            </span>
            <span class="text-slate-300">→</span>
            <span class="px-3 py-1.5 rounded-full font-semibold {{ $letter->reviewed_at ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                ⚖ Reviewed
            </span>
            <span class="text-slate-300">→</span>
            <span class="px-3 py-1.5 rounded-full font-semibold {{ $ws['key'] === 'approved' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                ✅ {{ $ws['key'] === 'approved' ? 'Approved' : 'Approval' }}
            </span>
        </div>
    </div>

    {{-- Letter PDF --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm mb-6 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 flex-wrap gap-3">
            <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-regular fa-file-pdf text-blue-500"></i> Sanction Letter PDF
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('agent.sanctions.pdf', $letter) }}" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <i class="fa-regular fa-eye"></i> Open in new tab
                </a>
                <a href="{{ route('agent.sanctions.download', $letter) }}" class="text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-download"></i> Download PDF
                </a>
            </div>
        </div>
        <iframe src="{{ route('agent.sanctions.pdf', $letter) }}" class="w-full" style="height:560px;" title="Sanction Letter PDF"></iframe>
    </div>

    {{-- Upload signed PDF --}}
    <div id="upload" class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-6 {{ $letter->canUpload() ? '' : '' }}">
        <div class="flex items-center gap-2 mb-1">
            <i class="fa-solid fa-file-signature text-blue-500"></i>
            <div class="font-bold text-slate-800 text-sm">Upload Signed Sanction Letter PDF</div>
        </div>

        @if($letter->canUpload())
        <p class="text-slate-500 text-xs mt-2 mb-5">
            After downloading, please print / physically or electronically sign the PDF, then upload the signed copy (PDF only, up to 10MB).
        </p>

        <form action="{{ route('agent.sanctions.upload', $letter) }}" method="POST" enctype="multipart/form-data" id="signed-upload-form" class="max-w-2xl">
            @csrf
            @if($ws['key'] === 'reupload_required')
            <div class="mb-4 bg-orange-50 border border-orange-200 rounded-lg px-3 py-2 text-xs text-orange-700">
                ⚠️ This is a <strong>re-upload</strong>. The previously uploaded PDF will be replaced for review while keeping the upload history.
            </div>
            @endif

            <div id="signed-drop-zone" class="border-2 border-dashed border-blue-200 rounded-xl p-8 text-center cursor-pointer hover:bg-blue-50 transition mb-4 group"
                 onclick="document.getElementById('signed-file-input').click()">
                <i class="fa-solid fa-cloud-arrow-up text-3xl text-blue-400 mb-2 group-hover:text-blue-600"></i>
                <p class="text-sm font-semibold text-slate-700">Click or drag the signed PDF here</p>
                <p class="text-xs text-slate-400 mt-1">PDF only · up to 10MB</p>
                <input type="file" name="signed_pdf" id="signed-file-input" class="hidden" accept="application/pdf" required onchange="updateSignedFileName(this)">
            </div>

            <div id="signed-file-display" class="hidden mb-4 bg-green-50 border border-green-200 rounded-lg px-3 py-2 flex items-center gap-2 text-sm text-green-700">
                <i class="fa-solid fa-file-circle-check"></i>
                <span id="signed-file-text"></span>
            </div>

            @error('signed_pdf')
            <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg px-3 py-2 mb-3">{{ $message }}</div>
            @enderror

            <button type="submit" id="signed-upload-btn"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload Signed PDF
            </button>
        </form>
        @elseif($ws['key'] === 'under_review' && $letter->signed_pdf_path)
        <p class="text-xs text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 mt-3 inline-block">
            ⏳ Your signed PDF is under review. Please wait for the admin's decision before uploading again.
        </p>
        <div class="mt-4 flex items-center gap-3 flex-wrap">
            <div class="text-xs text-slate-500 font-semibold">Your latest signed PDF:</div>
            <a href="{{ route('agent.sanctions.signed-pdf', $letter) }}" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <i class="fa-regular fa-eye"></i> Preview signed copy
            </a>
        </div>
        @elseif($ws['key'] === 'approved')
        <div class="mt-3 bg-green-50 border border-green-200 rounded-lg px-3 py-2 text-xs text-green-700 inline-block">
            <i class="fa-solid fa-circle-check mr-1"></i> This letter is approved. No further upload required.
        </div>
        @else
        <p class="text-xs text-slate-400 italic mt-3">Upload is not available for this letter right now.</p>
        @endif
    </div>
</div>

<script>
    function updateSignedFileName(input) {
        if (input.files.length > 0) {
            document.getElementById('signed-file-text').textContent = input.files[0].name;
            document.getElementById('signed-file-display').classList.remove('hidden');
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
                signedFileInput.files = files;
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

    @if(($letter->signed_pdf_upload_count ?? 0) > 0)
    // Prevent accidental double submit when under review (no form present anyway).
    @endif
</script>
@endsection