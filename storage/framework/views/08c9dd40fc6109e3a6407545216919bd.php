<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
    .badge-open { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .badge-in_progress { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-resolved { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .badge-closed { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">SUPPORT MANAGEMENT</div>
        <h1>Support Tickets</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">Manage and resolve issues submitted by agents.</p>
    </div>
</div>

<div class="panel" style="padding: 0; background: transparent; border: none; box-shadow: none;">
    <div class="table-wrap">
        <table id="ticketsTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Agent Name</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Submitted On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($ticket->id); ?></td>
                    <td><strong><?php echo e($ticket->user->name); ?></strong><br><small><?php echo e($ticket->user->email); ?></small></td>
                    <td><?php echo e($ticket->title); ?></td>
                    <td>
                        <span class="badge badge-<?php echo e($ticket->status); ?>">
                            <?php echo e(ucfirst(str_replace('_', ' ', $ticket->status))); ?>

                        </span>
                    </td>
                    <td><?php echo e($ticket->created_at->format('Y-m-d H:i')); ?></td>
                    <td>
                        <a class="btn tiny secondary" href="<?php echo e(route('admin.tickets.show', $ticket)); ?>">Review</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#ticketsTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']], 
    });
});
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/admin/tickets/index.blade.php ENDPATH**/ ?>