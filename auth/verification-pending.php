<?php
require_once __DIR__ . '/../config.php';

$email = $_SESSION['pending_verification_email'] ?? '';
$name = $_SESSION['pending_verification_name'] ?? '';

if (empty($email)) {
    redirect('/auth/login.php');
}

$error = '';
$success = '';
$can_resend = true;
$wait_time = 0;

// Get user info
$stmt = $pdo->prepare("SELECT id, verification_attempts, last_verification_sent FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Check if user needs to wait 15 minutes after 3 attempts
    if ($user['verification_attempts'] >= 3 && $user['last_verification_sent']) {
        $last_sent = strtotime($user['last_verification_sent']);
        $now = time();
        $diff = $now - $last_sent;
        
        if ($diff < 900) { // 15 minutes = 900 seconds
            $can_resend = false;
            $wait_time = ceil((900 - $diff) / 60);
            $error = "Anda telah mencapai batas maksimal pengiriman. Silakan tunggu $wait_time menit atau cek email Anda terlebih dahulu.";
        } else {
            // Reset attempts after 15 minutes
            $stmt = $pdo->prepare("UPDATE users SET verification_attempts = 0 WHERE id = ?");
            $stmt->execute([$user['id']]);
            $can_resend = true;
        }
    } elseif ($user['verification_attempts'] < 3 && $user['last_verification_sent']) {
        // Check 60 second cooldown between resends
        $last_sent = strtotime($user['last_verification_sent']);
        $now = time();
        $diff = $now - $last_sent;
        
        if ($diff < 60) {
            $can_resend = false;
            $wait_time = 60 - $diff;
            $error = "Mohon tunggu $wait_time detik sebelum mengirim ulang.";
        }
    }
}

// Handle resend request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend']) && $can_resend && $user) {
    try {
        require_once __DIR__ . '/../includes/email-helper.php';
        
        // Generate new token
        $verification_token = bin2hex(random_bytes(32));
        $verification_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Update user
        $stmt = $pdo->prepare("UPDATE users SET 
            email_verification_token = ?, 
            email_verification_expiry = ?, 
            last_verification_sent = NOW(),
            verification_attempts = verification_attempts + 1 
            WHERE id = ?");
        $stmt->execute([$verification_token, $verification_expiry, $user['id']]);
        
        // Send email
        $verification_link = SITE_URL . "auth/verify-email.php?token=" . $verification_token;
        $emailSent = sendVerificationEmail($email, $name, $verification_link);
        
        if ($emailSent) {
            $success = 'Email verifikasi berhasil dikirim ulang! Cek inbox atau folder spam Anda.';
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT verification_attempts FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user = $stmt->fetch();
            
            $can_resend = false; // Prevent immediate resend
            $wait_time = 60;
        } else {
            $error = 'Gagal mengirim email. Silakan coba lagi.';
        }
    } catch (Exception $e) {
        error_log('Resend verification error: ' . $e->getMessage());
        $error = 'Terjadi kesalahan. Silakan coba lagi.';
    }
}

$page_title = 'Verifikasi Email - Dorve House';
include __DIR__ . '/../includes/header.php';
?>

<style>
.verification-container {
    max-width: 600px;
    margin: 80px auto;
    padding: 40px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
}
.verification-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
}
.verification-title {
    font-size: 28px;
    font-weight: 700;
    color: #1F2937;
    text-align: center;
    margin-bottom: 16px;
}
.verification-text {
    font-size: 16px;
    color: #6B7280;
    text-align: center;
    line-height: 1.6;
    margin-bottom: 32px;
}
.email-display {
    background: #F3F4F6;
    padding: 16px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 32px;
}
.email-display strong {
    color: #1F2937;
    font-size: 18px;
}
.resend-section {
    text-align: center;
    padding: 24px;
    background: #F9FAFB;
    border-radius: 12px;
    margin-bottom: 24px;
}
.resend-btn {
    display: inline-block;
    padding: 14px 32px;
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s;
}
.resend-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}
.resend-btn:disabled {
    background: #D1D5DB;
    cursor: not-allowed;
    transform: none;
}
.info-box {
    background: #DBEAFE;
    border-left: 4px solid #3B82F6;
    padding: 16px;
    border-radius: 6px;
    margin-bottom: 24px;
}
.warning-box {
    background: #FEF3C7;
    border-left: 4px solid #F59E0B;
    padding: 16px;
    border-radius: 6px;
    margin-bottom: 24px;
}
.error-box {
    background: #FEE2E2;
    border-left: 4px solid #EF4444;
    padding: 16px;
    border-radius: 6px;
    margin-bottom: 24px;
}
.success-box {
    background: #D1FAE5;
    border-left: 4px solid #10B981;
    padding: 16px;
    border-radius: 6px;
    margin-bottom: 24px;
}
.attempts-counter {
    display: inline-block;
    background: #1F2937;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    margin-left: 8px;
}
</style>

<div class="verification-container">
    <div class="verification-icon">📧</div>
    <h1 class="verification-title">Verifikasi Email Anda</h1>
    <p class="verification-text">
        Kami telah mengirim email verifikasi ke:
    </p>
    
    <div class="email-display">
        <strong><?php echo htmlspecialchars($email); ?></strong>
    </div>
    
    <?php if ($success): ?>
        <div class="success-box">
            <strong>✓ <?php echo $success; ?></strong>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-box">
            <strong>⚠️ <?php echo $error; ?></strong>
        </div>
    <?php endif; ?>
    
    <div class="info-box">
        <strong>📝 Langkah-langkah:</strong>
        <ol style="margin: 12px 0 0 20px; padding: 0;">
            <li>Buka email Anda</li>
            <li>Cari email dari "Dorve House"</li>
            <li>Klik tombol "Verifikasi Email"</li>
            <li>Periksa folder Spam/Junk jika tidak ada di Inbox</li>
        </ol>
    </div>
    
    <?php if ($user && $user['verification_attempts'] > 0): ?>
        <div class="warning-box">
            <strong>📊 Pengiriman Email:</strong>
            <span class="attempts-counter"><?php echo $user['verification_attempts']; ?>/3</span>
            <?php if ($user['verification_attempts'] >= 3): ?>
                <p style="margin: 8px 0 0; font-size: 14px; color: #92400E;">
                    ⚠️ Batas maksimal tercapai. Tunggu 15 menit atau cek email Anda.
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="resend-section">
        <p style="margin: 0 0 16px; font-size: 14px; color: #6B7280;">
            Tidak menerima email?
        </p>
        
        <form method="POST">
            <button type="submit" name="resend" class="resend-btn" id="resendBtn" <?php echo !$can_resend ? 'disabled' : ''; ?>>
                <?php if ($can_resend): ?>
                    🔄 Kirim Ulang Email
                <?php else: ?>
                    <span id="btnText">⏳ Tunggu <?php echo $wait_time > 60 ? ceil($wait_time / 60) . ' menit' : $wait_time . ' detik'; ?></span>
                <?php endif; ?>
            </button>
        </form>
        
        <?php if ($user && $user['verification_attempts'] >= 3 && !$can_resend): ?>
            <p style="margin: 16px 0 0; font-size: 13px; color: #6B7280;">
                Setelah menunggu, Anda bisa coba <strong>daftar ulang</strong> dengan email yang sama.
            </p>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 24px;">
        <a href="/auth/login.php" style="color: #3B82F6; text-decoration: none; font-weight: 600;">
            ← Kembali ke Login
        </a>
    </div>
</div>

<?php if (!$can_resend && $wait_time > 0): ?>
<script>
// Countdown timer for resend button
let countdown = <?php echo $wait_time; ?>;
const button = document.getElementById('resendBtn');
const btnText = document.getElementById('btnText');
const isMinutes = countdown > 60;

const interval = setInterval(() => {
    countdown--;

    if (countdown > 0) {
        if (isMinutes && countdown > 60) {
            const minutes = Math.ceil(countdown / 60);
            btnText.textContent = `⏳ Tunggu ${minutes} menit`;
        } else {
            btnText.textContent = `⏳ Tunggu ${countdown} detik`;
        }
    } else {
        btnText.textContent = '🔄 Kirim Ulang Email';
        button.disabled = false;
        clearInterval(interval);

        // Reload page to update state
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
}, 1000);
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
