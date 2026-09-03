<?php $__env->startSection('content'); ?><div class="page-head">
    <div>
        <div class="eyebrow">AGENT MANAGEMENT</div>
        <h1>Agents</h1>
        <p>Admins can create, update, activate/deactivate, and manage every agent. Each agent can have a maximum of one virtual card. The card is created separately by an authorized Admin.</p>
    </div><a class="btn primary" href="<?php echo e(route('agents.create')); ?>">+ New Agent</a>
</div>
<div class="panel">
    <form class="toolbar"><input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search agent name, email or phone..."><button class="btn secondary">Search</button></form>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Business</th>
                    <th>Virtual Card</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody><?php $__empty_1 = true; $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr>
                    <td><a class="link" href="<?php echo e(route('agents.show',$agent)); ?>"><strong><?php echo e($agent->name); ?></strong></a><small><?php echo e($agent->email); ?> · <?php echo e($agent->phone ?: 'No phone'); ?></small></td>
                    <td><?php echo e($agent->business?->name ?: '—'); ?></td>
                    <td><?php if($agent->virtualCard): ?><a class="link" href="<?php echo e(route('cards.show',$agent->virtualCard)); ?>"><?php echo e($agent->virtualCard->reference); ?></a><small><?php echo e(ucfirst($agent->virtualCard->status->value)); ?></small><?php else: ?><span class="badge failed">Missing</span><?php endif; ?></td>
                    <td><span class="badge <?php echo e($agent->status==='active'?'success':'neutral'); ?>"><?php echo e(ucfirst($agent->status)); ?></span></td>
                    <td><?php echo e($agent->created_at->format('d M Y')); ?></td>
                    <td class="flex items-center gap-2">
                        <a class="btn tiny" href="<?php echo e(route('agents.edit',$agent)); ?>">Manage</a>
                        <form method="POST" action="<?php echo e(route('agents.toggle-status', $agent)); ?>" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn tiny <?php echo e($agent->status === 'active' ? 'secondary' : 'primary'); ?>" onclick="return confirm('Are you sure you want to <?php echo e($agent->status === 'active' ? 'disable' : 'enable'); ?> login for this agent?');">
                                <?php echo e($agent->status === 'active' ? 'Disable Login' : 'Enable Login'); ?>

                            </button>
                        </form>
                    </td>
                </tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr>
                    <td colspan="6" class="empty">No agents found.</td>
                </tr><?php endif; ?></tbody>
        </table>
    </div>
    <div class="pagination"><?php echo e($agents->links()); ?></div>
</div><?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views\agents\index.blade.php ENDPATH**/ ?>