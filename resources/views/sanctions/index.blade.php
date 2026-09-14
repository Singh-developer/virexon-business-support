@extends('layouts.app')

@section('content')
<style>
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; white-space: nowrap; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
    .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .status-badge.sent { background: #dcfce7; color: #16a34a; }
    .status-badge.draft { background: #fef3c7; color: #d97706; }
    .status-badge.approved { background: #d1fae5; color: #065f46; }
    .status-badge.under_review { background: #dbeafe; color: #1e40af; }
    .status-badge.reupload_required { background: #ffedd5; color: #9a3412; }
    .status-badge.pending { background: #f1f5f9; color: #475569; }
    .ws-yes { color: #16a34a; font-weight: 700; }
    .ws-no { color: #94a3b8; font-weight: 600; }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">SANCTION LETTERS</div>
        <h1>Sanction Letters</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">Track the Agent <-> Admin workflow for every sanction letter.</p>
    </div>
    <a class="btn primary" href="{{ route('sanctions.create') }}">+ New Letter</a>
</div>

{{-- Status filter --}}
<form method="GET" action="{{ route('sanctions.index') }}" style="margin-bottom:16px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
    <select name="status" onchange="this.form.submit()" style="padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#1e293b; background:#fff;">
        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="under_review" {{ $status === 'under_review' ? 'selected' : '' }}>Under Review</option>
        <option value="reupload_required" {{ $status === 'reupload_required' ? 'selected' : '' }}>Re-upload Required</option>
        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
    </select>
    @include('partials.per-page-fields', ['perPage' => $letters->perPage()])
    @if($letters->hasPages() || $status !== 'all')
    <a href="{{ route('sanctions.index') }}" style="font-size:13px;color:#1557d6;text-decoration:none;">Reset</a>
    @endif
</form>

<div class="table-wrap">
    <table class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>No.</th>
                <th>Agent</th>
                <th>Created</th>
                <th>Sent</th>
                <th>Downloaded</th>
                <th>Signed Upload</th>
                <th>Review Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($letters as $letter)
            @php $ws = $letter->workflowStatus(); @endphp
            <tr>
                <td class="mono">{{ $letter->sanction_letter_no }}</td>
                <td>
                    <strong>{{ $letter->user->name }}</strong><br>
                    <small style="color:#64748b;">{{ $letter->user->email }}</small>
                </td>
                <td>{{ $letter->created_at?->format('d M Y') ?? '—' }}</td>
                <td>
                    @if($letter->status === 'sent' && $letter->sent_at)
                        <span class="ws-yes">Yes</span><br>
                        <small style="color:#94a3b8;">{{ $letter->sent_at->format('d M Y, h:i A') }}</small>
                    @else
                        <span class="ws-no">No</span>
                    @endif
                </td>
                <td>
                    @if($letter->downloaded_at)
                        <span class="ws-yes">Yes</span><br>
                        <small style="color:#94a3b8;">{{ $letter->downloaded_at->format('d M Y, h:i A') }}</small>
                    @else
                        <span class="ws-no">No</span>
                    @endif
                </td>
                <td>
                    @if(($letter->signed_pdf_upload_count ?? 0) > 0)
                        <span class="ws-yes">Yes</span><br>
                        <small style="color:#94a3b8;">
                            {{ $letter->signed_pdf_upload_count }} file(s)
                            @if($letter->signed_pdf_uploaded_at)
                            · {{ $letter->signed_pdf_uploaded_at->format('d M Y') }}
                            @endif
                        </small>
                    @else
                        <span class="ws-no">No</span>
                    @endif
                </td>
                <td>
                    <span class="status-badge {{ $ws['key'] }}">{{ $ws['label'] }}</span>
                </td>
                <td>
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        <a href="{{ route('sanctions.show', $letter) }}" class="btn secondary tiny">View</a>
                        <a href="{{ route('sanctions.download', $letter) }}" class="btn secondary tiny">PDF</a>
                        <a href="{{ route('sanctions.review', $letter) }}" class="btn secondary tiny">Review</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding: 30px; color:#64748b;">
                    No sanction letters found{{ $status !== 'all' ? ' for the selected status' : '' }}.
                    <a href="{{ route('sanctions.create') }}">Create the first one.</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($letters->hasPages())
    <div style="margin-top:15px; display:flex; justify-content:center;">
        {{ $letters->links() }}
    </div>
    @endif
</div>

@if($status !== 'all')
<div style="margin-top:14px; font-size:12px; color:#94a3b8; text-align:right;">
    Showing {{ $letters->total() }} letter(s) with status "{{ ucwords(str_replace('_', ' ', $status)) }}".
</div>
@endif
@endsection