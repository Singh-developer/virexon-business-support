@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 lg:px-6 py-8">

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

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Sanction Letter</h1>
        <p class="text-slate-500 text-sm mt-1">View, download, sign and upload your sanction letter PDF.</p>
    </div>

    @php
        $approvedCount = $letters->map(fn($l) => $l->workflowStatus()['key'])->filter(fn($k) => $k === 'approved')->count();
        $pendingCount = $letters->map(fn($l) => $l->workflowStatus()['key'])->filter(fn($k) => $k === 'pending')->count();
        $underCount = $letters->map(fn($l) => $l->workflowStatus()['key'])->filter(fn($k) => $k === 'under_review')->count();
        $actionCount = $letters->map(fn($l) => $l->workflowStatus()['key'])->filter(fn($k) => $k === 'reupload_required')->count();
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-slate-600">{{ $pendingCount }}</div>
            <div class="text-xs text-slate-600 font-medium mt-1">Pending</div>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $underCount }}</div>
            <div class="text-xs text-blue-700 font-medium mt-1">Under Review</div>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-orange-600">{{ $actionCount }}</div>
            <div class="text-xs text-orange-700 font-medium mt-1">Action Required</div>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $approvedCount }}</div>
            <div class="text-xs text-green-700 font-medium mt-1">Approved</div>
        </div>
    </div>

    <div class="mb-5 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
        <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-2"></i><strong>Important Note:</strong> Download, carefully review, sign, and re-upload the Sanction Letter within <strong>48 hours</strong>. Failure to complete this process within the specified time will result in <strong>cancellation of the sanction</strong>. After submission, the Sanction Letter will be reviewed and approved, following which the applicant will be required to <strong>complete the subsequent process</strong>.
    </div>

    @php $statusMeta = [
        'approved'          => ['label' => 'Approved',          'badge' => 'bg-green-50 text-green-700 border-green-200', 'icon' => 'fa-solid fa-check'],
        'under_review'      => ['label' => 'Under Review',      'badge' => 'bg-blue-50 text-blue-700 border-blue-200',     'icon' => 'fa-solid fa-clock'],
        'reupload_required' => ['label' => 'Re-upload Required','badge' => 'bg-orange-50 text-orange-700 border-orange-200','icon' => 'fa-solid fa-rotate-right'],
        'pending'           => ['label' => 'Pending',           'badge' => 'bg-slate-50 text-slate-600 border-slate-200',  'icon' => 'fa-regular fa-hourglass'],
    ]; @endphp

    @include('partials.per-page', ['perPage' => $letters->perPage()])

    @forelse($letters as $letter)
    @php
        $ws = $letter->workflowStatus();
        $meta = $statusMeta[$ws['key']];
    @endphp
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-5 {{ $ws['key'] === 'reupload_required' ? 'border-orange-200 ring-1 ring-orange-200' : '' }}">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <i class="fa-regular fa-file-pdf text-blue-500 text-lg"></i>
                </div>
                <div>
                    <div class="font-bold text-slate-900 text-sm">{{ $letter->sanction_letter_no }}</div>
                    <div class="text-slate-400 text-xs mt-0.5">{{ $letter->subject }}</div>
                    <div class="text-slate-400 text-xs mt-0.5">Sent: {{ $letter->sent_at?->format('d M Y, h:i A') ?? $letter->created_at->format('d M Y') }}</div>
                    @if($letter->canUpload())
                    <div class="text-xs mt-0.5 {{ $ws['key'] === 'reupload_required' ? 'text-orange-500' : 'text-blue-600' }}">
                        <i class="fa-regular fa-hourglass-half mr-1"></i> Upload within: <strong>{{ $letter->uploadTimeRemaining() }}</strong> (deadline {{ $letter->uploadDeadline()->format('d M Y, h:i A') }})
                    </div>
                    @elseif($letter->uploadDeadlinePassed() && $ws['key'] === 'pending')
                    <div class="text-xs mt-0.5 text-red-500">
                        <i class="fa-solid fa-lock mr-1"></i> Upload window expired — contact admin to open a new slot.
                    </div>
                    @endif
                    @if($letter->downloaded_at)
                    <div class="text-slate-400 text-xs mt-0.5">Downloaded: {{ $letter->downloaded_at->format('d M Y, h:i A') }}</div>
                    @endif
                    @if($letter->hasProcessFee())
                    <div class="text-xs mt-0.5">
                        💰 Processing Fee:
                        @if($letter->processFeePaid())
                        <span class="text-green-600 font-semibold">Paid ₹ {{ number_format($letter->processFeeAmount(), 2) }}</span>
                        @elseif($letter->processFeePending())
                        <span class="text-amber-600 font-semibold">Payment Processing · ₹ {{ number_format($letter->processFeeAmount(), 2) }}</span>
                        @else
                        <span class="text-red-600 font-semibold">₹ {{ number_format($letter->processFeeAmount(), 2) }} — Pay to upload signed copy</span>
                        @endif
                    </div>
                    @endif
                    @if(($letter->signed_pdf_upload_count ?? 0) > 0)
                    <div class="text-slate-400 text-xs mt-0.5">{{ $letter->signed_pdf_upload_count }} signed file(s) uploaded: {{ $letter->signed_pdf_uploaded_at?->format('d M Y, h:i A') }}</div>
                    @endif
                </div>
            </div>
            <div class="flex flex-col items-end gap-2">
                <span class="text-xs font-bold {{ $meta['badge'] }} border px-2.5 py-1 rounded-full flex items-center gap-1">
                    <i class="{{ $meta['icon'] }} text-[10px]"></i> {{ $meta['label'] }}
                </span>
                @if($ws['key'] === 'reupload_required' && $letter->review_comment)
                <span class="text-[11px] text-orange-700 bg-orange-50 border border-orange-100 rounded-md px-2 py-1 max-w-xs">
                    Admin note: {{ $letter->review_comment }}
                </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-50">
            <a href="{{ route('agent.sanctions.show', $letter) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <i class="fa-regular fa-eye"></i> View
            </a>
            <span class="text-slate-200">|</span>
            <a href="{{ route('agent.sanctions.pdf', $letter) }}" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <i class="fa-regular fa-file-pdf"></i> Preview PDF
            </a>
            <span class="text-slate-200">|</span>
            <a href="{{ route('agent.sanctions.download', $letter) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <i class="fa-solid fa-download"></i> Download PDF
            </a>
            @if($letter->processFeePaid())
                @if($letter->canUpload())
                <a href="{{ route('agent.sanctions.show', $letter) }}#upload"
                   class="ml-auto text-xs font-semibold px-3 py-1.5 rounded-lg {{ $ws['key'] === 'reupload_required' ? 'bg-orange-500 hover:bg-orange-600 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white' }} flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-up-from-bracket text-[10px]"></i>
                    {{ $ws['key'] === 'reupload_required' ? 'Re-upload Signed Copy' : 'Upload Signed Copy' }}
                </a>
                @endif
            @else
            <a href="{{ route('agent.sanctions.show', $letter) }}#pay-fee"
               class="ml-auto text-xs font-semibold px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white flex items-center gap-1.5 transition">
                <i class="fa-solid fa-credit-card text-[10px]"></i>
                @if($letter->processFeePending())
                <i class="fa-solid fa-rotate-right text-[9px]"></i> Pay Again ₹ {{ number_format($letter->processFeeAmount(), 2) }}
                @else
                Pay Processing Fee ₹ {{ number_format($letter->processFeeAmount(), 2) }}
                @endif
            </a>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-10 text-center">
        <div class="text-4xl mb-3">✉️</div>
        <div class="font-semibold text-slate-700">No sanction letters yet</div>
        <div class="text-slate-400 text-sm mt-1">Sanction letters issued to you by the admin will appear here.</div>
    </div>
    @endforelse

    @if($letters->hasPages())
    <div class="flex justify-center mt-6">
        {{ $letters->links() }}
    </div>
    @endif
</div>
@endsection