<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name','Agent Business Support') }} @isset($title) · {{ $title }} @endisset</title>@vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">🤝</div>
                <div>
                    <div class="brand-title">AGENT</div>
                    <div class="brand-sub">BUSINESS SUPPORT</div>
                </div>
            </div>
            <div class="sidebar-scroll">
                <div class="nav-label">WORKSPACE</div><a class="nav-item {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">⌂ <span>Dashboard</span></a><a class="nav-item {{ request()->routeIs('businesses.*')?'active':'' }}" href="{{ route('businesses.index') }}">▦ <span>Businesses</span></a>@if(auth()->user()->isAdmin())<a class="nav-item {{ request()->routeIs('agents.*')?'active':'' }}" href="{{ route('agents.index') }}">◉ <span>Agents</span></a>@endif<a class="nav-item {{ request()->routeIs('cards.*')?'active':'' }}" href="{{ route('cards.index') }}">▣ <span>Virtual Cards</span></a><a class="nav-item {{ request()->routeIs('payments.*')?'active':'' }}" href="{{ route('payments.index') }}">↗ <span>Payments</span></a><a class="nav-item {{ request()->routeIs('transactions.*')?'active':'' }}" href="{{ route('transactions.index') }}">≡ <span>Transactions</span></a>@if(in_array(auth()->user()->role?->slug,['super-admin','admin'],true))<a class="nav-item {{ request()->routeIs('settings.*')?'active':'' }}" href="{{ route('settings.gateways') }}">⚙ <span>Settings</span></a>@endif
            </div>
            <div class="sidebar-footer">
                <div class="secure">● Live secure environment</div>
                <div class="support">Need help?<br><strong>support@agent.local</strong></div>
            </div>
        </aside>
        <main class="main">
            <header class="topbar">
                <div class="mobile-menu">☰</div>
                <div class="breadcrumbs">Agent Business Support <span>/</span> {{ $title ?? 'Dashboard' }}</div>
                <div class="top-actions"><span class="pill online">● System Healthy</span>
                    <div class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                    <div>
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->role?->name }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="icon-btn" title="Logout">↪</button></form>
                </div>
            </header>
            <section class="content">@if(session('success'))<div class="flash success">✓ {{ session('success') }}</div>@endif
                @if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif{{ $slot ?? '' }}@yield('content')</section>

            <a
                class="nav-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}"
                href="{{ route('settings.profile') }}">
                ⚙
                <span>My Settings</span>
            </a>

            @if(auth()->user()->isAdmin())

            <a
                class="nav-item {{ request()->routeIs('settings.gateways', 'settings.payment-mode') ? 'active' : '' }}"
                href="{{ route('settings.gateways') }}">
                ⚙
                <span>Platform Settings</span>
            </a>

            @endif
            <footer class="footer">© {{ now()->year }} Agent Business Support · Partnering your growth</footer>
        </main>
    </div>
</body>

</html>