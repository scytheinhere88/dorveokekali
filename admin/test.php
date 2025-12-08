<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Test Page - Dorve</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #000;
            color: #0f0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #0a0a0a;
            border: 2px solid #0f0;
            padding: 30px;
            border-radius: 8px;
        }
        h1 {
            color: #0ff;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
            text-shadow: 0 0 10px #0ff;
        }
        h2 {
            color: #0f0;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0f0;
            font-size: 20px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #001100;
            border-left: 4px solid #0f0;
        }
        .info-line {
            margin: 8px 0;
            padding: 8px;
            background: #000;
            border: 1px solid #333;
            border-radius: 4px;
        }
        .label {
            color: #ff0;
            font-weight: bold;
        }
        .value {
            color: #0ff;
        }
        .status-ok {
            color: #0f0;
            font-weight: bold;
        }
        .status-error {
            color: #f00;
            font-weight: bold;
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
        pre {
            background: #000;
            padding: 15px;
            border: 1px solid #333;
            border-radius: 4px;
            overflow-x: auto;
            color: #0ff;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 ADMIN TEST PAGE</h1>

        <div class="section">
            <h2>📊 Session Status</h2>
            <div class="info-line">
                <span class="label">Session ID:</span>
                <span class="value"><?php echo session_id() ?: 'NO SESSION'; ?></span>
            </div>
            <div class="info-line">
                <span class="label">Session Status:</span>
                <span class="<?php echo session_status() === PHP_SESSION_ACTIVE ? 'status-ok' : 'status-error'; ?>">
                    <?php echo session_status() === PHP_SESSION_ACTIVE ? '✅ ACTIVE' : '❌ INACTIVE'; ?>
                </span>
            </div>
        </div>

        <div class="section">
            <h2>👤 Session Variables</h2>
            <?php if (!empty($_SESSION)): ?>
                <?php foreach ($_SESSION as $key => $value): ?>
                    <div class="info-line">
                        <span class="label">$_SESSION['<?php echo htmlspecialchars($key); ?>']:</span>
                        <span class="value"><?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="info-line">
                    <span class="status-error">❌ No session variables found!</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>🔐 Authentication Check</h2>
            <div class="info-line">
                <span class="label">isLoggedIn():</span>
                <span class="<?php echo isLoggedIn() ? 'status-ok' : 'status-error'; ?>">
                    <?php echo isLoggedIn() ? '✅ TRUE' : '❌ FALSE'; ?>
                </span>
            </div>
            <div class="info-line">
                <span class="label">isAdmin():</span>
                <span class="<?php echo isAdmin() ? 'status-ok' : 'status-error'; ?>">
                    <?php echo isAdmin() ? '✅ TRUE' : '❌ FALSE'; ?>
                </span>
            </div>
        </div>

        <div class="section">
            <h2>👨‍💼 Current User Data</h2>
            <?php
            $current_user = getCurrentUser();
            if ($current_user):
            ?>
                <div class="info-line">
                    <span class="label">ID:</span>
                    <span class="value"><?php echo htmlspecialchars($current_user['id']); ?></span>
                </div>
                <div class="info-line">
                    <span class="label">Name:</span>
                    <span class="value"><?php echo htmlspecialchars($current_user['name']); ?></span>
                </div>
                <div class="info-line">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($current_user['email']); ?></span>
                </div>
                <div class="info-line">
                    <span class="label">Role:</span>
                    <span class="value"><?php echo htmlspecialchars($current_user['role']); ?></span>
                </div>
            <?php else: ?>
                <div class="info-line">
                    <span class="status-error">❌ No user data found (not logged in)</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>🔍 Full Session Dump</h2>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>

        <div class="section">
            <h2>⚙️ PHP Configuration</h2>
            <div class="info-line">
                <span class="label">PHP Version:</span>
                <span class="value"><?php echo PHP_VERSION; ?></span>
            </div>
            <div class="info-line">
                <span class="label">Session Save Path:</span>
                <span class="value"><?php echo session_save_path(); ?></span>
            </div>
            <div class="info-line">
                <span class="label">Session Cookie Name:</span>
                <span class="value"><?php echo session_name(); ?></span>
            </div>
        </div>

        <div class="section">
            <h2>🔗 Quick Links</h2>
            <a href="login.php" class="btn">🔐 Login Page</a>
            <a href="debug-database.php" class="btn">📊 Database Debug</a>
            <?php if (isAdmin()): ?>
                <a href="index.php" class="btn">📊 Admin Dashboard</a>
            <?php endif; ?>
            <a href="../auth/logout.php" class="btn" style="border-color: #f00; color: #f00;">🚪 Logout</a>
        </div>
    </div>
</body>
</html>
