<?php $__env->startSection('content'); ?>

<?php
$isAgent = auth()->user()->isAgent();
$card = $agent->virtualCard;

if (!$card) {
    // If agent has no card, we shouldn't try to render the card details
    echo "<div class='panel'><div class='panel-head'><h3>No Virtual Card</h3><p>This agent does not have a virtual card assigned yet.</p></div></div>";
}
?>

<?php if($card): ?>
<?php
$statusValue = $card->status instanceof \BackedEnum
? $card->status->value
: $card->status;
?>

<div class="page-head">

    <div>

        <div class="eyebrow">
            <?php echo e($isAgent ? 'MY VIRTUAL CARD' : 'CARD DETAILS'); ?>

        </div>

        <h1>
            <?php echo e($card->reference); ?>

        </h1>

        <p>
            <?php echo e($card->cardholder_name); ?>

            ·
            <?php echo e($card->business->name ?? 'No Business'); ?>

        </p>

    </div>

    <div class="flex items-center gap-3">
        <span class="badge <?php echo e($agent->status==='active'?'success':'neutral'); ?>">
            <?php echo e(ucfirst($agent->status)); ?>

        </span>
        
        <?php if(auth()->user()->isAdmin()): ?>
        <a href="<?php echo e(route('agents.edit', $agent)); ?>" class="btn tiny secondary">
            Edit Agent
        </a>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('agents.toggle-status', $agent)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>
            <button type="submit" class="btn tiny <?php echo e($agent->status === 'active' ? 'secondary' : 'primary'); ?>" onclick="return confirm('Are you sure you want to <?php echo e($agent->status === 'active' ? 'disable' : 'enable'); ?> login for this agent?');">
                <?php echo e($agent->status === 'active' ? 'Disable Login' : 'Enable Login'); ?>

            </button>
        </form>
    </div>
</div>

<?php if(session('card_created') && session('generated_cvv')): ?>

<div class="flash success">

    <strong>Virtual card created successfully.</strong>

    <div style="margin-top:10px;">
        Sandbox CVV:
        <strong class="mono">
            <?php echo e(session('generated_cvv')); ?>

        </strong>
    </div>

    <small>
        This CVV is shown only at creation time and is not stored
        permanently by the application.
    </small>

</div>

<?php endif; ?>




<div class="card-visual">

    <div class="card-top">

        <div class="card-brand">
            VIRTUAL
        </div>

        <div class="card-type">
            CARD
        </div>

    </div>

    <div
        id="card-pan-display"
        class="card-number">
        <?php echo e($card->maskedPan()); ?>

    </div>

    <div class="card-bottom">

        <div>

            <div class="card-label">
                CARD HOLDER
            </div>

            <div class="card-holder">
                <?php echo e(strtoupper($card->cardholder_name)); ?>

            </div>

        </div>

        <div>

            <div class="card-label">
                EXPIRES
            </div>

            <div class="card-expiry">

                <?php echo e($card->expiry_date->format('m/y')); ?>


            </div>

        </div>

    </div>

</div>




<div class="panel">

    <div class="panel-head">

        <div>

            <h3>
                Card Information
            </h3>

            <p>
                Sandbox card data only.
            </p>

        </div>

    </div>


    <div class="card-information-grid">

        <div class="info-item">

            <span class="info-label">
                Card Holder
            </span>

            <strong>
                <?php echo e($card->cardholder_name); ?>

            </strong>

        </div>


        <div class="info-item">

            <span class="info-label">
                Expiry
            </span>

            <strong>
                <?php echo e($card->expiry_date->format('m / Y')); ?>

            </strong>

        </div>


        <div class="info-item">

            <span class="info-label">
                Card Number
            </span>

            <strong
                id="card-pan-text"
                class="mono">
                <?php echo e($card->maskedPan()); ?>

            </strong>

        </div>


        <div class="info-item">

            <span class="info-label">
                CVV
            </span>

            <strong class="mono">
                •••
            </strong>

            <small>
                CVV is provided by the issuer/provider and is not
                permanently stored by this application.
            </small>

        </div>

    </div>


    <div class="form-actions">

        <button
            type="button"
            id="reveal-pan-button"
            class="btn primary">
            Show Full Card Number
        </button>

        <button
            type="button"
            id="copy-pan-button"
            class="btn secondary"
            style="display:none;">
            Copy Card Number
        </button>

    </div>

</div>




<div class="stats-grid three">

    <div class="stat-card">

        <div class="stat-label">
            Overall Limit
        </div>

        <div class="stat-value">
            ₹<?php echo e(number_format($card->card_limit, 2)); ?>

        </div>

    </div>


    <div class="stat-card">

        <div class="stat-label">
            Current Usage
        </div>

        <div class="stat-value">
            ₹<?php echo e(number_format($card->current_usage, 2)); ?>

        </div>

    </div>


    <div class="stat-card">

        <div class="stat-label">
            Remaining
        </div>

        <div class="stat-value">
            ₹<?php echo e(number_format($card->remaining_limit, 2)); ?>

        </div>

    </div>

</div>


<?php if(! $isAgent): ?>



<div class="panel">

    <div class="panel-head">

        <div>

            <h3>
                Limit Policy
            </h3>

            <p>
                Card spending configuration.
            </p>

        </div>

    </div>


    <div class="limits">

        <div>

            <span>
                Daily
            </span>

            <strong>
                ₹<?php echo e(number_format($card->daily_limit, 2)); ?>

            </strong>

        </div>


        <div>

            <span>
                Monthly
            </span>

            <strong>
                ₹<?php echo e(number_format($card->monthly_limit, 2)); ?>

            </strong>

        </div>


        <div>

            <span>
                Per Transaction
            </span>

            <strong>
                ₹<?php echo e(number_format($card->per_transaction_limit, 2)); ?>

            </strong>

        </div>

    </div>

</div>

<?php endif; ?>


<script>
    const revealButton =
        document.getElementById('reveal-pan-button');

    const copyButton =
        document.getElementById('copy-pan-button');

    const panDisplay =
        document.getElementById('card-pan-display');

    const panText =
        document.getElementById('card-pan-text');


    let fullPan = null;


    revealButton?.addEventListener('click', async function() {

        revealButton.disabled = true;

        revealButton.innerText =
            'Loading...';

        try {

            const response = await fetch(
                <?php echo json_encode(route('cards.reveal-pan', $card), 512) ?>, {
                    method: 'POST',

                    headers: {
                        'X-CSRF-TOKEN': <?php echo json_encode(csrf_token(), 15, 512) ?>,

                        'Accept': 'application/json',
                    },
                }
            );


            if (!response.ok) {

                throw new Error(
                    'Unable to reveal card number.'
                );

            }


            const data =
                await response.json();


            fullPan =
                data.formatted;


            panDisplay.innerText =
                data.formatted;


            panText.innerText =
                data.formatted;


            revealButton.innerText =
                'Card Number Visible';


            copyButton.style.display =
                'inline-flex';


        } catch (error) {

            console.error(error);

            alert(
                'Unable to reveal the card number.'
            );

            revealButton.disabled =
                false;

            revealButton.innerText =
                'Show Full Card Number';
        }

    });


    copyButton?.addEventListener('click', async function() {

        if (!fullPan) {
            return;
        }

        try {

            await navigator.clipboard.writeText(
                fullPan
            );

            copyButton.innerText =
                'Copied';

            setTimeout(() => {

                copyButton.innerText =
                    'Copy Card Number';

            }, 1800);

        } catch (error) {

            alert(
                'Unable to copy card number.'
            );

        }

    });
</script>
<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/agents/show.blade.php ENDPATH**/ ?>