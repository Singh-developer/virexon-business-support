<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full w-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agent Business Support - Dashboard</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
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
    
    <?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?>

    <!-- External CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
</head>
<body class="bg-[#F4F6F8] text-slate-800 antialiased min-h-screen w-full flex flex-col selection:bg-blue-500 selection:text-white">

    <!-- TOP HEADER NAVBAR (FULL WIDTH) -->
    <header class="bg-[#0B1727] text-white sticky top-0 z-40 border-b border-slate-800/80 shadow-md w-full">
        <div class="w-full px-4 lg:px-6 py-2.5 flex items-center justify-between">
            
            <!-- Left: Mobile Menu Toggle + Logo & Brand -->
            <div class="flex items-center space-x-3">
                <!-- Mobile Hamburger Toggle Button -->
                <button id="mobile-menu-open-btn" class="lg:hidden text-slate-200 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition focus:outline-none" aria-label="Open Mobile Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Logo -->
                <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center space-x-2">
                    <img src="<?php echo e(asset('images/virexon-light.png')); ?>" alt="Logo" class="h-8 object-contain">
                </a>
            </div>

            <!-- Right Contacts & Profile Info -->
            <div class="flex items-center space-x-3 sm:space-x-4 lg:space-x-6 text-xs lg:text-sm">
                <!-- Phone -->
                <div class="hidden md:flex items-center space-x-2 text-slate-200">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="font-semibold text-white">0120-1234567</span>
                </div>

                <span class="hidden md:inline text-slate-600">|</span>

                <!-- Email -->
                <div class="hidden md:flex items-center space-x-2 text-slate-200">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-semibold text-white">support@firmname.com</span>
                </div>

                <!-- Bell Icon with red count 3 badge -->
                <div class="relative cursor-pointer p-1">
                    <svg class="w-5 h-5 text-slate-200 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">3</span>
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
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="hidden xl:block text-left leading-tight">
                        <div class="font-bold text-xs text-white"><?php echo e(Auth::check() ? Auth::user()->name : 'Guest'); ?></div>
                        <div class="text-[11px] text-slate-300"><?php echo e(Auth::check() && Auth::user()->role ? Auth::user()->role->name : 'Working Agent'); ?></div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>

                    <!-- Dropdown Menu -->
                    <div id="header-profile-dropdown" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl py-1 hidden border border-slate-200 z-50">
                        <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition">Profile</a>
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
                        <div class="w-8 h-8 rounded-full bg-[#0B1727] flex items-center justify-center text-white font-bold text-xs">
                            🤝
                        </div>
                        <div class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">AGENT <span class="text-[#0D6EFD]">SUPPORT</span></div>
                    </div>
                    <button id="mobile-menu-close-btn" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-1.5 text-xs font-semibold">
                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-[#0D6EFD] text-white shadow-sm font-bold transition">
                        <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span class="text-sm">Dashboard</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <div class="w-5 h-5 rounded-full border border-slate-400 flex items-center justify-center text-slate-600 font-bold text-[10px]">
                            $
                        </div>
                        <span class="text-xs">Advance & Balance</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-xs">Commission Adjustment</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-xs">Advance Ledger</span>
                    </a>

                    <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <div class="flex items-center space-x-3">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs">Documents</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <div class="flex items-center space-x-3">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="text-xs">Compliance</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <div class="flex items-center space-x-3">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="text-xs">Notifications</span>
                        </div>
                        <span class="bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">2</span>
                    </a>

                    <div class="flex flex-col space-y-2">
                        <button type="button" id="mobile-profile-btn" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
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

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-xs">Support & Help</span>
                    </a>

                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="w-full">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="flex items-center w-full space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-red-600 transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
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
                <div class="font-bold text-xs text-[#0D6EFD]">0120-1234567</div>
                <div class="text-[10px] text-slate-500 truncate">support@firmname.com</div>
            </div>
        </div>
    </div>

    <!-- MAIN APP CONTAINER (FULL SCREEN WIDTH) -->
    <div class="flex-1 flex w-full">

        <!-- DESKTOP LEFT SIDEBAR -->
        <aside class="w-64 bg-white border-r border-slate-200/90 hidden lg:flex flex-col justify-between shrink-0 p-4 min-h-[calc(100vh-61px)]">
            <!-- Sidebar Navigation Links -->
            <nav class="space-y-1.5 text-xs font-semibold">
                
                <!-- Dashboard (Active Item) -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-[#0D6EFD] text-white shadow-sm font-bold transition">
                    <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span class="text-sm">Dashboard</span>
                </a>

                <!-- Advance & Balance -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                    <div class="w-5 h-5 rounded-full border border-slate-400 flex items-center justify-center text-slate-600 font-bold text-[10px]">
                        $
                    </div>
                    <span class="text-xs">Advance & Balance</span>
                </a>

                <!-- Commission Adjustment -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-xs">Commission Adjustment</span>
                </a>

                <!-- Advance Ledger -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-xs">Advance Ledger</span>
                </a>

                <!-- Documents -->
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                    <div class="flex items-center space-x-3">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-xs">Documents</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <!-- Compliance -->
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                    <div class="flex items-center space-x-3">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="text-xs">Compliance</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <!-- Notifications -->
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                    <div class="flex items-center space-x-3">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="text-xs">Notifications</span>
                    </div>
                    <span class="bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">2</span>
                </a>

                <!-- Profile -->
                <div class="flex flex-col space-y-2">
                    <button type="button" id="desktop-profile-btn" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-xs">Profile</span>
                    </button>
                    <!-- User Details under Profile Section -->
                    <div id="desktop-profile-dropdown" class="px-6 py-2 ml-2 border-l-2 border-slate-100 hidden">
                        <div class="text-[11px] text-slate-500">Name</div>
                        <div class="text-xs font-bold text-slate-700"><?php echo e(Auth::check() ? Auth::user()->name : 'N/A'); ?></div>
                        <div class="text-[11px] text-slate-500 mt-1.5">Agent ID</div>
                        <div class="text-xs font-bold text-slate-700"><?php echo e(Auth::check() && Auth::user()->detail ? Auth::user()->detail->agent_id_number : 'N/A'); ?></div>
                    </div>
                </div>

                <!-- Support & Help -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-xs">Support & Help</span>
                </a>

                <!-- Logout -->
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="w-full">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="flex items-center w-full space-x-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-red-600 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="text-xs">Logout</span>
                    </button>
                </form>
            </nav>

            <!-- Bottom Need Help Box Widget -->
            <div class="mt-6 bg-slate-50/80 rounded-xl p-4 border border-slate-200/80">
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-[#0D6EFD] shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800 text-xs">Need Help?</div>
                        <div class="text-[10px] text-slate-500">Monday - Saturday (10 AM - 6 PM)</div>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="font-bold text-sm text-[#0D6EFD]">0120-1234567</div>
                    <div class="text-[11px] text-slate-500 truncate">support@firmname.com</div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA (FULL SCREEN SPAN WITH TALLER HERO BANNER) -->
        <main class="flex-1 p-4 lg:p-6 space-y-6 overflow-x-hidden min-w-0">

            <!-- HERO BLUE BANNER (INCREASED HEIGHT: min-h-[290px], py-10 lg:py-12) -->
            <div class="hero-banner-bg relative rounded-2xl overflow-hidden shadow-md p-6 lg:p-10 py-10 lg:py-12 text-white min-h-[290px] flex items-center">
                
                <!-- Left Background Bar Chart Pillars -->
                <div class="absolute bottom-0 left-44 pointer-events-none flex items-end space-x-2.5 z-0 opacity-40">
                    <div class="w-10 h-28 bg-white/70 rounded-t-sm"></div>
                    <div class="w-10 h-36 bg-white/80 rounded-t-sm"></div>
                    <div class="w-10 h-20 bg-white/60 rounded-t-sm"></div>
                    <div class="w-10 h-44 bg-white/80 rounded-t-sm"></div>
                    <div class="w-10 h-32 bg-white/70 rounded-t-sm"></div>
                </div>

                <!-- Center Background Dark Navy Bars -->
                <div class="absolute bottom-0 left-[48%] pointer-events-none hidden lg:flex items-end space-x-2 z-0 opacity-30">
                    <div class="w-8 h-24 bg-[#041227] rounded-t-sm"></div>
                    <div class="w-8 h-36 bg-[#041227] rounded-t-sm"></div>
                    <div class="w-8 h-40 bg-[#041227] rounded-t-sm"></div>
                    <div class="w-8 h-28 bg-[#041227] rounded-t-sm"></div>
                </div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center w-full">
                    
                    <!-- Left Greeting Text -->
                    <div class="lg:col-span-5 space-y-2">
                        <div class="text-[#3F6F9D] text-sm lg:text-base font-semibold">Welcome back</div>
                        <h1 class="text-3xl lg:text-4xl font-extrabold text-white tracking-tight drop-shadow-md"><?php echo e(Auth::user()->name); ?>!</h1>
                        <p class="text-[#3F6F9D] text-sm lg:text-base pt-1">Grow your business with our support.</p>
                    </div>

                    <!-- Center Figures & White Curved Arrow Graphic -->
                    <div class="lg:col-span-3 hidden lg:flex flex-col items-center justify-center relative min-h-[170px]">
                        
                        <!-- Upward White Arrow Curve Line -->
                        <div class="absolute inset-0 pointer-events-none z-10">
                            <svg class="w-full h-full" viewBox="0 0 200 100" fill="none">
                                <path d="M 5 85 C 70 75, 110 40, 185 10" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round"/>
                                <polygon points="172,6 195,8 183,26" fill="#FFFFFF"/>
                            </svg>
                        </div>

                        <!-- Two Figures Standing (Left Navy, Right Green) -->
                        <div class="relative z-0 flex items-end space-x-2.5 pt-8">
                            <!-- Left Figure (Navy Torso) -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-t-full bg-[#FAD7A0] relative overflow-hidden border border-slate-700">
                                    <div class="absolute top-0 inset-x-0 h-3.5 bg-[#1C2833]"></div>
                                </div>
                                <div class="w-11 h-20 bg-[#0B254E] rounded-t-md relative flex justify-center pt-1 mt-0.5">
                                    <div class="w-3 h-5 bg-white clip-v"></div>
                                </div>
                            </div>

                            <!-- Right Figure (Green Torso) -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-t-full bg-[#F5CBA7] relative overflow-hidden border border-slate-700">
                                    <div class="absolute top-0 inset-x-0 h-3.5 bg-[#17202A]"></div>
                                </div>
                                <div class="w-11 h-20 bg-[#00897B] rounded-t-md relative flex justify-center pt-1 mt-0.5">
                                    <div class="w-3 h-5 bg-white"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Overlay Dark Box Card -->
                    <div class="lg:col-span-4 bg-[#051329] border border-white/10 rounded-2xl p-6 shadow-2xl flex items-center space-x-4">
                        <!-- Rupee Icon in Circle -->
                        <div class="w-14 h-14 rounded-full bg-[#0A1C36] border-2 border-white flex items-center justify-center text-white shrink-0 shadow-md">
                            <span class="text-2xl font-bold">₹</span>
                        </div>

                        <!-- Card Values -->
                        <div class="space-y-1 flex-1">
                            <div class="font-bold text-white text-base">Business Support Advance</div>
                            <div class="text-xs text-slate-400">Up to</div>
                            <div class="text-3xl font-extrabold text-[#F59E0B] tracking-tight">₹5,00,000</div>
                            <div class="text-xs text-slate-300 pt-1 flex items-center space-x-2">
                                <span class="font-semibold">Maximum 60 Months Tenure</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ROW 1: 4 SUMMARY CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Card 1: Approved Advance Amount -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-[#E8F8F5] flex items-center justify-center text-[#27AE60] shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">Approved Advance Amount</div>
                        <div class="text-2xl font-extrabold text-[#27AE60]">₹0</div>
                        <div class="text-xs text-slate-500 pt-0.5">
                            Maximum Limit <span class="font-semibold text-slate-700">₹5,00,000</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Advance Disbursed -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-[#EBF5FB] flex items-center justify-center text-[#1565C0] shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">Advance Disbursed</div>
                        <div class="text-2xl font-extrabold text-[#1565C0]">₹0</div>
                        
                    </div>
                </div>

                <!-- Card 3: Outstanding Balance -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-[#FEF5E7] flex items-center justify-center text-[#E65100] shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">Outstanding Balance</div>
                        <div class="text-2xl font-extrabold text-[#E65100]">₹0</div>
                        <div class="text-xs text-slate-500 pt-0.5">
                            Repayment Tenure <span class="font-semibold text-slate-700">60 Months</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Total Month of Commission (UPDATED ICON MATCHING TEXT AMOUNT) -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-[#F4ECF7] flex items-center justify-center text-[#6A1B9A] font-extrabold text-xl shrink-0">
                        ₹
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">Total Month of Commission</div>
                        <div class="text-2xl font-extrabold text-[#6A1B9A]">₹0</div>
                        <div class="text-xs text-slate-500 pt-0.5">
                            Processing / Finance Charge <span class="font-semibold text-slate-700">₹0</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ROW 2: 3 COLUMNS CARDS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- CARD 1: Outstanding Overview -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h2 class="font-bold text-slate-800 text-sm">Outstanding Overview</h2>
                            <button class="text-slate-400 hover:text-slate-600 text-lg leading-none">•••</button>
                        </div>

                        <div class="grid grid-cols-12 gap-3 items-center">
                            <!-- Left Stats -->
                            <div class="col-span-7 space-y-3 text-xs">
                                <div>
                                    <div class="text-slate-500 font-medium">Total Approved</div>
                                    <div class="text-base font-extrabold text-slate-800">₹0</div>
                                </div>
                                <div>
                                    <div class="text-slate-500 font-medium">Total Repaid (via Commission)</div>
                                    <div class="text-base font-extrabold text-[#27AE60]">₹0</div>
                                </div>
                                <div>
                                    <div class="text-slate-500 font-medium">Outstanding Balance</div>
                                    <div class="text-base font-extrabold text-[#E65100]">₹0</div>
                                </div>
                            </div>

                            <!-- Donut Progress Ring -->
                            <div class="col-span-5 flex justify-center">
                                <div class="relative w-24 h-24 flex items-center justify-center">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-slate-200" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path class="text-[#27AE60]" stroke-dasharray="36, 100" stroke-width="4" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                        <span class="text-base font-black text-slate-800 leading-none">36%</span>
                                        <span class="text-[10px] text-slate-500 font-medium pt-0.5">Repaid</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Notice Callout -->
                    <div class="bg-[#F1F8E9] border border-emerald-200/70 rounded-xl p-3 flex items-start space-x-3 text-xs">
                        <div class="w-5 h-5 rounded-full bg-[#27AE60] text-white flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="space-y-1 flex-1">
                            <p class="text-emerald-950 font-medium leading-tight">Repayment is automatically adjusted from your future commissions.</p>
                            <a href="#" class="inline-block font-bold text-[#0D6EFD] hover:underline">View Ledger →</a>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Commission Adjustment (Monthly) -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <h2 class="font-bold text-slate-800 text-sm">Commission Adjustment (Monthly)</h2>
                            <button class="text-slate-400 hover:text-slate-600 text-lg leading-none">•••</button>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-50">
                                <span class="text-slate-500 font-medium">Monthly Commission</span>
                                <span class="font-extrabold text-slate-800 text-sm">₹0</span>
                            </div>
                            <!-- <div class="flex items-center justify-between pb-1.5 border-b border-slate-50">
                                <span class="text-slate-500 font-medium">Adjustment Percentage</span>
                                <span class="font-extrabold text-slate-800 text-sm">20%</span>
                            </div> -->
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-50">
                                <span class="text-slate-500 font-medium">Monthly Adjustment Amount</span>
                                <span class="font-extrabold text-slate-800 text-sm">₹0</span>
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <span class="text-slate-700 font-bold">Net Commission Paid</span>
                                <span class="font-black text-slate-900 text-base">₹0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Calendar Box -->
                    <div class="bg-[#EBF3FE] border border-blue-200/80 rounded-xl p-3 flex items-center space-x-3 text-xs">
                        <div class="w-9 h-9 rounded-lg bg-[#0D6EFD] text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-blue-800 font-semibold text-[11px]">Next Adjustment Date</div>
                            
                        </div>
                    </div>
                </div>

                <!-- CARD 3: Quick Actions -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h2 class="font-bold text-slate-800 text-sm">Quick Actions</h2>
                        <button class="text-slate-400 hover:text-slate-600 text-lg leading-none">•••</button>
                    </div>

                    <div class="space-y-2 text-xs">
                        <!-- Action 1 -->
                        <a href="#" class="flex items-center justify-between p-2.5 rounded-xl bg-[#E8F8F5] hover:bg-emerald-100/70 border border-emerald-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#27AE60] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Apply for Advance</div>
                                    <div class="text-[10px] text-slate-500">Request new Business Support Advance</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <!-- Action 2 -->
                        <a href="#" class="flex items-center justify-between p-2.5 rounded-xl bg-[#EBF5FB] hover:bg-blue-100/70 border border-blue-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#1565C0] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">View Advance Ledger</div>
                                    <div class="text-[10px] text-slate-500">Check all transactions and adjustments</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <!-- Action 3 -->
                        <a href="#" class="flex items-center justify-between p-2.5 rounded-xl bg-[#F4ECF7] hover:bg-purple-100/70 border border-purple-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#6A1B9A] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Download Agreement (Schedule A)</div>
                                    <div class="text-[10px] text-slate-500">Business Support Advance Agreement</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <!-- Action 4 -->
                        <a href="#" class="flex items-center justify-between p-2.5 rounded-xl bg-[#FEF5E7] hover:bg-amber-100/70 border border-amber-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#E65100] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 11-18 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Download Undertaking (Schedule B)</div>
                                    <div class="text-[10px] text-slate-500">Repayment via Commission Undertaking</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <!-- Action 5 -->
                        <a href="#" class="flex items-center justify-between p-2.5 rounded-xl bg-[#E0F2F1] hover:bg-teal-100/70 border border-teal-100 transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 rounded-lg bg-[#00897B] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">Download Direction Letter (Schedule C)</div>
                                    <div class="text-[10px] text-slate-500">Commission Adjustment Direction</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                    </div>
                </div>

            </div>

            <!-- ROW 3: COMPLIANCE SNAPSHOT -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 space-y-3">
                <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3">Compliance Snapshot</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 items-center">

                    <!-- 1. KYC -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 border border-slate-200 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">KYC Documents</div>
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Compliant</span>
                        </div>
                    </div>

                    <!-- 2. PAN -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-[#27AE60] border border-slate-200 shrink-0">
                            <span class="text-[9px] font-extrabold border border-[#27AE60] px-0.5 rounded">PAN</span>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">PAN Verified</div>
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Compliant</span>
                        </div>
                    </div>

                    <!-- 3. Bank -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 border border-slate-200 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">Bank Details Verified</div>
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Compliant</span>
                        </div>
                    </div>

                    <!-- 4. Agreement -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 border border-slate-200 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">Agreement Signed</div>
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Compliant</span>
                        </div>
                    </div>

                    <!-- 5. Undertaking -->
                    <div class="flex items-center space-x-3 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 border border-slate-200 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <div class="font-bold text-slate-800 text-xs truncate">Undertaking Submitted</div>
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E8F8F5] text-[#27AE60]">Compliant</span>
                        </div>
                    </div>

                    <!-- 6. Status Shield Card -->
                    <div class="bg-[#E8F8F5] border border-emerald-200 rounded-xl p-3 flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full bg-[#27AE60] text-white flex items-center justify-center shrink-0 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-extrabold text-emerald-950 text-xs">You are Compliant</div>
                            <div class="text-[10px] text-emerald-800 font-medium">All good to continue business with us.</div>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <!-- FOOTER NAVBAR (FULL WIDTH) -->
    <footer class="bg-[#0B1727] text-slate-400 text-xs border-t border-slate-800 py-4 px-4 sm:px-6 mt-auto w-full">
        <div class="w-full flex flex-col md:flex-row items-center justify-between gap-3 text-center md:text-left">
            <div class="whitespace-nowrap font-medium text-slate-300">© 2024 Firm Name. All rights reserved.</div>
            
            <div class="flex flex-wrap items-center justify-center gap-x-2 sm:gap-x-4 gap-y-1 text-slate-300">
                <a href="#" class="whitespace-nowrap hover:text-white transition">Terms & Conditions</a>
                <span class="text-slate-600">|</span>
                <a href="#" class="whitespace-nowrap hover:text-white transition">Privacy Policy</a>
                <span class="text-slate-600">|</span>
                <a href="#" class="whitespace-nowrap hover:text-white transition">Compliance</a>
            </div>

            <div class="whitespace-nowrap font-medium text-slate-400">Version 1.0.0</div>
        </div>
    </footer>

    <!-- INTERACTIVE JAVASCRIPT FOR MOBILE DRAWER MENU -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const drawer = document.getElementById('mobile-sidebar-drawer');
            const openBtn = document.getElementById('mobile-menu-open-btn');
            const closeBtn = document.getElementById('mobile-menu-close-btn');
            const backdrop = document.getElementById('mobile-drawer-backdrop');

            function openDrawer() {
                drawer.classList.remove('hidden');
                document.body.classList.add('no-scroll');
            }

            function closeDrawer() {
                drawer.classList.add('hidden');
                document.body.classList.remove('no-scroll');
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

            const desktopProfileBtn = document.getElementById('desktop-profile-btn');
            const desktopProfileDropdown = document.getElementById('desktop-profile-dropdown');
            if (desktopProfileBtn && desktopProfileDropdown) {
                desktopProfileBtn.addEventListener('click', function() {
                    desktopProfileDropdown.classList.toggle('hidden');
                });
            }

            const mobileProfileBtn = document.getElementById('mobile-profile-btn');
            const mobileProfileDropdown = document.getElementById('mobile-profile-dropdown');
            if (mobileProfileBtn && mobileProfileDropdown) {
                mobileProfileBtn.addEventListener('click', function() {
                    mobileProfileDropdown.classList.toggle('hidden');
                });
            }

            // Close header dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (headerProfileDropdown && !headerProfileDropdown.classList.contains('hidden') && !headerProfileBtn.contains(e.target)) {
                    headerProfileDropdown.classList.add('hidden');
                }
            });
        });
    </script>

</body>
</html>
<?php /**PATH E:\xampp\htdocs\laravel\agent-business-support\resources\views\dashboard\dashboard.blade.php ENDPATH**/ ?>