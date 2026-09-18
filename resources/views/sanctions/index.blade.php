@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<style>
    .dt-buttons { margin-bottom: 15px; }
    .dataTables_wrapper .dataTables_filter { margin-bottom: 15px; }
    .dataTables_wrapper .dataTables_length { margin-bottom: 15px; }
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .filter-container { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; flex-wrap: wrap; }
    .filter-container > div { display: flex; flex-direction: column; gap: 5px; }
    .filter-container input, .filter-container select { padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; color: #1e293b; background: white; }
    .filter-container label { font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }

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
        <p style="color:#64748b; font-size:14px; margin-top:5px;">Track the Agent &lt;-&gt; Admin workflow for every sanction letter. Filter, export or search letters.</p>
    </div>
    <a class="btn primary" href="{{ route('sanctions.create') }}">+ New Letter</a>
</div>

<div class="panel" style="padding: 0; background: transparent; border: none; box-shadow: none;">
    <div class="filter-container">
        <div>
            <label>Start Date</label>
            <input type="date" id="min-date" name="min">
        </div>
        <div>
            <label>End Date</label>
            <input type="date" id="max-date" name="max">
        </div>
        <div>
            <label>Review Status</label>
            <select id="review-filter">
                <option value="">All Statuses</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="under_review" {{ $status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                <option value="reupload_required" {{ $status === 'reupload_required' ? 'selected' : '' }}>Re-upload Required</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
            </select>
        </div>
        <div>
            <label>Sent Status</label>
            <select id="sent-filter">
                <option value="">All Letters</option>
                <option value="yes">Sent</option>
                <option value="no">Not Sent</option>
            </select>
        </div>
        <div style="flex-grow: 1;"></div>
        <div style="align-self: flex-end;">
            @if(!empty($filterAgent))
            <span style="font-size:13px;color:#1e40af;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:6px 12px;">
                👤 Agent: <strong>{{ $filterAgent->name }}</strong> ({{ $filterAgent->email }})
            </span>
            @endif
            @if($status !== 'all' || !empty($filterAgent))
            <a href="{{ route('sanctions.index') }}" style="font-size:13px;color:#1557d6;text-decoration:none; margin-left:10px;">Reset</a>
            @endif
        </div>
    </div>

    @if(!empty($filterAgent))
    <div style="margin-bottom:12px;">
        <a href="{{ route('agents.edit', $filterAgent->id) }}" style="font-size:13px;color:#64748b;text-decoration:none;">← Back to Agent ({{ $filterAgent->name }})</a>
    </div>
    @endif

    <div class="table-wrap">
        <table id="sanctionsTable" class="display nowrap" style="width:100%">
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
                @php
                    $ws = $letter->workflowStatus();
                    $isSent = ($letter->status === 'sent' && $letter->sent_at) ? 'yes' : 'no';
                @endphp
                <tr data-created="{{ $letter->created_at?->format('Y-m-d') }}" data-review="{{ $ws['key'] }}" data-sent="{{ $isSent }}">
                    <td class="mono">{{ $letter->sanction_letter_no }}</td>
                    <td>
                        <strong>{{ $letter->user->name }}</strong><br>
                        <small style="color:#64748b;">{{ $letter->user->email }}</small>
                    </td>
                    <td data-order="{{ $letter->created_at?->timestamp ?? 0 }}">{{ $letter->created_at?->format('d M Y') ?? '—' }}</td>
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
                            <form method="POST" action="{{ route('sanctions.destroy', $letter) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn secondary tiny" style="background:#dc2626;color:#fff;border:1px solid #b91c1c;" onclick="return confirm('Move this sanction letter to trash? The PDF and any signed uploads stay on disk until permanently deleted from the Trash.');">
                                    Trash
                                </button>
                            </form>
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
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

<script>
$(document).ready(function() {
    // Export only visible columns and never the Actions column (last).
    var table = $('#sanctionsTable').DataTable({
        dom: 'Blfrtip',
        buttons: [
            { extend: 'colvis', text: 'Select Columns', className: 'btn secondary tiny' },
            { extend: 'copy', className: 'btn secondary tiny', exportOptions: { columns: exportCols } },
            { extend: 'csv', className: 'btn secondary tiny', exportOptions: { columns: exportCols } },
            { extend: 'excel', className: 'btn secondary tiny', exportOptions: { columns: exportCols } },
            { extend: 'pdf', className: 'btn secondary tiny', exportOptions: { columns: exportCols }, orientation: 'landscape' },
            { extend: 'print', className: 'btn secondary tiny', exportOptions: { columns: exportCols } }
        ],
        pageLength: 20,
        lengthMenu: [20, 40, 100],
        columnDefs: [
            { targets: -1, orderable: false }
        ],
        order: [[2, 'desc']],
        language: {
            search: "Quick Search:"
        }
    });

    function exportCols(idx) {
        // Skip the Actions column (last visible index).
        var total = table.columns().count();
        return idx !== total - 1 && table.column(idx).visible();
    }

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'sanctionsTable') return true;
        var node = table.row(dataIndex).node();
        if (!node) return true;
        var min = $('#min-date').val();
        var max = $('#max-date').val();
        var dateStr = $(node).data('created');
        if (min && dateStr < min) return false;
        if (max && dateStr > max) return false;

        var reviewVal = $('#review-filter').val();
        if (reviewVal && $(node).data('review') !== reviewVal) return false;

        var sentVal = $('#sent-filter').val();
        if (sentVal && String($(node).data('sent')) !== sentVal) return false;

        return true;
    });

    $('#min-date, #max-date, #review-filter, #sent-filter').on('change', function() {
        table.draw();
    });
});
</script>
@endsection
