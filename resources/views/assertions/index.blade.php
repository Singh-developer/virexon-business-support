@extends('layouts.app')

@section('content')
<style>
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
    .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .status-badge.sent { background: #dcfce7; color: #16a34a; }
    .status-badge.draft { background: #fef3c7; color: #d97706; }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">ASSERTION LETTERS</div>
        <h1>Assertion Letters</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">View all sent assertion letters, download PDFs or send new ones to agents.</p>
    </div>
    <a class="btn primary" href="{{ route('assertions.create') }}">+ New Letter</a>
</div>

<div class="table-wrap">
    <table class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Agent</th>
                <th>Subject</th>
                <th>Business</th>
                <th>Status</th>
                <th>Sent At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($letters as $letter)
            <tr>
                <td>#{{ $letter->id }}</td>
                <td>
                    <strong>{{ $letter->user->name }}</strong><br>
                    <small style="color:#64748b;">{{ $letter->user->email }}</small>
                </td>
                <td>{{ $letter->subject }}</td>
                <td>{{ $letter->business?->name ?? '—' }}</td>
                <td>
                    <span class="status-badge {{ $letter->status }}">
                        @if($letter->status === 'sent') ✓ Sent @else Draft @endif
                    </span>
                </td>
                <td>{{ $letter->sent_at?->format('d M Y, h:i A') ?? '—' }}</td>
                <td>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <a href="{{ route('assertions.show', $letter) }}" class="btn secondary tiny">View</a>
                        <a href="{{ route('assertions.download', $letter) }}" class="btn secondary tiny">PDF</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding: 30px; color:#64748b;">
                    No assertion letters found. <a href="{{ route('assertions.create') }}">Create the first one.</a>
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
@endsection
