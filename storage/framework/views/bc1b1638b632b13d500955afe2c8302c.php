<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance & Balance — Agent Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F4F6F8; }</style>
</head>
<body class="text-slate-800 antialiased min-h-screen">
    <?php echo $__env->make('partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-8 transition-all">
        
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Advance & Balance</h1>
                <p class="text-slate-500 text-sm mt-1">Manage your advances and view your commission history.</p>
            </div>
        </div>

        <?php if(session('success')): ?>
        <div class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-200 mb-6 flex items-center gap-3">
            <i class="fa-solid fa-circle-check"></i> <?php echo e(session('success')); ?>

        </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 mb-6 flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation"></i> <?php echo e(session('error')); ?>

        </div>
        <?php endif; ?>

        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Net Commissions Earned</div>
                <div class="text-3xl font-bold text-slate-800">₹<?php echo e(number_format($totalCommission, 2)); ?></div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Total Advance Deductions</div>
                <div class="text-3xl font-bold text-slate-800">₹<?php echo e(number_format($totalDeductions, 2)); ?></div>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm bg-gradient-to-br from-slate-800 to-slate-900 text-white">
                <div class="text-slate-300 text-sm font-semibold uppercase tracking-wider mb-2">Outstanding Advance</div>
                <div class="text-3xl font-bold text-white">
                    <?php if($activeAdvance): ?>
                        ₹<?php echo e(number_format($activeAdvance->outstanding_amount, 2)); ?>

                    <?php else: ?>
                        ₹0.00
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if($activeAdvance): ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-8 overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                    <h2 class="font-bold text-slate-800">Current Advance Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <div class="mb-4">
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Original Amount Granted</div>
                                <div class="text-xl font-bold text-slate-800">₹<?php echo e(number_format($activeAdvance->total_amount, 2)); ?></div>
                            </div>
                            <div class="mb-4">
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Repayment Status</div>
                                <?php if($activeAdvance->repayment_type === 'unselected'): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                        Action Required: Select Repayment Method
                                    </span>
                                <?php elseif($activeAdvance->repayment_type === 'one_time'): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        One-Time Deduction
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                        EMI: ₹<?php echo e(number_format($activeAdvance->emi_amount, 2)); ?> / payout
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <?php if($activeAdvance->repayment_type === 'unselected'): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                                <h3 class="font-bold text-blue-900 mb-2">How would you like to repay this advance?</h3>
                                <p class="text-sm text-blue-700 mb-4">You must select a repayment method before you can receive commission payouts.</p>
                                
                                <form action="<?php echo e(route('advances.repayment')); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="space-y-4">
                                        <label class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-blue-300">
                                            <input type="radio" name="repayment_type" value="one_time" class="mt-1" onclick="document.getElementById('emi_options').style.display='none'">
                                            <div>
                                                <div class="font-bold text-slate-800">One-Time Deduction</div>
                                                <div class="text-xs text-slate-500">The entire outstanding amount will be deducted from your next commission payout(s) until cleared.</div>
                                            </div>
                                        </label>
                                        
                                        <label class="flex items-start gap-3 p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-blue-300">
                                            <input type="radio" name="repayment_type" value="emi" class="mt-1" onclick="document.getElementById('emi_options').style.display='block'">
                                            <div>
                                                <div class="font-bold text-slate-800">EMI (Monthly / Per Payout)</div>
                                                <div class="text-xs text-slate-500">A fixed amount will be deducted from each commission payout.</div>
                                            </div>
                                        </label>

                                        <div id="emi_options" style="display:none;" class="pl-7 pt-2">
                                            <label class="block text-xs font-bold text-slate-600 mb-1">EMI Amount to Deduct (₹)</label>
                                            <input type="number" name="emi_amount" step="0.01" min="1" max="<?php echo e($activeAdvance->outstanding_amount); ?>" placeholder="Enter amount..." class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                        </div>
                                    </div>
                                    <button type="submit" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                        Confirm Repayment Plan
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50 px-6 py-4 flex justify-between items-center">
                <h2 class="font-bold text-slate-800">Commission & Deduction History</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200">
                            <th class="px-6 py-3 font-semibold">Date</th>
                            <th class="px-6 py-3 font-semibold">Description</th>
                            <th class="px-6 py-3 font-semibold text-right">Gross Commission</th>
                            <th class="px-6 py-3 font-semibold text-right text-red-500">Advance Deduction</th>
                            <th class="px-6 py-3 font-semibold text-right text-green-600">Net Received</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-600"><?php echo e($commission->created_at->format('d M Y, h:i A')); ?></td>
                            <td class="px-6 py-4 font-medium text-slate-800"><?php echo e($commission->description); ?></td>
                            <td class="px-6 py-4 text-right">₹<?php echo e(number_format($commission->gross_amount, 2)); ?></td>
                            <td class="px-6 py-4 text-right text-red-500">
                                <?php echo e($commission->advance_deduction > 0 ? '- ₹' . number_format($commission->advance_deduction, 2) : '—'); ?>

                            </td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">₹<?php echo e(number_format($commission->net_amount, 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                No commission records found yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>

<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/advances/index.blade.php ENDPATH**/ ?>