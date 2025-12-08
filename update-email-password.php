<?php
/**
 * Update Gmail App Password Tool
 * Tool simpel untuk update app password tanpa edit file manual
 */

$email_helper_file = __DIR__ . '/includes/email-helper.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Update Email Password - Dorve.id</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            max-width: 600px;
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: white;
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 32px;
        }
        .box {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 2px solid;
        }
        .info {
            background: #DBEAFE;
            color: #1E40AF;
            border-color: #3B82F6;
        }
        .success {
            background: #D1FAE5;
            color: #065F46;
            border-color: #10B981;
        }
        .error {
            background: #FEE2E2;
            color: #991B1B;
            border-color: #EF4444;
        }
        .warning {
            background: #FEF3C7;
            color: #92400E;
            border-color: #F59E0B;
        }
        .form-group {
            margin-bottom: 24px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1a1a1a;
            font-size: 14px;
        }
        input[type="email"],
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
            font-family: 'Courier New', monospace;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .hint {
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        .btn:active {
            transform: translateY(0);
        }
        .btn-secondary {
            background: #6b7280;
            margin-left: 12px;
        }
        .btn-secondary:hover {
            background: #4b5563;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .steps {
            background: #f9fafb;
            padding: 24px;
            border-radius: 12px;
            margin-top: 24px;
        }
        .steps h3 {
            color: #1a1a1a;
            margin-bottom: 16px;
            font-size: 18px;
        }
        .steps ol {
            padding-left: 24px;
        }
        .steps li {
            margin-bottom: 12px;
            line-height: 1.6;
            color: #374151;
        }
        .steps a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .steps a:hover {
            text-decoration: underline;
        }
        code {
            background: #1a1a1a;
            color: #10B981;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        strong {
            color: #1a1a1a;
        }
        .current-config {
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }
        .current-config div {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .current-config div:last-child {
            border-bottom: none;
        }
        .current-config .label {
            font-weight: 600;
            color: #6b7280;
        }
        .current-config .value {
            font-family: 'Courier New', monospace;
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Update Email Password</h1>
            <p>Tool untuk update Gmail App Password dengan mudah</p>
        </div>

        <div class="content">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
                try {
                    $new_email = trim($_POST['email']);
                    $new_password = trim($_POST['app_password']);

                    // Validasi input
                    if (empty($new_email) || empty($new_password)) {
                        throw new Exception('Email dan App Password tidak boleh kosong!');
                    }

                    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Format email tidak valid!');
                    }

                    // Remove spaces from password (Google app passwords have spaces)
                    $new_password = str_replace(' ', '', $new_password);

                    if (strlen($new_password) != 16) {
                        throw new Exception('App Password harus 16 karakter (tanpa spasi)!');
                    }

                    // Read current file
                    if (!file_exists($email_helper_file)) {
                        throw new Exception('File email-helper.php tidak ditemukan!');
                    }

                    $content = file_get_contents($email_helper_file);

                    // Update email
                    $content = preg_replace(
                        "/define\('SMTP_USERNAME',\s*'[^']*'\);/",
                        "define('SMTP_USERNAME', '$new_email');",
                        $content
                    );

                    // Update password
                    $content = preg_replace(
                        "/define\('SMTP_PASSWORD',\s*'[^']*'\);/",
                        "define('SMTP_PASSWORD', '$new_password');",
                        $content
                    );

                    // Save file
                    if (file_put_contents($email_helper_file, $content) === false) {
                        throw new Exception('Gagal menulis file! Periksa permission folder.');
                    }

                    echo '<div class="box success">';
                    echo '<strong>✅ Password berhasil di-update!</strong><br><br>';
                    echo 'Email: <code>' . htmlspecialchars($new_email) . '</code><br>';
                    echo 'Password: <code>' . str_repeat('*', 12) . substr($new_password, -4) . '</code>';
                    echo '</div>';

                    echo '<div class="box info">';
                    echo '<strong>🎯 Next Step:</strong><br><br>';
                    echo 'Test email system sekarang untuk memastikan konfigurasi sudah benar!';
                    echo '</div>';

                    echo '<div style="margin-top: 24px;">';
                    echo '<a href="/diagnose-email.php" class="btn">🔍 Test Email Now</a>';
                    echo '<a href="/" class="btn btn-secondary">← Back to Home</a>';
                    echo '</div>';

                } catch (Exception $e) {
                    echo '<div class="box error">';
                    echo '<strong>❌ Update Failed!</strong><br><br>';
                    echo htmlspecialchars($e->getMessage());
                    echo '</div>';

                    echo '<a href="" class="btn">🔄 Try Again</a>';
                }
            } else {
                // Show current config
                $current_email = 'dorveofficial@gmail.com'; // Default
                $current_pass = '****';

                if (file_exists($email_helper_file)) {
                    $content = file_get_contents($email_helper_file);
                    if (preg_match("/define\('SMTP_USERNAME',\s*'([^']*)'\);/", $content, $matches)) {
                        $current_email = $matches[1];
                    }
                    if (preg_match("/define\('SMTP_PASSWORD',\s*'([^']*)'\);/", $content, $matches)) {
                        $current_pass = str_repeat('*', 12) . substr($matches[1], -4);
                    }
                }

                echo '<div class="box warning">';
                echo '<strong>⚠️ Current Configuration:</strong>';
                echo '</div>';

                echo '<div class="current-config">';
                echo '<div><span class="label">Email:</span><span class="value">' . htmlspecialchars($current_email) . '</span></div>';
                echo '<div><span class="label">Password:</span><span class="value">' . $current_pass . '</span></div>';
                echo '</div>';

                // Instructions
                echo '<div class="steps">';
                echo '<h3>📋 Cara Generate Gmail App Password:</h3>';
                echo '<ol>';
                echo '<li><strong>Login Gmail:</strong> <a href="https://mail.google.com" target="_blank">mail.google.com</a> (dorveofficial@gmail.com)</li>';
                echo '<li><strong>Buka Google Account Security:</strong><br><a href="https://myaccount.google.com/security" target="_blank">myaccount.google.com/security</a></li>';
                echo '<li><strong>Pastikan 2-Step Verification ON</strong><br>Kalau OFF, aktifkan dulu!</li>';
                echo '<li><strong>Scroll ke "App passwords"</strong><br>Atau langsung: <a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a></li>';
                echo '<li><strong>Klik "Generate"</strong> atau buat baru</li>';
                echo '<li><strong>Pilih:</strong> Mail → Other (Custom name) → Ketik: "Dorve Website"</li>';
                echo '<li><strong>Copy 16 karakter</strong> (tanpa spasi!)</li>';
                echo '<li><strong>Paste di form bawah!</strong></li>';
                echo '</ol>';
                echo '</div>';

                // Form
                echo '<form method="POST" style="margin-top: 32px;">';

                echo '<div class="form-group">';
                echo '<label>📧 Gmail Address:</label>';
                echo '<input type="email" name="email" value="' . htmlspecialchars($current_email) . '" required>';
                echo '<div class="hint">Email Gmail yang digunakan untuk SMTP</div>';
                echo '</div>';

                echo '<div class="form-group">';
                echo '<label>🔑 App Password (16 karakter):</label>';
                echo '<input type="text" name="app_password" placeholder="abcd efgh ijkl mnop" maxlength="19" required>';
                echo '<div class="hint">Copy-paste langsung dari Google! Spasi otomatis dihapus.</div>';
                echo '</div>';

                echo '<div class="box info">';
                echo '<strong>💡 Tips:</strong><br>';
                echo '• Password format: <code>xxxx xxxx xxxx xxxx</code> (16 karakter + spasi)<br>';
                echo '• Spasi akan otomatis dihapus oleh system<br>';
                echo '• Jangan pakai password Gmail biasa! Harus App Password!';
                echo '</div>';

                echo '<button type="submit" name="update_password" class="btn">💾 Update Password</button>';
                echo '<a href="/" class="btn btn-secondary">Cancel</a>';
                echo '</form>';
            }
            ?>
        </div>
    </div>
</body>
</html>
