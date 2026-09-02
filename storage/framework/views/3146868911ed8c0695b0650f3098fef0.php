<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login · Agent Business Support</title><?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>
</head>

<body class="login-page">
    <div class="login-card">
        <div class="login-brand">
            <div class="brand-mark large">🤝</div>
            <div class="brand-title">AGENT</div>
            <div class="brand-sub">BUSINESS SUPPORT</div>
            <div class="brand-tag">PARTNERING YOUR GROWTH</div>
        </div>
        <div class="login-panel">
            <h1>Welcome Back!</h1>
            <p>Login to your account to continue</p>
            <form method="POST" action="<?php echo e(route('login.attempt')); ?>"><?php echo csrf_field(); ?><label>Email</label><input name="email" type="email" value="<?php echo e(old('email')); ?>" placeholder="Enter email" required><label>Password</label><input name="password" type="password" placeholder="Enter password" required>
                
                <button class="primary full">Login →</button>
            </form>
            <div class="demo-credentials"><strong>Local demo</strong><br>admin@agent-support.local / Admin@12345<br>agent@agent-support.local / Agent@12345</div>
        </div>
    </div>
</body>

</html><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/auth/login.blade.php ENDPATH**/ ?>