@extends('layouts.app')

@section('content')
<style>
    .rv-row { background: white; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 14px; overflow: hidden; }
    .rv-panel { background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; margin-bottom: 18px; }
    .rv-fact { display:flex; justify-content:space-between; gap:12px; padding:9px 0; border-bottom:1px dashed #e2e8f0; font-size:13px; }
    .rv-fact:last-child { border-bottom: 0; }
    .rv-fact .k { color:#64748b; }
    .rv-fact .v { font-weight:700; color:#1e293b; text-align:right; }
    .status-pill { font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 99px; white-space: nowrap; display:inline-block; }
    .status-pill.approved  { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
    .status-pill.under_review { background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe; }
    .status-pill.reupload_required { background:#ffedd5; color:#9a3412; border:1px solid #fed7aa; }
    .status-pill.pending { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
    .ws-yes { color:#16a34a; font-weight:700; }
    .ws-no { color:#94a3b8; font-weight:600; }
    .note-input { padding:8px 10px; border:1px solid #e2e8f0; border-radius:6px; font-size:13px; color:#1e293b; width:100%; }
    .note-input:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,.15); }
</style>

<div class="page-head" style="margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <a href="{{ route('sanctions.index') }}" style="color:#64748b;text-decoration:none;font-size:13px;display:inline-flex;align-items:center;gap:4px;margin-bottom:8px;">
            ← Back to Sanction Letters
        </a>
        <div class="eyebrow">SIGNED PDF REVIEW</div>
        <h1 style="font-size:22px;margin-bottom:4px;">
            {{ $letter->sanction_letter_no }}
        </h1>
        <p style="color:#64748b;font-size:13px;margin:0;">
            {{ $letter->user->name }}
            (Agent ID: <strong>{{ $letter->user->detail?->agent_id_number ?? 'N/A' }}</strong>)
            &nbsp;|&nbsp; {{ $letter->user->email }}
        </p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        @php $ws = $letter->workflowStatus(); @endphp
        <span class="status-pill {{ $ws['key'] }}">{{ $ws['label'] }}</span>
        <a href="{{ route('sanctions.show', $letter) }}" class="btn secondary tiny">Letter Details</a>
        <a href="{{ route('sanctions.download', $letter) }}" class="btn secondary tiny">Original PDF</a>
    </div>
</div>

@if(session('success'))
<div class="flash success" style="margin-bottom:16px;">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="flash error" style="margin-bottom:16px;">✗ {{ session('error') }}</div>
@endif

<div style="display:grid; grid-template-columns: 340px 1fr; gap:18px; align-items:start;">
    {{-- LEFT: status facts --}}
    <div>
        <div class="rv-panel" style="padding:0 20px;">
            <div style="font-weight:700;font-size:13px;color:#1e293b;padding:14px 0;border-bottom:1px solid #e2e8f0;margin-bottom:4px;">
                📊 Workflow Status
            </div>
            <div class="rv-fact">
                <span class="k">Sanction Letter Created</span>
                <span class="v"><span class="ws-yes">Yes</span></span>
            </div>
            <div class="rv-fact">
                <span class="k">Sent to Agent</span>
                @if($letter->status === 'sent' && $letter->sent_at)
                    <span class="v"><span class="ws-yes">Yes</span><br><small style="color:#94a3b8;font-weight:400;">{{ $letter->sent_at->format('d M Y, h:i A') }}</small></span>
                @else
                    <span class="v"><span class="ws-no">No</span></span>
                @endif
            </div>
            <div class="rv-fact">
                <span class="k">Agent Downloaded PDF</span>
                @if($letter->downloaded_at)
                    <span class="v"><span class="ws-yes">Yes</span><br><small style="color:#94a3b8;font-weight:400;">{{ $letter->downloaded_at->format('d M Y, h:i A') }}</small></span>
                @else
                    <span class="v"><span class="ws-no">No</span></span>
                @endif
            </div>
            <div class="rv-fact">
                <span class="k">Agent Uploaded Signed PDF</span>
                @if(($letter->signed_pdf_upload_count ?? 0) > 0)
                    <span class="v"><span class="ws-yes">Yes</span><br><small style="color:#94a3b8;font-weight:400;">{{ $letter->signed_pdf_uploaded_at?->format('d M Y, h:i A') }}</small></span>
                @else
                    <span class="v"><span class="ws-no">No</span></span>
                @endif
            </div>
            <div class="rv-fact">
                <span class="k">Review Status</span>
                <span class="v"><span class="status-pill {{ $ws['key'] }}">{{ $ws['label'] }}</span></span>
            </div>
            @if($letter->reviewed_by || $letter->reviewed_at)
            <div class="rv-fact">
                <span class="k">Reviewed By / At</span>
                <span class="v"><small style="font-weight:400;color:#94a3b8;">{{ $letter->reviewedBy?->name ?? '—' }}<br>{{ $letter->reviewed_at?->format('d M Y, h:i A') }}</small></span>
            </div>
            @endif
        </div>

        {{-- Admin decision --}}
        <div class="rv-panel">
            <div style="font-weight:700;font-size:13px;color:#1e293b;margin-bottom:10px;">⚖️ Admin Decision</div>

            @if(($letter->signed_pdf_upload_count ?? 0) === 0)
            <div style="font-size:12px;color:#94a3b8;font-style:italic;">
                No signed PDF uploaded yet. Once the agent uploads it, review it here and approve or request a re-upload.
            </div>
            @elseif($letter->review_status === 'approved')
                <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:8px;padding:12px 14px;font-size:13px;color:#065f46;">
                    ✅ This signed PDF has been <strong>Approved</strong>. The workflow is complete.
                </div>
            @else
                <form method="POST" action="{{ route('sanctions.approve', $letter) }}" style="margin-bottom:14px;">
                    @csrf
                    <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;display:block;margin-bottom:6px;">Approval Note (optional)</label>
                    <textarea name="review_comment" rows="2" class="note-input" placeholder="e.g. Signed copy verified & accepted.">{{ $letter->review_comment }}</textarea>
                    <div style="margin-top:10px;">
                        <button type="submit" class="btn primary tiny" style="width:100%;justify-content:center;">✓ Approve Signed PDF</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('sanctions.reupload-required', $letter) }}">
                    @csrf
                    <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;display:block;margin-bottom:6px;">Re-upload Reason (sent to agent)</label>
                    <textarea name="review_comment" rows="2" class="note-input" placeholder="e.g. Signature is not clearly visible. Please sign again and re-upload.">{{ $letter->review_status === 'reupload_required' ? $letter->review_comment : '' }}</textarea>
                    <div style="margin-top:10px;">
                        <button type="submit" class="btn secondary tiny" style="width:100%;justify-content:center;color:#9a3412;border-color:#fed7aa;">↺ Request Re-upload</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- RIGHT: signed PDF preview + history --}}
    <div>
        @if($letter->latestUpload)
        @php $latest = $letter->latestUpload; @endphp
        <div class="rv-panel" style="padding:0 0 16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid #e2e8f0;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
                <div style="font-weight:700;font-size:13px;color:#1e293b;">🗂 Latest Signed PDF</div>
                <div style="display:flex;gap:8px;">
                    <a href="{{ route('sanctions.signed-pdf', [$letter, $latest]) }}" target="_blank" class="btn secondary tiny">View</a>
                    <a href="{{ route('sanctions.signed-download', [$letter, $latest]) }}" class="btn primary tiny">Download</a>
                </div>
            </div>

            @if($letter->review_status === 'reupload_required')
            <div style="margin:0 20px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:12px;color:#92400e;">
                ⚠️ This upload is awaiting a <strong>re-upload</strong>. You asked {{ $letter->user->name }} to upload a corrected signed PDF.
            </div>
            @endif

            <div style="margin:0 20px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                <iframe src="{{ route('sanctions.signed-pdf', [$letter, $latest]) }}" style="width:100%;height:640px;border:0;" title="Signed Sanction Letter PDF"></iframe>
            </div>

            <div style="margin-top:12px;padding:0 20px;font-size:12px;color:#64748b;">
                📎 {{ $latest->original_name ?? 'signed-sanction-letter.pdf' }}
                &nbsp;·&nbsp; Uploaded {{ $latest->uploaded_at?->format('d M Y, h:i A') ?? $latest->created_at->format('d M Y, h:i A') }}
                @if($latest->file_size) &nbsp;·&nbsp; {{ number_format($latest->file_size / 1024, 1) }} KB @endif
                @if($latest->uploader) &nbsp;·&nbsp; by {{ $latest->uploader->name }} @endif
            </div>
        </div>
        @else
        <div class="rv-panel">
            <div style="font-size:13px;color:#94a3b8;text-align:center;padding:30px 0;">
                <div style="font-size:34px;margin-bottom:8px;">🗂️</div>
                No signed PDF has been uploaded by the agent yet.
            </div>
        </div>
        @endif

        {{-- Upload history --}}
        @if($letter->uploads->isNotEmpty())
        <div class="rv-panel">
            <div style="font-weight:700;font-size:13px;color:#1e293b;margin-bottom:12px;">🕘 Upload History</div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;border-bottom:1px solid #e2e8f0;">
                        <th style="padding:8px;">#</th>
                        <th style="padding:8px;">Uploaded At</th>
                        <th style="padding:8px;">File</th>
                        <th style="padding:8px;">Uploaded By</th>
                        <th style="padding:8px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($letter->uploads as $idx => $upload)
                    <tr style="border-bottom:1px solid #f1f5f9;font-size:13px;">
                        <td style="padding:8px;">
                            {{ $letter->uploads->count() - $idx }}
                            @if($upload->id === $letter->latestUpload?->id)
                            <span class="status-pill pending" style="margin-left:4px;padding:1px 8px;font-size:10px;">LATEST</span>
                            @endif
                        </td>
                        <td style="padding:8px;white-space:nowrap;">{{ $upload->uploaded_at?->format('d M Y, h:i A') ?? $upload->created_at->format('d M Y, h:i A') }}</td>
                        <td style="padding:8px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $upload->original_name ?? $upload->file_path }}
                        </td>
                        <td style="padding:8px;white-space:nowrap;">{{ $upload->uploader?->name ?? '—' }}</td>
                        <td style="padding:8px;">
                            <a href="{{ route('sanctions.signed-pdf', [$letter, $upload]) }}" target="_blank" class="btn secondary tiny">View</a>
                            <a href="{{ route('sanctions.signed-download', [$letter, $upload]) }}" class="btn secondary tiny" style="margin-left:4px;">Download</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection