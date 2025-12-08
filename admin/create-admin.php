<?php
require_once __DIR__ . '/../config.php';

$success = '';
$error = '';
$created_user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (!$name || !$email || !$password || !$confirm_password) {
        $error = 'All fields are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already exists!';
            } else {
                // Create admin user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, role, email_verified, created_at, updated_at)
                    VALUES (?, ?, ?, 'admin', 1, NOW(), NOW())
                ");
                $stmt->execute([$name, $email, $hashed_password]);

                $created_user = [
                    'id' => $pdo->lastInsertId(),
                    'name' => $name,
                    'email' => $email,
                    'password_plain' => $password
                ];

                $success = 'Admin user created successfully!';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - Dorve</title>
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

        .container {
            background: white;
            border-radius: 12px;
            padding: 48px;
            width: 100%;
            max-width: 540px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .logo {
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 3px;
            margin-bottom: 12px;
            color: #1A1A1A;
        }

        .subtitle {
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

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 12px;
        }

        .btn-primary {
            background: #1A1A1A;
            color: white;
        }

        .btn-primary:hover {
            background: #000000;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: #F5F5F5;
            color: #1A1A1A;
        }

        .btn-secondary:hover {
            background: #E5E5E5;
        }

        .alert {
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .alert-error {
            background: #FEE;
            border: 1px solid #FCC;
            color: #C33;
        }

        .alert-success {
            background: #EFE;
            border: 1px solid #CFC;
            color: #3C3;
        }

        .success-box {
            background: #F8F9FA;
            padding: 24px;
            border-radius: 8px;
            margin: 24px 0;
            border-left: 4px solid #28a745;
        }

        .success-box h3 {
            color: #28a745;
            margin-bottom: 16px;
            font-size: 18px;
        }

        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background: white;
            border-radius: 4px;
            margin: 8px 0;
            border: 1px solid #E8E8E8;
        }

        .credential-item strong {
            color: #1A1A1A;
        }

        .credential-item code {
            background: #F5F5F5;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #0066CC;
        }

        .warning-box {
            background: #FFF3CD;
            border: 1px solid #FFE69C;
            padding: 16px;
            border-radius: 6px;
            margin: 16px 0;
            color: #856404;
            font-size: 14px;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-group .btn {
            flex: 1;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">DORVE</div>
        <div class="subtitle">Create Admin User</div>

        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success && $created_user): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>

            <div class="success-box">
                <h3>🎉 Admin User Created Successfully!</h3>
                <div class="credential-item">
                    <strong>User ID:</strong>
                    <code><?php echo htmlspecialchars($created_user['id']); ?></code>
                </div>
                <div class="credential-item">
                    <strong>Name:</strong>
                    <code><?php echo htmlspecialchars($created_user['name']); ?></code>
                </div>
                <div class="credential-item">
                    <strong>Email:</strong>
                    <code><?php echo htmlspecialchars($created_user['email']); ?></code>
                </div>
                <div class="credential-item">
                    <strong>Password:</strong>
                    <code><?php echo htmlspecialchars($created_user['password_plain']); ?></code>
                </div>
            </div>

            <div class="warning-box">
                ⚠️ <strong>IMPORTANT:</strong> Save these credentials now! The password is shown only once.
            </div>

            <div class="btn-group">
                <a href="login.php" class="btn btn-primary" style="text-align: center; text-decoration: none; display: block;">Login Now</a>
                <a href="create-admin.php" class="btn btn-secondary" style="text-align: center; text-decoration: none; display: block;">Create Another</a>
            </div>

        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password (min 6 characters)</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary">Create Admin User</button>
                <a href="debug-database.php" class="btn btn-secondary" style="text-align: center; text-decoration: none; display: block;">Back to Debug</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
