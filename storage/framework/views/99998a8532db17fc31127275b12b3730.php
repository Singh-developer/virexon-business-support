<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name','Agent Business Support')); ?> <?php if(isset($title)): ?> · <?php echo e($title); ?> <?php endif; ?></title><?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>
</head>

<body>
    <?php if(auth()->check() && auth()->user()->isAgent()): ?>
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
            <?php echo $__env->make('partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="flex-1 flex w-full justify-center">
                <main class="flex-1 p-4 lg:p-6 space-y-6 max-w-7xl">
                    <section class="content" style="background: transparent; box-shadow: none; border: none; padding: 0;">
                        <?php if(session('success')): ?><div class="flash success">✓ <?php echo e(session('success')); ?></div><?php endif; ?>
                        <?php if($errors->any()): ?><div class="flash error"><?php echo e($errors->first()); ?></div><?php endif; ?>
                        <?php echo e($slot ?? ''); ?>

                        <?php echo $__env->yieldContent('content'); ?>
                    </section>
                </main>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin Layout Wrapper -->
        <div class="app-shell">
            <aside class="sidebar">
                <div class="brand">
                    <img src="<?php echo e(asset('images/virexon-light.png')); ?>" alt="Virexon" style="width: calc(100%/4*2); height: auto;">
                    <!-- <div class="brand-mark">🤝</div> -->
                    <div>
                        <div class="brand-title">AGENT</div>
                        <div class="brand-sub">BUSINESS SUPPORT</div>
                    </div>
                </div>
                <div class="sidebar-scroll">
                    <div class="nav-label">WORKSPACE</div>
                    <a class="nav-item <?php echo e(request()->routeIs('dashboard', 'admin.dashboard')?'active':''); ?>" href="<?php echo e(auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')); ?>">⌂ <span>Dashboard</span></a>
                    <a class="nav-item <?php echo e(request()->routeIs('businesses.*')?'active':''); ?>" href="<?php echo e(route('businesses.index')); ?>">▦ <span>Businesses</span></a>
                    <?php if(auth()->user()->isAdmin()): ?><a class="nav-item <?php echo e(request()->routeIs('agents.*')?'active':''); ?>" href="<?php echo e(route('agents.index')); ?>">◉ <span>Agents</span></a><?php endif; ?>
                    <a class="nav-item <?php echo e(request()->routeIs('cards.*')?'active':''); ?>" href="<?php echo e(route('cards.index')); ?>">▣ <span>Virtual Cards</span></a>
                    <a class="nav-item <?php echo e(request()->routeIs('payments.*')?'active':''); ?>" href="<?php echo e(route('payments.index')); ?>">↗ <span>Payments</span></a>
                    <a class="nav-item <?php echo e(request()->routeIs('transactions.*')?'active':''); ?>" href="<?php echo e(route('transactions.index')); ?>">≡ <span>Transactions</span></a>
                    <div class="nav-label" style="margin-top:20px;">SETTINGS</div>
                    <a class="nav-item <?php echo e(request()->routeIs('settings.profile') ? 'active' : ''); ?>" href="<?php echo e(route('settings.profile')); ?>">⚙ <span>My Settings</span></a>
                    <?php if(auth()->user()->isAdmin()): ?>
                    <a class="nav-item <?php echo e(request()->routeIs('settings.gateways', 'settings.payment-mode') ? 'active' : ''); ?>" href="<?php echo e(route('settings.gateways')); ?>">⚙ <span>Platform Settings</span></a>
                    <?php endif; ?>
                </div>
                <div class="sidebar-footer">
                    <div class="secure">● Live secure environment</div>
                    <div class="support">Need help?<br><strong>support@virexon.in</strong></div>
                </div>
            </aside>
            <main class="main">
                <header class="topbar">
                    <div class="mobile-menu">☰</div>
                    <div class="breadcrumbs">Agent Business Support <span>/</span> <?php echo e($title ?? 'Dashboard'); ?></div>
                    <div class="top-actions"><span class="pill online">● System Healthy</span>
                        <div class="avatar"><?php echo e(strtoupper(substr(auth()->user()->name,0,1))); ?></div>
                        <div>
                            <div class="user-name"><?php echo e(auth()->user()->name); ?></div>
                            <div class="user-role"><?php echo e(auth()->user()->role?->name); ?></div>
                        </div>
                        <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="icon-btn" title="Logout">↪</button></form>
                    </div>
                </header>
                <section class="content"><?php if(session('success')): ?><div class="flash success">✓ <?php echo e(session('success')); ?></div><?php endif; ?>
                    <?php if($errors->any()): ?><div class="flash error"><?php echo e($errors->first()); ?></div><?php endif; ?><?php echo e($slot ?? ''); ?><?php echo $__env->yieldContent('content'); ?></section>
                <footer class="footer">© <?php echo e(now()->year); ?> Agent Business Support · Partnering your growth</footer>
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
    <?php endif; ?>
</body>

</html><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/layouts/app.blade.php ENDPATH**/ ?>