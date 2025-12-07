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

$page_title = 'Change Password - Dorve';
include __DIR__ . '/../includes/header.php';
?>

<style>
    .member-content h1 { font-family: 'Playfair Display', serif; font-size: 36px; margin-bottom: 40px; }
    .form-card { background: var(--white); border: 1px solid rgba(0,0,0,0.08); border-radius: 8px; padding: 40px; max-width: 600px; }
    .form-group { margin-bottom: 24px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; }
    .form-group input { width: 100%; padding: 14px 16px; border: 1px solid rgba(0,0,0,0.15); border-radius: 4px; font-size: 15px; font-family: 'Inter', sans-serif; }
    .form-group input:focus { outline: none; border-color: var(--charcoal); }
    .btn { padding: 14px 32px; background: var(--charcoal); color: var(--white); border: none; border-radius: 4px; font-size: 15px; font-weight: 500; cursor: pointer; transition: all 0.3s; }
    .btn:hover { background: #000; }
    .alert { padding: 16px; border-radius: 4px; margin-bottom: 24px; font-size: 14px; }
    .alert-success { background: #D4EDDA; color: #155724; border: 1px solid #C3E6CB; }
    .alert-error { background: #F8D7DA; color: #721C24; border: 1px solid #F5C6CB; }
</style>

<div class="member-layout">
    <?php include __DIR__ . '/../includes/member-sidebar.php'; ?>

    <div class="member-content">
        <h1>Change Password</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <small style="color: var(--grey); font-size: 13px; margin-top: 4px; display: block;">Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn">Change Password</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
