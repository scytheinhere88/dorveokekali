<?php
// Simple script to add mobile CSS link to header.php

$header_file = __DIR__ . '/../includes/header.php';
$content = file_get_contents($header_file);

// Check if already added
if (strpos($content, 'mobile-responsive.css') !== false) {
    echo "✅ Mobile CSS link already exists in header.php!";
    exit;
}

// Add before </head>
$mobile_css_link = '    <link rel="stylesheet" href="/includes/mobile-responsive.css">' . "\n";
$content = str_replace('</head>', $mobile_css_link . '</head>', $content);

file_put_contents($header_file, $content);

echo "✅ Successfully added mobile CSS link to header.php!";
?>
