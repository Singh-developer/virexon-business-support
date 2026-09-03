<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name','Agent Business Support') }} @isset($title) · {{ $title }} @endisset</title>@vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body>
    @if(auth()->check() && auth()->user()->isAgent())
        <!-- Google Fonts & Tailwind (for Agent Layout) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
        <div class="bg-[#F4F6F8] text-slate-800 antialiased min-h-screen w-full flex flex-col font-sans">
            @include('partials.navbar')
            <div class="flex-1 flex w-full justify-center">
                <main class="flex-1 p-4 lg:p-6 space-y-6 max-w-7xl">
                    <section class="content" style="background: transparent; box-shadow: none; border: none; padding: 0;">
                        @if(session('success'))<div class="flash success">✓ {{ session('success') }}</div>@endif
                        @if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
                        {{ $slot ?? '' }}
                        @yield('content')
                    </section>
                </main>
            </div>
        </div>
    @else
        <!-- Admin Layout Wrapper -->
        <div class="app-shell">
            <aside class="sidebar">
                <div class="brand">
                    <img src="{{ asset('images/virexon-light.png') }}" alt="Virexon" style="width: calc(100%/4*2); height: auto;">
                    <!-- <div class="brand-mark">🤝</div> -->
                    <div>
                        <div class="brand-title">AGENT</div>
                        <div class="brand-sub">BUSINESS SUPPORT</div>
                    </div>
                </div>
                <div class="sidebar-scroll">
                    <div class="nav-label">WORKSPACE</div>
                    <a class="nav-item {{ request()->routeIs('dashboard', 'admin.dashboard')?'active':'' }}" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}">⌂ <span>Dashboard</span></a>
                    <a class="nav-item {{ request()->routeIs('businesses.*')?'active':'' }}" href="{{ route('businesses.index') }}">▦ <span>Businesses</span></a>
                    @if(auth()->user()->isAdmin())<a class="nav-item {{ request()->routeIs('agents.*')?'active':'' }}" href="{{ route('agents.index') }}">◉ <span>Agents</span></a>@endif
                    <a class="nav-item {{ request()->routeIs('cards.*')?'active':'' }}" href="{{ route('cards.index') }}">▣ <span>Virtual Cards</span></a>
                    <a class="nav-item {{ request()->routeIs('payments.*')?'active':'' }}" href="{{ route('payments.index') }}">↗ <span>Payments</span></a>
                    <a class="nav-item {{ request()->routeIs('transactions.*')?'active':'' }}" href="{{ route('transactions.index') }}">≡ <span>Transactions</span></a>
                    <div class="nav-label" style="margin-top:20px;">SETTINGS</div>
                    <a class="nav-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}" href="{{ route('settings.profile') }}">⚙ <span>My Settings</span></a>
                    @if(auth()->user()->isAdmin())
                    <a class="nav-item {{ request()->routeIs('settings.gateways', 'settings.payment-mode') ? 'active' : '' }}" href="{{ route('settings.gateways') }}">⚙ <span>Platform Settings</span></a>
                    @endif
                </div>
                <div class="sidebar-footer">
                    <div class="secure">● Live secure environment</div>
                    <div class="support">Need help?<br><strong>support@virexon.in</strong></div>
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
                <footer class="footer">© {{ now()->year }} Agent Business Support · Partnering your growth</footer>
            </main>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mobileMenu = document.querySelector('.mobile-menu');
                const sidebar = document.querySelector('.sidebar');

                if (mobileMenu && sidebar) {
                    mobileMenu.addEventListener('click', function(e) {
                        e.stopPropagation();
                        sidebar.classList.toggle('mobile-open');
                    });

                    document.addEventListener('click', function(e) {
                        if (sidebar.classList.contains('mobile-open') && !sidebar.contains(e.target)) {
                            sidebar.classList.remove('mobile-open');
                        }
                    });
                }
            });
        </script>
        <style>
            @media (max-width: 760px) {
                .sidebar.mobile-open {
                    display: flex !important;
                    position: fixed;
                    top: 0;
                    left: 0;
                    height: auto;
                    max-height: max-content;
                    overflow-y: auto;
                    z-index: 9999;
                    width: 250px;
                    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
                }

                .sidebar.mobile-open::-webkit-scrollbar {
                    width: 5px;
                }

                .sidebar.mobile-open::-webkit-scrollbar-thumb {
                    background: #888;
                    border-radius: 10px;
                }
            }
        </style>
    @endif
</body>

</html>