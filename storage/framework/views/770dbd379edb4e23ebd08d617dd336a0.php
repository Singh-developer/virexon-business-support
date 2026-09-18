<?php $__env->startSection('content'); ?>
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
</style>

<?php $isAdmin = auth()->user()->isAdmin(); ?>

<div class="page-head">
    <div>
        <div class="eyebrow">PAYMENT MANAGEMENT</div>
        <h1>Payments</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">Gateway-aware payment operations with status, verification and auditability. Filter and search live.</p>
    </div>
    <a class="btn primary" href="<?php echo e(route('payments.create')); ?>">+ New Payment</a>
</div>

<div class="panel" style="padding: 0; background: transparent; border: none; box-shadow: none;">
    <div class="filter-container">
        <div>
            <label>Start Date</label>
            <input type="date" id="pay-min-date" name="min">
        </div>
        <div>
            <label>End Date</label>
            <input type="date" id="pay-max-date" name="max">
        </div>
        <?php if($isAdmin): ?>
        <div>
            <label>Agent</label>
            <select id="pay-agent-filter">
                <option value="">All agents</option>
                <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($agent->id); ?>" <?php if((string) request('agent_id') === (string) $agent->id): echo 'selected'; endif; ?>><?php echo e($agent->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <?php endif; ?>
        <div>
            <label>Status</label>
            <select id="pay-status-filter">
                <option value="">All statuses</option>
                <?php $__currentLoopData = ['created','pending','processing','successful','failed','cancelled','refunded']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s); ?>" <?php if(request('status') === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label>Gateway</label>
            <select id="pay-gateway-filter">
                <option value="">All gateways</option>
                <option value="mock" <?php if(request('gateway') === 'mock'): echo 'selected'; endif; ?>>Mock</option>
                <option value="razorpay" <?php if(request('gateway') === 'razorpay'): echo 'selected'; endif; ?>>Razorpay</option>
                <option value="paytm" <?php if(request('gateway') === 'paytm'): echo 'selected'; endif; ?>>Paytm</option>
            </select>
        </div>
        <div>
            <label>Type</label>
            <select id="pay-type-filter">
                <option value="">All types</option>
                <option value="spending" <?php if(request('payment_type') === 'spending'): echo 'selected'; endif; ?>>Spending</option>
                <option value="repayment" <?php if(request('payment_type') === 'repayment'): echo 'selected'; endif; ?>">Repayment</option>
            </select>
        </div>
        <div style="flex-grow: 1;"></div>
    </div>

    <div class="table-wrap">
        <table id="paymentsTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Payment</th>
                    <?php if($isAdmin): ?>
                        <th>Agent</th>
                    <?php endif; ?>
                    <th>Business</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr data-created="<?php echo e($p->created_at?->format('Y-m-d')); ?>"
                        data-agent="<?php echo e($p->user_id); ?>"
                        data-status="<?php echo e($p->status->value); ?>"
                        data-gateway="<?php echo e(strtolower($p->gateway)); ?>"
                        data-type="<?php echo e($p->isRepayment() ? 'repayment' : 'spending'); ?>">
                        <td><a class="link mono" href="<?php echo e(route('payments.show', $p)); ?>"><?php echo e($p->reference); ?></a></td>
                        <?php if($isAdmin): ?>
                            <td><?php echo e($p->user?->name ?? '—'); ?></td>
                        <?php endif; ?>
                        <td><?php echo e($p->business->name); ?></td>
                        <td>
                            <?php if($p->isRepayment()): ?>
                                <span class="badge" style="background: #E8F8F5; color: #27AE60;">Repayment</span>
                            <?php else: ?>
                                <span class="badge" style="background: #FEF5E7; color: #E65100;">Spending</span>
                            <?php endif; ?>
                        </td>
                        <td data-order="<?php echo e($p->isRepayment() ? -$p->amount : $p->amount); ?>" style="font-weight: 600; color: <?php echo e($p->isRepayment() ? '#27AE60' : '#1565C0'); ?>">
                            <?php if($p->isRepayment()): ?>−<?php endif; ?> ₹<?php echo e(number_format($p->amount, 2)); ?>

                        </td>
                        <td><span class="badge neutral"><?php echo e(strtoupper($p->gateway)); ?></span></td>
                        <td><span class="badge <?php echo e($p->status->value); ?>"><?php echo e(ucfirst($p->status->value)); ?></span></td>
                        <td data-order="<?php echo e($p->created_at?->timestamp ?? 0); ?>"><?php echo e($p->created_at->format('d M Y H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e($isAdmin ? 8 : 7); ?>" style="text-align:center; padding: 30px; color:#64748b;">No payments yet.</td>
                    </tr>
                <?php endif; ?>
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
    var table = $('#paymentsTable').DataTable({
        dom: 'Blfrtip',
        buttons: [
            { extend: 'colvis', text: 'Select Columns', className: 'btn secondary tiny' },
            { extend: 'copy', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } },
            { extend: 'csv', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } },
            { extend: 'excel', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } },
            { extend: 'pdf', className: 'btn secondary tiny', exportOptions: { columns: ':visible' }, orientation: 'landscape' },
            { extend: 'print', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } }
        ],
        pageLength: 20,
        lengthMenu: [20, 40, 100],
        order: [[<?php echo e($isAdmin ? 7 : 6); ?>, 'desc']],
        language: {
            search: "Quick Search:"
        },
        // Seed live search from ?q= so old shared links keep working.
        search: { search: <?php echo json_encode(request('q', '')); ?> }
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'paymentsTable') return true;
        var node = table.row(dataIndex).node();
        if (!node) return true;

        var min = $('#pay-min-date').val();
        var max = $('#pay-max-date').val();
        var dateStr = $(node).data('created');
        if (min && dateStr < min) return false;
        if (max && dateStr > max) return false;

        var agentVal = $('#pay-agent-filter').val();
        if (agentVal && String($(node).data('agent')) !== agentVal) return false;

        var statusVal = $('#pay-status-filter').val();
        if (statusVal && $(node).data('status') !== statusVal) return false;

        var gatewayVal = $('#pay-gateway-filter').val();
        if (gatewayVal && String($(node).data('gateway')).toLowerCase() !== String(gatewayVal).toLowerCase()) return false;

        var typeVal = $('#pay-type-filter').val();
        if (typeVal && $(node).data('type') !== typeVal) return false;

        return true;
    });

    $('#pay-min-date, #pay-max-date, #pay-agent-filter, #pay-status-filter, #pay-gateway-filter, #pay-type-filter').on('change', function() {
        table.draw();
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/payments/index.blade.php ENDPATH**/ ?>