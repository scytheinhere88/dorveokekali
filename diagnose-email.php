<?php
/**
 * Advanced Email Diagnostic Tool
 * Cek detailed error kenapa email gak masuk
 */

require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$phpmailer_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($phpmailer_path)) {
    require_once $phpmailer_path;
}

// Test email address
$test_email = 'officiallangkalytica@gmail.com';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔍 Email Diagnostics - Dorve.id</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
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
            padding: 12px;
            background: #f9fafb;
            border-left: 4px solid #1a1a1a;
        }
        .box {
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
            border: 2px solid;
        }
        .success { background: #D1FAE5; color: #065F46; border-color: #10B981; }
        .error { background: #FEE2E2; color: #991B1B; border-color: #EF4444; }
        .info { background: #DBEAFE; color: #1E40AF; border-color: #3B82F6; }
        .warning { background: #FEF3C7; color: #92400E; border-color: #F59E0B; }
        .code-box {
            background: #1a1a1a;
            color: #10B981;
            padding: 20px;
            border-radius: 8px;
            margin: 16px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .step {
            font-weight: 700;
            color: #1a1a1a;
            font-size: 18px;
        }
        ul { line-height: 1.8; }
        code {
            background: #f5f5f5;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #DC2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Advanced Email Diagnostic</h1>

        <div class="info">
            <strong>📧 Test Email:</strong> <code><?php echo htmlspecialchars($test_email); ?></code>
        </div>

        <?php
        // Step 1: Check PHPMailer
        echo '<h2><span class="step">STEP 1:</span> PHPMailer Status</h2>';

        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            echo '<div class="box error">';
            echo '<strong>❌ PHPMailer NOT INSTALLED!</strong><br><br>';
            echo 'Install dulu:<br>';
            echo '<code>composer require phpmailer/phpmailer</code>';
            echo '</div>';
            exit;
        }

        echo '<div class="box success">✅ PHPMailer installed & ready</div>';

        // Step 2: Check Config
        echo '<h2><span class="step">STEP 2:</span> SMTP Configuration</h2>';

        $smtp_host = 'smtp.gmail.com';
        $smtp_port = 587;
        $smtp_user = 'dorveofficial@gmail.com';
        $smtp_pass = 'nqztkewcyrxowrsg';

        echo '<div class="code-box">';
        echo "SMTP_HOST: $smtp_host\n";
        echo "SMTP_PORT: $smtp_port\n";
        echo "SMTP_USER: $smtp_user\n";
        echo "SMTP_PASS: " . str_repeat('*', strlen($smtp_pass)) . " (length: " . strlen($smtp_pass) . ")\n";
        echo '</div>';

        // Step 3: Test SMTP Connection
        echo '<h2><span class="step">STEP 3:</span> Test SMTP Connection</h2>';

        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 2; // Enable verbose debug output
        $mail->Debugoutput = function($str, $level) {
            echo htmlspecialchars($str) . "<br>";
        };

        try {
            echo '<div class="code-box">';

            // Server settings
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtp_port;
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom($smtp_user, 'Dorve.id');
            $mail->addAddress($test_email, 'Test User');

            // Content
            $mail->isHTML(true);
            $mail->Subject = '🔍 Test Email from Dorve.id - ' . date('H:i:s');
            $mail->Body = '<h2>Test Email Berhasil!</h2><p>Email ini dikirim pada: <strong>' . date('Y-m-d H:i:s') . '</strong></p><p>Kalau kamu menerima email ini, berarti <strong>SMTP sudah berfungsi dengan baik!</strong> ✅</p>';

            $result = $mail->send();

            echo '</div>';

            if ($result) {
                echo '<div class="box success">';
                echo '<strong>🎉 EMAIL BERHASIL DIKIRIM!</strong><br><br>';
                echo '✅ SMTP connection: <strong>OK</strong><br>';
                echo '✅ Authentication: <strong>OK</strong><br>';
                echo '✅ Email sent: <strong>OK</strong><br><br>';
                echo '<strong>Next step:</strong><br>';
                echo '1. Cek inbox email <code>' . htmlspecialchars($test_email) . '</code><br>';
                echo '2. Jika tidak ada, <strong>CEK FOLDER SPAM/JUNK!</strong><br>';
                echo '3. Tunggu 1-2 menit (kadang ada delay)<br>';
                echo '</div>';
            } else {
                echo '<div class="box error">❌ Email gagal dikirim (no result)</div>';
            }

        } catch (Exception $e) {
            echo '</div>';

            echo '<div class="box error">';
            echo '<strong>❌ ERROR DETECTED!</strong><br><br>';
            echo '<strong>Error Message:</strong><br>';
            echo '<code>' . htmlspecialchars($e->getMessage()) . '</code><br><br>';

            // Analyze error
            $error_msg = $e->getMessage();

            if (strpos($error_msg, 'Invalid address') !== false) {
                echo '<strong>🔍 Diagnosis:</strong> Email address format salah<br><br>';
                echo '<strong>Solution:</strong><br>';
                echo '- Pastikan email <code>' . htmlspecialchars($test_email) . '</code> valid<br>';
            }
            elseif (strpos($error_msg, 'Username and Password not accepted') !== false ||
                    strpos($error_msg, 'Authentication failed') !== false ||
                    strpos($error_msg, '535') !== false) {
                echo '<strong>🔍 Diagnosis:</strong> App Password SALAH atau BELUM AKTIF!<br><br>';
                echo '<strong>Solutions:</strong><br>';
                echo '1. <strong>Generate App Password BARU</strong>:<br>';
                echo '   - Login ke Gmail: <code>dorveofficial@gmail.com</code><br>';
                echo '   - Buka: <a href="https://myaccount.google.com/security" target="_blank">Google Account Security</a><br>';
                echo '   - Pastikan <strong>2-Step Verification</strong> sudah ON<br>';
                echo '   - Scroll ke <strong>App Passwords</strong><br>';
                echo '   - Generate untuk <strong>Mail</strong> app<br>';
                echo '   - Copy 16 karakter (tanpa spasi!)<br><br>';
                echo '2. Update di <code>/includes/email-helper.php</code> line 23<br><br>';
                echo '3. <strong>PENTING:</strong> App password harus <strong>16 karakter tanpa spasi</strong><br>';
            }
            elseif (strpos($error_msg, 'Could not connect to SMTP host') !== false ||
                    strpos($error_msg, 'Connection timed out') !== false) {
                echo '<strong>🔍 Diagnosis:</strong> Tidak bisa connect ke Gmail SMTP<br><br>';
                echo '<strong>Solutions:</strong><br>';
                echo '1. Cek koneksi internet<br>';
                echo '2. Firewall blocking port 587?<br>';
                echo '3. Try port 465 dengan SSL instead of STARTTLS<br>';
            }
            else {
                echo '<strong>🔍 Diagnosis:</strong> Unknown error<br><br>';
                echo 'Copy error message di atas dan kasih tau saya!<br>';
            }

            echo '</div>';
        }

        // Step 4: Recommendations
        echo '<h2><span class="step">STEP 4:</span> Recommendations</h2>';

        echo '<div class="box info">';
        echo '<strong>📋 Checklist Gmail SMTP:</strong><br><br>';
        echo '✅ <strong>2-Step Verification</strong> harus AKTIF<br>';
        echo '✅ <strong>App Password</strong> sudah generate (bukan password biasa!)<br>';
        echo '✅ App password <strong>16 karakter TANPA SPASI</strong><br>';
        echo '✅ SMTP Host: <code>smtp.gmail.com</code><br>';
        echo '✅ SMTP Port: <code>587</code> (STARTTLS)<br>';
        echo '✅ Email sender: <code>dorveofficial@gmail.com</code><br><br>';
        echo '<strong>💡 Tips:</strong><br>';
        echo '- Kadang email masuk ke <strong>Spam/Junk</strong><br>';
        echo '- Ada delay 1-2 menit setelah test<br>';
        echo '- Kalau masih gagal, generate app password BARU<br>';
        echo '</div>';
        ?>

        <div style="margin-top: 40px; text-align: center;">
            <a href="/" style="display: inline-block; padding: 12px 32px; background: #1a1a1a; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">← Kembali ke Home</a>
        </div>
    </div>
</body>
</html>
