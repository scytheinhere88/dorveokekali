<?php
require_once __DIR__ . '/../config.php';

if (!isAdmin()) die('Unauthorized');

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Setup Email System</title>";
echo "<style>body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; }";
echo ".success { color: #10B981; } .error { color: #EF4444; }</style></head><body>";
echo "<h1>📧 Setting up Email System...</h1>";

try {
    // Check and add email verification columns to users table
    $stmt = $pdo->query("DESCRIBE users");
    $columns = array_column($stmt->fetchAll(), 'Field');
    
    $success_count = 0;
    
    if (!in_array('email_verified', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER email");
        echo "<p class='success'>✓ Added email_verified column</p>";
        $success_count++;
    } else {
        echo "<p class='success'>✓ email_verified column exists</p>";
    }
    
    if (!in_array('email_verification_token', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(255) NULL AFTER email_verified");
        echo "<p class='success'>✓ Added email_verification_token column</p>";
        $success_count++;
    } else {
        echo "<p class='success'>✓ email_verification_token column exists</p>";
    }
    
    if (!in_array('email_verification_expiry', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email_verification_expiry DATETIME NULL AFTER email_verification_token");
        echo "<p class='success'>✓ Added email_verification_expiry column</p>";
        $success_count++;
    } else {
        echo "<p class='success'>✓ email_verification_expiry column exists</p>";
    }
    
    if (!in_array('verification_attempts', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN verification_attempts INT DEFAULT 0 AFTER email_verification_expiry");
        echo "<p class='success'>✓ Added verification_attempts column</p>";
        $success_count++;
    } else {
        echo "<p class='success'>✓ verification_attempts column exists</p>";
    }
    
    if (!in_array('last_verification_sent', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_verification_sent DATETIME NULL AFTER verification_attempts");
        echo "<p class='success'>✓ Added last_verification_sent column</p>";
        $success_count++;
    } else {
        echo "<p class='success'>✓ last_verification_sent column exists</p>";
    }
    
    if (!in_array('password_reset_token', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(255) NULL");
        echo "<p class='success'>✓ Added password_reset_token column</p>";
        $success_count++;
    } else {
        echo "<p class='success'>✓ password_reset_token column exists</p>";
    }
    
    if (!in_array('password_reset_expiry', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN password_reset_expiry DATETIME NULL");
        echo "<p class='success'>✓ Added password_reset_expiry column</p>";
        $success_count++;
    } else {
        echo "<p class='success'>✓ password_reset_expiry column exists</p>";
    }
    
    if (!in_array('is_suspended', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_suspended TINYINT(1) DEFAULT 0");
        echo "<p class='success'>✓ Added is_suspended column</p>";
        $success_count++;
    } else {
        echo "<p class='success'>✓ is_suspended column exists</p>";
    }
    
    echo "<h2 class='success'>✅ Setup Complete!</h2>";
    echo "<p><strong>$success_count</strong> new columns added/verified</p>";
    echo "<p><a href='/admin/index.php' style='padding: 10px 20px; background: #10B981; color: white; text-decoration: none; border-radius: 6px;'>← Back to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>
