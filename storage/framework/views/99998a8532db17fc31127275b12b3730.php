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
            <a href="<?php echo e(auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')); ?>">
                <div class="brand">
                    <img src="<?php echo e(asset('images/virexon-light.png')); ?>" alt="Virexon" style="width: calc(100%/4*2); height: auto;">
                    <!-- <div class="brand-mark">🤝</div> -->
                    <div>
                        <div class="brand-title">AGENT</div>
                        <div class="brand-sub">BUSINESS SUPPORT</div>
                    </div>
                </div>
            </a>
            <div class="sidebar-scroll">
                <div class="nav-label">WORKSPACE</div>
                <a class="nav-item <?php echo e(request()->routeIs('dashboard', 'admin.dashboard')?'active':''); ?>" href="<?php echo e(auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')); ?>">⌂ <span>Dashboard</span></a>
                <a class="nav-item <?php echo e(request()->routeIs('businesses.*')?'active':''); ?>" href="<?php echo e(route('businesses.index')); ?>">▦ <span>Businesses</span></a>
                <?php if(auth()->user()->isAdmin()): ?><a class="nav-item <?php echo e(request()->routeIs('agents.*')?'active':''); ?>" href="<?php echo e(route('agents.index')); ?>">◉ <span>Agents</span></a><?php endif; ?>
                <a class="nav-item <?php echo e(request()->routeIs('cards.*')?'active':''); ?>" href="<?php echo e(route('cards.index')); ?>">▣ <span>Virtual Cards</span></a>
                <a class="nav-item <?php echo e(request()->routeIs('payments.*')?'active':''); ?>" href="<?php echo e(route('payments.index')); ?>">↗ <span>Payments</span></a>
                <a class="nav-item <?php echo e(request()->routeIs('transactions.*')?'active':''); ?>" href="<?php echo e(route('transactions.index')); ?>">≡ <span>Transactions</span></a>
                <?php if(auth()->user()->isAdmin()): ?><a class="nav-item <?php echo e(request()->routeIs('assertions.*')?'active':''); ?>" href="<?php echo e(route('assertions.index')); ?>">✉ <span>Assertion Letters</span></a><?php endif; ?>
                <a class="nav-item <?php echo e(request()->routeIs('admin.tickets.*')?'active':''); ?>" href="<?php echo e(route('admin.tickets.index')); ?>">⚑ <span>Support Tickets</span></a>
                <div class="nav-label" style="margin-top:20px;">SETTINGS</div>
                <a class="nav-item <?php echo e(request()->routeIs('settings.profile') ? 'active' : ''); ?>" href="<?php echo e(route('settings.profile')); ?>">⚙ <span>My Settings</span></a>
                <?php if(auth()->user()->isAdmin()): ?>
                <a class="nav-item <?php echo e(request()->routeIs('settings.gateways', 'settings.payment-mode') ? 'active' : ''); ?>" href="<?php echo e(route('settings.gateways')); ?>">⚙ <span>Platform Settings</span></a>
                <a class="nav-item <?php echo e(request()->routeIs('admin.roles.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.roles.index')); ?>">🛡 <span>Roles</span></a>
                <a class="nav-item <?php echo e(request()->routeIs('admin.users.index') ? 'active' : ''); ?>" href="<?php echo e(route('admin.users.index')); ?>">👥 <span>Users</span></a>
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

                    <?php if(auth()->user()->isAdmin()): ?>
                    <div class="notification-container" style="position: relative; display: inline-block;">
                        <button onclick="toggleNotifDropdown(event)" class="icon-btn" style="position: relative; border: none; background: transparent; cursor: pointer; font-size: 1.25rem;">
                            🔔
                            <?php $unreadCount = auth()->user()->unreadNotifications->count(); ?>
                            <?php if($unreadCount > 0): ?>
                            <span style="position: absolute; top: -5px; right: -5px; background: #e11d48; color: white; font-size: 0.65rem; font-weight: bold; border-radius: 99px; min-width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; line-height: 1;">
                                <?php echo e($unreadCount > 9 ? '9+' : $unreadCount); ?>

                            </span>
                            <?php endif; ?>
                        </button>

                        <div id="notif-dropdown" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; width: 320px; background: white; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; z-index: 50; overflow: hidden; text-align: left;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                                <span style="font-weight: 600; font-size: 0.875rem; color: #1e293b;">Notifications</span>
                                <?php if($unreadCount > 0): ?>
                                <form method="POST" action="<?php echo e(route('notifications.mark-read')); ?>" style="margin: 0;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" style="border: none; background: transparent; font-size: 0.75rem; color: #3b82f6; cursor: pointer; padding: 0;">Mark all read</button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php $__empty_1 = true; $__currentLoopData = auth()->user()->notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                $notifUrl = '#';
                                if (isset($notification->data['ticket_id']) && auth()->user()->isAdmin()) {
                                $notifUrl = route('admin.tickets.show', $notification->data['ticket_id']);
                                } elseif (isset($notification->data['ticket_id'])) {
                                $notifUrl = route('tickets.show', $notification->data['ticket_id']);
                                } elseif (isset($notification->data['agent_id'])) {
                                $notifUrl = route('agents.edit', $notification->data['agent_id']);
                                }
                                $finalUrl = route('notifications.read', ['id' => $notification->id, 'redirect' => $notifUrl]);
                                ?>
                                <a href="<?php echo e($finalUrl); ?>" style="display: block; padding: 12px 16px; border-bottom: 1px solid #f1f5f9; text-decoration: none; background: <?php echo e($notification->read_at ? '#ffffff' : '#f0f9ff'); ?>; transition: background 0.2s;">
                                    <div style="font-size: 0.875rem; color: #1e293b; font-weight: <?php echo e($notification->read_at ? '400' : '600'); ?>; line-height: 1.4;">
                                        <?php echo e($notification->data['message'] ?? 'New notification'); ?>

                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                                        <?php echo e($notification->created_at->diffForHumans()); ?>

                                    </div>
                                </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div style="padding: 16px; text-align: center; color: #64748b; font-size: 0.875rem;">
                                    No notifications.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <script>
                        function toggleNotifDropdown(e) {
                            e.stopPropagation();
                            const dropdown = document.getElementById('notif-dropdown');
                            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                                dropdown.style.display = 'block';
                            } else {
                                dropdown.style.display = 'none';
                            }
                        }

                        // Close dropdown when clicking outside
                        document.addEventListener('click', function(e) {
                            const dropdown = document.getElementById('notif-dropdown');
                            if (dropdown && dropdown.style.display === 'block' && !dropdown.contains(e.target)) {
                                dropdown.style.display = 'none';
                            }
                        });
                    </script>
                    <?php endif; ?>

                    <div class="avatar"><?php echo e(strtoupper(substr(auth()->user()->name,0,1))); ?></div>
                    <div>
                        <div class="user-name"><?php echo e(auth()->user()->name); ?></div>
                        <div class="user-role"><?php echo e(auth()->user()->role?->name); ?></div>
                    </div>
                    <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="icon-btn" title="Logout">↪</button></form>
                </div>
            </header>
            <section class="content">
                <?php if(session('success')): ?><div class="flash success">✓ <?php echo e(session('success')); ?></div><?php endif; ?>
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