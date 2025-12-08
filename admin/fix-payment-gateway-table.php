<?php
/**
 * FIX PAYMENT GATEWAY SETTINGS TABLE
 * Auto-fix missing columns
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
    <title>Fix Payment Gateway Table</title>
    <style>
        body { font-family: monospace; padding: 40px; background: #1a1a1a; color: #00ff00; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #00aaff; }
        .warning { color: #ffaa00; }
        pre { background: #000; padding: 20px; border-radius: 8px; }
        h1 { color: #ffffff; }
    </style>
</head>
<body>
    <h1>🔧 FIX PAYMENT GATEWAY TABLE</h1>
    <pre><?php

try {
    echo "Starting payment_gateway_settings table fix...\n\n";

    // STEP 1: Create table if not exists
    echo "STEP 1: Checking if table exists...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `payment_gateway_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `gateway_name` VARCHAR(50) UNIQUE NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<span class='success'>✅ Table exists or created</span>\n\n";

    // STEP 2: Add all required columns
    echo "STEP 2: Adding missing columns...\n";

    $columns_to_add = [
        'api_key' => 'VARCHAR(255) NULL',
        'api_secret' => 'VARCHAR(255) NULL',
        'server_key' => 'VARCHAR(255) NULL',
        'client_key' => 'VARCHAR(255) NULL',
        'merchant_id' => 'VARCHAR(255) NULL',
        'client_id' => 'VARCHAR(255) NULL',
        'client_secret' => 'VARCHAR(255) NULL',
        'is_production' => 'TINYINT(1) DEFAULT 0',
        'is_active' => 'TINYINT(1) DEFAULT 0'
    ];

    // Get existing columns
    $stmt = $pdo->query("SHOW COLUMNS FROM payment_gateway_settings");
    $existing_columns = [];
    while ($row = $stmt->fetch()) {
        $existing_columns[] = $row['Field'];
    }

    foreach ($columns_to_add as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            try {
                $pdo->exec("ALTER TABLE payment_gateway_settings ADD COLUMN `$column` $definition");
                echo "<span class='success'>✅ Added column: $column</span>\n";
            } catch (PDOException $e) {
                echo "<span class='warning'>⚠️ Could not add $column: " . $e->getMessage() . "</span>\n";
            }
        } else {
            echo "<span class='info'>ℹ️ Column exists: $column</span>\n";
        }
    }

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ TABLE STRUCTURE FIXED!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    // STEP 3: Show final structure
    echo "Final Table Structure:\n";
    echo "<span class='info'>====================</span>\n";
    $stmt = $pdo->query("DESCRIBE payment_gateway_settings");
    while ($row = $stmt->fetch()) {
        $required = in_array($row['Field'], ['server_key', 'client_key']) ? ' <span class="warning">(IMPORTANT FOR MIDTRANS)</span>' : '';
        echo sprintf("%-20s %-20s %s\n",
            $row['Field'],
            $row['Type'],
            $required
        );
    }

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ ALL DONE!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    echo "<span class='info'>Next steps:</span>\n";
    echo "1. Go to: <a href='/admin/settings/payment-settings.php' style='color: #00aaff;'>/admin/settings/payment-settings.php</a>\n";
    echo "2. Scroll to 'Midtrans Configuration'\n";
    echo "3. Enter your Midtrans Server Key and Client Key\n";
    echo "4. Click 'Save Midtrans Settings'\n";
    echo "5. Should work without errors now! ✅\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    echo "\n<span class='info'>Stack trace:</span>\n";
    echo "<span class='error'>" . htmlspecialchars($e->getTraceAsString()) . "</span>\n";
}

?></pre>
</body>
</html>
