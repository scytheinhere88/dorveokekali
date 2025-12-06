<?php
require_once __DIR__ . '/../config.php';

if (isLoggedIn()) {
    redirect('/member/dashboard.php');
}

$token = $_GET['token'] ?? '';
$error = '';
$success = false;
$user = null;

// Verify token
if ($token) {
    $stmt = $pdo->prepare("
        SELECT id, name, email, password_reset_expiry 
        FROM users 
        WHERE password_reset_token = ?
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Check if token expired
        if ($user['password_reset_expiry'] && strtotime($user['password_reset_expiry']) < time()) {
            $error = 'Link reset password sudah kadaluarsa (lebih dari 1 jam). Silakan minta link baru.';
            $user = null;
        }
    } else {
        $error = 'Link reset password tidak valid.';
    }
} else {
    $error = 'Token tidak ditemukan.';
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = 'Password tidak boleh kosong';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok';
    } else {
        // Update password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET password = ?,
                    password_reset_token = NULL,
                    password_reset_expiry = NULL,
                    password_reset_attempts = 0
                WHERE id = ?
            ");
            $stmt->execute([$password_hash, $user['id']]);
            
            $success = true;
        } catch (Exception $e) {
            error_log('Password reset error: ' . $e->getMessage());
            $error = 'Gagal mereset password. Silakan coba lagi.';
        }
    }
}

$page_title = 'Reset Password - Atur Ulang Kata Sandi | Dorve House';
include __DIR__ . '/../includes/header.php';
?>

<style>
    .auth-container {
        max-width: 480px;
        margin: 80px auto;
        padding: 0 24px;
    }

    .auth-card {
        background: var(--white);
        padding: 60px 50px;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 8px;
    }

    .auth-title {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        margin-bottom: 12px;
        text-align: center;
    }

    .auth-subtitle {
        text-align: center;
        color: var(--grey);
        font-size: 14px;
        margin-bottom: 40px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 8px;
        color: var(--charcoal);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-input {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid rgba(0,0,0,0.15);
        border-radius: 4px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--charcoal);
    }

    .submit-btn {
        width: 100%;
        padding: 16px;
        background: var(--charcoal);
        color: var(--white);
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s;
    }

    .submit-btn:hover {
        background: #000;
    }

    .alert {
        padding: 16px;
        border-radius: 6px;
        margin-bottom: 24px;
        font-size: 14px;
    }

    .alert-error {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FCA5A5;
    }

    .alert-success {
        background: #D1FAE5;
        color: #065F46;
        border: 1px solid #6EE7B7;
    }

    .back-link {
        text-align: center;
        margin-top: 24px;
        font-size: 14px;
    }

    .back-link a {
        color: var(--charcoal);
        text-decoration: underline;
    }

    .icon-wrapper {
        text-align: center;
        font-size: 48px;
        margin-bottom: 20px;
    }

    .password-requirements {
        background: var(--cream);
        padding: 16px;
        border-radius: 6px;
        margin-bottom: 24px;
        font-size: 13px;
    }

    .password-requirements ul {
        margin: 8px 0 0 20px;
        line-height: 1.8;
        color: var(--grey);
    }

    @media (max-width: 768px) {
        .auth-card {
            padding: 40px 30px;
        }

        .auth-title {
            font-size: 28px;
        }
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <?php if ($success): ?>
            <div class="icon-wrapper">✓</div>
            <h1 class="auth-title">Password Berhasil Direset!</h1>
            <div class="alert alert-success">
                Password Anda telah berhasil diubah.<br>
                Sekarang Anda bisa login dengan password baru.
            </div>
            <a href="/auth/login.php" class="submit-btn" style="display: block; text-decoration: none; text-align: center;">
                🔓 Login Sekarang
            </a>
        <?php elseif ($error && !$user): ?>
            <div class="icon-wrapper">❌</div>
            <h1 class="auth-title">Link Tidak Valid</h1>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <a href="/auth/forgot-password.php" class="submit-btn" style="display: block; text-decoration: none; text-align: center;">
                📧 Minta Link Baru
            </a>
        <?php else: ?>
            <div class="icon-wrapper">🔑</div>
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-subtitle">
                Buat password baru untuk akun Anda
            </p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="password-requirements">
                <strong>Password harus memenuhi:</strong>
                <ul>
                    <li>Minimal 6 karakter</li>
                    <li>Kombinasi huruf dan angka disarankan</li>
                    <li>Hindari password yang mudah ditebak</li>
                </ul>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Minimal 6 karakter"
                        required
                        minlength="6"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        class="form-input" 
                        placeholder="Ulangi password baru"
                        required
                        minlength="6"
                    >
                </div>

                <button type="submit" class="submit-btn">
                    🔒 Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="/auth/login.php">← Kembali ke Login</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
