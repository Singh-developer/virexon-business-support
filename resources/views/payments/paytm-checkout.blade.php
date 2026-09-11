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
        button { margin-top: 16px; padding: 8px 20px; border: none; background: #00b9f5; color: #fff; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p>Redirecting you to Paytm secure checkout...</p>
        <p style="font-size:12px;color:#999;">Please do not close this window.</p>
    </div>

    @if(isset($txnToken))
        <form method="POST" action="{{ $environment === 'production'
            ? 'https://secure.paytmpayments.com/theia/api/v1/showPaymentPage'
            : 'https://securestage.paytmpayments.com/theia/api/v1/showPaymentPage' }}?mid={{ $mid }}&orderId={{ $orderId }}" id="paytm-checkout-form">
            <input type="hidden" name="mid" value="{{ $mid }}">
            <input type="hidden" name="orderId" value="{{ $orderId }}">
            <input type="hidden" name="txnToken" value="{{ $txnToken }}">
            <button type="submit" style="position:absolute;left:-9999px;">Continue</button>
        </form>
        <button onclick="document.getElementById('paytm-checkout-form').submit()">Click here if you are not redirected</button>
    @elseif(isset($checksum) && isset($params))
        <form method="POST" action="{{ $environment === 'production'
            ? 'https://secure.paytmpayments.com/theia/api/v1/showCheckoutPage'
            : 'https://securestage.paytmpayments.com/theia/api/v1/showCheckoutPage' }}?mid={{ $params['MID'] }}&orderId={{ $params['ORDER_ID'] }}" id="paytm-checkout-form">
            @foreach($params as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <input type="hidden" name="CHECKSUMHASH" value="{{ $checksum }}">
            <button type="submit" style="position:absolute;left:-9999px;">Continue</button>
        </form>
        <button onclick="document.getElementById('paytm-checkout-form').submit()">Click here if you are not redirected</button>
    @else
        <p style="color:#c33;">Could not start Paytm checkout. Please go back and try again.</p>
    @endif

    <script>
        setTimeout(function(){
            var form = document.getElementById('paytm-checkout-form');
            if (form) { form.submit(); }
        }, 1500);
    </script>
</body>
</html>