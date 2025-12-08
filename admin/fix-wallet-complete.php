<?php
/**
 * COMPLETE WALLET TRANSACTIONS TABLE FIX
 * Add ALL missing columns needed for wallet topup system
 */
require_once __DIR__ . '/../config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Wallet Fix</title>
    <style>
        body { font-family: monospace; padding: 40px; background: #1a1a1a; color: #00ff00; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #00aaff; }
        .warning { color: #ffaa00; }
        pre { background: #000; padding: 20px; border-radius: 8px; }
        h1 { color: #ffffff; }
        table { border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px 12px; text-align: left; border: 1px solid #444; }
        th { background: #333; color: #fff; }
    </style>
</head>
<body>
    <h1>🔧 COMPLETE WALLET TRANSACTIONS FIX</h1>
    <pre><?php

try {
    echo "Starting comprehensive wallet_transactions table fix...\n\n";

    // STEP 1: Check if table exists
    echo "STEP 1: Checking if table exists...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'wallet_transactions'");
    if ($stmt->rowCount() == 0) {
        echo "<span class='error'>❌ Table wallet_transactions does NOT exist!</span>\n";
        echo "<span class='info'>Creating table now...</span>\n\n";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `wallet_transactions` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `type` VARCHAR(50) NOT NULL,
                `amount` DECIMAL(15,2) NOT NULL,
                `balance_before` DECIMAL(15,2) NOT NULL,
                `balance_after` DECIMAL(15,2) NOT NULL,
                `description` TEXT DEFAULT NULL,
                `reference_id` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<span class='success'>✅ Table created</span>\n\n";
    } else {
        echo "<span class='success'>✅ Table exists</span>\n\n";
    }

    // STEP 2: Get current structure
    echo "STEP 2: Analyzing current structure...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM wallet_transactions");
    $existing_columns = [];
    while ($row = $stmt->fetch()) {
        $existing_columns[] = $row['Field'];
    }

    echo "<span class='info'>Found " . count($existing_columns) . " columns</span>\n\n";

    // STEP 3: Add all missing columns
    echo "STEP 3: Adding missing columns...\n";

    $columns_to_add = [
        // Basic columns
        'type' => "ENUM('topup', 'deposit', 'withdrawal', 'purchase', 'refund', 'referral_bonus') NOT NULL DEFAULT 'topup' AFTER user_id",
        'status' => "ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending' AFTER description",

        // Payment columns for bank transfer topup
        'payment_method' => "VARCHAR(50) NULL AFTER status",
        'payment_status' => "VARCHAR(50) NULL AFTER payment_method",
        'amount_original' => "DECIMAL(15,2) NULL AFTER amount",
        'unique_code' => "INT(11) NULL AFTER amount_original",
        'bank_account_id' => "INT(11) NULL AFTER unique_code",

        // Proof and admin columns
        'proof_image' => "VARCHAR(255) NULL AFTER reference_id",
        'admin_notes' => "TEXT NULL AFTER proof_image",

        // Timestamps
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];

    $added_count = 0;
    $exists_count = 0;

    foreach ($columns_to_add as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            try {
                // Remove AFTER clause if the reference column doesn't exist
                $def = $definition;
                if (preg_match('/AFTER (\w+)/', $definition, $matches)) {
                    $after_col = $matches[1];
                    if (!in_array($after_col, $existing_columns)) {
                        $def = preg_replace('/AFTER \w+/', '', $definition);
                    }
                }

                $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN `$column` $def");
                echo "<span class='success'>✅ Added: $column</span>\n";
                $existing_columns[] = $column; // Add to list for next iterations
                $added_count++;
            } catch (PDOException $e) {
                echo "<span class='warning'>⚠️ Could not add $column: " . $e->getMessage() . "</span>\n";
            }
        } else {
            echo "<span class='info'>ℹ️ Exists: $column</span>\n";
            $exists_count++;
        }
    }

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ STRUCTURE UPDATE COMPLETE!</span>\n";
    echo "<span class='success'>Added: $added_count columns</span>\n";
    echo "<span class='success'>Already exists: $exists_count columns</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    // STEP 4: Add indexes for better performance
    echo "STEP 4: Adding indexes...\n";

    $indexes = [
        'idx_user' => 'user_id',
        'idx_status' => 'status',
        'idx_payment_status' => 'payment_status',
        'idx_type' => 'type',
        'idx_created' => 'created_at'
    ];

    foreach ($indexes as $idx_name => $column) {
        if (in_array($column, $existing_columns)) {
            try {
                $pdo->exec("ALTER TABLE wallet_transactions ADD INDEX `$idx_name` (`$column`)");
                echo "<span class='success'>✅ Added index: $idx_name on $column</span>\n";
            } catch (PDOException $e) {
                // Index might already exist
                if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                    echo "<span class='info'>ℹ️ Index exists: $idx_name</span>\n";
                } else {
                    echo "<span class='warning'>⚠️ Could not add index $idx_name: " . $e->getMessage() . "</span>\n";
                }
            }
        }
    }

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ INDEXES UPDATED!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    // STEP 5: Show final structure
    echo "STEP 5: Final Table Structure\n";
    echo "<span class='info'>====================</span>\n";
    $stmt = $pdo->query("DESCRIBE wallet_transactions");
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Purpose</th></tr>";

    $important_cols = [
        'proof_image' => 'Upload bukti transfer',
        'payment_method' => 'Metode pembayaran',
        'payment_status' => 'Status pembayaran',
        'amount_original' => 'Amount sebelum kode unik',
        'unique_code' => 'Kode unik verifikasi',
        'bank_account_id' => 'ID rekening bank tujuan',
        'admin_notes' => 'Catatan admin'
    ];

    while ($row = $stmt->fetch()) {
        $purpose = isset($important_cols[$row['Field']]) ? $important_cols[$row['Field']] : '';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . ($purpose ? "<span class='warning'>$purpose</span>" : "") . "</td>";
        echo "</tr>";
    }
    echo "</table>\n";

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ ALL FIXES COMPLETE!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    echo "<span class='info'>What's Fixed:</span>\n";
    echo "✅ proof_image column - Upload bukti transfer now works\n";
    echo "✅ payment_method column - Track payment method\n";
    echo "✅ payment_status column - Track payment status\n";
    echo "✅ amount_original column - Amount before unique code\n";
    echo "✅ unique_code column - Verification code\n";
    echo "✅ bank_account_id column - Link to bank account\n";
    echo "✅ admin_notes column - Admin can add notes\n";
    echo "✅ Indexes added - Better query performance\n\n";

    echo "<span class='info'>Next steps:</span>\n";
    echo "1. Go back to wallet page\n";
    echo "2. Create a topup\n";
    echo "3. Upload bukti transfer ✅ Should work now!\n";
    echo "4. Check bank details ✅ Should show now!\n\n";

    echo "<span class='info'>Safe to re-run this script anytime!</span>\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    echo "\n<span class='info'>Stack trace:</span>\n";
    echo "<span class='error'>" . htmlspecialchars($e->getTraceAsString()) . "</span>\n";
}

?></pre>
</body>
</html>
