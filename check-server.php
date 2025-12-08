<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>SERVER CHECK - DORVE.ID</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #0f0;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            font-size: 32px;
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            border: 3px solid #0f0;
            background: #003300;
        }
        .success { background: #003300; border-color: #0f0; color: #0f0; }
        .error { background: #330000; border-color: #f00; color: #f00; }
        .warning { background: #332200; border-color: #ff0; color: #ff0; }
        .box {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #0f0;
            border-radius: 5px;
        }
        h2 {
            font-size: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid currentColor;
        }
        pre {
            background: #001100;
            padding: 15px;
            border-radius: 3px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .highlight {
            background: #ffff00;
            color: #000;
            padding: 2px 5px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        td {
            padding: 8px;
            border: 1px solid currentColor;
        }
        td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .copy-box {
            background: #001100;
            padding: 15px;
            border: 2px dashed #0f0;
            margin: 15px 0;
        }
        .big {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>🔍 SERVER CONFIGURATION CHECK</h1>

<div class="box success">
    <h2>✅ THIS FILE IS LOADING!</h2>
    <p><strong>File:</strong> <?php echo __FILE__; ?></p>
    <p><strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    <p><span class="highlight">If you can see this, PHP is working perfectly!</span></p>
</div>

<div class="box">
    <h2>🖥️ SERVER SOFTWARE</h2>
    <table>
        <tr>
            <td>Server Software</td>
            <td class="<?php echo (stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false) ? 'warning' : 'success'; ?>">
                <strong><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></strong>
                <?php if (stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false): ?>
                    <br><span class="warning">⚠️ NGINX DETECTED! .htaccess will NOT work!</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td>PHP Version</td>
            <td><?php echo phpversion(); ?></td>
        </tr>
        <tr>
            <td>PHP SAPI</td>
            <td><?php echo php_sapi_name(); ?></td>
        </tr>
        <tr>
            <td>Document Root</td>
            <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></td>
        </tr>
        <tr>
            <td>Script Filename</td>
            <td><?php echo __FILE__; ?></td>
        </tr>
    </table>
</div>

<?php if (function_exists('apache_get_modules')): ?>
<div class="box success">
    <h2>✅ APACHE MODULES DETECTED</h2>
    <?php
    $modules = apache_get_modules();
    $hasRewrite = in_array('mod_rewrite', $modules);
    ?>
    <table>
        <tr>
            <td>mod_rewrite</td>
            <td class="<?php echo $hasRewrite ? 'success' : 'error'; ?>">
                <?php echo $hasRewrite ? '✅ ENABLED' : '❌ DISABLED'; ?>
            </td>
        </tr>
    </table>
    <details>
        <summary style="cursor: pointer; margin-top: 10px;">Show all modules (<?php echo count($modules); ?>)</summary>
        <pre><?php echo implode("\n", $modules); ?></pre>
    </details>
</div>
<?php else: ?>
<div class="box warning">
    <h2>⚠️ apache_get_modules() NOT AVAILABLE</h2>
    <p>This usually means:</p>
    <ul style="margin-left: 30px;">
        <li>You're using <strong>Nginx</strong> (not Apache)</li>
        <li>You're using PHP-FPM</li>
        <li>.htaccess files will NOT work!</li>
    </ul>
</div>
<?php endif; ?>

<div class="box">
    <h2>📁 FILE EXISTENCE CHECK</h2>
    <table>
        <?php
        $files = [
            '.htaccess' => __DIR__ . '/.htaccess',
            'test-direct.php' => __DIR__ . '/test-direct.php',
            'admin/test-simple.php' => __DIR__ . '/admin/test-simple.php',
            'admin/login.php' => __DIR__ . '/admin/login.php',
            'admin/index.php' => __DIR__ . '/admin/index.php',
        ];

        foreach ($files as $name => $path) {
            $exists = file_exists($path);
            $readable = $exists && is_readable($path);
            echo "<tr>";
            echo "<td>$name</td>";
            echo "<td class='" . ($exists ? 'success' : 'error') . "'>";
            echo $exists ? '✅ EXISTS' : '❌ MISSING';
            if ($exists) {
                echo $readable ? ' (readable)' : ' <span class="error">(NOT READABLE)</span>';
                echo ' - ' . filesize($path) . ' bytes';
            }
            echo "</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

<?php
$htaccessPath = __DIR__ . '/.htaccess';
$htaccessExists = file_exists($htaccessPath);
?>

<div class="box <?php echo $htaccessExists ? 'success' : 'error'; ?>">
    <h2><?php echo $htaccessExists ? '📄 .htaccess FILE FOUND' : '❌ .htaccess FILE MISSING'; ?></h2>
    <?php if ($htaccessExists): ?>
        <p><strong>Size:</strong> <?php echo filesize($htaccessPath); ?> bytes</p>
        <p><strong>Readable:</strong> <?php echo is_readable($htaccessPath) ? '✅ Yes' : '❌ No'; ?></p>
        <details>
            <summary style="cursor: pointer; margin-top: 10px;">Show .htaccess content</summary>
            <pre><?php echo htmlspecialchars(file_get_contents($htaccessPath)); ?></pre>
        </details>
    <?php else: ?>
        <p class="error">⚠️ .htaccess file not found!</p>
    <?php endif; ?>
</div>

<div class="box error">
    <h2>🚨 CURRENT PROBLEM</h2>
    <p class="big">ALL URLs are redirecting to homepage!</p>
    <ul style="margin: 15px 0 15px 30px;">
        <li>test-direct.php → Homepage ❌</li>
        <li>admin/test-simple.php → 404 or Homepage ❌</li>
        <li>admin/login.php → Homepage ❌</li>
    </ul>
    <p class="big" style="margin-top: 20px;">This means:</p>
    <p style="margin: 10px 0;">
        <?php if (stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false): ?>
            <span class="warning">⚠️ You are using NGINX - .htaccess files do NOT work on Nginx!</span>
        <?php else: ?>
            <span class="error">⚠️ Either .htaccess is NOT being read (AllowOverride = None)<br>
            OR there's a VirtualHost/Server config forcing all requests to index.php</span>
        <?php endif; ?>
    </p>
</div>

<?php if (stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false): ?>
<div class="box warning">
    <h2>🔧 SOLUTION FOR NGINX</h2>
    <p class="big">You MUST contact your hosting provider and ask them to add this Nginx configuration:</p>
    <div class="copy-box">
        <pre>server {
    server_name dorve.id www.dorve.id;
    root <?php echo $_SERVER['DOCUMENT_ROOT'] ?? '/path/to/dorve'; ?>;
    index index.php index.html;

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Direct access to files and directories
    location ~ ^/(admin|api|auth|member|pages|test-.*\.php|diagnostic\.php) {
        try_files $uri $uri/ =404;
    }

    # Static assets
    location ~ ^/(uploads|public|sounds)/ {
        try_files $uri =404;
    }

    # Homepage routing for everything else
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
}</pre>
    </div>
    <p><strong>Tell them:</strong> "Please add this Nginx config for dorve.id domain"</p>
</div>
<?php else: ?>
<div class="box warning">
    <h2>🔧 SOLUTION FOR APACHE</h2>
    <p class="big">You MUST contact your hosting provider and ask them to:</p>
    <ol style="margin: 15px 0 15px 30px; line-height: 2;">
        <li><strong>Enable mod_rewrite:</strong> <code>a2enmod rewrite</code></li>
        <li><strong>Enable AllowOverride:</strong> Change VirtualHost config</li>
        <li><strong>Restart Apache:</strong> <code>systemctl restart apache2</code></li>
    </ol>
    <p class="big" style="margin-top: 20px;">Tell them to add this to VirtualHost config:</p>
    <div class="copy-box">
        <pre>&lt;VirtualHost *:80&gt;
    ServerName dorve.id
    ServerAlias www.dorve.id
    DocumentRoot <?php echo $_SERVER['DOCUMENT_ROOT'] ?? '/path/to/dorve'; ?>

    &lt;Directory <?php echo $_SERVER['DOCUMENT_ROOT'] ?? '/path/to/dorve'; ?>&gt;
        <span class="highlight">AllowOverride All</span>
        Require all granted
        Options +FollowSymLinks
    &lt;/Directory&gt;
&lt;/VirtualHost&gt;</pre>
    </div>
    <p><strong>The key is:</strong> <span class="highlight">AllowOverride All</span></p>
</div>
<?php endif; ?>

<div class="box">
    <h2>📧 EMAIL TEMPLATE FOR HOSTING PROVIDER</h2>
    <div class="copy-box">
        <pre>Subject: Enable .htaccess for dorve.id (<?php echo stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false ? 'Nginx Config Needed' : 'AllowOverride Issue'; ?>)

Hi Support Team,

My website dorve.id is having routing issues. All admin pages are redirecting to homepage instead of loading properly.

<strong>Current Server Info:</strong>
- Domain: dorve.id
- Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>

- Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?>

- PHP Version: <?php echo phpversion(); ?>


<?php if (stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false): ?>
<strong>Problem:</strong>
Since this is Nginx, .htaccess files don't work. I need proper Nginx configuration for URL routing.

<strong>Required Action:</strong>
Please add the Nginx configuration I've attached (see above) to properly route:
- Direct file access for /admin/, /api/, /auth/, /member/, etc.
- Homepage routing for other URLs
<?php else: ?>
<strong>Problem:</strong>
.htaccess file is not being read by the server (AllowOverride is probably set to "None")

<strong>Required Action:</strong>
1. Enable mod_rewrite: a2enmod rewrite
2. Set AllowOverride to "All" in VirtualHost config (see attached config)
3. Restart Apache: systemctl restart apache2
<?php endif; ?>

Please help resolve this urgently as admin panel is currently inaccessible.

Thank you!
</pre>
    </div>
</div>

<div class="box success">
    <h2>✅ QUICK ACCESS LINKS - TEST THESE AFTER PROVIDER FIXES</h2>
    <p>After your hosting provider fixes the config, test these:</p>
    <table>
        <tr><td>Test File 1</td><td><a href="/test-direct.php" style="color: #0f0;">/test-direct.php</a></td></tr>
        <tr><td>Test File 2</td><td><a href="/check-server.php" style="color: #0f0;">/check-server.php</a></td></tr>
        <tr><td>Admin Test</td><td><a href="/admin/test-simple.php" style="color: #0f0;">/admin/test-simple.php</a></td></tr>
        <tr><td>Admin Login</td><td><a href="/admin/login.php" style="color: #0f0;">/admin/login.php</a></td></tr>
        <tr><td>Diagnostic</td><td><a href="/diagnostic.php" style="color: #0f0;">/diagnostic.php</a></td></tr>
    </table>
</div>

</body>
</html>
