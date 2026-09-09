<!DOCTYPE html>
<html lang="en" class="h-full w-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Support Tickets - Agent Business Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-['Plus_Jakarta_Sans'] h-full flex flex-col">

    <?php echo $__env->make('partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Support Tickets</h1>
                <p class="text-slate-500 mt-1">Manage your support requests and issues</p>
            </div>
            <a href="<?php echo e(route('tickets.create')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">New Ticket</span>
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500 text-xl"></i>
                <p class="font-medium"><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <?php if($tickets->count() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider font-semibold">
                                <th class="p-4">ID</th>
                                <th class="p-4">Title</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Created</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 text-slate-500 font-medium">#<?php echo e($ticket->id); ?></td>
                                    <td class="p-4 text-slate-900 font-semibold"><?php echo e($ticket->title); ?></td>
                                    <td class="p-4">
                                        <?php if($ticket->status === 'open'): ?>
                                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">Open</span>
                                        <?php elseif($ticket->status === 'in_progress'): ?>
                                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">In Progress</span>
                                        <?php elseif($ticket->status === 'resolved'): ?>
                                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">Resolved</span>
                                        <?php else: ?>
                                            <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-xs font-bold">Closed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-slate-500 text-sm"><?php echo e($ticket->created_at->format('M d, Y')); ?></td>
                                    <td class="p-4 text-right">
                                        <a href="<?php echo e(route('tickets.show', $ticket)); ?>" class="text-blue-600 hover:text-blue-800 font-medium text-sm">View <i class="fa-solid fa-arrow-right ml-1"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-1">No tickets yet</h3>
                    <p class="text-slate-500 mb-6">You haven't created any support tickets.</p>
                    <a href="<?php echo e(route('tickets.create')); ?>" class="text-blue-600 font-medium hover:underline">Create your first ticket</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>

<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/tickets/index.blade.php ENDPATH**/ ?>