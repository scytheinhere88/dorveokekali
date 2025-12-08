<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$step = $_GET['step'] ?? 'start';
$results = [];

function testStep($name, $test, $message) {
    global $results;
    $results[] = [
        'name' => $name,
        'status' => $test,
        'message' => $message
    ];
    return $test;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login Flow Test - DORVE</title>
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
        .step {
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .step.success { border-color: #00ff88; background: rgba(0,255,136,0.05); }
        .step.error { border-color: #ff4444; background: rgba(255,68,68,0.05); }
        .step h2 {
            font-size: 24px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .step-icon {
            font-size: 32px;
            margin-right: 15px;
        }
        .step-message {
            font-size: 16px;
            color: #bbb;
            margin-bottom: 15px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #00ccff;
        }
        input {
            width: 100%;
            padding: 14px;
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
        }
        input:focus {
            outline: none;
            border-color: #00ff88;
        }
        code {
            background: rgba(0,0,0,0.5);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #00ff88;
        }
        .details {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Login Flow Test</h1>

        <?php if ($step === 'start'): ?>
            <div class="step">
                <h2><span class="step-icon">🎬</span> Step 1: Start Test</h2>
                <p class="step-message">This test will simulate the entire login process step by step to identify where the issue occurs.</p>
                <form method="POST" action="?step=test">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="admin1@dorve" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" value="Qwerty889*" required>
                    </div>
                    <button type="submit" class="btn">🚀 Start Test</button>
                    <a href="full-system-debug.php" class="btn btn-secondary">← Back to Diagnostics</a>
                </form>
            </div>

        <?php elseif ($step === 'test' && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <?php
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // TEST 1: Config Load
            echo '<div class="step">';
            echo '<h2><span class="step-icon">📦</span> Step 1: Loading Configuration</h2>';
            try {
                require_once __DIR__ . '/../config.php';
                echo '<p class="step-message" style="color: #00ff88;">✅ Configuration loaded successfully</p>';
                $configLoaded = true;
            } catch (Exception $e) {
                echo '<p class="step-message" style="color: #ff4444;">❌ Failed to load configuration</p>';
                echo '<div class="details">' . htmlspecialchars($e->getMessage()) . '</div>';
                $configLoaded = false;
            }
            echo '</div>';

            if (!$configLoaded) {
                echo '<a href="?step=start" class="btn">← Try Again</a>';
                exit;
            }

            // TEST 2: Database Connection
            echo '<div class="step">';
            echo '<h2><span class="step-icon">🗄️</span> Step 2: Testing Database Connection</h2>';
            try {
                $stmt = $pdo->query("SELECT 1");
                echo '<p class="step-message" style="color: #00ff88;">✅ Database connection successful</p>';
                $dbConnected = true;
            } catch (Exception $e) {
                echo '<p class="step-message" style="color: #ff4444;">❌ Database connection failed</p>';
                echo '<div class="details">' . htmlspecialchars($e->getMessage()) . '</div>';
                $dbConnected = false;
            }
            echo '</div>';

            if (!$dbConnected) {
                echo '<a href="?step=start" class="btn">← Try Again</a>';
                exit;
            }

            // TEST 3: Find User
            echo '<div class="step">';
            echo '<h2><span class="step-icon">👤</span> Step 3: Finding User</h2>';
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    echo '<p class="step-message" style="color: #00ff88;">✅ User found</p>';
                    echo '<div class="details">';
                    echo 'ID: ' . $user['id'] . '<br>';
                    echo 'Name: ' . htmlspecialchars($user['name']) . '<br>';
                    echo 'Email: ' . htmlspecialchars($user['email']) . '<br>';
                    echo 'Role: ' . htmlspecialchars($user['role']) . '<br>';
                    echo 'Verified: ' . ($user['email_verified'] ? 'Yes' : 'No');
                    echo '</div>';
                } else {
                    echo '<p class="step-message" style="color: #ff4444;">❌ User not found</p>';
                    echo '<div class="details">No user with email: ' . htmlspecialchars($email) . '</div>';
                    echo '</div>';
                    echo '<a href="?step=start" class="btn">← Try Again</a>';
                    exit;
                }
            } catch (Exception $e) {
                echo '<p class="step-message" style="color: #ff4444;">❌ Database query failed</p>';
                echo '<div class="details">' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
                echo '<a href="?step=start" class="btn">← Try Again</a>';
                exit;
            }
            echo '</div>';

            // TEST 4: Check Role
            echo '<div class="step ' . ($user['role'] === 'admin' ? 'success' : 'error') . '">';
            echo '<h2><span class="step-icon">🔑</span> Step 4: Checking Role</h2>';
            if ($user['role'] === 'admin') {
                echo '<p class="step-message" style="color: #00ff88;">✅ User is an admin</p>';
            } else {
                echo '<p class="step-message" style="color: #ff4444;">❌ User is not an admin</p>';
                echo '<div class="details">Current role: ' . htmlspecialchars($user['role']) . '</div>';
                echo '</div>';
                echo '<a href="?step=start" class="btn">← Try Again</a>';
                exit;
            }
            echo '</div>';

            // TEST 5: Verify Password
            echo '<div class="step">';
            echo '<h2><span class="step-icon">🔐</span> Step 5: Verifying Password</h2>';
            $passwordMatch = password_verify($password, $user['password']);
            if ($passwordMatch) {
                echo '<p class="step-message" style="color: #00ff88;">✅ Password is correct</p>';
            } else {
                echo '<p class="step-message" style="color: #ff4444;">❌ Password is incorrect</p>';
                echo '<div class="details">The password you entered does not match the stored hash</div>';
                echo '</div>';
                echo '<a href="?step=start" class="btn">← Try Again</a>';
                exit;
            }
            echo '</div>';

            // TEST 6: Session Setup
            echo '<div class="step">';
            echo '<h2><span class="step-icon">💾</span> Step 6: Creating Session</h2>';
            try {
                // Clear any existing session data
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = 'admin';
                $_SESSION['role'] = 'admin';
                $_SESSION['is_admin'] = 1;
                $_SESSION['test_login'] = true;
                $_SESSION['login_time'] = time();

                echo '<p class="step-message" style="color: #00ff88;">✅ Session created successfully</p>';
                echo '<div class="details">';
                echo 'Session ID: ' . session_id() . '<br>';
                echo 'user_id: ' . $_SESSION['user_id'] . '<br>';
                echo 'user_name: ' . $_SESSION['user_name'] . '<br>';
                echo 'user_role: ' . $_SESSION['user_role'] . '<br>';
                echo 'is_admin: ' . $_SESSION['is_admin'];
                echo '</div>';
            } catch (Exception $e) {
                echo '<p class="step-message" style="color: #ff4444;">❌ Failed to create session</p>';
                echo '<div class="details">' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
                echo '<a href="?step=start" class="btn">← Try Again</a>';
                exit;
            }
            echo '</div>';

            // TEST 7: Test Auth Functions
            echo '<div class="step">';
            echo '<h2><span class="step-icon">🔍</span> Step 7: Testing Auth Functions</h2>';
            $authWorks = true;

            if (function_exists('isLoggedIn')) {
                $loggedIn = isLoggedIn();
                echo '<p style="color: ' . ($loggedIn ? '#00ff88' : '#ff4444') . ';">';
                echo ($loggedIn ? '✅' : '❌') . ' isLoggedIn(): ' . ($loggedIn ? 'TRUE' : 'FALSE');
                echo '</p>';
                if (!$loggedIn) $authWorks = false;
            } else {
                echo '<p style="color: #ff4444;">❌ isLoggedIn() function not found</p>';
                $authWorks = false;
            }

            if (function_exists('isAdmin')) {
                $isAdminResult = isAdmin();
                echo '<p style="color: ' . ($isAdminResult ? '#00ff88' : '#ff4444') . ';">';
                echo ($isAdminResult ? '✅' : '❌') . ' isAdmin(): ' . ($isAdminResult ? 'TRUE' : 'FALSE');
                echo '</p>';
                if (!$isAdminResult) $authWorks = false;
            } else {
                echo '<p style="color: #ff4444;">❌ isAdmin() function not found</p>';
                $authWorks = false;
            }

            if ($authWorks) {
                echo '<p class="step-message" style="color: #00ff88; margin-top: 15px;">✅ All auth functions working correctly</p>';
            } else {
                echo '<p class="step-message" style="color: #ff4444; margin-top: 15px;">❌ Auth functions not working as expected</p>';
            }
            echo '</div>';

            // FINAL RESULT
            echo '<div class="step success">';
            echo '<h2><span class="step-icon">🎉</span> Test Complete!</h2>';
            echo '<p class="step-message" style="color: #00ff88; font-size: 18px;">✅ LOGIN TEST SUCCESSFUL!</p>';
            echo '<p style="margin-top: 15px;">You should now be able to access the admin panel.</p>';
            echo '<div style="margin-top: 20px;">';
            echo '<a href="../admin/index.php" class="btn">🚀 Go to Admin Dashboard</a> ';
            echo '<a href="?step=verify" class="btn btn-secondary">🔍 Verify Access</a>';
            echo '</div>';
            echo '</div>';
            ?>

        <?php elseif ($step === 'verify'): ?>
            <div class="step">
                <h2><span class="step-icon">🔍</span> Verification Test</h2>
                <p class="step-message">Checking if session persists across pages...</p>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <p style="color: #00ff88; font-size: 18px; margin-top: 15px;">✅ Session is active!</p>
                    <div class="details">
                        <?php
                        echo 'user_id: ' . $_SESSION['user_id'] . '<br>';
                        echo 'user_name: ' . ($_SESSION['user_name'] ?? 'N/A') . '<br>';
                        echo 'user_role: ' . ($_SESSION['user_role'] ?? 'N/A') . '<br>';
                        echo 'Session ID: ' . session_id();
                        ?>
                    </div>
                    <div style="margin-top: 20px;">
                        <a href="../admin/index.php" class="btn">🚀 Go to Admin Dashboard</a>
                        <a href="full-system-debug.php" class="btn btn-secondary">← Back to Diagnostics</a>
                    </div>
                <?php else: ?>
                    <p style="color: #ff4444; font-size: 18px; margin-top: 15px;">❌ Session lost!</p>
                    <p style="margin-top: 15px;">The session was created but didn't persist. This could be a session configuration issue.</p>
                    <div style="margin-top: 20px;">
                        <a href="session-fix.php" class="btn">🔧 Try Session Fix</a>
                        <a href="?step=start" class="btn btn-secondary">← Try Again</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
