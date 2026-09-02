<?php $__env->startSection('content'); ?>

<div class="page-head">

    <div>

        <div class="eyebrow">
            VIRTUAL CARD
        </div>

        <h1>
            Create Agent Card
        </h1>

        <p>
            Only Admin and Super Admin can create virtual cards.
            Each Agent can have one card.
        </p>

    </div>

</div>


<div class="panel form-panel">

    <form
        method="POST"
        action="<?php echo e(route('cards.store')); ?>"
    >

        <?php echo csrf_field(); ?>


        <div class="form-grid">


            

            <label class="wide">

                Agent

                <select
                    name="agent_id"
                    id="agent"
                    required
                >

                    <?php $__empty_1 = true; $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <option
                            value="<?php echo e($agent->id); ?>"
                            data-business="<?php echo e($agent->business_id); ?>"
                            data-name="<?php echo e($agent->name); ?>"
                        >

                            <?php echo e($agent->name); ?>


                            ·

                            <?php echo e($agent->email); ?>


                            ·

                            <?php echo e($agent->business?->name); ?>


                        </option>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <option value="">
                            All active agents already have a card.
                        </option>

                    <?php endif; ?>

                </select>

            </label>


            

            <input
                type="hidden"
                name="business_id"
                id="business_id"
                value="<?php echo e(old('business_id', $agents->first()?->business_id)); ?>"
            >


            

            <label class="wide">

                Cardholder Name

                <input
                    name="cardholder_name"
                    id="cardholder_name"
                    value="<?php echo e(old('cardholder_name', $agents->first()?->name)); ?>"
                    readonly
                    required
                >

            </label>


            

            <label>

                Overall Limit

                <input
                    name="card_limit"
                    type="number"
                    step="0.01"
                    min="0.01"
                    value="<?php echo e(old('card_limit', $card->card_limit)); ?>"
                    required
                >

            </label>


            <label>

                Daily Limit

                <input
                    name="daily_limit"
                    type="number"
                    step="0.01"
                    min="0.01"
                    value="<?php echo e(old('daily_limit', $card->daily_limit)); ?>"
                    required
                >

            </label>


            <label>

                Monthly Limit

                <input
                    name="monthly_limit"
                    type="number"
                    step="0.01"
                    min="0.01"
                    value="<?php echo e(old('monthly_limit', $card->monthly_limit)); ?>"
                    required
                >

            </label>


            <label>

                Per Transaction

                <input
                    name="per_transaction_limit"
                    type="number"
                    step="0.01"
                    min="0.01"
                    value="<?php echo e(old('per_transaction_limit', $card->per_transaction_limit)); ?>"
                    required
                >

            </label>


            

            <label>

                Status

                <select name="status">

                    <option value="active">
                        Active
                    </option>

                    <option value="blocked">
                        Blocked
                    </option>

                    <option value="suspended">
                        Suspended
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>

                </select>

            </label>

        </div>


        <div class="notice">

            <strong>
                Card generation
            </strong>

            <br>

            The sandbox provider generates the full card number,
            CVV, expiry and provider reference automatically.

            <br><br>

            The PAN is encrypted before storage.
            CVV is intentionally not stored permanently.

        </div>


        <div class="form-actions">

            <a
                class="btn secondary"
                href="<?php echo e(route('cards.index')); ?>"
            >
                Cancel
            </a>

            <button
                class="btn primary"
                <?php echo e($agents->isEmpty() ? 'disabled' : ''); ?>

            >
                Create Virtual Card
            </button>

        </div>

    </form>

</div>


<script>

    const agentSelect =
        document.querySelector('#agent');

    const businessInput =
        document.querySelector('#business_id');

    const cardholderInput =
        document.querySelector('#cardholder_name');


    function syncAgent()
    {
        const option =
            agentSelect?.selectedOptions[0];

        if (! option) {
            return;
        }


        businessInput.value =
            option.dataset.business || '';


        cardholderInput.value =
            option.dataset.name || '';
    }


    agentSelect?.addEventListener(
        'change',
        syncAgent
    );


    syncAgent();

</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/cards/form.blade.php ENDPATH**/ ?>