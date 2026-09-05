@extends('layouts.app')

@section('content')
<style>
    .doc-row { background: white; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 14px; overflow: hidden; transition: box-shadow .15s; }
    .doc-row:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
    .doc-row.status-approved  { border-left: 4px solid #22c55e; }
    .doc-row.status-pending   { border-left: 4px solid #3b82f6; }
    .doc-row.status-rejected  { border-left: 4px solid #ef4444; }
    .doc-row.status-re_upload { border-left: 4px solid #f97316; }
    .doc-row.status-not_uploaded { border-left: 4px solid #cbd5e1; }
    .doc-inner { display: flex; align-items: flex-start; gap: 16px; padding: 18px 20px; flex-wrap: wrap; }
    .status-pill { font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 99px; white-space: nowrap; }
    .status-pill.approved  { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .status-pill.pending   { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .status-pill.rejected  { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .status-pill.re_upload { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
    .status-pill.not_uploaded { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    .status-help-box { font-size: 11px; padding: 6px 10px; border-radius: 6px; margin-top: 4px; display: inline-block; }
    .status-help-approved  { background: #f0fdf4; color: #166534; }
    .status-help-pending   { background: #eff6ff; color: #1e40af; }
    .status-help-rejected  { background: #fef2f2; color: #991b1b; }
    .status-help-re_upload { background: #fff7ed; color: #9a3412; }
    .status-help-not_uploaded { background: #f8fafc; color: #64748b; }
    .action-select { padding: 7px 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #1e293b; min-width: 180px; cursor: pointer; }
    .action-select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.15); }
    .note-input { padding: 7px 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; color: #1e293b; flex: 1; min-width: 160px; }
    .note-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.15); }
</style>

{{-- =============================== PAGE HEAD =============================== --}}
<div class="page-head" style="margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <a href="{{ route('agents.edit', $agent->id) }}"
           style="color:#64748b;text-decoration:none;font-size:13px;display:inline-flex;align-items:center;gap:4px;margin-bottom:8px;">
            ← Back to Agent
        </a>
        <div class="eyebrow">DOCUMENT REVIEW</div>
        <h1 style="font-size:22px;margin-bottom:4px;">{{ $agent->name }}</h1>
        <p style="color:#64748b;font-size:13px;margin:0;">
            Agent ID: <strong>{{ $agent->detail?->agent_id_number ?? 'N/A' }}</strong>
            &nbsp;|&nbsp; Email: <strong>{{ $agent->email }}</strong>
            &nbsp;|&nbsp; App Status:
            @php $appStatus = $agent->detail?->application_status ?? 'pending'; @endphp
            <strong style="color:{{ match($appStatus) {'approved'=>'#065f46','rejected'=>'#991b1b','form_received'=>'#1e40af',default=>'#92400e'} }};">
                {{ ucfirst(str_replace('_',' ',$appStatus)) }}
            </strong>
        </p>
    </div>

    {{-- Summary Badges --}}
    @php
        $totalDocs      = count($types);
        $approvedCount  = $documents->where('status','approved')->count();
        $pendingCount   = $documents->where('status','pending')->count();
        $rejectedCount  = $documents->whereIn('status',['rejected','re_upload'])->count();
        $notUploadedCount = $totalDocs - $documents->count();
    @endphp
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <span class="status-pill approved" style="font-size:13px;padding:5px 16px;">✓ {{ $approvedCount }}/{{ $totalDocs }} Approved</span>
        @if($pendingCount)   <span class="status-pill pending"   style="font-size:13px;padding:5px 14px;">⏳ {{ $pendingCount }} Pending</span> @endif
        @if($rejectedCount)  <span class="status-pill rejected"  style="font-size:13px;padding:5px 14px;">✗ {{ $rejectedCount }} Action Needed</span> @endif
        @if($notUploadedCount) <span class="status-pill not_uploaded" style="font-size:13px;padding:5px 14px;">— {{ $notUploadedCount }} Not Uploaded</span> @endif
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="flash success" style="margin-bottom:16px;">✓ {{ session('success') }}</div>
@endif

{{-- =============================== STATUS LEGEND =============================== --}}
<div class="panel" style="margin-bottom:20px;padding:16px 20px;">
    <div style="font-weight:700;font-size:13px;color:#1e293b;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
        <span>📖</span> Status Legend — What each status means for the Agent
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
        <div style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;">
            <div style="font-size:12px;font-weight:700;color:#475569;margin-bottom:4px;">— Not Uploaded</div>
            <div style="font-size:11px;color:#64748b;line-height:1.5;">Agent has not uploaded this document yet.</div>
        </div>
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;">
            <div style="font-size:12px;font-weight:700;color:#1e40af;margin-bottom:4px;">⏳ Pending Review</div>
            <div style="font-size:11px;color:#1e40af;line-height:1.5;">Agent uploaded. Waiting for your review and decision.</div>
        </div>
        <div style="background:#d1fae5;border:1px solid #a7f3d0;border-radius:8px;padding:10px 14px;">
            <div style="font-size:12px;font-weight:700;color:#065f46;margin-bottom:4px;">✓ Approved</div>
            <div style="font-size:11px;color:#065f46;line-height:1.5;">Document accepted. Agent cannot re-upload unless you change status.</div>
        </div>
        <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;">
            <div style="font-size:12px;font-weight:700;color:#991b1b;margin-bottom:4px;">✗ Rejected</div>
            <div style="font-size:11px;color:#991b1b;line-height:1.5;">Agent notified. They can re-upload after seeing your rejection note.</div>
        </div>
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:10px 14px;">
            <div style="font-size:12px;font-weight:700;color:#9a3412;margin-bottom:4px;">↺ Re-upload Requested</div>
            <div style="font-size:11px;color:#9a3412;line-height:1.5;">Agent notified to re-upload with your note. Treated like a soft rejection.</div>
        </div>
    </div>
</div>

{{-- =============================== UPLOAD ENABLED GATE =============================== --}}
@if(!in_array($appStatus, ['form_received', 'approved']))
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:16px 20px;margin-bottom:18px;display:flex;align-items:flex-start;gap:12px;">
    <span style="font-size:20px;">⚠️</span>
    <div>
        <div style="font-weight:700;color:#92400e;font-size:14px;margin-bottom:4px;">Document Upload Not Yet Enabled for This Agent</div>
        <div style="font-size:13px;color:#78350f;line-height:1.6;">
            The agent's application status is currently <strong>{{ ucfirst(str_replace('_',' ',$appStatus)) }}</strong>.
            To allow the agent to upload documents, go to the
            <a href="{{ route('agents.edit', $agent->id) }}" style="color:#1e40af;text-decoration:underline;">Agent Management page</a>
            and change the status to <strong>"📋 Form Received — Request Docs"</strong>.
            The agent will be notified automatically.
        </div>
    </div>
</div>
@else
<div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:10px;padding:14px 20px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <span style="font-size:18px;">✅</span>
    <div style="font-size:13px;color:#065f46;font-weight:600;">
        Document upload is <strong>enabled</strong> for this agent (Status: {{ ucfirst(str_replace('_',' ',$appStatus)) }}).
        The agent can now upload all required documents.
    </div>
</div>
@endif

{{-- =============================== DOCUMENT ROWS =============================== --}}
@php
$docMeta = [
    'pan_card'       => ['icon' => '🪪', 'label' => 'PAN Card',                         'desc' => 'Clear scan of PAN card. Required for KYC.'],
    'aadhaar'        => ['icon' => '📋', 'label' => 'Aadhaar Card',                     'desc' => 'Front & back of Aadhaar. Used for identity verification.'],
    'photo'          => ['icon' => '📸', 'label' => 'Photograph',                       'desc' => 'Passport-size photo. Recent and clear.'],
    'bank_proof'     => ['icon' => '🏦', 'label' => 'Bank Passbook / Cancelled Cheque', 'desc' => 'First page of passbook or cancelled cheque leaf.'],
    'agent_id_proof' => ['icon' => '📄', 'label' => 'Agent ID Proof / Agreement',       'desc' => 'Agent ID card, appointment letter, or signed agreement.'],
    'address_proof'  => ['icon' => '🏠', 'label' => 'Address Proof',                    'desc' => 'Voter ID, utility bill, or any government address proof.'],
];
@endphp

@foreach($types as $type)
    @php
        $doc    = $documents[$type] ?? null;
        $status = $doc?->status ?? 'not_uploaded';
        $meta   = $docMeta[$type] ?? ['icon' => '📎', 'label' => ucfirst(str_replace('_',' ',$type)), 'desc' => ''];

        $statusHelp = match($status) {
            'approved'     => 'Document accepted. No action needed.',
            'pending'      => 'Agent has uploaded this. Review and take action below.',
            'rejected'     => 'You rejected this. Agent can see your note and re-upload.',
            're_upload'    => 'You requested re-upload. Waiting for agent to re-submit.',
            default        => 'Agent has not uploaded this document yet.',
        };
    @endphp

    <div class="doc-row status-{{ $status }}">
        <div class="doc-inner">

            {{-- Icon + Label --}}
            <div style="min-width:36px;font-size:26px;padding-top:2px;">{{ $meta['icon'] }}</div>

            {{-- Info --}}
            <div style="flex:1;min-width:180px;">
                <div style="font-weight:700;font-size:15px;color:#1e293b;">{{ $meta['label'] }}</div>
                <div style="font-size:12px;color:#94a3b8;margin-bottom:4px;">{{ $meta['desc'] }}</div>

                @if($doc)
                    <div style="font-size:12px;color:#64748b;margin-bottom:2px;">
                        📅 Uploaded: {{ $doc->created_at->format('d M Y, h:i A') }}
                        @if($doc->reviewed_at)
                        &nbsp;| 🔍 Reviewed: {{ $doc->reviewed_at->format('d M Y') }}
                        @endif
                    </div>
                    @if($doc->original_name)
                    <div style="font-size:11px;color:#94a3b8;">📎 {{ $doc->original_name }}</div>
                    @endif
                @else
                    <div style="font-size:12px;color:#94a3b8;font-style:italic;">No file uploaded yet by agent.</div>
                @endif

                {{-- Admin Note --}}
                @if($doc && $doc->admin_note)
                <div style="margin-top:8px;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;padding:7px 10px;font-size:12px;color:#92400e;">
                    <strong>Your Note:</strong> {{ $doc->admin_note }}
                </div>
                @endif

                {{-- Status Help Text --}}
                <div class="status-help-box status-help-{{ $status }}">
                    💬 {{ $statusHelp }}
                </div>
            </div>

            {{-- Status Badge --}}
            <div style="text-align:center;min-width:100px;padding-top:4px;">
                <span class="status-pill {{ $status }}">
                    {{ match($status) {
                        'approved'    => '✓ Approved',
                        'pending'     => '⏳ Pending Review',
                        'rejected'    => '✗ Rejected',
                        're_upload'   => '↺ Re-upload Requested',
                        default       => '— Not Uploaded',
                    } }}
                </span>
            </div>

            {{-- View File --}}
            @if($doc && $doc->file_path)
            <div style="padding-top:4px;">
                <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                   class="btn secondary tiny" style="text-decoration:none;white-space:nowrap;display:inline-flex;align-items:center;gap:4px;">
                    👁 View File
                </a>
            </div>
            @endif

            {{-- Admin Action Form --}}
            @if($doc)
            <form action="{{ route('admin.documents.review', [$agent->id, $doc->id]) }}" method="POST"
                  style="display:flex;align-items:flex-start;gap:8px;flex-wrap:wrap;min-width:280px;padding-top:2px;">
                @csrf
                @method('PATCH')
                <div>
                    <label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;display:block;margin-bottom:4px;">Your Decision</label>
                    <select name="status" class="action-select">
                        <option value="approved"  {{ $status==='approved'  ? 'selected':'' }}>✓ Approve Document</option>
                        <option value="pending"   {{ $status==='pending'   ? 'selected':'' }}>⏳ Keep as Pending</option>
                        <option value="rejected"  {{ $status==='rejected'  ? 'selected':'' }}>✗ Reject Document</option>
                        <option value="re_upload" {{ $status==='re_upload' ? 'selected':'' }}>↺ Request Re-upload</option>
                    </select>
                </div>
                <div style="flex:1;min-width:160px;">
                    <label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;display:block;margin-bottom:4px;">Reason / Note (sent to agent)</label>
                    <input type="text" name="admin_note" class="note-input" placeholder="e.g. Document is blurry, please re-upload" value="{{ $doc->admin_note }}">
                </div>
                <div style="padding-top:18px;">
                    <button type="submit" class="btn primary tiny" style="white-space:nowrap;">Save Decision</button>
                </div>
            </form>
            @else
            <div style="padding-top:8px;font-size:12px;color:#cbd5e1;font-style:italic;min-width:200px;">
                @if(in_array($appStatus, ['form_received', 'approved']))
                    ⌛ Waiting for agent to upload this document.
                @else
                    🔒 Enable uploads first by updating the agent's application status.
                @endif
            </div>
            @endif
        </div>
    </div>
@endforeach

{{-- Footer Quick Action --}}
<div class="panel" style="margin-top:24px;padding:16px 20px;background:#f8fafc;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div style="font-size:13px;color:#64748b;">
        @if($approvedCount === $totalDocs)
            🎉 <strong style="color:#065f46;">All documents approved!</strong> You can now give this agent final approval.
        @else
            📋 {{ $totalDocs - $approvedCount }} document(s) still need action.
        @endif
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('agents.edit', $agent->id) }}" class="btn secondary" style="text-decoration:none;font-size:13px;">
            ← Back to Agent
        </a>
        @if($approvedCount === $totalDocs && $appStatus !== 'approved')
        <form method="POST" action="{{ route('agents.application-status', $agent) }}" style="margin:0;">
            @csrf
            <input type="hidden" name="application_status" value="approved">
            <button type="submit" class="btn primary" onclick="return confirm('Give this agent final approval?')">
                ✅ Give Final Approval
            </button>
        </form>
        @endif
    </div>
</div>

@endsection
