<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Assertion Letter</title>
<style>
    body { margin: 0; padding: 0; font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; background: #f4f6f8; }
    .wrapper { max-width: 600px; margin: 0 auto; background: #fff; }
    .header { background: linear-gradient(135deg, #062b66, #0a3f86); color: #fff; padding: 30px 40px; text-align: center; }
    .header-brand { font-size: 24px; font-weight: 900; letter-spacing: 2px; }
    .header-sub { font-size: 10px; color: #90acd2; letter-spacing: 3px; margin-top: 4px; font-weight: 700; }
    .body { padding: 36px 40px; }
    .body h2 { color: #062b66; font-size: 20px; margin-bottom: 16px; }
    .body p { line-height: 1.7; margin-bottom: 14px; color: #334155; }
    .cta { display: inline-block; background: #1557d6; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 10px; }
    .footer { background: #f8fafc; padding: 20px 40px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="header-brand">AGENT BUSINESS SUPPORT</div>
        <div class="header-sub">PARTNERING YOUR GROWTH</div>
    </div>
    <div class="body">
        <h2>Hello, {{ $agent->name }}!</h2>
        <p>Please find attached your <strong>{{ $letter->title }}</strong> from Agent Business Support.</p>
        <p>This letter has been officially issued for your records and may be used for any formal or verification purposes.</p>
        <p>The attachment is a PDF document. If you have any questions, please contact the support team.</p>
        <p style="margin-top:24px">Warm regards,<br><strong>{{ $letter->signature_name }}</strong><br>
        @if($letter->signature_designation){{ $letter->signature_designation }}<br>@endif
        @if($letter->signature_company){{ $letter->signature_company }}@endif
        </p>
    </div>
    <div class="footer">
        Agent Business Support &bull; &copy; {{ now()->year }}.<br>
        This is an automated message. Please do not reply directly to this email.
    </div>
</div>
</body>
</html>
