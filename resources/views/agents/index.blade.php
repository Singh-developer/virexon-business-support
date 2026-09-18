@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">

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
    
    /* Bulk action bar */
    .bulk-bar { display: flex; align-items: center; gap: 10px; background: #eff6ff; border: 1px solid #bfdbfe; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; flex-wrap: wrap; }
    .bulk-bar .count { font-weight: 700; color: #1e40af; }
    .bulk-bar .count small { display: block; font-weight: 500; color: #64748b; }
    .agent-checkbox, #selectAllTop { width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb; }
    table.dataTable tbody td.dt-body-center, table.dataTable thead th.dt-body-center { text-align: center; }
    
    /* DataTable overrides to fit design */
    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">AGENT MANAGEMENT</div>
        <h1>Agents</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px; max-width: 800px;">Admins can create, update, activate/deactivate, and manage every agent. Filter, select columns, export data or import new agents.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-shrink: 0;">
        <button class="btn secondary" onclick="document.getElementById('importModal').style.display='block'">
            <i class="fa-solid fa-file-import"></i> Import CSV
        </button>
        <a class="btn primary" href="{{ route('agents.create') }}">+ New Agent</a>
    </div>
</div>

@if(session('import_errors'))
<div class="flash error" style="margin-bottom: 20px;">
    <strong>Some rows could not be imported:</strong>
    <ul style="margin: 8px 0 0 18px; list-style: disc; text-align: left;">
        @foreach(session('import_errors') as $errorLine)
        <li>{{ $errorLine }}</li>
        @endforeach
    </ul>
</div>
@endif

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
            <label>Login Status</label>
            <select id="status-filter">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div>
            <label>App Status</label>
            <select id="app-status-filter">
                <option value="">All Applications</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div style="flex-grow: 1;"></div>
    </div>

    <div id="bulkBar" class="bulk-bar" style="display:none;">
        <span class="count">
            <span id="bulkCount">0</span> agent(s) selected
            <small>Apply to the current selection</small>
        </span>
        <button type="button" id="bulkTrash" class="btn tiny" style="background:#dc2626;color:#fff;border:1px solid #b91c1c;"><i class="fa-solid fa-trash"></i> Move to Trash</button>
        <button type="button" id="bulkEnable" class="btn tiny primary"><i class="fa-solid fa-user-check"></i> Enable</button>
        <button type="button" id="bulkDisable" class="btn tiny secondary"><i class="fa-solid fa-user-slash"></i> Disable</button>
        <button type="button" id="bulkClear" class="btn tiny" style="border:1px solid #cbd5e1;">Clear</button>
    </div>

    <form id="bulkForm" method="POST" action="{{ route('agents.bulk') }}" style="display:none;">
        @csrf
        <input type="hidden" name="action" id="bulkAction" value="">
        <input type="hidden" name="ids" id="bulkIds" value="">
    </form>

    <!-- Bulk Trash Confirmation Modal -->
    <div id="trashModal" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
        <div style="background-color:#fff; margin: 10% auto; padding: 24px; border-radius:12px; width: 480px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
            <h3 style="margin-top:0; font-weight:700; font-size:20px; color:#1e293b; margin-bottom:12px;">Move agents to trash?</h3>
            <p style="font-size:14px; color:#64748b; margin-bottom:20px; line-height:1.6;">
                <strong style="color:#1e293b;" id="trashCount">0</strong> selected agent(s) will be moved to trash. Their virtual card, sanction letters, documents, advances, commissions, payments and references will also be trashed. Files are kept until permanently deleted, and this can be undone from the Trash.
            </p>
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="btn secondary" onclick="document.getElementById('trashModal').style.display='none'">Cancel</button>
                <button type="button" id="trashConfirm" class="btn primary" style="background:#dc2626; border:1px solid #b91c1c;">Move to Trash</button>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table id="agentsTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th style="width:32px; text-align:center;"><input type="checkbox" id="selectAllTop" aria-label="Select all agents"></th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Agent ID No</th>
                    <th>PAN Number</th>
                    <th>DOB</th>
                    <th>City</th>
                    <th>Business</th>
                    <th>Virtual Card</th>
                    <th>Max Limit</th>
                    <th>Commission</th>
                    <th>Status</th>
                    <th>App Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agents as $agent)
                @php $appStatus = $agent->detail->application_status ?? 'pending'; @endphp
                <tr data-status="{{ $agent->status }}" data-appstatus="{{ $appStatus }}" data-created="{{ $agent->created_at->format('Y-m-d') }}" data-id="{{ $agent->id }}">
                    <td style="text-align:center;"><input type="checkbox" class="agent-checkbox" aria-label="Select {{ $agent->name }}"></td>
                    <td>{{ $agent->id }}</td>
                    <td><a class="link" href="{{ route('agents.show', $agent) }}"><strong>{{ $agent->name }}</strong></a></td>
                    <td>{{ $agent->email }}</td>
                    <td>{{ $agent->phone ?: ($agent->detail->mobile ?? '—') }}</td>
                    <td>{{ $agent->detail->agent_id_number ?? '—' }}</td>
                    <td>{{ $agent->detail->pan_number ?? '—' }}</td>
                    <td>{{ $agent->detail->date_of_birth ?? '—' }}</td>
                    <td>{{ $agent->detail->current_city ?? '—' }}</td>
                    <td>{{ $agent->business?->name ?: '—' }}</td>
                    <td>
                        @if($agent->virtualCard)
                            {{ $agent->virtualCard->reference }} ({{ ucfirst($agent->virtualCard->status->value) }})
                        @else
                            Missing
                        @endif
                    </td>
                    <td>
                        <span style="font-size:12px; font-weight:700; color:#1e40af;">
                            ₹{{ number_format($agent->detail->max_limit ?? 500000, 0) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $commType = $agent->detail->commission_type ?? 'percentage';
                        @endphp
                        <span style="font-size:12px; font-weight:700; color:#059669;">
                            @if($commType === 'fixed')
                                ₹{{ number_format($agent->detail->commission_fixed ?? 0, 0) }} fixed
                            @else
                                {{ number_format($agent->detail->commission_rate ?? 0, 2) }}%
                            @endif
                        </span>
                    </td>
                    <td><span class="badge {{ $agent->status==='active'?'success':'neutral' }}">{{ ucfirst($agent->status) }}</span></td>
                    <td>
                        <span class="badge {{ $appStatus === 'approved' ? 'success' : ($appStatus === 'rejected' ? 'failed' : 'neutral') }}">
                            {{ ucfirst($appStatus) }}
                        </span>
                    </td>
                    <td>{{ $agent->created_at->format('Y-m-d') }}</td>
                    <td class="flex items-center gap-2">
                        <button type="button" class="btn tiny secondary open-agent-modal" data-agent-id="{{ $agent->id }}" title="Preview uploaded files, login access & commission">
                            <i class="fa-solid fa-folder-open"></i> View Files
                        </button>
                        <a class="btn tiny" href="{{ route('agents.edit', $agent) }}">Manage</a>
                        <form method="POST" action="{{ route('agents.toggle-status', $agent) }}" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn tiny {{ $agent->status === 'active' ? 'secondary' : 'primary' }}" onclick="return confirm('Toggle login access for this agent?');">
                                {{ $agent->status === 'active' ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('agents.destroy', $agent) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn tiny" style="background:#dc2626;color:#fff;border:1px solid #b91c1c;" onclick="return confirm('Move this agent to trash? Its virtual card, sanction letters, documents, advances, commissions, payments and references will also be trashed. Files are kept until permanently deleted, and this can be undone from the Trash.');">
                                Trash
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
    <div style="background-color:#fff; margin: 10% auto; padding: 24px; border-radius:12px; width: 450px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <h3 style="margin-top:0; font-weight:700; font-size:20px; color:#1e293b; margin-bottom:12px;">Import Agents via CSV</h3>
        <p style="font-size:14px; color:#64748b; margin-bottom:20px; line-height: 1.5;">
            Upload a CSV file containing at least <code>name</code> and <code>email</code> columns.<br><br>
            Required for new agents: <code>phone</code> (10-digit mobile) and <code>pan_number</code> (format ABCDE1234F).<br>
            Default login password will be <code>{pan_number}@{last 4 digits of phone}</code>, e.g. <code>ABCDE1234F@3210</code>.<br><br>
            Other optional columns: <code>agent_id_number</code>, <code>status</code>.<br><br>
            <a href="{{ route('agents.import.sample') }}" style="color: #3b82f6; text-decoration: underline; font-weight: 500;"><i class="fa-solid fa-download"></i> Download Sample CSV</a>
        </p>
        <form method="POST" action="{{ route('agents.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="csv_file" accept=".csv" required style="margin-bottom:20px; width:100%; padding: 10px; border: 1px dashed #cbd5e1; border-radius: 6px;">
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="btn secondary" onclick="document.getElementById('importModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn primary">Import CSV</button>
            </div>
        </form>
    </div>
</div>

<!-- Agent "View Files" Popup -->
<div id="agentModal" style="display:none; position:fixed; z-index:101; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); overflow-y:auto;">
    <div style="background-color:#fff; margin: 5% auto; padding: 24px; border-radius:12px; width: 640px; max-width: 94%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
            <div>
                <h3 style="margin:0; font-weight:700; font-size:20px; color:#1e293b;" id="agentModalTitle">Manage Agent</h3>
                <span class="badge neutral" id="agentModalStatus" style="margin-top:6px;">—</span>
            </div>
            <button type="button" class="btn tiny secondary" onclick="document.getElementById('agentModal').style.display='none'">✕ Close</button>
        </div>

        <!-- Login Access -->
        <div style="border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px;">
            <h4 style="margin:0 0 10px; font-size:14px; font-weight:700; color:#1e293b;">Login Access</h4>
            <form method="POST" id="loginForm" style="display:flex; align-items:center; gap:12px;">
                @csrf
                @method('PATCH')
                <label style="font-size:13px; font-weight:600; color:#475569;">Login Status</label>
                <select name="status" id="loginStatus" style="padding:8px; border:1px solid #cbd5e1; border-radius:4px; font-size:13px;">
                    <option value="active">Enabled</option>
                    <option value="inactive">Disabled</option>
                </select>
                <button type="submit" class="btn tiny primary">Save Login Access</button>
            </form>
            <p style="margin:10px 0 0; font-size:12px; color:#64748b;">When disabled, the agent is logged out and can no longer sign in.</p>
        </div>

        <!-- Application Status -->
        <div style="border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px;">
            <h4 style="margin:0 0 10px; font-size:14px; font-weight:700; color:#1e293b;">Application Status</h4>
            <form method="POST" id="appForm" style="display:flex; align-items:center; gap:12px;">
                @csrf
                @method('PATCH')
                <label style="font-size:13px; font-weight:600; color:#475569;">Status</label>
                <select name="application_status" id="appStatus" style="padding:8px; border:1px solid #cbd5e1; border-radius:4px; font-size:13px;">
                    <option value="pending">Pending</option>
                    <option value="form_received">Form Received</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <button type="submit" class="btn tiny primary">Save Application Status</button>
            </form>
            <p style="margin:10px 0 0; font-size:12px; color:#64748b;">Marking as "Form Received" enables the agent to upload their documents.</p>
        </div>

        <!-- Limit & Commission -->
        <div style="border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px;">
            <h4 style="margin:0 0 12px; font-size:14px; font-weight:700; color:#1e293b;">Limit & Commission</h4>
            <form method="POST" id="commForm">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="save">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#475569;">Max Limit (₹)</label>
                        <input type="number" step="0.01" min="0" name="max_limit" id="fieldMaxLimit" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:4px; margin-top:4px;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#475569;">Commission Type</label>
                        <select name="commission_type" id="fieldCommType" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:4px; margin-top:4px;">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed (₹)</option>
                        </select>
                    </div>
                    <div id="commRateWrap" style="display:block;">
                        <label style="font-size:12px; font-weight:600; color:#475569;">Commission Rate (%)</label>
                        <input type="number" step="0.0001" min="0" max="100" name="commission_rate" id="fieldCommRate" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:4px; margin-top:4px;">
                    </div>
                    <div id="commFixedWrap" style="display:none;">
                        <label style="font-size:12px; font-weight:600; color:#475569;">Commission Fixed (₹)</label>
                        <input type="number" step="0.01" min="0" name="commission_fixed" id="fieldCommFixed" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:4px; margin-top:4px;">
                    </div>
                </div>
                <div style="margin-top:12px; text-align:right;">
                    <button type="submit" class="btn tiny primary">Save Limit & Commission</button>
                </div>
            </form>
        </div>

        <!-- Uploaded Files (Preview Only) -->
        <div style="border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px;">
            <h4 style="margin:0 0 12px; font-size:14px; font-weight:700; color:#1e293b;">Uploaded Documents</h4>
            <p style="margin:0 0 12px; font-size:12px; color:#64748b;">
                Files below are what the agent uploaded from their dashboard. To approve/reject them,
                <a id="reviewAllLink" href="#" target="_blank" rel="noopener" style="color:#2563eb; font-weight:600;">open the full review page</a>.
            </p>
            <div id="agentFilesList"></div>
        </div>

        <!-- Sanction Letters (status can be changed here, similar to documents) -->
        <div style="border:1px solid #e2e8f0; border-radius:8px; padding:16px;">
            <h4 style="margin:0 0 12px; font-size:14px; font-weight:700; color:#1e293b;">Sanction Letter Status</h4>
            <p style="margin:0 0 12px; font-size:12px; color:#64748b;">
                Change each letter's review status directly here (works even after Approved).
                For full preview use
                <a id="reviewSanctionsLink" href="#" target="_blank" rel="noopener" style="color:#2563eb; font-weight:600;">open sanction letters</a>.
            </p>
            <div id="agentSanctionsList"></div>
        </div>
    </div>
</div>

@php
    $agentManifest = $agents->map(function ($a) {
        return [
            'id'        => $a->id,
            'name'      => $a->name,
            'status'    => $a->status,
            'application_status' => $a->detail?->application_status ?? 'pending',
            'max_limit' => $a->detail?->max_limit !== null ? (float) $a->detail->max_limit : null,
            'commission_type'  => $a->detail?->commission_type ?? 'percentage',
            'commission_rate'  => $a->detail?->commission_rate !== null ? (float) $a->detail->commission_rate : null,
            'commission_fixed' => $a->detail?->commission_fixed !== null ? (float) $a->detail->commission_fixed : null,
            'documents' => $a->documents->map(function ($d) {
                return [
                    'type'          => $d->document_type,
                    'type_label'    => \App\Models\AgentDocument::typeLabel($d->document_type) . (!empty($d->slot) && $d->slot !== 'default' ? ' (' . ucfirst($d->slot) . ')' : ''),
                    'status'        => $d->status,
                    'original_name' => $d->original_name,
                    'url'           => \Illuminate\Support\Facades\Storage::url($d->file_path),
                ];
            })->values()->all(),
            'sanctions' => $a->sanctionLetters->map(function ($s) {
                return [
                    'id'            => $s->id,
                    'no'            => $s->sanction_letter_no ?? ('#' . $s->id),
                    'review_status' => $s->review_status ?? 'pending',
                    'has_signed'    => !empty($s->signed_pdf_path),
                    'upload_count'  => (int) ($s->signed_pdf_upload_count ?? 0),
                ];
            })->values()->all(),
        ];
    })->keyBy('id')->all();
@endphp
<script>window.AGENT_DATA = @json($agentManifest);</script>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#agentsTable').DataTable({
        dom: 'Blfrtip',
        select: {
            style: 'multi',
            selector: 'td.dt-select-noop'
        },
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
            { targets: 0, orderable: false, className: 'dt-body-center', width: '32px' },
            { targets: [1, 5, 6, 7, 8], visible: false }
        ],
        order: [[15, 'desc']],
        language: {
            search: "Quick Search:"
        }
    });

    // Export only visible columns and never the checkbox column.
    function exportCols(idx) {
        return idx !== 0 && table.column(idx).visible();
    }

    // Selection is driven entirely by the checkboxes in the DOM. DataTables
    // Select state is kept in sync as a bonus, but bulk actions always read
    // the checked checkboxes, so what you see is exactly what gets acted on.
    function getSelectedIds() {
        var ids = [];
        $('#agentsTable .agent-checkbox:checked').each(function () {
            var id = $(this).closest('tr').data('id');
            if (id && $.inArray(id, ids) === -1) {
                ids.push(id);
            }
        });
        return ids;
    }

    function updateSelectionUI() {
        var count = $('#agentsTable .agent-checkbox:checked').length;
        var total = $('#agentsTable .agent-checkbox').length;
        var head = $('#selectAllTop');

        $('#bulkCount').text(count);
        $('#bulkBar').toggle(count > 0);

        if (count === 0) {
            head.prop('checked', false).prop('indeterminate', false);
        } else if (count === total) {
            head.prop('checked', true).prop('indeterminate', false);
        } else {
            head.prop('checked', true).prop('indeterminate', true);
        }
    }

    $('#agentsTable').on('change', '.agent-checkbox', function () {
        var checked = $(this).prop('checked');
        var tr = $(this).closest('tr');

        tr.toggleClass('selected', checked);
        try {
            if (checked) {
                table.row(tr).select();
            } else {
                table.row(tr).deselect();
            }
        } catch (e) { /* Select state is optional; DOM is the source of truth */ }

        updateSelectionUI();
    });

    $('#selectAllTop').on('change', function () {
        var checked = $(this).prop('checked');

        $('#agentsTable .agent-checkbox').prop('checked', checked);
        $('#agentsTable tbody tr').toggleClass('selected', checked);

        try {
            if (checked) {
                table.rows().select();
            } else {
                table.rows().deselect();
            }
        } catch (e) { }

        updateSelectionUI();
    });

    $('#bulkClear').on('click', function () {
        $('#agentsTable .agent-checkbox').prop('checked', false);
        $('#agentsTable tbody tr').removeClass('selected');
        try { table.rows().deselect(); } catch (e) { }
        updateSelectionUI();
    });

    function bulkSubmit(action) {
        var ids = getSelectedIds();
        if (!ids.length) return;

        $('#bulkAction').val(action);
        $('#bulkIds').val(ids.join(','));

        if (action === 'trash') {
            $('#trashCount').text(ids.length);
            document.getElementById('trashModal').style.display = 'block';
            return;
        }

        $('#bulkForm').submit();
    }

    $('#trashConfirm').on('click', function () {
        document.getElementById('trashModal').style.display = 'none';
        $('#bulkForm').submit();
    });

    $('#bulkTrash').on('click', function () { bulkSubmit('trash'); });
    $('#bulkEnable').on('click', function () { bulkSubmit('activate'); });
    $('#bulkDisable').on('click', function () { bulkSubmit('deactivate'); });

    // ------------------------------------------------------------------//
    // Agent popup: files + login access + limit/commission                //
    // ------------------------------------------------------------------//
    var agentsBase = "{{ url('agents') }}";

    function toggleCommFields() {
        var isPercentage = $('#fieldCommType').val() === 'percentage';
        $('#commRateWrap').css('display', isPercentage ? 'block' : 'none');
        $('#commFixedWrap').css('display', isPercentage ? 'none' : 'block');
    }

    var pdfjsReady = null;

    function ensurePdfJs() {
        if (pdfjsReady) return pdfjsReady;
        pdfjsReady = new Promise(function (resolve, reject) {
            var lib = document.createElement('script');
            lib.src = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js';
            lib.onload = function () {
                var worker = document.createElement('script');
                worker.src = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
                worker.onload = function () {
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = worker.src;
                    resolve(window.pdfjsLib);
                };
                worker.onerror = reject;
                document.body.appendChild(worker);
            };
            lib.onerror = reject;
            document.body.appendChild(lib);
        });
        return pdfjsReady;
    }

    // Render the first page of a PDF onto a canvas so admin sees it as an image.
    function renderPdfPreview(url, box) {
        ensurePdfJs().then(function (PdfLib) {
            return PdfLib.getDocument({ url: url }).promise.then(function (pdfDoc) {
                return pdfDoc.getPage(1).then(function (page) {
                    var boxW = 132, boxH = 168;
                    var base = page.getViewport({ scale: 1 });
                    var scale = Math.min(boxW / base.width, boxH / base.height);
                    var viewport = page.getViewport({ scale: scale });
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.floor(viewport.width);
                    canvas.height = Math.floor(viewport.height);
                    canvas.style.maxWidth = '100%';
                    return page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport })
                        .promise.then(function () {
                            box.empty();
                            box.append(canvas);
                        });
                });
            });
        }).catch(function () {
            box.html('<span style="font-size:11px; color:#94a3b8; text-align:center; padding:4px;">Preview unavailable. Use&nbsp;"Open Original".</span>');
        });
    }

    function openAgentModal(id) {
        var data = window.AGENT_DATA[String(id)];
        if (!data) return;

        $('#agentModalTitle').text(data.name);
        $('#agentModalStatus').text('Login: ' + (data.status === 'active' ? 'Enabled' : 'Disabled'))
            .attr('class', 'badge ' + (data.status === 'active' ? 'success' : 'failed'));

        $('#loginStatus').val(data.status);
        $('#loginForm').attr('action', agentsBase + '/' + id + '/toggle-status');

        $('#appStatus').val(data.application_status || 'pending');
        $('#appForm').attr('action', agentsBase + '/' + id + '/application-status');

        $('#fieldMaxLimit').val(data.max_limit === null ? '' : data.max_limit);
        $('#fieldCommType').val(data.commission_type || 'percentage');
        $('#fieldCommRate').val(data.commission_rate === null ? '' : data.commission_rate);
        $('#fieldCommFixed').val(data.commission_fixed === null ? '' : data.commission_fixed);
        toggleCommFields();
        $('#commForm').attr('action', agentsBase + '/' + id + '/limit-commission');
        $('#reviewAllLink').attr('href', "{{ url('admin/agents') }}" + '/' + id + '/documents');
        $('#reviewSanctionsLink').attr('href', "{{ url('sanctions') }}" + '?agent_id=' + id);

        var list = $('#agentFilesList').empty();
        if (!data.documents.length) {
            list.html('<p style="margin:0; font-size:13px; color:#94a3b8;">No documents uploaded by this agent yet.</p>');
        } else {
            data.documents.forEach(function (doc) {
                var badgeClass = 'neutral';
                if (doc.status === 'approved') badgeClass = 'success';
                if (doc.status === 'rejected') badgeClass = 'failed';
                var isPdf = /\.pdf$/i.test(doc.url);
                var preview;
                if (isPdf) {
                    preview = '<div class="pdf-thumb" data-pdf="' + doc.url + '" style="width:132px; height:168px; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:6px; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">' +
                        '<span style="color:#94a3b8;">Loading…</span></div>';
                } else {
                    preview = '<a href="' + doc.url + '" target="_blank" rel="noopener" style="flex-shrink:0;"><img src="' + doc.url + '" alt="' + (doc.original_name || doc.type_label) + '" style="max-width:132px; max-height:168px; border-radius:6px; border:1px solid #e2e8f0; display:block;"></a>';
                }
                var row = $(
                    '<div style="display:flex; align-items:flex-start; gap:10px; padding:8px 10px; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:8px; background:#f8fafc;">' +
                        preview +
                        '<div style="flex:1; min-width:0;">' +
                            '<div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">' +
                                '<span style="font-weight:700; font-size:13px; color:#1e293b;">' + doc.type_label + '</span>' +
                                '<span class="badge ' + badgeClass + '">' + doc.status + '</span>' +
                                (isPdf ? '<span class="badge neutral">PDF → image preview</span>' : '') +
                            '</div>' +
                            '<div style="margin-top:4px; font-size:12px; color:#64748b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + (doc.original_name || '') + '">' + (doc.original_name || '') + '</div>' +
                            '<div style="margin-top:6px;"><a href="' + doc.url + '" target="_blank" rel="noopener" class="btn tiny secondary">Open Original</a></div>' +
                        '</div>' +
                    '</div>'
                );
                list.append(row);
                if (isPdf) renderPdfPreview(doc.url, row.find('.pdf-thumb'));
            });
        }

        // Sanction letters with inline status changer.
        var sList = $('#agentSanctionsList').empty();
        var sanctions = data.sanctions || [];
        var csrfToken = '{{ csrf_token() }}';
        var sanctionsBase = "{{ url('sanctions') }}";
        if (!sanctions.length) {
            sList.html('<p style="margin:0; font-size:13px; color:#94a3b8;">No sanction letters issued to this agent yet.</p>');
        } else {
            sanctions.forEach(function (s) {
                var badgeClass = 'neutral';
                if (s.review_status === 'approved') badgeClass = 'success';
                if (s.review_status === 'reupload_required' || s.review_status === 'rejected') badgeClass = 'failed';
                if (s.review_status === 'under_review') badgeClass = 'neutral';
                var signedTxt = s.has_signed ? 'Signed copy uploaded (' + s.upload_count + ')' : 'Signed copy not uploaded';
                var opts = ['pending', 'under_review', 'reupload_required', 'approved'].map(function (st) {
                    return '<option value="' + st + '"' + (s.review_status === st ? ' selected' : '') + '>' + st.replace('_', ' ') + '</option>';
                }).join('');
                var row = $(
                    '<div style="display:flex; align-items:flex-start; gap:10px; padding:8px 10px; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:8px; background:#f8fafc;">' +
                        '<div style="width:40px;height:48px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">✉️</div>' +
                        '<div style="flex:1; min-width:0;">' +
                            '<div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">' +
                                '<span style="font-weight:700; font-size:13px; color:#1e293b;">' + s.no + '</span>' +
                                '<span class="badge ' + badgeClass + '">' + s.review_status + '</span>' +
                            '</div>' +
                            '<div style="margin-top:4px; font-size:12px; color:#64748b;">' + signedTxt + '</div>' +
                            '<form method="POST" action="' + sanctionsBase + '/' + s.id + '/status" style="margin-top:8px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">' +
                                '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                                '<select name="review_status" style="padding:6px 8px; border:1px solid #cbd5e1; border-radius:4px; font-size:12px;">' + opts + '</select>' +
                                '<button type="submit" class="btn tiny primary" onclick="return confirm(\'Change this letter\\\'s review status? The agent will be notified.\');">Save Status</button>' +
                                '<a href="' + sanctionsBase + '/' + s.id + '/review" target="_blank" rel="noopener" class="btn tiny secondary">Review Letter</a>' +
                            '</form>' +
                        '</div>' +
                    '</div>'
                );
                sList.append(row);
            });
        }

        document.getElementById('agentModal').style.display = 'block';
    }

    $('#agentsTable').on('click', '.open-agent-modal', function () {
        openAgentModal($(this).data('agent-id'));
    });

    $('#fieldCommType').on('change', toggleCommFields);

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var node = table.row(dataIndex).node();
        var min = $('#min-date').val();
        var max = $('#max-date').val();
        var dateStr = $(node).data('created');
        if (min && dateStr < min) return false;
        if (max && dateStr > max) return false;

        var statusVal = $('#status-filter').val();
        if (statusVal && $(node).data('status') !== statusVal) return false;

        var appVal = $('#app-status-filter').val();
        if (appVal && $(node).data('appstatus') !== appVal) return false;

        return true;
    });

    $('#min-date, #max-date, #status-filter, #app-status-filter').on('change', function() {
        table.draw();
    });
});
</script>
@endsection