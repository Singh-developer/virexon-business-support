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

    <form method="POST" action="{{ $environment === 'production'
        ? 'https://secure.paytmpayments.com/theia/api/v1/showCheckoutPage'
        : 'https://securestage.paytmpayments.com/theia/api/v1/showCheckoutPage' }}?mid={{ $params['MID'] }}&orderId={{ $params['ORDER_ID'] }}"
        name="paytm_form" id="paytm-checkout-form">
        @foreach($params as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <input type="hidden" name="CHECKSUMHASH" value="{{ $checksum }}">
        <button type="submit">Click here if not redirected</button>
    </form>

    <script>setTimeout(function(){ document.getElementById('paytm-checkout-form').submit(); }, 1500);</script>
</body>
</html>
