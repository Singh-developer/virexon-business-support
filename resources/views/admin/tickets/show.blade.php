@extends('layouts.app')

@section('content')
<style>
    .ticket-layout { display: flex; flex-direction: column; gap: 20px; }
    @media (min-width: 768px) {
        .ticket-layout { flex-direction: row; align-items: flex-start; }
        .ticket-main { flex: 2; min-width: 0; }
        .ticket-sidebar { flex: 1; min-width: 0; }
    }
    
    .ticket-container { background: white; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .ticket-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; flex-wrap: wrap; }
    .ticket-body { padding: 20px; }
    .ticket-meta { margin-top: 10px; color: #64748b; font-size: 13px; line-height: 1.5; }
    .status-form { background: white; border-radius: 8px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
</style>

<div class="page-head" style="margin-bottom: 20px;">
    <div>
        <a href="{{ route('admin.tickets.index') }}" style="color: #64748b; text-decoration: none; font-size: 13px; display: inline-block; margin-bottom: 10px;">&larr; Back to Tickets</a>
        <h1 style="font-size: 24px; margin: 0; color: #0f172a;">Review Ticket #{{ $ticket->id }}</h1>
    </div>
</div>

<div class="ticket-layout">
    
    <div class="ticket-main ticket-container">
        <div class="ticket-header">
            <div>
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">{{ $ticket->title }}</h2>
                <div class="ticket-meta">
                    Submitted by <strong><a href="{{ route('agents.edit', $ticket->user_id) }}" class="link">{{ $ticket->user->name }}</a></strong> on {{ $ticket->created_at->format('M d, Y h:i A') }}
                </div>
            </div>
            <span class="badge" style="
                @if($ticket->status === 'open') background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;
                @elseif($ticket->status === 'in_progress') background: #fef3c7; color: #92400e; border: 1px solid #fde68a;
                @elseif($ticket->status === 'resolved') background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;
                @else background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; @endif
            ">
                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
            </span>
        </div>
        <div class="ticket-body">
            <div style="white-space: pre-wrap; color: #334155; line-height: 1.6;">{{ $ticket->message }}</div>
            
            @if($ticket->attachment_path)
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <h4 style="margin-top: 0; font-size: 14px; color: #475569; margin-bottom: 10px;">Attachment</h4>
                <a href="{{ Storage::url($ticket->attachment_path) }}" target="_blank" class="btn secondary tiny">
                    View File
                </a>
            </div>
            @endif
        </div>
    </div>

    <div class="ticket-sidebar status-form">
        <h3 style="margin-top: 0; font-size: 16px; margin-bottom: 15px;">Update Status</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Updating the status will automatically send a notification to the agent.</p>
        
        <form action="{{ route('admin.tickets.update', $ticket) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Status</label>
                <select name="status" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 14px;">
                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            
            <button type="submit" class="btn primary" style="width: 100%;">Save Changes</button>
        </form>
    </div>

</div>
@endsection

