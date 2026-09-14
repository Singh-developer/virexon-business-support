<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<style>
    .dt-buttons { margin-bottom: 15px; }
    .dataTables_wrapper .dataTables_filter { margin-bottom: 15px; }
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .filter-container { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; flex-wrap: wrap; }
    .filter-container > div { display: flex; flex-direction: column; gap: 5px; }
    .filter-container input, .filter-container select { padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; color: #1e293b; background: white; }
    .filter-container label { font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    
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

    <div class="table-wrap">
        <table id="agentsTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
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
                <tr data-status="<?php echo e($agent->status); ?>" data-appstatus="<?php echo e($appStatus); ?>" data-created="<?php echo e($agent->created_at->format('Y-m-d')); ?>">
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
            Optional columns: <code>phone</code>, <code>agent_id_number</code>, <code>pan_number</code>, <code>status</code>.<br><br>
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

<script>
$(document).ready(function() {
    var table = $('#agentsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'colvis', text: 'Select Columns', className: 'btn secondary tiny' },
            { extend: 'copy', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } },
            { extend: 'csv', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } },
            { extend: 'excel', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } },
            { extend: 'pdf', className: 'btn secondary tiny', exportOptions: { columns: ':visible' }, orientation: 'landscape' },
            { extend: 'print', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } }
        ],
        pageLength: 25,
        columnDefs: [
            { targets: [0, 4, 5, 6, 7], visible: false }
        ],
        order: [[14, 'desc']],
        language: {
            search: "Quick Search:"
        }
    });

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