<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/email-helper.php';

// Test email address
$test_email = 'officiallangkalytica@gmail.com';
$test_name = 'Test User';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Email System - Dorve.id</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1a1a1a;
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 12px;
        }
        h2 {
            color: #2d2d2d;
            margin-top: 32px;
        }
        .status {
            padding: 12px 20px;
            border-radius: 8px;
            margin: 16px 0;
            font-weight: 600;
        }
        .success {
            background: #D1FAE5;
            color: #065F46;
            border: 2px solid #10B981;
        }
        .error {
            background: #FEE2E2;
            color: #991B1B;
            border: 2px solid #EF4444;
        }
        .info {
            background: #DBEAFE;
            color: #1E40AF;
            border: 2px solid #3B82F6;
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
        }
        .warning {
            background: #FEF3C7;
            color: #92400E;
            border: 2px solid #F59E0B;
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
        }
        code {
            background: #f5f5f5;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        .config-box {
            background: #1a1a1a;
            color: #10B981;
            padding: 20px;
            border-radius: 8px;
            margin: 16px 0;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #1a1a1a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #2d2d2d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email System Test - Dorve.id</h1>

        <div class="info">
            <strong>ℹ️ Test akan mengirim email ke:</strong> <code><?php echo htmlspecialchars($test_email); ?></code>
            <br><br>
            Pastikan email di <code>test-email.php</code> sudah diganti dengan email kamu!
        </div>

        <?php
        // Check PHPMailer
        $phpmailer_available = class_exists('PHPMailer\PHPMailer\PHPMailer');
        echo "<h2>1. PHPMailer Status</h2>";
        if ($phpmailer_available) {
            echo '<div class="status success">✅ PHPMailer INSTALLED - SMTP Ready!</div>';
        } else {
            echo '<div class="status error">❌ PHPMailer NOT FOUND - Using mail() fallback</div>';
            echo '<div class="warning"><strong>⚠️ Warning:</strong> Tanpa PHPMailer, email mungkin tidak terkirim. Install dengan:<br><code>composer require phpmailer/phpmailer</code></div>';
        }

        // Check SMTP Config
        echo "<h2>2. SMTP Configuration</h2>";
        echo '<div class="config-box">';
        echo 'SMTP_HOST: ' . SMTP_HOST . '<br>';
        echo 'SMTP_PORT: ' . SMTP_PORT . '<br>';
        echo 'SMTP_USERNAME: ' . SMTP_USERNAME . '<br>';
        echo 'SMTP_PASSWORD: ' . (SMTP_PASSWORD === 'your-app-password' ? '⚠️ NOT CONFIGURED' : '✅ Configured') . '<br>';
        echo '</div>';

        if (SMTP_USERNAME === 'your-email@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
            echo '<div class="warning">';
            echo '<strong>⚠️ SMTP Not Configured!</strong><br>';
            echo 'Edit <code>/includes/email-helper.php</code> line 22-23:<br><br>';
            echo "define('SMTP_USERNAME', 'your-gmail@gmail.com');<br>";
            echo "define('SMTP_PASSWORD', 'your-app-password');<br><br>";
            echo '<strong>Baca:</strong> <a href="/EMAIL-SETUP-GUIDE.md">EMAIL-SETUP-GUIDE.md</a>';
            echo '</div>';
        }

        // Test 1: Verification Email
        echo "<h2>3. Test Email Verifikasi</h2>";
        try {
            $token = bin2hex(random_bytes(16));
            $link = 'https://dorve.id/auth/verify-email.php?token=' . $token;
            $result1 = sendVerificationEmail($test_email, $test_name, $link);

            if ($result1) {
                echo '<div class="status success">✅ Email verifikasi BERHASIL dikirim!</div>';
            } else {
                echo '<div class="status error">❌ Email verifikasi GAGAL dikirim</div>';
            }
        } catch (Exception $e) {
            echo '<div class="status error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        // Test 2: Password Reset Email
        echo "<h2>4. Test Email Reset Password</h2>";
        try {
            $token = bin2hex(random_bytes(16));
            $link = 'https://dorve.id/auth/reset-password.php?token=' . $token;
            $result2 = sendPasswordResetEmail($test_email, $test_name, $link);

            if ($result2) {
                echo '<div class="status success">✅ Email reset password BERHASIL dikirim!</div>';
            } else {
                echo '<div class="status error">❌ Email reset password GAGAL dikirim</div>';
            }
        } catch (Exception $e) {
            echo '<div class="status error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        // Summary
        echo "<hr>";
        echo "<h2>📊 Summary</h2>";

        if (($result1 ?? false) && ($result2 ?? false)) {
            echo '<div class="status success">';
            echo '<strong>🎉 SEMUA TEST BERHASIL!</strong><br><br>';
            echo '✅ Email verifikasi sent<br>';
            echo '✅ Email reset password sent<br><br>';
            echo '<strong>Next step:</strong> Cek inbox email <code>' . htmlspecialchars($test_email) . '</code><br>';
            echo 'Jika tidak ada, cek folder <strong>Spam/Junk</strong>!';
            echo '</div>';
        } else {
            echo '<div class="status error">';
            echo '<strong>❌ TEST GAGAL!</strong><br><br>';
            echo '<strong>Troubleshooting:</strong><br>';
            echo '1. Install PHPMailer: <code>composer require phpmailer/phpmailer</code><br>';
            echo '2. Setup Gmail App Password (bukan password biasa!)<br>';
            echo '3. Update config di <code>/includes/email-helper.php</code><br>';
            echo '4. Baca panduan lengkap: <a href="/EMAIL-SETUP-GUIDE.md">EMAIL-SETUP-GUIDE.md</a>';
            echo '</div>';
        }

        echo '<div class="info">';
        echo '<strong>📖 Panduan Lengkap:</strong><br>';
        echo 'Baca file <a href="/EMAIL-SETUP-GUIDE.md" target="_blank"><strong>EMAIL-SETUP-GUIDE.md</strong></a> untuk setup lengkap Gmail SMTP!';
        echo '</div>';
        ?>

        <a href="/" class="btn">← Kembali ke Home</a>
    </div>
</body>
</html>
