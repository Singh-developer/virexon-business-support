<?php $__env->startSection('content'); ?>
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
        <a class="btn primary" href="<?php echo e(route('agents.create')); ?>">+ New Agent</a>
    </div>
</div>

<?php if(session('import_errors')): ?>
<div class="flash error" style="margin-bottom: 20px;">
    <strong>Some rows could not be imported:</strong>
    <ul style="margin: 8px 0 0 18px; list-style: disc; text-align: left;">
        <?php $__currentLoopData = session('import_errors'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $errorLine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($errorLine); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>

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

    <form id="bulkForm" method="POST" action="<?php echo e(route('agents.bulk')); ?>" style="display:none;">
        <?php echo csrf_field(); ?>
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
                <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $appStatus = $agent->detail->application_status ?? 'pending'; ?>
                <tr data-status="<?php echo e($agent->status); ?>" data-appstatus="<?php echo e($appStatus); ?>" data-created="<?php echo e($agent->created_at->format('Y-m-d')); ?>" data-id="<?php echo e($agent->id); ?>">
                    <td style="text-align:center;"><input type="checkbox" class="agent-checkbox" aria-label="Select <?php echo e($agent->name); ?>"></td>
                    <td><?php echo e($agent->id); ?></td>
                    <td><a class="link" href="<?php echo e(route('agents.show', $agent)); ?>"><strong><?php echo e($agent->name); ?></strong></a></td>
                    <td><?php echo e($agent->email); ?></td>
                    <td><?php echo e($agent->phone ?: ($agent->detail->mobile ?? '—')); ?></td>
                    <td><?php echo e($agent->detail->agent_id_number ?? '—'); ?></td>
                    <td><?php echo e($agent->detail->pan_number ?? '—'); ?></td>
                    <td><?php echo e($agent->detail->date_of_birth ?? '—'); ?></td>
                    <td><?php echo e($agent->detail->current_city ?? '—'); ?></td>
                    <td><?php echo e($agent->business?->name ?: '—'); ?></td>
                    <td>
                        <?php if($agent->virtualCard): ?>
                            <?php echo e($agent->virtualCard->reference); ?> (<?php echo e(ucfirst($agent->virtualCard->status->value)); ?>)
                        <?php else: ?>
                            Missing
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size:12px; font-weight:700; color:#1e40af;">
                            ₹<?php echo e(number_format($agent->detail->max_limit ?? 500000, 0)); ?>

                        </span>
                    </td>
                    <td>
                        <?php
                            $commType = $agent->detail->commission_type ?? 'percentage';
                        ?>
                        <span style="font-size:12px; font-weight:700; color:#059669;">
                            <?php if($commType === 'fixed'): ?>
                                ₹<?php echo e(number_format($agent->detail->commission_fixed ?? 0, 0)); ?> fixed
                            <?php else: ?>
                                <?php echo e(number_format($agent->detail->commission_rate ?? 0, 2)); ?>%
                            <?php endif; ?>
                        </span>
                    </td>
                    <td><span class="badge <?php echo e($agent->status==='active'?'success':'neutral'); ?>"><?php echo e(ucfirst($agent->status)); ?></span></td>
                    <td>
                        <span class="badge <?php echo e($appStatus === 'approved' ? 'success' : ($appStatus === 'rejected' ? 'failed' : 'neutral')); ?>">
                            <?php echo e(ucfirst($appStatus)); ?>

                        </span>
                    </td>
                    <td><?php echo e($agent->created_at->format('Y-m-d')); ?></td>
                    <td class="flex items-center gap-2">
                        <a class="btn tiny" href="<?php echo e(route('agents.edit', $agent)); ?>">Manage</a>
                        <form method="POST" action="<?php echo e(route('agents.toggle-status', $agent)); ?>" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn tiny <?php echo e($agent->status === 'active' ? 'secondary' : 'primary'); ?>" onclick="return confirm('Toggle login access for this agent?');">
                                <?php echo e($agent->status === 'active' ? 'Disable' : 'Enable'); ?>

                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('agents.destroy', $agent)); ?>" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn tiny" style="background:#dc2626;color:#fff;border:1px solid #b91c1c;" onclick="return confirm('Move this agent to trash? Its virtual card, sanction letters, documents, advances, commissions, payments and references will also be trashed. Files are kept until permanently deleted, and this can be undone from the Trash.');">
                                Trash
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
            <a href="<?php echo e(route('agents.import.sample')); ?>" style="color: #3b82f6; text-decoration: underline; font-weight: 500;"><i class="fa-solid fa-download"></i> Download Sample CSV</a>
        </p>
        <form method="POST" action="<?php echo e(route('agents.import')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="file" name="csv_file" accept=".csv" required style="margin-bottom:20px; width:100%; padding: 10px; border: 1px dashed #cbd5e1; border-radius: 6px;">
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="btn secondary" onclick="document.getElementById('importModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn primary">Import CSV</button>
            </div>
        </form>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/agents/index.blade.php ENDPATH**/ ?>