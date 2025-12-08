<?php
// Test file to verify .htaccess is working
header('Content-Type: application/json');

echo json_encode([
    'status' => 'OK',
    'message' => '.htaccess is working! PHP files are accessible!',
    'file' => __FILE__,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'Unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
    'mod_rewrite' => function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) ? 'Enabled' : 'Unknown/Check via phpinfo'
]);
