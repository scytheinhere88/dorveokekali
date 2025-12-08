<?php
// FORCE LOGIN - BYPASS ALL CHECKS
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>FORCE LOGIN</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; }
        .box { background: #2a2a2a; padding: 20px; margin: 20px 0; border-radius: 10px; border: 2px solid #00ff00; }
        .success { color: #00ff00; }
        .error { color: #ff4444; }
        h1 { color: #00ff00; text-align: center; }
        .btn { background: #00aa00; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 18px; text-decoration: none; display: inline-block; margin: 10px; }
        .btn:hover { background: #00cc00; }
    </style>
</head>
<body>
<h1>🔥 FORCE LOGIN - EMERGENCY ACCESS 🔥</h1>";

// Get first admin
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'admin' ORDER BY id DESC LIMIT 1");
$admin = $stmt->fetch();

if (!$admin) {
    echo "<div class='box'>";
    echo "<p class='error'>❌ NO ADMIN FOUND! Creating one now...</p>";

    // Create emergency admin
    $hashedPassword = password_hash('Admin123!@#', PASSWORD_DEFAULT);
    $referralCode = 'EMRG-' . strtoupper(substr(md5(uniqid()), 0, 8));

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified, referral_code, created_at)
                          VALUES (?, ?, ?, 'admin', 1, ?, NOW())");
    $stmt->execute(['Emergency Admin', 'emergency@dorve', $hashedPassword, $referralCode]);

    $admin = [
        'id' => $pdo->lastInsertId(),
        'name' => 'Emergency Admin',
        'email' => 'emergency@dorve',
        'role' => 'admin'
    ];

    echo "<p class='success'>✅ Emergency admin created!</p>";
    echo "<p class='success'>Email: emergency@dorve</p>";
    echo "<p class='success'>Password: Admin123!@#</p>";
    echo "</div>";
}

// FORCE CREATE SESSION
if (session_status() === PHP_SESSION_NONE) {
    // Configure session
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', 1);

    session_start();
}

// Regenerate session ID
session_regenerate_id(true);

// SET ALL SESSION VARIABLES
$_SESSION['user_id'] = $admin['id'];
$_SESSION['user_email'] = $admin['email'];
$_SESSION['user_name'] = $admin['name'];
$_SESSION['user_role'] = 'admin';
$_SESSION['is_admin'] = true;
$_SESSION['logged_in'] = true;
$_SESSION['admin_logged_in'] = true;
$_SESSION['force_login'] = true;
$_SESSION['login_time'] = time();

// Save session
session_write_close();

echo "<div class='box'>";
echo "<h2 class='success'>✅ SESSION CREATED!</h2>";
echo "<p><strong>User ID:</strong> {$admin['id']}</p>";
echo "<p><strong>Email:</strong> {$admin['email']}</p>";
echo "<p><strong>Name:</strong> {$admin['name']}</p>";
echo "<p><strong>Role:</strong> admin</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<br>";
echo "<p style='font-size: 20px; color: #00ff00;'>🎉 YOU ARE NOW LOGGED IN! 🎉</p>";
echo "<br>";
echo "<a href='/admin/index.php' class='btn'>🚀 GO TO ADMIN DASHBOARD</a>";
echo "<a href='/admin/orders/index.php' class='btn'>📦 GO TO ORDERS</a>";
echo "</div>";

echo "<div class='box'>";
echo "<h3>Session Data:</h3>";
echo "<pre>";
session_start();
print_r($_SESSION);
session_write_close();
echo "</pre>";
echo "</div>";

echo "</body></html>";
?>
