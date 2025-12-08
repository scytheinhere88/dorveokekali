<!DOCTYPE html>
<html>
<head>
    <title>Server Diagnostic - Dorve.id</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #333; }
        .ok { border-left-color: #22c55e; }
        .error { border-left-color: #ef4444; }
        .warning { border-left-color: #f59e0b; }
        h2 { margin: 0 0 15px 0; color: #333; }
        pre { background: #f9f9f9; padding: 10px; overflow-x: auto; }
        .test-links { display: flex; gap: 10px; margin-top: 10px; }
        .test-links a { padding: 8px 16px; background: #333; color: white; text-decoration: none; border-radius: 4px; }
        .test-links a:hover { background: #555; }
    </style>
</head>
<body>
    <h1>🔍 Server Diagnostic Report - Dorve.id</h1>

    <div class="box ok">
        <h2>✅ This File is Accessible</h2>
        <p><strong>File:</strong> <?php echo __FILE__; ?></p>
        <p><strong>This proves PHP is working!</strong></p>
    </div>

    <div class="box <?php echo function_exists('apache_get_modules') ? 'ok' : 'warning'; ?>">
        <h2>🌐 Server Information</h2>
        <pre><?php
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Script Filename: " . (__FILE__) . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server API: " . php_sapi_name() . "\n";
        ?></pre>
    </div>

    <div class="box <?php echo function_exists('apache_get_modules') ? 'ok' : 'warning'; ?>">
        <h2>🔧 Apache Modules</h2>
        <?php if (function_exists('apache_get_modules')): ?>
            <pre><?php
            $modules = apache_get_modules();
            echo "mod_rewrite: " . (in_array('mod_rewrite', $modules) ? '✅ ENABLED' : '❌ DISABLED') . "\n";
            echo "\nAll modules:\n";
            foreach ($modules as $module) {
                echo "  - $module\n";
            }
            ?></pre>
        <?php else: ?>
            <p><strong>⚠️ apache_get_modules() not available</strong></p>
            <p>This might mean you're using Nginx or PHP-FPM. Check with hosting provider.</p>
        <?php endif; ?>
    </div>

    <div class="box">
        <h2>📁 Directory Check</h2>
        <pre><?php
$dirs = ['admin', 'api', 'auth', 'member', 'pages', 'includes', 'uploads', 'public'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    $exists = is_dir($path);
    echo "$dir: " . ($exists ? "✅ EXISTS" : "❌ NOT FOUND") . "\n";
}
        ?></pre>
    </div>

    <div class="box">
        <h2>📄 Critical File Check</h2>
        <pre><?php
$files = [
    'admin/login.php',
    'admin/index.php',
    'admin/test.php',
    'auth/login.php',
    'pages/cart.php',
    'api/orders/create.php',
    'test-htaccess.php',
    '.htaccess'
];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    echo "$file: " . ($exists ? "✅ EXISTS" : "❌ NOT FOUND");
    if ($exists && $file === '.htaccess') {
        echo " (" . filesize($path) . " bytes)";
    }
    echo "\n";
}
        ?></pre>
    </div>

    <div class="box">
        <h2>🧪 Test Links - Try These</h2>
        <div class="test-links">
            <a href="/admin/test.php" target="_blank">Test Admin Access</a>
            <a href="/admin/login.php" target="_blank">Admin Login</a>
            <a href="/admin/" target="_blank">Admin Dashboard</a>
            <a href="/test-htaccess.php" target="_blank">Test .htaccess</a>
            <a href="/" target="_blank">Homepage</a>
        </div>
    </div>

    <div class="box warning">
        <h2>⚠️ Current Issue</h2>
        <p><strong>Problem:</strong> https://dorve.id/admin/login.php returns 404</p>
        <p><strong>Expected:</strong> Should load the admin login page</p>
        <p><strong>Actual:</strong> Getting redirected to homepage (index.php)</p>

        <h3 style="margin-top: 20px;">Possible Causes:</h3>
        <ol>
            <li><strong>Server not reading .htaccess</strong> - AllowOverride might be set to "None"</li>
            <li><strong>Using Nginx</strong> - .htaccess doesn't work on Nginx, need nginx.conf</li>
            <li><strong>mod_rewrite disabled</strong> - Apache module not enabled</li>
            <li><strong>File permissions</strong> - .htaccess not readable by server</li>
            <li><strong>Wrong directory</strong> - .htaccess not in document root</li>
        </ol>
    </div>

    <div class="box">
        <h2>🛠️ Current .htaccess Rules</h2>
        <pre><?php
$htaccess_path = __DIR__ . '/.htaccess';
if (file_exists($htaccess_path)) {
    echo htmlspecialchars(file_get_contents($htaccess_path));
} else {
    echo "❌ .htaccess file not found!";
}
        ?></pre>
    </div>

    <div class="box ok">
        <h2>✅ Next Steps</h2>
        <ol>
            <li>Click on "Test Admin Access" button above</li>
            <li>If you see "ADMIN DIRECTORY IS ACCESSIBLE!" → .htaccess is now working! ✅</li>
            <li>If you get 404 or homepage → Contact hosting provider with this info:
                <pre style="margin-top: 10px;">Subject: Enable mod_rewrite and AllowOverride for dorve.id

Hi, my website dorve.id is having issues with .htaccess rewrite rules.

Please verify:
1. mod_rewrite module is enabled
2. AllowOverride is set to "All" (not "None") for my document root
3. .htaccess file is being read from the correct location

Current issue: Admin pages return 404 despite files existing.
Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?>

Thank you!</pre>
            </li>
        </ol>
    </div>

    <div class="box">
        <h2>📋 System Information Summary</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f9f9f9;">
                <td style="padding: 8px; font-weight: bold;">Item</td>
                <td style="padding: 8px; font-weight: bold;">Status</td>
            </tr>
            <tr>
                <td style="padding: 8px;">PHP Working</td>
                <td style="padding: 8px;">✅ Yes</td>
            </tr>
            <tr style="background: #f9f9f9;">
                <td style="padding: 8px;">Admin Files Exist</td>
                <td style="padding: 8px;"><?php echo file_exists(__DIR__ . '/admin/login.php') ? '✅ Yes' : '❌ No'; ?></td>
            </tr>
            <tr>
                <td style="padding: 8px;">.htaccess Exists</td>
                <td style="padding: 8px;"><?php echo file_exists(__DIR__ . '/.htaccess') ? '✅ Yes' : '❌ No'; ?></td>
            </tr>
            <tr style="background: #f9f9f9;">
                <td style="padding: 8px;">mod_rewrite</td>
                <td style="padding: 8px;">
                    <?php
                    if (function_exists('apache_get_modules')) {
                        echo in_array('mod_rewrite', apache_get_modules()) ? '✅ Enabled' : '❌ Disabled';
                    } else {
                        echo '⚠️ Unknown (check with provider)';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
