<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Redirecting to Paytm...</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f5f7fa; }
        .loader { text-align: center; }
        .spinner { width: 40px; height: 40px; border: 4px solid #e0e0e0; border-top: 4px solid #00b9f5; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        p { color: #555; font-size: 14px; }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p>Redirecting you to Paytm secure checkout...</p>
        <p style="font-size:12px;color:#999;">Please do not close this window.</p>
    </div>

    <form method="post"
          action="<?php echo e($environment === 'production'
              ? 'https://secure.paytmpayments.com/theia/api/v1/showPaymentPage'
              : 'https://securestage.paytmpayments.com/theia/api/v1/showPaymentPage'); ?>?mid=<?php echo e($mid); ?>&orderId=<?php echo e($orderId); ?>"
          name="paytm_form">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="mid" value="<?php echo e($mid); ?>">
        <input type="hidden" name="orderId" value="<?php echo e($orderId); ?>">
        <input type="hidden" name="txnToken" value="<?php echo e($txnToken); ?>">
        <input type="hidden" name="txnAmount" value="<?php echo e($amount); ?>">
        <button type="submit">Click here if not redirected</button>
    </form>

    <script>setTimeout(function(){ document.paytm_form.submit(); }, 1500);</script>
</body>
</html>
<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/paytm/redirect.blade.php ENDPATH**/ ?>