<?php
$dashboard = file_get_contents(__DIR__ . '/resources/views/dashboard/dashboard.blade.php');
$lines = explode("\n", $dashboard);

// Remove lines 38-175
array_splice($lines, 37, 138);
$dashboard = implode("\n", $lines);

// Also remove scripts from bottom 
// Let's just find the script block and remove it
$scriptStart = strpos($dashboard, '<script>');
if ($scriptStart !== false) {
    // Actually wait, there is the tailwind script in head. 
    // I only want to remove the script before </body>
    $lastScript = strrpos($dashboard, '<script>');
    $lastScriptEnd = strrpos($dashboard, '</script>');
    if ($lastScript !== false && $lastScriptEnd !== false) {
        $dashboard = substr_replace($dashboard, '', $lastScript, $lastScriptEnd - $lastScript + 9);
    }
}

file_put_contents(__DIR__ . '/resources/views/dashboard/dashboard.blade.php', $dashboard);
echo "Cleaned up dashboard!\n";

