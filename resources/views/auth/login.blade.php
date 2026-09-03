<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login · Agent Business Support</title>@vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="login-page">
    <div class="login-card">
        <div class="login-brand">
            <!-- <div class="brand-mark large">🤝</div> -->
            <div><a href=""><img src="{{ asset('images/virexon-light.png')}}" alt="Virexon" style="max-width: 40%;" /><a></div>
            <div class="brand-title">AGENT</div>
            <div class="brand-sub">BUSINESS SUPPORT</div>
            <div class="brand-tag">PARTNERING YOUR GROWTH</div>
        </div>
        <div class="login-panel">
            <h1>Welcome Back!</h1>
            <p>Login to your account to continue</p>
            <form method="POST" action="{{ route('login.attempt') }}">@csrf
            
            @error('login')
                <div class="flash error" style="color: red; font-size: 12px; margin-bottom: 10px;">{{ $message }}</div>
            @enderror

            <label>Email / Agent ID</label>
            <input name="login" type="text" value="{{ old('login') }}" placeholder="Enter email or Agent ID" required>
            
            <label>Password</label>
            <input name="password" type="password" placeholder="Enter password" required>
                {{-- <div class="remember">
                    <label><input type="checkbox" name="remember" value="1"> Remember me</label><span>Forgot Password?</span>
                </div> --}}
                <button class="btn primary full">Login →</button>
            </form>
            @if (request()->query('slug') === 'test')
            <div class="demo-credentials"><strong>Local demo</strong><br>admin@agent-support.local / Admin@12345<br>agent@agent-support.local / Agent@12345</div>
            @endif
        </div>
    </div>
</body>

</html>
