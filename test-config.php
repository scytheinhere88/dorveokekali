<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Config Test - DORVE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #1a1a1a;
            color: #fff;
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }
        h1 { color: #0f0; text-align: center; font-size: 42px; }
        .box {
            background: #2a2a2a;
            border: 2px solid #0f0;
            padding: 25px;
            margin: 20px 0;
            border-radius: 10px;
        }
        .success { border-color: #0f0; }
        .error { border-color: #f00; }
        pre {
            background: #000;
            color: #0f0;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        a {
            display: inline-block;
            padding: 12px 25px;
            background: #0f0;
            color: #000;
            text-decoration: none;
            font-weight: bold;
            margin: 5px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>🔍 CONFIG TEST</h1>

    <div class="box success">
        <h2>✅ THIS FILE LOADS!</h2>
        <p>File: <code><?php echo __FILE__; ?></code></p>
    </div>

    <div class="box">
        <h2>📁 File Structure Check</h2>
        <pre><?php
$files = [
    'config.php' => __DIR__ . '/config.php',
    'index.php' => __DIR__ . '/index.php',
    'admin/login.php' => __DIR__ . '/admin/login.php',
    'admin/index.php' => __DIR__ . '/admin/index.php',
    'admin/test-simple.php' => __DIR__ . '/admin/test-simple.php',
];

foreach ($files as $name => $path) {
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    echo sprintf("%-25s %s %s\n",
        $name,
        $exists ? '✅ EXISTS' : '❌ MISSING',
        $readable ? '(readable)' : ''
    );
}
        ?></pre>
    </div>

    <div class="box <?php
    try {
        require_once __DIR__ . '/config.php';
        echo 'success';
    } catch (Exception $e) {
        echo 'error';
    }
    ?>">
        <h2>⚙️ Config.php Load Test</h2>
        <?php
        try {
            if (!defined('DB_HOST')) {
                require_once __DIR__ . '/config.php';
            }
            echo '<p>✅ <strong>CONFIG LOADED SUCCESSFULLY!</strong></p>';
            echo '<pre>';
            echo 'Database Host: ' . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
            echo 'Database Name: ' . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";
            echo '</pre>';

            // Test DB connection
            if (isset($pdo)) {
                echo '<p>✅ <strong>DATABASE OBJECT EXISTS!</strong></p>';
                try {
                    $stmt = $pdo->query("SELECT 1");
                    echo '<p>✅ <strong>DATABASE CONNECTION WORKS!</strong></p>';

                    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                    $adminCount = $stmt->fetchColumn();
                    echo '<p>👥 <strong>Admin Users Found: ' . $adminCount . '</strong></p>';
                } catch (Exception $e) {
                    echo '<p>❌ <strong>Database query failed:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
            } else {
                echo '<p>⚠️ $pdo variable not set</p>';
            }
        } catch (Exception $e) {
            echo '<p>❌ <strong>CONFIG LOAD FAILED:</strong></p>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        }
        ?>
    </div>

    <div class="box">
        <h2>🔑 Session Test</h2>
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        echo '<p>Session Status: ' . ['DISABLED', 'NONE', 'ACTIVE'][session_status()] . '</p>';
        echo '<p>Session ID: ' . session_id() . '</p>';
        if (isset($_SESSION['user_id'])) {
            echo '<p>✅ <strong>USER LOGGED IN:</strong> ID=' . $_SESSION['user_id'] . '</p>';
        } else {
            echo '<p>⚠️ No active session</p>';
        }
        ?>
    </div>

    <div class="box">
        <h2>🚀 NEXT STEPS</h2>
        <p><strong>Try accessing these URLs directly:</strong></p>
        <div style="margin: 20px 0;">
            <a href="/test-direct.php" target="_blank">Test Root Access</a>
            <a href="/admin/test-simple.php" target="_blank">Test Admin Access</a>
            <a href="/admin/login.php" target="_blank">Admin Login</a>
            <a href="/diagnostic.php" target="_blank">Full Diagnostic</a>
        </div>
    </div>

    <div class="box">
        <h2>📋 Server Info</h2>
        <pre><?php
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Script Filename: " . __FILE__ . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
        ?></pre>
    </div>

</body>
</html>
