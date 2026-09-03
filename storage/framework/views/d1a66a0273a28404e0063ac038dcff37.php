<?php $__env->startSection('content'); ?>

<div class="page-head">

    <div>

        <div class="eyebrow">
            CARD MANAGEMENT
        </div>

        <h1>
            Virtual Cards
        </h1>

        <p>
            Sandbox cards use encrypted PAN storage and provider
            references. CVV is never retained permanently.
        </p>

    </div>


    <?php if(auth()->user()->isAdmin()): ?>

        <a
            class="btn primary"
            href="<?php echo e(route('cards.create')); ?>"
        >
            + Create Card
        </a>

    <?php endif; ?>

</div>


<div class="panel">


    <form class="toolbar">

        <input
            name="q"
            value="<?php echo e(request('q')); ?>"
            placeholder="Search card, holder or last 4 digits"
        >

        <button class="btn secondary">
            Search
        </button>

    </form>


    <div class="table-wrap">

        <table>

            <thead>

                <tr>

                    <th>Card</th>

                    <th>Business</th>

                    <th>Agent</th>

                    <th>Limits</th>

                    <th>Usage</th>

                    <th>Expiry</th>

                    <th>Status</th>

                </tr>

            </thead>


            <tbody>

                <?php $__empty_1 = true; $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                    <tr>

                        <td>

                            <a
                                class="link mono"
                                href="<?php echo e(route('cards.show', $card)); ?>"
                            >

                                <?php echo e($card->reference); ?>


                            </a>

                            <div class="muted mono">

                                •••• <?php echo e($card->last4); ?>


                            </div>

                        </td>


                        <td>

                            <?php echo e($card->business->name); ?>


                        </td>


                        <td>

                            <?php echo e($card->agent?->name ?? '—'); ?>


                        </td>


                        <td>

                            ₹<?php echo e(number_format($card->card_limit, 2)); ?>


                            <div class="muted">

                                Daily
                                ₹<?php echo e(number_format($card->daily_limit, 2)); ?>


                                ·

                                Txn
                                ₹<?php echo e(number_format($card->per_transaction_limit, 2)); ?>


                            </div>

                        </td>


                        <td>

                            <strong>
                                ₹<?php echo e(number_format($card->current_usage, 2)); ?>

                            </strong>

                            <div class="muted">

                                ₹<?php echo e(number_format($card->remaining_limit, 2)); ?>

                                remaining

                            </div>

                        </td>


                        <td>

                            <?php echo e($card->expiry_date->format('d M Y')); ?>


                        </td>


                        <td>

                            <span class="badge success">

                                <?php echo e(ucfirst($card->status->value)); ?>


                            </span>

                        </td>

                    </tr>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                    <tr>

                        <td
                            colspan="7"
                            style="text-align:center;"
                        >

                            No virtual cards found.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>


    <div class="pagination">

        <?php echo e($cards->links()); ?>


    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views\cards\index.blade.php ENDPATH**/ ?>