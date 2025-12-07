<?php
require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user = getCurrentUser();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($current && $new && $confirm) {
        if (password_verify($current, $user['password'])) {
            if ($new === $confirm) {
                if (strlen($new) >= 6) {
                    $hashed = password_hash($new, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed, $_SESSION['user_id']]);
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'New password must be at least 6 characters';
                }
            } else {
                $error = 'New passwords do not match';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    } else {
        $error = 'All fields are required';
    }
}

$page_title = 'Change Password - Dorve.id';
include __DIR__ . '/../includes/header.php';
?>

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    .prof-wrapper {
        display: flex;
        max-width: 1400px;
        margin: 100px auto 60px;
        padding: 0 40px;
        gap: 48px;
        align-items: flex-start;
    }

    .prof-sidebar {
        width: 280px;
        min-width: 280px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 120px;
        overflow: hidden;
    }

    .prof-sidebar-header {
        padding: 24px;
        background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        color: white;
        text-align: center;
    }

    .prof-sidebar-header h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .prof-sidebar-header p {
        font-size: 13px;
        opacity: 0.9;
    }

    .prof-nav {
        list-style: none;
        padding: 12px;
        margin: 0;
    }

    .prof-nav li {
        margin-bottom: 4px;
    }

    .prof-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        color: #4B5563;
        text-decoration: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .prof-nav a:hover {
        background: #F3F4F6;
        color: #1F2937;
    }

    .prof-nav a.active {
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: white;
        font-weight: 600;
    }

    .prof-nav .logout {
        border-top: 1px solid #E5E7EB;
        margin-top: 12px;
        padding-top: 16px;
    }

    .prof-nav .logout a {
        color: #EF4444;
    }

    .prof-content {
        flex: 1;
        min-width: 0;
        background: white;
        border-radius: 20px;
        padding: 48px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    }

    .prof-content h1 {
        font-family: 'Playfair Display', serif;
        font-size: 40px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #1F2937;
    }

    .page-description {
        color: #6B7280;
        margin-bottom: 36px;
        font-size: 16px;
    }

    .form-card {
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 16px;
        padding: 40px;
        max-width: 600px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
        color: #374151;
    }

    .form-group input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #E5E7EB;
        border-radius: 12px;
        font-size: 15px;
        font-family: 'Inter', sans-serif;
        transition: all 0.3s;
        background: white;
    }

    .form-group input:focus {
        outline: none;
        border-color: #667EEA;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .btn {
        padding: 16px 32px;
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-weight: 500;
    }

    .alert-success {
        background: #D1FAE5;
        color: #065F46;
        border: 1px solid #10B981;
    }

    .alert-error {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #EF4444;
    }

    @media (max-width: 968px) {
        .prof-wrapper {
            flex-direction: column;
            padding: 0 20px;
            margin: 80px auto 40px;
            gap: 24px;
        }

        .prof-sidebar {
            width: 100%;
            position: relative;
            top: 0;
        }

        .prof-nav {
            display: flex;
            overflow-x: auto;
            gap: 8px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .prof-nav::-webkit-scrollbar {
            display: none;
        }

        .prof-nav li {
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .prof-nav a {
            white-space: nowrap;
            padding: 10px 16px;
            font-size: 13px;
        }

        .prof-nav .logout {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        .prof-content {
            padding: 32px 24px;
        }

        .prof-content h1 {
            font-size: 28px;
        }

        .form-card {
            padding: 24px;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<div class="prof-wrapper">
    <aside class="prof-sidebar">
        <div class="prof-sidebar-header">
            <h3>Welcome back!</h3>
            <p><?php echo htmlspecialchars($user['name'] ?? $user['email']); ?></p>
        </div>

        <ul class="prof-nav">
            <li><a href="/member/dashboard.php">🏠 Dashboard</a></li>
            <li><a href="/member/orders.php">📦 My Orders</a></li>
            <li><a href="/member/wallet.php">💰 My Wallet</a></li>
            <li><a href="/member/address-book.php">📍 Address Book</a></li>
            <li><a href="/member/referral.php">👥 My Referrals</a></li>
            <li><a href="/member/vouchers/index.php">🎟️ My Vouchers</a></li>
            <li><a href="/member/reviews.php">⭐ My Reviews</a></li>
            <li><a href="/member/profile.php">👤 Edit Profile</a></li>
            <li><a href="/member/password.php" class="active">🔐 Change Password</a></li>
            <li class="logout"><a href="/auth/logout.php">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="prof-content">
        <h1>🔐 Change Password</h1>
        <p class="page-description">Update your account password for security</p>

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
                    <small style="color: #6B7280; font-size: 13px; margin-top: 4px; display: block;">Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn">🔒 Change Password</button>
            </form>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
