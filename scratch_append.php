<?php
$dashboard = file_get_contents(__DIR__ . '/resources/views/dashboard/dashboard.blade.php');
$lines = explode("\n", $dashboard);
$drawer = implode("\n", array_slice($lines, 37, 138)); // lines 38-175

$script = <<<'EOD'

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Sidebar Toggle
        const openBtn = document.getElementById('mobile-menu-open-btn');
        const closeBtn = document.getElementById('mobile-menu-close-btn');
        const drawer = document.getElementById('mobile-sidebar-drawer');
        const backdrop = document.getElementById('mobile-drawer-backdrop');

        function openDrawer() {
            if(drawer) drawer.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeDrawer() {
            if(drawer) drawer.classList.add('hidden');
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

        const mobileProfileBtn = document.getElementById('mobile-profile-btn');
        const mobileProfileDropdown = document.getElementById('mobile-profile-dropdown');
        if (mobileProfileBtn && mobileProfileDropdown) {
            mobileProfileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
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
EOD;

file_put_contents(__DIR__ . '/resources/views/partials/navbar.blade.php', "\n" . $drawer . "\n" . $script, FILE_APPEND);
echo "Appended!\n";

