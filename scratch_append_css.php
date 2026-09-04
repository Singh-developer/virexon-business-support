<?php
$css = file_get_contents(__DIR__ . '/resources/css/app.css');
$css .= "\nhtml, body { max-width: 100vw; overflow-x: hidden; }\n";
file_put_contents(__DIR__ . '/resources/css/app.css', $css);
echo "Appended correctly!";

