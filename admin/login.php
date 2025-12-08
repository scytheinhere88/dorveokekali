<?php
require_once __DIR__ . '/../config.php';

if (isLoggedIn() && isAdmin()) {
    redirect('/admin/index.php');
}

$error = '';
$debug_info = [];

// Enable debug mode (set to false in production)
$debug_mode = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $debug_info[] = "📧 Email received: " . htmlspecialchars($email);
    $debug_info[] = "🔐 Password received: " . (strlen($password) > 0 ? "Yes (" . strlen($password) . " chars)" : "No");

    if ($email && $password) {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $any_user = $stmt->fetch();

            if ($any_user) {
                $debug_info[] = "✅ User found with email: " . htmlspecialchars($email);
                $debug_info[] = "👤 User role: " . htmlspecialchars($any_user['role']);
                $debug_info[] = "🔑 Password hash exists: " . (strlen($any_user['password']) > 0 ? "Yes" : "No");

                // Check password
                $password_match = password_verify($password, $any_user['password']);
                $debug_info[] = "🔐 Password verification: " . ($password_match ? "✅ MATCH" : "❌ NO MATCH");

                // Check role
                if ($any_user['role'] !== 'admin') {
                    $debug_info[] = "❌ User is not an admin (role: " . htmlspecialchars($any_user['role']) . ")";
                    $error = 'This user is not an administrator';
                } elseif (!$password_match) {
                    $debug_info[] = "❌ Password does not match";
                    $error = 'Invalid email or password';
                }
            } else {
                $debug_info[] = "❌ No user found with email: " . htmlspecialchars($email);
                $error = 'Invalid email or password';
            }

            // Now check with admin role filter
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $debug_info[] = "✅ Admin login successful!";
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = 'admin';
                $_SESSION['is_admin'] = 1;
                redirect('/admin/index.php');
            } else {
                if (!$error) {
                    $error = 'Invalid email or password';
                }
            }
        } catch (Exception $e) {
            $debug_info[] = "❌ Database error: " . $e->getMessage();
            $error = 'Database error occurred';
        }
    } else {
        $error = 'Please fill all fields';
        $debug_info[] = "❌ Missing email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Dorve</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            padding: 48px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .login-logo {
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 3px;
            margin-bottom: 12px;
            color: #1A1A1A;
        }

        .login-subtitle {
            text-align: center;
            color: #6F6F6F;
            margin-bottom: 40px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: #1A1A1A;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #E8E8E8;
            border-radius: 6px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #1A1A1A;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: #1A1A1A;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 12px;
        }

        .btn-login:hover {
            background: #000000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .error {
            background: #FEE;
            border: 1px solid #FCC;
            color: #C33;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .back-link {
            text-align: center;
            margin-top: 24px;
        }

        .back-link a {
            color: #6F6F6F;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #1A1A1A;
        }

        .demo-info {
            background: #F8F9FA;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #6F6F6F;
        }

        .demo-info strong {
            color: #1A1A1A;
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">DORVE</div>
        <div class="login-subtitle">Admin Panel Login</div>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($debug_mode && count($debug_info) === 0): ?>
            <div class="demo-info">
                <strong>🔧 Debug Tools Available:</strong>
                <div style="margin-top: 8px;">
                    <a href="debug-database.php" style="color: #0066CC; text-decoration: none;">📊 Check Database & Users</a>
                </div>
                <div style="margin-top: 4px;">
                    <a href="create-admin.php" style="color: #0066CC; text-decoration: none;">➕ Create Admin User</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($debug_mode && !empty($debug_info)): ?>
            <div class="demo-info">
                <strong>🔍 Debug Information:</strong>
                <?php foreach ($debug_info as $info): ?>
                    <div style="margin: 4px 0; font-size: 12px; font-family: 'Courier New', monospace;">
                        <?php echo $info; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">Login to Admin</button>
        </form>

        <div class="back-link">
            <a href="/index.php">← Back to Website</a>
        </div>
    </div>
</body>
</html>
