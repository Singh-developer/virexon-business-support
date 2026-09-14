<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
    .role-form { display: inline-flex; align-items: center; gap: 4px; }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">ACCESS CONTROL</div>
        <h1>Users</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">List of all platform users. Admins (Super Admin or Admin role) can reassign a user's role inline.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-shrink: 0;">
        <a class="btn secondary" href="<?php echo e(route('admin.roles.index')); ?>">← Roles</a>
    </div>
</div>

<div class="panel" style="padding: 0; background: transparent; border: none; box-shadow: none;">
    <?php echo $__env->make('partials.per-page', ['perPage' => $users->perPage()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="table-wrap">
        <table id="usersTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Business</th>
                    <th>Created</th>
                    <th class="text-end">Assign Role</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $badge = match($user->status) {
                        'active'  => 'success',
                        'inactive'=> 'failed',
                        default   => 'pending',
                    };
                ?>
                <tr>
                    <td><?php echo e($user->id); ?></td>
                    <td><strong><?php echo e($user->name); ?></strong></td>
                    <td><?php echo e($user->email); ?></td>
                    <td><span class="badge <?php echo e($user->role?->slug === 'super-admin' ? 'neutral' : ($user->role?->slug === 'admin' ? 'success' : 'pending' )); ?>"><?php echo e($user->role?->name ?? '—'); ?></span></td>
                    <td><span class="badge <?php echo e($badge); ?>"><?php echo e(ucfirst($user->status)); ?></span></td>
                    <td><?php echo e($user->business?->name ?? '—'); ?></td>
                    <td><?php echo e(optional($user->created_at)?->format('Y-m-d H:i') ?? '—'); ?></td>
                    <td class="text-end">
                        <?php if($user->role?->slug === 'super-admin'): ?>
                            <span class="text-muted" style="font-size:12px;color:#94a3b8">Protected</span>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('admin.users.role', $user)); ?>" class="role-form" onsubmit="return confirm('Reassign role for <?php echo e($user->name); ?>?');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <select name="role_id" required>
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($role->id); ?>" <?php if($user->role_id === $role->id): echo 'selected'; endif; ?>><?php echo e($role->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="submit" class="btn tiny primary">Update</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center" style="padding:20px;color:#94a3b8;">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="padding: 15px 20px;">
        <?php echo e($users->withQueryString()->links()); ?>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    var $t = $('#usersTable');
    // Skip DataTables when only the empty-state (colspan) row is present.
    if ($t.length && $t.find('tbody td[colspan]').length === 0) {
        $t.DataTable({
            pageLength: 20,
            lengthMenu: [20, 40, 100],
            order: [[6, 'desc']],
            columnDefs: [{ targets: [7], orderable: false }],
            language: { search: "Quick Search:" }
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/admin/roles/users.blade.php ENDPATH**/ ?>