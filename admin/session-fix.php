<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_GET['action'] ?? 'check';
$fixed = false;
$issues = [];
$fixes = [];

// Check session configuration
function checkSessionConfig() {
    global $issues;

    $checks = [
        'Session Status' => session_status(),
        'Session Save Path' => session_save_path(),
        'Session Name' => session_name(),
        'Session Cookie Lifetime' => ini_get('session.cookie_lifetime'),
        'Session Cookie Path' => ini_get('session.cookie_path'),
        'Session Use Cookies' => ini_get('session.use_cookies'),
        'Session Use Only Cookies' => ini_get('session.use_only_cookies'),
    ];

    $savePath = session_save_path() ?: ini_get('session.save_path') ?: '/tmp';
    if (!is_writable($savePath)) {
        $issues[] = "Session save path is not writable: $savePath";
    }

    return $checks;
}

// Fix session configuration
function fixSessionConfig() {
    global $fixes;

    // Set proper session configuration
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0); // Until browser closes
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');

    $fixes[] = "Set session.use_cookies = 1";
    $fixes[] = "Set session.use_only_cookies = 1";
    $fixes[] = "Set session.cookie_lifetime = 0";
    $fixes[] = "Set session.cookie_path = /";
    $fixes[] = "Set session.cookie_httponly = 1";
    $fixes[] = "Set session.cookie_samesite = Lax";

    return true;
}

// Start session with proper config
if ($action === 'fix') {
    fixSessionConfig();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = checkSessionConfig();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Session Fix - DORVE</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f0f0f;
            color: #fff;
            padding: 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        h1 {
            text-align: center;
            font-size: 42px;
            margin: 30px 0;
            color: #00ff88;
        }
        .section {
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #00ccff;
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #00ff88 0%, #00ccff 100%);
            color: #000;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            border: none;
            cursor: pointer;
            margin: 5px;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,255,136,0.3);
        }
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        th {
            background: rgba(255,255,255,0.05);
            font-weight: 600;
            color: #00ccff;
        }
        .success { color: #00ff88; }
        .error { color: #ff4444; }
        .warning { color: #ffaa00; }
        .info-box {
            background: rgba(0,204,255,0.1);
            border: 1px solid #00ccff;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .error-box {
            background: rgba(255,68,68,0.1);
            border: 1px solid #ff4444;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .success-box {
            background: rgba(0,255,136,0.1);
            border: 1px solid #00ff88;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        ul {
            list-style: none;
            padding-left: 0;
        }
        li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        li:before {
            content: "→";
            position: absolute;
            left: 0;
            color: #00ff88;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Session Configuration Fix</h1>

        <div class="section">
            <h2>📊 Current Session Configuration</h2>
            <table>
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
                <?php foreach ($config as $key => $value): ?>
                    <tr>
                        <td><?php echo $key; ?></td>
                        <td><code style="color: #00ff88;"><?php echo is_bool($value) ? ($value ? 'true' : 'false') : htmlspecialchars($value); ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="success-box">
                    <p class="success">✅ <strong>Session Active</strong></p>
                    <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
                    <p>Session ID: <?php echo session_id(); ?></p>
                </div>
            <?php else: ?>
                <div class="error-box">
                    <p class="error">❌ <strong>No Active Session</strong></p>
                    <p>No user is currently logged in.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($issues)): ?>
            <div class="section">
                <h2>⚠️ Issues Detected</h2>
                <div class="error-box">
                    <ul>
                        <?php foreach ($issues as $issue): ?>
                            <li><?php echo htmlspecialchars($issue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($action === 'fix' && !empty($fixes)): ?>
            <div class="section">
                <h2>✅ Fixes Applied</h2>
                <div class="success-box">
                    <ul>
                        <?php foreach ($fixes as $fix): ?>
                            <li><?php echo htmlspecialchars($fix); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="section">
            <h2>🚀 Actions</h2>

            <?php if ($action !== 'fix'): ?>
                <a href="?action=fix" class="btn">🔧 Apply Session Fixes</a>
            <?php else: ?>
                <div class="info-box">
                    <p><strong>✅ Session configuration has been optimized!</strong></p>
                    <p style="margin-top: 10px;">Try logging in again.</p>
                </div>
            <?php endif; ?>

            <a href="test-login-flow.php" class="btn btn-secondary">🧪 Test Login Flow</a>
            <a href="login.php" class="btn btn-secondary">🔐 Go to Login</a>
            <a href="full-system-debug.php" class="btn btn-secondary">← Back to Diagnostics</a>

            <?php if ($action === 'fix' && !isset($_SESSION['user_id'])): ?>
                <div style="margin-top: 20px;">
                    <p style="margin-bottom: 10px;">Session configuration fixed. Now test the login:</p>
                    <a href="test-login-flow.php" class="btn">🧪 Run Login Test</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>💡 Tips</h2>
            <ul>
                <li>Make sure cookies are enabled in your browser</li>
                <li>Try clearing your browser cache and cookies</li>
                <li>Use incognito/private mode to test fresh</li>
                <li>Check that session save path is writable on the server</li>
                <li>Verify .htaccess is not blocking session cookies</li>
            </ul>
        </div>
    </div>
</body>
</html>
