<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    .ov-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .ov-table th {
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
        padding: 10px 14px;
        border-bottom: 1px solid #e2e8f0;
    }

    .ov-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .ov-table tbody tr:hover {
        background: #f8fafc;
    }

    .ov-agent-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #e0f2fe;
        color: #0369a1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
    }

    .ov-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 99px;
        white-space: nowrap;
        display: inline-block;
    }

    .ov-pill.green {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .ov-pill.blue {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .ov-pill.red {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .ov-pill.slate {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .ov-pill.amber {
        background: #ffedd5;
        color: #9a3412;
        border: 1px solid #fed7aa;
    }

    .ov-bar {
        height: 6px;
        border-radius: 99px;
        background: #e2e8f0;
        overflow: hidden;
        min-width: 90px;
    }

    .ov-bar>div {
        height: 100%;
        background: #22c55e;
        border-radius: 99px;
    }

    .ov-count {
        font-size: 11px;
        color: #64748b;
        margin-top: 3px;
    }

    .ov-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        max-width: 100%;
    }

    .ov-table {
        min-width: 760px;
    }

    .ov-table th,
    .ov-table td {
        white-space: nowrap;
    }

    .ov-table td:first-child {
        white-space: normal;
        min-width: 220px;
    }

    @media (max-width: 640px) {
        .ov-table {
            font-size: 12px;
        }

        .ov-table th,
        .ov-table td {
            padding: 10px;
        }
    }
</style>

<div class="page-head" style="margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <div class="eyebrow">AGENT DOCUMENTS</div>
        <h1 style="font-size:22px;margin-bottom:4px;">Documents Overview</h1>
        <p style="color:#64748b;font-size:13px;margin:0;">Live status of every agent's document submission. Click an agent to review their documents.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <?php
        $allPending = $agents->sum('pending');
        $allAction = $agents->sum('action_needed');
        $allApproved = $agents->sum('approved');
        $allUploaded = $agents->sum('uploaded');
        $allNotUp = $agents->sum('not_uploaded');
        ?>
        <?php if($allPending): ?> <span class="ov-pill blue">⏳ <?php echo e($allPending); ?> Awaiting Review</span> <?php endif; ?>
        <?php if($allAction): ?> <span class="ov-pill red">✗ <?php echo e($allAction); ?> Action Needed</span> <?php endif; ?>
        <span class="ov-pill green">✓ <?php echo e($allApproved); ?> Approved</span>
        <span class="ov-pill slate">— <?php echo e($allNotUp); ?> Not Uploaded</span>
    </div>
</div>

<?php if(session('success')): ?>
<div class="flash success" style="margin-bottom:16px;">✓ <?php echo e(session('success')); ?></div>
<?php endif; ?>

<?php
$statusStyle = [
'approved' => 'green',
'form_received' => 'blue',
'pending' => 'amber',
'rejected' => 'red',
];
?>

<div class="panel" style="padding:0;overflow:hidden;">
    <?php if($agents->isEmpty()): ?>
    <div style="padding:60px 20px;text-align:center;color:#94a3b8;font-size:14px;">
        No active agents found. Register or activate agents to review their documents.
    </div>
    <?php else: ?>
    <div class="ov-scroll">
    <table id="documentsOverviewTable" class="ov-table">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Application Status</th>
                    <th style="min-width:150px;">Document Progress</th>
                    <th>Uploaded</th>
                    <th>Pending</th>
                    <th>Action Needed</th>
                    <th>Review</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $agent = $row['agent']; ?>
                <tr>
                    <td>
                        <a href="<?php echo e(route('admin.documents.index', $agent->id)); ?>">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span class="ov-agent-avatar"><?php echo e(strtoupper(substr($agent->name,0,1))); ?></span>
                                <div>
                                    <div style="font-weight:700;color:#1e293b;"><?php echo e($agent->name); ?></div>
                                    <div style="font-size:11px;color:#94a3b8;"><?php echo e($agent->email); ?> · Agent ID: <?php echo e($agent->detail?->agent_id_number ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        </a>
                    </td>
                    <td>
                        <span class="ov-pill <?php echo e($statusStyle[$row['app_status']] ?? 'slate'); ?>"><?php echo e(ucfirst(str_replace('_',' ',$row['app_status']))); ?></span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="ov-bar" style="flex:1;">
                                <div style="width: <?php echo e($row['total'] > 0 ? round($row['approved'] / $row['total'] * 100) : 0); ?>%"></div>
                            </div>
                            <div class="ov-count" style="white-space:nowrap;margin-top:0;"><?php echo e($row['approved']); ?>/<?php echo e($row['total']); ?></div>
                        </div>
                    </td>
                    <td><span class="ov-pill slate"><?php echo e($row['uploaded']); ?></span></td>
                    <td>
                        <?php if($row['pending'] > 0): ?>
                        <span class="ov-pill blue"><?php echo e($row['pending']); ?></span>
                        <?php else: ?>
                        <span style="color:#cbd5e1;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($row['action_needed'] > 0): ?>
                        <span class="ov-pill red"><?php echo e($row['action_needed']); ?></span>
                        <?php else: ?>
                        <span style="color:#cbd5e1;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo e(route('admin.documents.index', $agent->id)); ?>" class="btn secondary tiny" style="text-decoration:none;white-space:nowrap;">
                            <?php echo e($row['is_compliant'] ? '✓ Review' : 'Review →'); ?>

                        </a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="panel" style="margin-top:20px;padding:14px 20px;background:#f8fafc;font-size:12px;color:#64748b;">
    💡 Tip: Open an agent's row to approve / reject / request re-upload for each document. New uploads land here under <strong>Awaiting Review</strong>.
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    var $t = $('#documentsOverviewTable');
    // Skip DataTables when the table is absent (no active agents yet).
    if ($t.length && $t.find('tbody td[colspan]').length === 0) {
        $t.DataTable({
            pageLength: 20,
            lengthMenu: [20, 40, 100],
            order: [],
            language: { search: "Quick Search:" }
        });
    }
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/admin/documents/overview.blade.php ENDPATH**/ ?>