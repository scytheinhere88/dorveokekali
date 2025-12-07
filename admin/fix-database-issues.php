<?php
require_once __DIR__ . '/../config.php';

if (!isAdmin()) die('Unauthorized');

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Fix Database Issues</title>";
echo "<style>
    body { font-family: 'Inter', sans-serif; max-width: 1000px; margin: 40px auto; padding: 30px; background: #f5f5f5; }
    .container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #1a1a1a; margin-bottom: 10px; }
    .subtitle { color: #666; margin-bottom: 30px; }
    .success { color: #10B981; padding: 12px 16px; background: #D1FAE5; border-radius: 6px; margin: 10px 0; border-left: 4px solid #10B981; }
    .error { color: #EF4444; padding: 12px 16px; background: #FEE2E2; border-radius: 6px; margin: 10px 0; border-left: 4px solid #EF4444; }
    .warning { color: #F59E0B; padding: 12px 16px; background: #FEF3C7; border-radius: 6px; margin: 10px 0; border-left: 4px solid #F59E0B; }
    .section { margin: 30px 0; padding: 20px; background: #f9fafb; border-radius: 8px; }
    .section h2 { color: #1a1a1a; margin-bottom: 15px; font-size: 20px; }
    .btn { display: inline-block; padding: 12px 24px; background: #1a1a1a; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
    .btn:hover { background: #000; }
</style></head><body><div class='container'>";

echo "<h1>🔧 Fix Database Issues</h1>";
echo "<p class='subtitle'>Fixing marquee text, referral system, and wallet transactions...</p>";

$fixCount = 0;
$errorCount = 0;

// ===================================================
// FIX 1: Update banners table to support 'marquee'
// ===================================================
echo "<div class='section'>";
echo "<h2>1️⃣ Fix Banners Table (Marquee Text Support)</h2>";

try {
    // Check current banner_type definition
    $stmt = $pdo->query("SHOW COLUMNS FROM banners LIKE 'banner_type'");
    $column = $stmt->fetch();

    if ($column) {
        // Update ENUM to include 'marquee'
        $pdo->exec("ALTER TABLE banners MODIFY COLUMN banner_type ENUM('slider', 'popup', 'marquee') DEFAULT 'slider'");
        echo "<p class='success'>✓ Updated banner_type ENUM to include 'marquee'</p>";
        $fixCount++;
    } else {
        // Add banner_type column if it doesn't exist
        $pdo->exec("ALTER TABLE banners ADD COLUMN banner_type ENUM('slider', 'popup', 'marquee') DEFAULT 'slider' AFTER subtitle");
        echo "<p class='success'>✓ Added banner_type column with 'marquee' support</p>";
        $fixCount++;
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error fixing banners table: " . $e->getMessage() . "</p>";
    $errorCount++;
}

echo "</div>";

// ===================================================
// FIX 2: Create commission_tiers table
// ===================================================
echo "<div class='section'>";
echo "<h2>2️⃣ Create Commission Tiers Table</h2>";

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'commission_tiers'");

    if ($stmt->rowCount() == 0) {
        // Create commission_tiers table
        $pdo->exec("
            CREATE TABLE commission_tiers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL COMMENT 'Tier name (e.g., Tier 1, VIP, etc.)',
                min_topup DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Minimum first topup amount',
                max_topup DECIMAL(15,2) NULL COMMENT 'Maximum first topup amount (NULL = unlimited)',
                commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Commission percentage (e.g., 5.00 for 5%)',
                free_shipping_vouchers INT DEFAULT 0 COMMENT 'Number of free shipping vouchers to give',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_topup_range (min_topup, max_topup)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Referral commission tiers based on referred user first topup amount'
        ");
        echo "<p class='success'>✓ Created commission_tiers table</p>";

        // Insert default tiers
        $pdo->exec("
            INSERT INTO commission_tiers (name, min_topup, max_topup, commission_percent, free_shipping_vouchers) VALUES
            ('Tier 1: Under 500K', 0, 499999, 3.00, 1),
            ('Tier 2: 500K - 1M', 500000, 999999, 4.00, 2),
            ('Tier 3: 1M - 5M', 1000000, 4999999, 5.00, 2),
            ('Tier 4: 5M+', 5000000, NULL, 6.00, 3)
        ");
        echo "<p class='success'>✓ Inserted 4 default commission tiers</p>";
        $fixCount++;
    } else {
        echo "<p class='success'>✓ commission_tiers table already exists</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error creating commission_tiers table: " . $e->getMessage() . "</p>";
    $errorCount++;
}

echo "</div>";

// ===================================================
// FIX 3: Fix wallet_transactions table
// ===================================================
echo "<div class='section'>";
echo "<h2>3️⃣ Fix Wallet Transactions Table</h2>";

try {
    // Check if wallet_transactions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'wallet_transactions'");

    if ($stmt->rowCount() == 0) {
        // Create wallet_transactions table
        $pdo->exec("
            CREATE TABLE wallet_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type ENUM('topup', 'purchase', 'refund', 'admin_credit', 'admin_debit', 'referral_commission') NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                amount_original DECIMAL(15,2) NULL COMMENT 'Original amount before unique code',
                unique_code INT NULL COMMENT 'Unique 3-digit code for bank transfer',
                balance_before DECIMAL(15,2) NOT NULL DEFAULT 0,
                balance_after DECIMAL(15,2) NOT NULL DEFAULT 0,
                payment_method VARCHAR(50) NULL COMMENT 'bank_transfer, wallet, etc.',
                payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
                bank_account_id INT NULL COMMENT 'Reference to bank_accounts table',
                reference_id VARCHAR(100) NULL COMMENT 'External reference (order_id, topup_id, etc.)',
                description TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_status (payment_status),
                INDEX idx_type (type),
                INDEX idx_reference (reference_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Wallet transaction history for users'
        ");
        echo "<p class='success'>✓ Created wallet_transactions table with payment_status column</p>";
        $fixCount++;
    } else {
        // Check if required columns exist and add them in correct order
        $stmt = $pdo->query("DESCRIBE wallet_transactions");
        $columns = array_column($stmt->fetchAll(), 'Field');

        $missingColumns = [];
        if (!in_array('amount_original', $columns)) $missingColumns[] = 'amount_original';
        if (!in_array('unique_code', $columns)) $missingColumns[] = 'unique_code';
        if (!in_array('payment_method', $columns)) $missingColumns[] = 'payment_method';
        if (!in_array('payment_status', $columns)) $missingColumns[] = 'payment_status';
        if (!in_array('bank_account_id', $columns)) $missingColumns[] = 'bank_account_id';

        if (!empty($missingColumns)) {
            // Add columns in correct order
            if (in_array('amount_original', $missingColumns)) {
                $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN amount_original DECIMAL(15,2) NULL COMMENT 'Original amount before unique code' AFTER amount");
                echo "<p class='success'>✓ Added amount_original column</p>";
            }
            if (in_array('unique_code', $missingColumns)) {
                $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN unique_code INT NULL COMMENT 'Unique 3-digit code for bank transfer' AFTER amount_original");
                echo "<p class='success'>✓ Added unique_code column</p>";
            }
            if (in_array('payment_method', $missingColumns)) {
                $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN payment_method VARCHAR(50) NULL COMMENT 'bank_transfer, wallet, etc.' AFTER balance_after");
                echo "<p class='success'>✓ Added payment_method column</p>";
            }
            if (in_array('payment_status', $missingColumns)) {
                $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending' AFTER payment_method");
                echo "<p class='success'>✓ Added payment_status column</p>";
            }
            if (in_array('bank_account_id', $missingColumns)) {
                $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN bank_account_id INT NULL COMMENT 'Reference to bank_accounts table' AFTER payment_status");
                echo "<p class='success'>✓ Added bank_account_id column</p>";
            }
            $fixCount++;
        } else {
            echo "<p class='success'>✓ wallet_transactions table has all required columns</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error fixing wallet_transactions table: " . $e->getMessage() . "</p>";
    $errorCount++;
}

echo "</div>";

// ===================================================
// SUMMARY
// ===================================================
echo "<div class='section'>";
echo "<h2>📊 Summary</h2>";
echo "<p><strong>Fixes Applied:</strong> $fixCount</p>";
echo "<p><strong>Errors:</strong> $errorCount</p>";

if ($errorCount == 0) {
    echo "<p class='success'><strong>✅ All database issues have been fixed successfully!</strong></p>";
    echo "<ul>";
    echo "<li>✓ Marquee text can now be saved (banner_type supports 'marquee')</li>";
    echo "<li>✓ Referral page will work (commission_tiers table created)</li>";
    echo "<li>✓ Add/deduct balance will work (payment_status column exists)</li>";
    echo "</ul>";
} else {
    echo "<p class='warning'><strong>⚠️ Some issues could not be fixed. Please check the errors above.</strong></p>";
}

echo "</div>";

echo "<a href='/admin/index.php' class='btn'>← Back to Admin Dashboard</a>";
echo "<a href='/admin/settings/marquee-text.php' class='btn'>Test Marquee Text</a>";
echo "<a href='/admin/referrals/index.php' class='btn'>Test Referral Page</a>";

echo "</div></body></html>";
?>
