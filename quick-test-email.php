<?php
/**
 * Quick Email Test - Super Simple
 * Test email tanpa PHPMailer (pakai mail() function)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/email-helper.php';

$test_email = 'officiallangkalytica@gmail.com';

echo "<h1>🧪 Quick Email Test</h1>";
echo "<p>Testing email system...</p>";

// Test kirim email
$token = bin2hex(random_bytes(16));
$link = 'https://dorve.id/auth/verify-email.php?token=' . $token;

echo "<h3>Sending test verification email to: $test_email</h3>";

$result = sendVerificationEmail($test_email, 'Test User', $link);

if ($result) {
    echo "<div style='padding: 20px; background: #D1FAE5; border: 2px solid #10B981; border-radius: 8px; margin: 20px 0;'>";
    echo "<h2 style='color: #065F46; margin: 0;'>✅ EMAIL SENT SUCCESSFULLY!</h2>";
    echo "<p style='color: #065F46; margin: 10px 0 0;'>Check inbox: <strong>$test_email</strong></p>";
    echo "<p style='color: #065F46; margin: 5px 0 0;'>If not in inbox, check <strong>Spam/Junk</strong> folder!</p>";
    echo "</div>";

    echo "<h3>📧 Email Details:</h3>";
    echo "<ul>";
    echo "<li>To: $test_email</li>";
    echo "<li>Subject: Verifikasi Email Anda - Dorve House</li>";
    echo "<li>From: Dorve.id - Pusat Fashion Indonesia</li>";
    echo "</ul>";

    echo "<p><strong>✅ Email system is working!</strong></p>";
} else {
    echo "<div style='padding: 20px; background: #FEE2E2; border: 2px solid #EF4444; border-radius: 8px; margin: 20px 0;'>";
    echo "<h2 style='color: #991B1B; margin: 0;'>❌ EMAIL FAILED TO SEND</h2>";
    echo "<p style='color: #991B1B; margin: 10px 0 0;'>Possible reasons:</p>";
    echo "<ul style='color: #991B1B;'>";
    echo "<li>PHPMailer not installed (install via: install-phpmailer.php)</li>";
    echo "<li>SMTP credentials incorrect</li>";
    echo "<li>Server mail() function disabled</li>";
    echo "</ul>";
    echo "</div>";

    echo "<h3>🔧 Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Install PHPMailer: <a href='/install-phpmailer.php'>install-phpmailer.php</a></li>";
    echo "<li>Check SMTP config in: /includes/email-helper.php</li>";
    echo "<li>Run full test: <a href='/test-email.php'>test-email.php</a></li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><a href='/' style='padding: 10px 20px; background: #1a1a1a; color: white; text-decoration: none; border-radius: 6px;'>← Back to Home</a></p>";
?>
