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
            <!-- <div class="brand-mark large">🤝</div> -->
            <div><a href=""><img src="<?php echo e(asset('images/virexon-light.png')); ?>" alt="Virexon" style="max-width: 40%;" /><a></div>
            <div class="brand-title">AGENT</div>
            <div class="brand-sub">BUSINESS SUPPORT</div>
            <div class="brand-tag">PARTNERING YOUR GROWTH</div>
        </div>
        <div class="login-panel">
            <h1>Welcome Back!</h1>
            <p>Login to your account to continue</p>
            <form method="POST" action="<?php echo e(route('login.attempt')); ?>"><?php echo csrf_field(); ?>
            
            <?php $__errorArgs = ['login'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="flash error" style="color: red; font-size: 12px; margin-bottom: 10px;"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <label>Email / Agent ID</label>
            <input name="login" type="text" value="<?php echo e(old('login')); ?>" placeholder="Enter email or Agent ID" required>
            
            <label>Password</label>
            <div style="position: relative;">
                <input name="password" id="login-password" type="password" placeholder="Enter password" required style="width: 100%; padding-right: 40px; box-sizing: border-box;">
                <button type="button" onclick="togglePassword('login-password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; font-size: 1.2rem; padding: 0;">
                    <span class="eye-icon">👁️</span>
                </button>
            </div>
            
            <script>
                function togglePassword(inputId, btn) {
                    const input = document.getElementById(inputId);
                    if (input.type === 'password') {
                        input.type = 'text';
                        btn.querySelector('.eye-icon').innerText = '🙈';
                    } else {
                        input.type = 'password';
                        btn.querySelector('.eye-icon').innerText = '👁️';
                    }
                }
            </script>
                
                <button class="btn primary full">Login →</button>
            </form>
            <?php if(request()->query('slug') === 'test'): ?>
            <div class="demo-credentials"><strong>Local demo</strong><br>admin@agent-support.local / Admin@12345<br>agent@agent-support.local / Agent@12345</div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/auth/login.blade.php ENDPATH**/ ?>