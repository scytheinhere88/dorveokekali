<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Debug - Dorve Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #00ff00;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #000;
            border: 2px solid #00ff00;
            padding: 30px;
            border-radius: 8px;
        }
        h1 {
            color: #00ff00;
            margin-bottom: 30px;
            text-align: center;
            font-size: 32px;
            text-shadow: 0 0 10px #00ff00;
        }
        h2 {
            color: #0ff;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0ff;
        }
        .section {
            margin-bottom: 40px;
            padding: 20px;
            background: #0a0a0a;
            border-left: 4px solid #00ff00;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: #0a0a0a;
        }
        th {
            background: #003300;
            color: #00ff00;
            padding: 12px;
            text-align: left;
            border: 1px solid #00ff00;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border: 1px solid #333;
            color: #0f0;
        }
        tr:hover {
            background: #002200;
        }
        .status {
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .status.success {
            background: #003300;
            color: #00ff00;
            border: 1px solid #00ff00;
        }
        .status.error {
            background: #330000;
            color: #ff0000;
            border: 1px solid #ff0000;
        }
        .status.warning {
            background: #333300;
            color: #ffff00;
            border: 1px solid #ffff00;
        }
        .code {
            background: #0a0a0a;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0;
            border: 1px solid #333;
        }
        .info {
            color: #0ff;
            margin: 10px 0;
        }
        .warning {
            color: #ff0;
            margin: 10px 0;
        }
        .error {
            color: #f00;
            margin: 10px 0;
        }
        .success {
            color: #0f0;
            margin: 10px 0;
        }
        .btn-create-admin {
            background: #003300;
            color: #00ff00;
            border: 2px solid #00ff00;
            padding: 15px 30px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
            display: inline-block;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-create-admin:hover {
            background: #00ff00;
            color: #000;
            box-shadow: 0 0 20px #00ff00;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DATABASE DEBUG CONSOLE</h1>

        <div class="section">
            <h2>📊 Database Connection</h2>
            <?php
            try {
                $pdo->query("SELECT 1");
                echo '<p class="success">✅ Database connection: <span class="status success">CONNECTED</span></p>';
                echo '<p class="info">📍 Host: ' . DB_HOST . '</p>';
                echo '<p class="info">📍 Database: ' . DB_NAME . '</p>';
            } catch (Exception $e) {
                echo '<p class="error">❌ Database connection: <span class="status error">FAILED</span></p>';
                echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <div class="section">
            <h2>👥 Users Table Structure</h2>
            <?php
            try {
                $stmt = $pdo->query("DESCRIBE users");
                $columns = $stmt->fetchAll();

                if ($columns) {
                    echo '<table>';
                    echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
                    foreach ($columns as $col) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($col['Field']) . '</td>';
                        echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                        echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                        echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                        echo '<td>' . htmlspecialchars($col['Default'] ?? 'NULL') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="error">❌ Users table not found!</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <div class="section">
            <h2>👨‍💼 Admin Users</h2>
            <?php
            try {
                $stmt = $pdo->query("SELECT id, name, email, role, email_verified, created_at FROM users WHERE role = 'admin'");
                $admins = $stmt->fetchAll();

                if ($admins) {
                    echo '<p class="success">✅ Found ' . count($admins) . ' admin user(s)</p>';
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Created</th></tr>';
                    foreach ($admins as $admin) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($admin['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($admin['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($admin['email']) . '</td>';
                        echo '<td><span class="status success">' . htmlspecialchars($admin['role']) . '</span></td>';
                        echo '<td>' . ($admin['email_verified'] ? '✅' : '❌') . '</td>';
                        echo '<td>' . htmlspecialchars($admin['created_at']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="error">❌ No admin users found in database!</p>';
                    echo '<p class="warning">⚠️ You need to create an admin user to login.</p>';
                    echo '<a href="create-admin.php" class="btn-create-admin">🔧 CREATE ADMIN USER</a>';
                }
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <div class="section">
            <h2>📈 All Users (Total Count)</h2>
            <?php
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
                $result = $stmt->fetch();
                echo '<p class="info">📊 Total users in database: <strong>' . $result['total'] . '</strong></p>';

                // Get role breakdown
                $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
                $roles = $stmt->fetchAll();

                if ($roles) {
                    echo '<table>';
                    echo '<tr><th>Role</th><th>Count</th></tr>';
                    foreach ($roles as $role) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($role['role']) . '</td>';
                        echo '<td>' . htmlspecialchars($role['count']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <div class="section">
            <h2>🔐 Password Test</h2>
            <p class="info">Testing password hashing and verification...</p>
            <?php
            $test_password = 'admin123';
            $hashed = password_hash($test_password, PASSWORD_DEFAULT);
            $verify = password_verify($test_password, $hashed);

            echo '<div class="code">';
            echo '<p>Test Password: <strong>' . htmlspecialchars($test_password) . '</strong></p>';
            echo '<p>Hashed: <strong>' . htmlspecialchars(substr($hashed, 0, 50)) . '...</strong></p>';
            echo '<p>Verification: ' . ($verify ? '<span class="status success">✅ PASSED</span>' : '<span class="status error">❌ FAILED</span>') . '</p>';
            echo '</div>';
            ?>
        </div>

        <div class="section">
            <h2>⚙️ PHP Configuration</h2>
            <?php
            echo '<p class="info">PHP Version: <strong>' . PHP_VERSION . '</strong></p>';
            echo '<p class="info">Session ID: <strong>' . session_id() . '</strong></p>';
            echo '<p class="info">Session Status: <strong>' . (session_status() === PHP_SESSION_ACTIVE ? '✅ ACTIVE' : '❌ INACTIVE') . '</strong></p>';
            ?>
        </div>

        <div class="section">
            <h2>🔧 Quick Actions</h2>
            <a href="create-admin.php" class="btn-create-admin">➕ Create New Admin User</a>
            <a href="login.php" class="btn-create-admin" style="background: #000033; border-color: #0ff; color: #0ff;">🔐 Go to Login Page</a>
        </div>
    </div>
</body>
</html>
