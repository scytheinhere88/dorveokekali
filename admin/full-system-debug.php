<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$all_checks = [];
$has_errors = false;

function addCheck($category, $name, $status, $message, $details = '') {
    global $all_checks, $has_errors;
    $all_checks[] = [
        'category' => $category,
        'name' => $name,
        'status' => $status, // 'success', 'error', 'warning', 'info'
        'message' => $message,
        'details' => $details
    ];
    if ($status === 'error') {
        $has_errors = true;
    }
}

// ==================== 1. PHP ENVIRONMENT ====================
$category = 'PHP Environment';

addCheck($category, 'PHP Version', 'info', 'Version: ' . phpversion());
addCheck($category, 'Session Status', session_status() === PHP_SESSION_ACTIVE ? 'success' : 'error',
    'Session: ' . ['DISABLED', 'NONE', 'ACTIVE'][session_status()]);

$sessionPath = session_save_path() ?: ini_get('session.save_path') ?: '/tmp';
$sessionWritable = is_writable($sessionPath);
addCheck($category, 'Session Path', $sessionWritable ? 'success' : 'error',
    'Path: ' . $sessionPath,
    $sessionWritable ? 'Writable' : 'NOT WRITABLE!');

addCheck($category, 'Error Reporting', 'info', 'Level: ' . error_reporting());
addCheck($category, 'Display Errors', 'info', 'Display: ' . (ini_get('display_errors') ? 'ON' : 'OFF'));

// ==================== 2. FILE STRUCTURE ====================
$category = 'File Structure';

$critical_files = [
    'Config' => __DIR__ . '/../config.php',
    'Admin Index' => __DIR__ . '/index.php',
    'Admin Login' => __DIR__ . '/login.php',
    'Auth Check' => __DIR__ . '/includes/auth-check.php',
    'Admin Header' => __DIR__ . '/includes/admin-header.php',
    'Admin Footer' => __DIR__ . '/includes/admin-footer.php',
];

foreach ($critical_files as $name => $path) {
    if (file_exists($path)) {
        $readable = is_readable($path);
        addCheck($category, $name, $readable ? 'success' : 'error',
            $readable ? 'File exists and readable' : 'File exists but NOT readable',
            $path);
    } else {
        addCheck($category, $name, 'error', 'File NOT FOUND!', $path);
    }
}

// ==================== 3. DATABASE CONNECTION ====================
$category = 'Database';

try {
    require_once __DIR__ . '/../config.php';
    addCheck($category, 'Config Load', 'success', 'Config loaded successfully');

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetchColumn();
    addCheck($category, 'Database Connection', 'success', "Connected! Total users: $userCount");

    // Check tables
    $tables = ['users', 'products', 'orders', 'categories', 'wallet_transactions'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            addCheck($category, "Table: $table", 'success', "$count rows", "Table exists and accessible");
        } catch (Exception $e) {
            addCheck($category, "Table: $table", 'error', 'Table error', $e->getMessage());
        }
    }

} catch (Exception $e) {
    addCheck($category, 'Database Connection', 'error', 'Connection failed!', $e->getMessage());
}

// ==================== 4. ADMIN USERS ====================
$category = 'Admin Users';

try {
    $stmt = $pdo->query("SELECT id, name, email, role, email_verified, created_at FROM users WHERE role = 'admin' ORDER BY id");
    $admins = $stmt->fetchAll();

    if (empty($admins)) {
        addCheck($category, 'Admin Count', 'error', 'NO ADMIN USERS FOUND!', 'You need to create an admin user');
    } else {
        addCheck($category, 'Admin Count', 'success', count($admins) . ' admin(s) found');

        foreach ($admins as $idx => $admin) {
            $details = "ID: {$admin['id']}, Name: {$admin['name']}, Email: {$admin['email']}, Verified: " . ($admin['email_verified'] ? 'Yes' : 'No');
            addCheck($category, "Admin #" . ($idx + 1), 'info', $admin['email'], $details);
        }
    }
} catch (Exception $e) {
    addCheck($category, 'Admin Users', 'error', 'Query failed', $e->getMessage());
}

// ==================== 5. SESSION STATE ====================
$category = 'Session State';

if (isset($_SESSION['user_id'])) {
    addCheck($category, 'Session Active', 'success', 'User logged in!', 'User ID: ' . $_SESSION['user_id']);
    addCheck($category, 'Session Data', 'info', 'User: ' . ($_SESSION['user_name'] ?? 'N/A'));
    addCheck($category, 'User Role', 'info', 'Role: ' . ($_SESSION['user_role'] ?? $_SESSION['role'] ?? 'N/A'));
    addCheck($category, 'Is Admin', isset($_SESSION['is_admin']) ? 'success' : 'warning',
        'is_admin flag: ' . (isset($_SESSION['is_admin']) ? 'Set' : 'Not set'));
} else {
    addCheck($category, 'Session Active', 'warning', 'No active session', 'Not logged in');
}

addCheck($category, 'Session ID', 'info', 'ID: ' . session_id());

// ==================== 6. AUTH FUNCTIONS ====================
$category = 'Auth Functions';

$authFunctions = ['isLoggedIn', 'isAdmin', 'redirect'];
foreach ($authFunctions as $func) {
    if (function_exists($func)) {
        addCheck($category, "Function: $func", 'success', 'Function exists');
    } else {
        addCheck($category, "Function: $func", 'error', 'Function NOT found!');
    }
}

// Test isLoggedIn
if (function_exists('isLoggedIn')) {
    $loggedIn = isLoggedIn();
    addCheck($category, 'isLoggedIn() Test', $loggedIn ? 'success' : 'warning',
        'Result: ' . ($loggedIn ? 'TRUE (logged in)' : 'FALSE (not logged in)'));
}

// Test isAdmin
if (function_exists('isAdmin')) {
    $isAdmin = isAdmin();
    addCheck($category, 'isAdmin() Test', $isAdmin ? 'success' : 'warning',
        'Result: ' . ($isAdmin ? 'TRUE (is admin)' : 'FALSE (not admin)'));
}

// ==================== 7. PASSWORD TEST ====================
$category = 'Password Test';

$test_credentials = [
    ['email' => 'admin1@dorve', 'password' => 'Qwerty889*'],
    ['email' => 'admin2@dorve', 'password' => 'MajuTerus88*']
];

foreach ($test_credentials as $cred) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$cred['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            addCheck($category, $cred['email'], 'warning', 'User not found in database');
        } else {
            $passwordMatch = password_verify($cred['password'], $user['password']);
            addCheck($category, $cred['email'], $passwordMatch ? 'success' : 'error',
                $passwordMatch ? 'Password CORRECT!' : 'Password INCORRECT',
                "User ID: {$user['id']}, Role: {$user['role']}");
        }
    } catch (Exception $e) {
        addCheck($category, $cred['email'], 'error', 'Query error', $e->getMessage());
    }
}

// ==================== 8. .HTACCESS CHECK ====================
$category = 'Server Config';

$htaccessPath = __DIR__ . '/../.htaccess';
if (file_exists($htaccessPath)) {
    $htaccess = file_get_contents($htaccessPath);
    $hasRewrite = (stripos($htaccess, 'RewriteEngine') !== false && stripos($htaccess, 'RewriteEngine Off') === false);
    addCheck($category, '.htaccess', 'info', 'File exists',
        $hasRewrite ? 'RewriteEngine appears to be ON' : 'RewriteEngine appears to be OFF');
} else {
    addCheck($category, '.htaccess', 'warning', 'File not found');
}

// ==================== HTML OUTPUT ====================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>DORVE Full System Diagnostic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
            color: #fff;
            padding: 20px;
            line-height: 1.6;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 {
            text-align: center;
            font-size: 48px;
            margin: 30px 0;
            background: linear-gradient(135deg, #00ff88 0%, #00ccff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            letter-spacing: 2px;
        }
        .summary {
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .summary h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }
        .summary.error { border-color: #ff4444; }
        .summary.success { border-color: #00ff88; }
        .category {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .category h2 {
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            color: #00ccff;
        }
        .check {
            background: rgba(255,255,255,0.02);
            border-left: 4px solid #666;
            padding: 15px 20px;
            margin-bottom: 12px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .check:hover {
            background: rgba(255,255,255,0.05);
            transform: translateX(5px);
        }
        .check.success { border-left-color: #00ff88; }
        .check.error { border-left-color: #ff4444; background: rgba(255,68,68,0.1); }
        .check.warning { border-left-color: #ffaa00; }
        .check.info { border-left-color: #00ccff; }
        .check-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .check-icon {
            font-size: 24px;
            margin-right: 12px;
            min-width: 30px;
        }
        .check-name {
            font-weight: 600;
            font-size: 16px;
            flex: 1;
        }
        .check-message {
            font-size: 14px;
            color: #bbb;
            margin-left: 42px;
        }
        .check-details {
            font-size: 13px;
            color: #888;
            margin-left: 42px;
            margin-top: 5px;
            font-family: 'Courier New', monospace;
            background: rgba(0,0,0,0.3);
            padding: 8px 12px;
            border-radius: 4px;
        }
        .actions {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            border-radius: 12px;
            padding: 30px;
            margin-top: 30px;
            text-align: center;
        }
        .actions h2 {
            font-size: 28px;
            margin-bottom: 25px;
            color: #00ff88;
        }
        .btn {
            display: inline-block;
            padding: 16px 32px;
            margin: 10px;
            background: linear-gradient(135deg, #00ff88 0%, #00ccff 100%);
            color: #000;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,255,136,0.3);
        }
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            box-shadow: 0 10px 30px rgba(255,255,255,0.1);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat {
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-value {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .stat-label {
            font-size: 14px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DORVE FULL SYSTEM DIAGNOSTIC</h1>

        <div class="summary <?php echo $has_errors ? 'error' : 'success'; ?>">
            <h2><?php echo $has_errors ? '⚠️ ISSUES DETECTED' : '✅ SYSTEM HEALTHY'; ?></h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-value" style="color: #00ff88;">
                        <?php echo count(array_filter($all_checks, fn($c) => $c['status'] === 'success')); ?>
                    </div>
                    <div class="stat-label">Passed</div>
                </div>
                <div class="stat">
                    <div class="stat-value" style="color: #ff4444;">
                        <?php echo count(array_filter($all_checks, fn($c) => $c['status'] === 'error')); ?>
                    </div>
                    <div class="stat-label">Errors</div>
                </div>
                <div class="stat">
                    <div class="stat-value" style="color: #ffaa00;">
                        <?php echo count(array_filter($all_checks, fn($c) => $c['status'] === 'warning')); ?>
                    </div>
                    <div class="stat-label">Warnings</div>
                </div>
                <div class="stat">
                    <div class="stat-value" style="color: #00ccff;">
                        <?php echo count(array_filter($all_checks, fn($c) => $c['status'] === 'info')); ?>
                    </div>
                    <div class="stat-label">Info</div>
                </div>
            </div>
        </div>

        <?php
        $categories = array_unique(array_column($all_checks, 'category'));
        foreach ($categories as $cat) {
            $checks = array_filter($all_checks, fn($c) => $c['category'] === $cat);
            echo "<div class='category'>";
            echo "<h2>$cat</h2>";
            foreach ($checks as $check) {
                $icons = [
                    'success' => '✅',
                    'error' => '❌',
                    'warning' => '⚠️',
                    'info' => 'ℹ️'
                ];
                echo "<div class='check {$check['status']}'>";
                echo "<div class='check-header'>";
                echo "<div class='check-icon'>{$icons[$check['status']]}</div>";
                echo "<div class='check-name'>{$check['name']}</div>";
                echo "</div>";
                echo "<div class='check-message'>{$check['message']}</div>";
                if ($check['details']) {
                    echo "<div class='check-details'>{$check['details']}</div>";
                }
                echo "</div>";
            }
            echo "</div>";
        }
        ?>

        <div class="actions">
            <h2>🚀 QUICK ACTIONS</h2>
            <a href="test-login-flow.php" class="btn">🧪 Test Login Flow</a>
            <a href="create-new-admin.php" class="btn">➕ Create New Admin</a>
            <a href="session-fix.php" class="btn">🔧 Fix Session</a>
            <a href="login.php" class="btn btn-secondary">🔐 Go to Login</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php" class="btn btn-secondary">📊 Go to Dashboard</a>
            <?php endif; ?>
        </div>

        <div class="category">
            <h2>💡 Recommendations</h2>
            <?php if ($has_errors): ?>
                <div class="check error">
                    <div class="check-header">
                        <div class="check-icon">🔴</div>
                        <div class="check-name">Critical Issues Found</div>
                    </div>
                    <div class="check-message">Please fix the errors shown above before attempting to log in.</div>
                </div>
            <?php endif; ?>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="check warning">
                    <div class="check-header">
                        <div class="check-icon">🔑</div>
                        <div class="check-name">Not Logged In</div>
                    </div>
                    <div class="check-message">Use the "Test Login Flow" button above to test the login process step by step.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
