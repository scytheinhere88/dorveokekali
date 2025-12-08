<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>LOGIN DEBUG TOOL</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; }
        .section { background: #2a2a2a; padding: 20px; margin: 20px 0; border-radius: 10px; border: 2px solid #444; }
        .success { color: #00ff00; }
        .error { color: #ff4444; }
        .warning { color: #ffaa00; }
        .info { color: #00aaff; }
        h1 { color: #00ff00; text-align: center; }
        h2 { color: #00aaff; border-bottom: 2px solid #00aaff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; background: #333; }
        th { background: #444; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #444; }
        code { background: #1a1a1a; padding: 2px 5px; border-radius: 3px; color: #00ff00; }
        .test-btn { background: #00aa00; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 5px; }
        .test-btn:hover { background: #00cc00; }
        input[type="text"], input[type="password"] { padding: 10px; width: 300px; background: #1a1a1a; border: 2px solid #444; color: #fff; border-radius: 5px; }
        .login-form { background: #3a3a3a; padding: 20px; border-radius: 10px; margin: 20px 0; }
    </style>
</head>
<body>
<h1>🔍 DORVE LOGIN DEBUG TOOL</h1>";

// ====================
// 1. PHP CONFIGURATION
// ====================
echo "<div class='section'>";
echo "<h2>1️⃣ PHP CONFIGURATION</h2>";
echo "<table>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

$phpVersion = phpversion();
echo "<tr><td>PHP Version</td><td><code>$phpVersion</code></td><td class='success'>✅</td></tr>";

$sessionStatus = session_status();
$statusText = ['DISABLED', 'NONE', 'ACTIVE'][$sessionStatus];
echo "<tr><td>Session Status</td><td><code>$statusText</code></td><td class='" . ($sessionStatus !== 0 ? 'success' : 'error') . "'>" . ($sessionStatus !== 0 ? '✅' : '❌') . "</td></tr>";

$sessionSavePath = session_save_path();
echo "<tr><td>Session Save Path</td><td><code>" . ($sessionSavePath ?: 'default') . "</code></td><td class='info'>ℹ️</td></tr>";

$sessionName = session_name();
echo "<tr><td>Session Name</td><td><code>$sessionName</code></td><td class='info'>ℹ️</td></tr>";

$cookieParams = session_get_cookie_params();
echo "<tr><td>Cookie Lifetime</td><td><code>{$cookieParams['lifetime']}s</code></td><td class='info'>ℹ️</td></tr>";
echo "<tr><td>Cookie Path</td><td><code>{$cookieParams['path']}</code></td><td class='info'>ℹ️</td></tr>";
echo "<tr><td>Cookie Domain</td><td><code>" . ($cookieParams['domain'] ?: 'none') . "</code></td><td class='info'>ℹ️</td></tr>";
echo "<tr><td>Cookie Secure</td><td><code>" . ($cookieParams['secure'] ? 'YES' : 'NO') . "</code></td><td class='warning'>⚠️</td></tr>";

echo "</table>";
echo "</div>";

// ====================
// 2. DATABASE CONNECTION
// ====================
echo "<div class='section'>";
echo "<h2>2️⃣ DATABASE CONNECTION</h2>";

try {
    require_once __DIR__ . '/../config.php';
    echo "<p class='success'>✅ Config loaded successfully</p>";

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p class='success'>✅ Database connected! Total users: <strong>{$result['count']}</strong></p>";

} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// ====================
// 3. ADMIN USERS CHECK
// ====================
echo "<div class='section'>";
echo "<h2>3️⃣ ADMIN USERS IN DATABASE</h2>";

try {
    $stmt = $pdo->query("SELECT id, name, email, role, email_verified, created_at FROM users WHERE role = 'admin' ORDER BY id DESC");
    $admins = $stmt->fetchAll();

    if (empty($admins)) {
        echo "<p class='error'>❌ NO ADMIN USERS FOUND!</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Created</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>{$admin['id']}</td>";
            echo "<td>" . htmlspecialchars($admin['name']) . "</td>";
            echo "<td><code>" . htmlspecialchars($admin['email']) . "</code></td>";
            echo "<td>{$admin['role']}</td>";
            echo "<td>" . ($admin['email_verified'] ? '<span class="success">✅</span>' : '<span class="error">❌</span>') . "</td>";
            echo "<td>{$admin['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// ====================
// 4. PASSWORD VERIFICATION TEST
// ====================
echo "<div class='section'>";
echo "<h2>4️⃣ PASSWORD VERIFICATION TEST</h2>";

$testCredentials = [
    ['email' => 'admin1@dorve', 'password' => 'Qwerty889*'],
    ['email' => 'admin2@dorve', 'password' => 'MajuTerus88*']
];

foreach ($testCredentials as $cred) {
    echo "<h3>Testing: {$cred['email']}</h3>";

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$cred['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "<p class='error'>❌ User NOT FOUND in database</p>";
        } else {
            echo "<p class='success'>✅ User found! ID: {$user['id']}</p>";
            echo "<p class='info'>📧 Email: {$user['email']}</p>";
            echo "<p class='info'>👤 Name: {$user['name']}</p>";
            echo "<p class='info'>🔑 Role: {$user['role']}</p>";
            echo "<p class='info'>✉️ Verified: " . ($user['email_verified'] ? 'YES' : 'NO') . "</p>";

            if (password_verify($cred['password'], $user['password'])) {
                echo "<p class='success'>✅✅✅ PASSWORD MATCH! Login should work!</p>";
            } else {
                echo "<p class='error'>❌ PASSWORD DOES NOT MATCH!</p>";
                echo "<p class='warning'>⚠️ Stored hash: <code>" . substr($user['password'], 0, 50) . "...</code></p>";
            }
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "<hr>";
}
echo "</div>";

// ====================
// 5. SESSION TEST
// ====================
echo "<div class='section'>";
echo "<h2>5️⃣ SESSION TEST</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<p class='info'>Current Session ID: <code>" . session_id() . "</code></p>";

if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>✅ Session exists! User ID: {$_SESSION['user_id']}</p>";
    echo "<p class='info'>Session data:</p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p class='warning'>⚠️ No active session</p>";
}

echo "</div>";

// ====================
// 6. LIVE LOGIN TEST
// ====================
echo "<div class='section'>";
echo "<h2>6️⃣ LIVE LOGIN TEST</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    echo "<div style='background: #1a1a1a; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 class='info'>Testing Login: $email</h3>";

    try {
        // Step 1: Find user
        echo "<p class='info'>Step 1: Finding user...</p>";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "<p class='error'>❌ Step 1 FAILED: User not found</p>";
        } else {
            echo "<p class='success'>✅ Step 1 PASSED: User found (ID: {$user['id']})</p>";

            // Step 2: Check role
            echo "<p class='info'>Step 2: Checking role...</p>";
            if ($user['role'] !== 'admin') {
                echo "<p class='error'>❌ Step 2 FAILED: User is not admin (role: {$user['role']})</p>";
            } else {
                echo "<p class='success'>✅ Step 2 PASSED: User is admin</p>";

                // Step 3: Verify password
                echo "<p class='info'>Step 3: Verifying password...</p>";
                if (!password_verify($password, $user['password'])) {
                    echo "<p class='error'>❌ Step 3 FAILED: Password incorrect</p>";
                } else {
                    echo "<p class='success'>✅ Step 3 PASSED: Password correct</p>";

                    // Step 4: Create session
                    echo "<p class='info'>Step 4: Creating session...</p>";

                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }

                    // Regenerate session
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['is_admin'] = true;
                    $_SESSION['logged_in'] = true;

                    echo "<p class='success'>✅ Step 4 PASSED: Session created</p>";
                    echo "<p class='success'>✅✅✅ LOGIN SUCCESSFUL!</p>";
                    echo "<p class='info'>Session ID: <code>" . session_id() . "</code></p>";
                    echo "<p class='info'>Session data:</p>";
                    echo "<pre>" . print_r($_SESSION, true) . "</pre>";

                    // Step 5: Test redirect
                    echo "<p class='info'>Step 5: Testing redirect...</p>";
                    echo "<p class='success'>✅ You should now be logged in!</p>";
                    echo "<a href='/admin/index.php' class='test-btn'>GO TO ADMIN DASHBOARD</a>";
                }
            }
        }

    } catch (Exception $e) {
        echo "<p class='error'>❌ EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    echo "</div>";
}

// Login form
echo "<div class='login-form'>";
echo "<form method='POST'>";
echo "<h3>🔐 Test Login Now</h3>";
echo "<p><input type='text' name='email' placeholder='Email' value='admin1@dorve' required></p>";
echo "<p><input type='password' name='password' placeholder='Password' value='Qwerty889*' required></p>";
echo "<p><button type='submit' name='test_login' class='test-btn'>🚀 TEST LOGIN</button></p>";
echo "</form>";
echo "</div>";

echo "</div>";

// ====================
// 7. FILE CHECKS
// ====================
echo "<div class='section'>";
echo "<h2>7️⃣ FILE CHECKS</h2>";

$files = [
    'Config' => __DIR__ . '/../config.php',
    'Admin Index' => __DIR__ . '/index.php',
    'Admin Login' => __DIR__ . '/login.php',
    'Simple Login' => __DIR__ . '/simple-login.php',
    'Auth Check' => __DIR__ . '/includes/auth-check.php'
];

echo "<table>";
echo "<tr><th>File</th><th>Path</th><th>Status</th></tr>";
foreach ($files as $name => $path) {
    $exists = file_exists($path);
    $readable = $exists && is_readable($path);
    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td><code>" . htmlspecialchars($path) . "</code></td>";
    echo "<td>";
    if ($exists && $readable) {
        echo "<span class='success'>✅ OK</span>";
    } elseif ($exists) {
        echo "<span class='warning'>⚠️ Not readable</span>";
    } else {
        echo "<span class='error'>❌ Not found</span>";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// ====================
// 8. RECOMMENDATIONS
// ====================
echo "<div class='section'>";
echo "<h2>8️⃣ RECOMMENDATIONS</h2>";

echo "<div style='background: #3a3a3a; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3 class='info'>Quick Actions:</h3>";
echo "<ol>";
echo "<li><strong>Create Fresh Admin:</strong> <a href='/admin/create-new-admin.php' class='test-btn'>CREATE NOW</a></li>";
echo "<li><strong>Test Login Here:</strong> Use form above ⬆️</li>";
echo "<li><strong>Try Simple Login:</strong> <a href='/admin/simple-login.php' class='test-btn'>SIMPLE LOGIN</a></li>";
echo "<li><strong>Try Normal Login:</strong> <a href='/admin/login.php' class='test-btn'>NORMAL LOGIN</a></li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #3a3a3a; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
echo "<h3 class='warning'>If Still Not Working:</h3>";
echo "<ul>";
echo "<li>Clear browser cookies and cache</li>";
echo "<li>Try incognito/private browsing</li>";
echo "<li>Check if session files are writable</li>";
echo "<li>Check server error logs</li>";
echo "<li>Make sure .htaccess is not blocking sessions</li>";
echo "</ul>";
echo "</div>";

echo "</div>";

echo "</body></html>";
?>
