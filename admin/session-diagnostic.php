<?php
// Session Diagnostic Tool
// This must be at the very top - no output before session_start()
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Diagnostic - Dorve Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #0f0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #000;
            border: 3px solid #0f0;
            padding: 30px;
            border-radius: 8px;
        }
        h1 {
            color: #0ff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            text-shadow: 0 0 10px #0ff;
        }
        h2 {
            color: #ff0;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff0;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #0a0a0a;
            border-left: 4px solid #0f0;
        }
        .info-line {
            margin: 8px 0;
            padding: 10px;
            background: #001100;
            border: 1px solid #003300;
            border-radius: 4px;
        }
        .label { color: #ff0; font-weight: bold; }
        .value { color: #0ff; }
        .status-ok { color: #0f0; font-weight: bold; }
        .status-error { color: #f00; font-weight: bold; }
        .status-warning { color: #ff0; font-weight: bold; }
        pre {
            background: #000;
            padding: 15px;
            border: 1px solid #333;
            border-radius: 4px;
            overflow-x: auto;
            color: #0ff;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #003300;
            color: #0f0;
            border: 2px solid #0f0;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 10px 0 0;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #0f0;
            color: #000;
            box-shadow: 0 0 20px #0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 SESSION DIAGNOSTIC TOOL</h1>

        <div class="section">
            <h2>📊 Session Status (Before Config Load)</h2>
            <div class="info-line">
                <span class="label">Session Status:</span>
                <span class="<?php echo session_status() === PHP_SESSION_ACTIVE ? 'status-ok' : 'status-error'; ?>">
                    <?php
                    $status = session_status();
                    switch($status) {
                        case PHP_SESSION_DISABLED:
                            echo '❌ DISABLED (0)';
                            break;
                        case PHP_SESSION_NONE:
                            echo '⚠️ NONE - Not started yet (1)';
                            break;
                        case PHP_SESSION_ACTIVE:
                            echo '✅ ACTIVE (2)';
                            break;
                        default:
                            echo '❓ UNKNOWN (' . $status . ')';
                    }
                    ?>
                </span>
            </div>
            <div class="info-line">
                <span class="label">Session ID (before):</span>
                <span class="value"><?php echo session_id() ?: 'EMPTY'; ?></span>
            </div>
        </div>

        <?php
        // Now load config which starts session
        require_once __DIR__ . '/../config.php';
        ?>

        <div class="section">
            <h2>📊 Session Status (After Config Load)</h2>
            <div class="info-line">
                <span class="label">Session Status:</span>
                <span class="<?php echo session_status() === PHP_SESSION_ACTIVE ? 'status-ok' : 'status-error'; ?>">
                    <?php
                    $status = session_status();
                    switch($status) {
                        case PHP_SESSION_DISABLED:
                            echo '❌ DISABLED (0)';
                            break;
                        case PHP_SESSION_NONE:
                            echo '⚠️ NONE - Not started (1)';
                            break;
                        case PHP_SESSION_ACTIVE:
                            echo '✅ ACTIVE (2)';
                            break;
                        default:
                            echo '❓ UNKNOWN (' . $status . ')';
                    }
                    ?>
                </span>
            </div>
            <div class="info-line">
                <span class="label">Session ID (after):</span>
                <span class="value"><?php echo session_id() ?: 'EMPTY'; ?></span>
            </div>
            <div class="info-line">
                <span class="label">Session Name:</span>
                <span class="value"><?php echo session_name(); ?></span>
            </div>
        </div>

        <div class="section">
            <h2>🍪 Cookie Configuration</h2>
            <?php
            $cookieParams = session_get_cookie_params();
            ?>
            <div class="info-line">
                <span class="label">Lifetime:</span>
                <span class="value"><?php echo $cookieParams['lifetime']; ?> seconds (0 = until browser closes)</span>
            </div>
            <div class="info-line">
                <span class="label">Path:</span>
                <span class="value"><?php echo htmlspecialchars($cookieParams['path']); ?></span>
            </div>
            <div class="info-line">
                <span class="label">Domain:</span>
                <span class="value"><?php echo htmlspecialchars($cookieParams['domain'] ?: '(empty - current domain)'); ?></span>
            </div>
            <div class="info-line">
                <span class="label">Secure:</span>
                <span class="value"><?php echo $cookieParams['secure'] ? 'Yes (HTTPS only)' : 'No (HTTP + HTTPS)'; ?></span>
            </div>
            <div class="info-line">
                <span class="label">HttpOnly:</span>
                <span class="value"><?php echo $cookieParams['httponly'] ? 'Yes' : 'No'; ?></span>
            </div>
            <div class="info-line">
                <span class="label">SameSite:</span>
                <span class="value"><?php echo $cookieParams['samesite'] ?? 'Not set'; ?></span>
            </div>
        </div>

        <div class="section">
            <h2>📝 Session Data</h2>
            <?php if (!empty($_SESSION)): ?>
                <?php foreach ($_SESSION as $key => $value): ?>
                    <div class="info-line">
                        <span class="label">$_SESSION['<?php echo htmlspecialchars($key); ?>']:</span>
                        <span class="value"><?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="info-line">
                    <span class="status-warning">⚠️ Session is empty (no data stored)</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>🌐 Server Environment</h2>
            <div class="info-line">
                <span class="label">PHP Version:</span>
                <span class="value"><?php echo PHP_VERSION; ?></span>
            </div>
            <div class="info-line">
                <span class="label">Session Save Path:</span>
                <span class="value"><?php echo session_save_path(); ?></span>
            </div>
            <div class="info-line">
                <span class="label">Session Module:</span>
                <span class="value"><?php echo ini_get('session.save_handler'); ?></span>
            </div>
            <div class="info-line">
                <span class="label">Session Use Cookies:</span>
                <span class="value"><?php echo ini_get('session.use_cookies') ? 'Yes' : 'No'; ?></span>
            </div>
            <div class="info-line">
                <span class="label">Session Use Only Cookies:</span>
                <span class="value"><?php echo ini_get('session.use_only_cookies') ? 'Yes' : 'No'; ?></span>
            </div>
            <div class="info-line">
                <span class="label">Session Cookie Lifetime:</span>
                <span class="value"><?php echo ini_get('session.cookie_lifetime'); ?> seconds</span>
            </div>
            <div class="info-line">
                <span class="label">HTTPS:</span>
                <span class="value"><?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Yes' : 'No'; ?></span>
            </div>
        </div>

        <div class="section">
            <h2>🍪 Cookies Received</h2>
            <?php if (!empty($_COOKIE)): ?>
                <?php foreach ($_COOKIE as $key => $value): ?>
                    <div class="info-line">
                        <span class="label">$_COOKIE['<?php echo htmlspecialchars($key); ?>']:</span>
                        <span class="value"><?php echo htmlspecialchars(substr($value, 0, 50)); ?><?php echo strlen($value) > 50 ? '...' : ''; ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="info-line">
                    <span class="status-error">❌ No cookies received from browser!</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>🧪 Session Write Test</h2>
            <?php
            // Try to write test data
            $_SESSION['test_value'] = 'test_' . time();
            $_SESSION['test_timestamp'] = date('Y-m-d H:i:s');
            ?>
            <div class="info-line">
                <span class="label">Test Value Written:</span>
                <span class="value"><?php echo $_SESSION['test_value']; ?></span>
            </div>
            <div class="info-line">
                <span class="label">Test Timestamp:</span>
                <span class="value"><?php echo $_SESSION['test_timestamp']; ?></span>
            </div>
            <div style="margin-top: 15px;">
                <a href="session-diagnostic.php" class="btn" style="background: #330033; border-color: #f0f;">🔄 Reload Page (Test Persistence)</a>
            </div>
            <div style="margin-top: 10px; color: #ff0; font-size: 12px;">
                ⚠️ If test values change on reload, session is NOT persisting!
            </div>
        </div>

        <div class="section">
            <h2>🔍 Full Session Dump</h2>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>

        <div class="section">
            <h2>🔗 Quick Actions</h2>
            <a href="simple-login.php" class="btn">🔐 Simple Login Test</a>
            <a href="test.php" class="btn">📊 Full Test Page</a>
            <a href="login.php" class="btn">🔑 Normal Login</a>
            <a href="debug-database.php" class="btn">💾 Database Debug</a>
        </div>
    </div>
</body>
</html>
