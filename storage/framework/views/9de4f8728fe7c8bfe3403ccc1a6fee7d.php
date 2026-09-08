    <!-- TOP HEADER NAVBAR (FULL WIDTH) -->
    <header class="bg-[#0B1727] text-white sticky top-0 z-40 border-b border-slate-800/80 shadow-md w-full">
        <div class="w-full px-4 lg:px-6 py-2.5 flex items-center justify-between">

            <!-- Left: Mobile Menu Toggle + Logo & Brand -->
            <div class="flex items-center space-x-3">
                <!-- Mobile Hamburger Toggle Button -->
                <button id="mobile-menu-open-btn" class="lg:hidden text-slate-200 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition focus:outline-none" aria-label="Open Mobile Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Logo -->
                <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center space-x-2">
                    <img src="<?php echo e(asset('images/virexon-light.png')); ?>" alt="Logo" class="h-10 sm:h-12 object-contain">
                </a>
            </div>

            <!-- Right Contacts & Profile Info -->
            <div class="flex items-center space-x-3 sm:space-x-4 lg:space-x-6 text-xs lg:text-sm">
                <!-- Phone -->
                <div class="hidden md:flex items-center space-x-2 text-slate-200">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <!-- <span class="font-semibold text-white">0120-1234567</span> -->
                </div>

                <span class="hidden md:inline text-slate-600">|</span>

                <!-- Email -->
                <div class="hidden md:flex items-center space-x-2 text-slate-200">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="font-semibold text-white">support@virexon.in</span>
                </div>

                <!-- Bell Icon with Notifications -->
                <div class="relative cursor-pointer p-1" id="agent-notif-btn">
                    <svg class="w-5 h-5 text-slate-200 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" />
                    </svg>
                    <?php if(Auth::check() && Auth::user()->unreadNotifications->count() > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                        <?php echo e(Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count()); ?>

                    </span>
                    <?php endif; ?>

                    <!-- Notification Dropdown -->
                    <div id="agent-notif-dropdown" class="absolute right-0 top-full mt-3 w-80 bg-white rounded-xl shadow-xl hidden border border-slate-200 z-50 overflow-hidden" style="cursor: default;">
                        <div class="flex justify-between items-center px-4 py-3 border-b border-slate-100 bg-slate-50">
                            <span class="font-bold text-sm text-slate-800">Notifications</span>
                            <?php if(Auth::check() && Auth::user()->unreadNotifications->count() > 0): ?>
                            <form method="POST" action="<?php echo e(route('notifications.mark-read')); ?>" class="m-0">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="text-xs font-semibold text-blue-600 hover:text-blue-800 focus:outline-none">Mark all read</button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <div class="max-h-[300px] overflow-y-auto">
                            <?php if(Auth::check()): ?>
                                <?php $__empty_1 = true; $__currentLoopData = Auth::user()->notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $notifUrl = '#';
                                        if (isset($notification->data['ticket_id'])) {
                                            $notifUrl = route('tickets.show', $notification->data['ticket_id']);
                                        }
                                        $finalUrl = route('notifications.read', ['id' => $notification->id, 'redirect' => $notifUrl]);
                                    ?>
                                    <a href="<?php echo e($finalUrl); ?>" class="block px-4 py-3 border-b border-slate-50 hover:bg-slate-50 transition <?php echo e($notification->read_at ? 'opacity-70' : 'bg-blue-50/30'); ?>">
                                        <div class="text-sm text-slate-800 <?php echo e($notification->read_at ? '' : 'font-semibold'); ?> leading-tight mb-1">
                                            <?php echo e($notification->data['message'] ?? 'New notification'); ?>

                                        </div>
                                        <div class="text-[11px] text-slate-500 font-medium">
                                            <?php echo e($notification->created_at->diffForHumans()); ?>

                                        </div>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="px-4 py-6 text-center text-slate-500 text-sm font-medium">
                                        No notifications yet.
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Agent ID Dropdown Button -->
                <div class="hidden sm:flex items-center space-x-1.5 bg-slate-800/90 hover:bg-slate-800 px-3 py-1.5 rounded text-slate-200 text-xs cursor-pointer border border-slate-700">
                    <span>Agent ID:</span>
                    <span class="font-bold text-white"><?php echo e(Auth::check() && Auth::user()->detail ? Auth::user()->detail->agent_id_number : 'N/A'); ?></span>
                </div>

                <!-- User Profile Dropdown -->
                <div class="relative flex items-center space-x-2 cursor-pointer sm:pl-1" id="header-profile-btn">
                    <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-slate-800 font-bold overflow-hidden shrink-0 shadow">
                        <svg class="w-6 h-6 text-slate-700 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="hidden xl:block text-left leading-tight">
                        <div class="font-bold text-xs text-white"><?php echo e(Auth::check() ? Auth::user()->name : 'Guest'); ?></div>
                        <div class="text-[11px] text-slate-300"><?php echo e(Auth::check() && Auth::user()->role ? Auth::user()->role->name : 'Working Agent'); ?></div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>

                    <!-- Dropdown Menu -->
                    <div id="header-profile-dropdown" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl py-1 hidden border border-slate-200 z-50">
                        <a href="<?php echo e(route('settings.profile')); ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">Profile</a>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-slate-50 transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>


    <!-- MOBILE SIDEBAR DRAWER OVERLAY -->
    <div id="mobile-sidebar-drawer" class="fixed inset-0 z-50 hidden transition-opacity duration-300">
        <!-- Backdrop dark overlay -->
        <div id="mobile-drawer-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

        <!-- Sidebar Panel Drawer -->
        <div class="relative w-72 max-w-[80vw] bg-white h-full shadow-2xl flex flex-col justify-between p-4 overflow-y-auto z-10">
            <div>
                <!-- Header inside drawer -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div class="flex items-center space-x-2">
                        <!-- <div class="w-8 h-8 rounded-full bg-[#0B1727] flex items-center justify-center text-white font-bold text-xs">
                            🤝
                        </div> -->
                        <div><a href="<?php echo e(route('dashboard')); ?>"><img src="<?php echo e(asset('images/virexon-light.png')); ?>" alt="Virexon" style="max-width: 40%;" /></a></div>
                        <div class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">AGENT <span class="text-[#0D6EFD]">SUPPORT</span></div>
                    </div>
                    <button id="mobile-menu-close-btn" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-1.5 text-xs font-semibold">
                    <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-[#0D6EFD] text-white shadow-sm font-bold transition">
                        <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                        </svg>
                        <span class="text-sm">Dashboard</span>
                    </a>

                    <!-- <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <div class="w-5 h-5 rounded-full border border-slate-400 flex items-center justify-center text-slate-600 font-bold text-[10px]">
                            $
                        </div>
                        <span class="text-xs">Advance & Balance</span>
                    </a> -->
                    <a href="<?php echo e(route('advances.index')); ?>" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <i class="fa-solid fa-wallet text-slate-500 w-4 text-center"></i>
                        <span class="text-xs">Advance & Balance</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs">Commission Adjustment</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs">Advance Ledger</span>
                    </a>

                    <a href="<?php echo e(route('register.agent')); ?>" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span class="text-xs">Fund Application</span>
                    </a>

                    <a href="<?php echo e(route('documents.index')); ?>" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs">My Documents</span>
                    </a>

                    <!-- <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <div class="flex items-center space-x-3">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs">Documents</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a> -->

                    <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <div class="flex items-center space-x-3">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span class="text-xs">Compliance</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <div class="flex items-center space-x-3">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" />
                            </svg>
                            <span class="text-xs">Notifications</span>
                        </div>
                        <span class="bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">2</span>
                    </a>

                    <div class="flex flex-col space-y-2">
                        <button type="button" id="mobile-profile-btn" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-xs">Profile</span>
                        </button>
                        <div id="mobile-profile-dropdown" class="px-6 py-2 ml-2 border-l-2 border-slate-100 hidden">
                            <div class="text-[11px] text-slate-500">Name</div>
                            <div class="text-xs font-bold text-slate-700"><?php echo e(Auth::check() ? Auth::user()->name : 'N/A'); ?></div>
                            <div class="text-[11px] text-slate-500 mt-1.5">Agent ID</div>
                            <div class="text-xs font-bold text-slate-700"><?php echo e(Auth::check() && Auth::user()->detail ? Auth::user()->detail->agent_id_number : 'N/A'); ?></div>
                        </div>
                    </div>

                    <a href="<?php echo e(route('tickets.index')); ?>" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs">Support Tickets</span>
                    </a>

                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="w-full">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="flex items-center w-full space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-red-600 transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="text-xs">Logout</span>
                        </button>
                    </form>
                </nav>
            </div>

            <!-- Bottom Support Info -->
            <div class="mt-6 bg-slate-50 rounded-xl p-3 border border-slate-200/80">
                <div class="font-bold text-slate-800 text-xs mb-1">Need Help?</div>
                <div class="text-[10px] text-slate-500 mb-2">Mon - Sat (10 AM - 6 PM)</div>
                <!-- <div class="font-bold text-xs text-[#0D6EFD]">0120-1234567</div> -->
                <div class="text-[10px] text-slate-500 truncate">support@virexon.in</div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Sidebar Toggle
            const openBtn = document.getElementById('mobile-menu-open-btn');
            const closeBtn = document.getElementById('mobile-menu-close-btn');
            const drawer = document.getElementById('mobile-sidebar-drawer');
            const backdrop = document.getElementById('mobile-drawer-backdrop');

            function openDrawer() {
                if (drawer) drawer.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeDrawer() {
                if (drawer) drawer.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            if (openBtn) openBtn.addEventListener('click', openDrawer);
            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (backdrop) backdrop.addEventListener('click', closeDrawer);

            // Profile Dropdown Toggles
            const headerProfileBtn = document.getElementById('header-profile-btn');
            const headerProfileDropdown = document.getElementById('header-profile-dropdown');

            if (headerProfileBtn && headerProfileDropdown) {
                headerProfileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    headerProfileDropdown.classList.toggle('hidden');
                });
            }

            // Notification Dropdown Toggle
            const agentNotifBtn = document.getElementById('agent-notif-btn');
            const agentNotifDropdown = document.getElementById('agent-notif-dropdown');

            if (agentNotifBtn && agentNotifDropdown) {
                agentNotifBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    agentNotifDropdown.classList.toggle('hidden');
                });
            }

            // Mobile Profile Toggles
            const mobileProfileBtn = document.getElementById('mobile-profile-btn');
            const mobileProfileDropdown = document.getElementById('mobile-profile-dropdown');
            if (mobileProfileBtn && mobileProfileDropdown) {
                mobileProfileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileProfileDropdown.classList.toggle('hidden');
                });
            }

            const desktopProfileBtn = document.getElementById('desktop-profile-btn');
            const desktopProfileDropdown = document.getElementById('desktop-profile-dropdown');
            if (desktopProfileBtn && desktopProfileDropdown) {
                desktopProfileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    desktopProfileDropdown.classList.toggle('hidden');
                });
            } 
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (headerProfileDropdown && !headerProfileDropdown.classList.contains('hidden') && !headerProfileBtn.contains(e.target)) {
                    headerProfileDropdown.classList.add('hidden');
                }
                if (agentNotifDropdown && !agentNotifDropdown.classList.contains('hidden') && !agentNotifBtn.contains(e.target)) {
                    agentNotifDropdown.classList.add('hidden');
                }
            });
        });
    </script><?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views/partials/navbar.blade.php ENDPATH**/ ?>