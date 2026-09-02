<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name','Agent Business Support')); ?> <?php if(isset($title)): ?> · <?php echo e($title); ?> <?php endif; ?></title><?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>
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
                <div class="support">Need help?<br><strong>support@agent.local</strong></div>
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
</body>

</html><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/layouts/app.blade.php ENDPATH**/ ?>