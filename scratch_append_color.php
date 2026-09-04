<?php
$css = file_get_contents(__DIR__ . '/resources/css/app.css');
// Strip null bytes if corrupted
$css = str_replace("\0", '', $css);
$css .= "\n.form-grid input, .form-grid select { color: #1e293b !important; }\n";
file_put_contents(__DIR__ . '/resources/css/app.css', $css);
echo "Appended correctly!";

