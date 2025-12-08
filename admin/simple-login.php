<?php
// SIMPLE LOGIN TEST - No output until after session is set
require_once __DIR__ . '/../config.php';

// If already logged in, redirect
if (isLoggedIn() && isAdmin()) {
    header("Location: /admin/index.php");
    exit();
}

$message = '';
$login_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Clear any existing session data
                $_SESSION = [];

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = 'admin';
                $_SESSION['role'] = 'admin';
                $_SESSION['is_admin'] = 1;
                $_SESSION['login_time'] = time();

                // Force session save
                session_write_close();

                // Start new session
                session_start();

                $login_success = true;
                $message = "Login successful! Session ID: " . session_id();

                // Redirect after 2 seconds
                header("Refresh: 2; url=/admin/index.php");
            } else {
                $message = "Invalid email or password";
            }
        } catch (Exception $e) {
            $message = "Database error: " . $e->getMessage();
        }
    } else {
        $message = "Please fill all fields";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login Test - Dorve Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .message {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        .message.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .message.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info-box {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #666;
        }
        .info-box strong {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }
        .links {
            margin-top: 20px;
            text-align: center;
        }
        .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .session-info {
            background: #e7f3ff;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 12px;
            font-family: 'Courier New', monospace;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SIMPLE LOGIN TEST</h1>
        <div class="subtitle">Testing Session & Authentication</div>

        <?php if ($message): ?>
            <div class="message <?php echo $login_success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($login_success): ?>
            <div class="info-box">
                <strong>Session Details:</strong>
                <div>Session ID: <?php echo session_id(); ?></div>
                <div>User ID: <?php echo $_SESSION['user_id'] ?? 'Not set'; ?></div>
                <div>User Name: <?php echo $_SESSION['user_name'] ?? 'Not set'; ?></div>
                <div>Role: <?php echo $_SESSION['role'] ?? 'Not set'; ?></div>
                <div>isAdmin(): <?php echo isAdmin() ? 'TRUE' : 'FALSE'; ?></div>
            </div>
            <div style="text-align: center; padding: 20px;">
                <p style="color: #28a745; font-size: 18px; font-weight: 600;">Redirecting to admin panel...</p>
                <p style="color: #666; margin-top: 10px;">
                    <a href="/admin/index.php" style="color: #667eea; text-decoration: none; font-weight: 600;">Click here if not redirected</a>
                </p>
            </div>
        <?php else: ?>
            <div class="info-box">
                <strong>Test Admin Accounts:</strong>
                <div>Email: adm1@dorve.id</div>
                <div>Email: adm2@dorve.id</div>
                <div>Or any admin account you created</div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">Login & Test Session</button>
            </form>

            <div class="session-info">
                Current Session ID: <?php echo session_id() ?: 'No session'; ?><br>
                Session Status: <?php echo session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE'; ?><br>
                Session Name: <?php echo session_name(); ?>
            </div>

            <div class="links">
                <a href="login.php">Normal Login</a>
                <a href="test.php">Test Page</a>
                <a href="debug-database.php">Debug DB</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
