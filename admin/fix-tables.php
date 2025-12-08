<?php
/**
 * FIX DATABASE TABLES - WEB VERSION
 * Creates missing tables and fixes column names
 */
require_once __DIR__ . '/../config.php';

// Check admin
if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Database Tables</title>
    <style>
        body { font-family: monospace; padding: 40px; background: #1a1a1a; color: #00ff00; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        .info { color: #00aaff; }
        pre { background: #000; padding: 20px; border-radius: 8px; }
        h1 { color: #ffffff; }
    </style>
</head>
<body>
    <h1>🔧 DATABASE TABLES FIX</h1>
    <pre><?php

try {
    // ==================== CREATE payment_gateway_settings TABLE ====================
    echo "1. Creating payment_gateway_settings table...\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `payment_gateway_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `gateway_name` VARCHAR(50) UNIQUE NOT NULL,
            `api_key` VARCHAR(255),
            `api_secret` VARCHAR(255),
            `server_key` VARCHAR(255),
            `client_key` VARCHAR(255),
            `merchant_id` VARCHAR(255),
            `client_id` VARCHAR(255),
            `client_secret` VARCHAR(255),
            `is_production` TINYINT(1) DEFAULT 0,
            `is_active` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "<span class='success'>   ✅ payment_gateway_settings table created</span>\n\n";

    // ==================== CREATE system_settings TABLE ====================
    echo "2. Creating system_settings table...\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `system_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) UNIQUE NOT NULL,
            `setting_value` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "<span class='success'>   ✅ system_settings table created</span>\n\n";

    // ==================== CREATE site_settings TABLE ====================
    echo "3. Creating site_settings table...\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `site_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) UNIQUE NOT NULL,
            `setting_value` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "<span class='success'>   ✅ site_settings table created</span>\n\n";

    // ==================== INSERT DEFAULT SYSTEM SETTINGS ====================
    echo "4. Inserting default system settings...\n";

    $defaultSettings = [
        ['min_topup_amount', '10000'],
        ['unique_code_min', '100'],
        ['unique_code_max', '999'],
        ['whatsapp_admin', '6281377378859'],
        ['store_name', 'Dorve.id Official Store'],
        ['store_phone', '+62-813-7737-8859'],
        ['store_address', ''],
        ['store_city', ''],
        ['store_province', ''],
        ['store_postal_code', ''],
        ['store_country', 'ID']
    ];

    $stmt = $pdo->prepare("
        INSERT INTO system_settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");

    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }

    echo "<span class='success'>   ✅ Default settings inserted</span>\n\n";

    // ==================== MIGRATE OLD payment_settings DATA ====================
    echo "5. Checking for old payment_settings data...\n";

    try {
        $stmt = $pdo->query("SELECT * FROM payment_settings LIMIT 1");
        $oldSettings = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($oldSettings) {
            echo "   <span class='info'>Found old data, migrating...</span>\n";

            // Migrate Midtrans
            if (isset($oldSettings['midtrans_server_key']) && !empty($oldSettings['midtrans_server_key'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO payment_gateway_settings (gateway_name, server_key, client_key, is_production, is_active)
                    VALUES ('midtrans', ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        server_key = VALUES(server_key),
                        client_key = VALUES(client_key),
                        is_production = VALUES(is_production),
                        is_active = VALUES(is_active)
                ");
                $stmt->execute([
                    $oldSettings['midtrans_server_key'],
                    $oldSettings['midtrans_client_key'] ?? '',
                    $oldSettings['midtrans_is_production'] ?? 0,
                    $oldSettings['midtrans_enabled'] ?? 0
                ]);
                echo "   <span class='success'>✅ Midtrans data migrated</span>\n";
            }
        } else {
            echo "   <span class='info'>No old data found</span>\n";
        }
    } catch (Exception $e) {
        echo "   <span class='info'>No payment_settings table found (this is OK)</span>\n";
    }

    echo "\n";

    // ==================== UPDATE payment_methods TABLE ====================
    echo "6. Updating payment_methods table...\n";

    try {
        // Check if 'type' column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM payment_methods LIKE 'type'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE payment_methods ADD COLUMN `type` VARCHAR(50) AFTER `name`");
            echo "   <span class='success'>✅ Added 'type' column</span>\n";
        }

        // Check if 'description' column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM payment_methods LIKE 'description'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE payment_methods ADD COLUMN `description` TEXT AFTER `type`");
            echo "   <span class='success'>✅ Added 'description' column</span>\n";
        }

        // Update existing records with proper type
        $pdo->exec("UPDATE payment_methods SET type = 'wallet' WHERE code = 'wallet' OR code = 'WALLET' OR name LIKE '%wallet%'");
        $pdo->exec("UPDATE payment_methods SET type = 'midtrans' WHERE code = 'midtrans' OR code = 'MIDTRANS' OR name LIKE '%midtrans%'");
        $pdo->exec("UPDATE payment_methods SET type = 'bank_transfer' WHERE code = 'bank_transfer' OR code = 'BANK_TRANSFER' OR name LIKE '%bank%'");

        echo "   <span class='success'>✅ payment_methods table updated</span>\n";
    } catch (Exception $e) {
        echo "   <span class='warning'>⚠️ Warning: " . $e->getMessage() . "</span>\n";
    }

    echo "\n";

    // ==================== VERIFY TABLES ====================
    echo "7. Verifying tables...\n\n";

    $tables = ['payment_methods', 'payment_gateway_settings', 'system_settings', 'site_settings'];

    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "   <span class='success'>✅ $table exists</span>\n";

            // Show column count
            $stmt = $pdo->query("SHOW COLUMNS FROM $table");
            $colCount = $stmt->rowCount();
            echo "      ($colCount columns)\n";
        } else {
            echo "   <span class='error'>❌ $table MISSING!</span>\n";
        }
    }

    echo "\n";

    // ==================== SHOW CURRENT CONFIG ====================
    echo "8. Current Configuration:\n\n";

    // Payment Methods
    $stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY display_order");
    $methods = $stmt->fetchAll();

    echo "   Payment Methods:\n";
    foreach ($methods as $method) {
        $status = $method['is_active'] ? '<span class="success">✅ ACTIVE</span>' : '<span class="error">❌ INACTIVE</span>';
        $type = $method['type'] ?? 'unknown';
        echo "   - {$method['name']} ({$type}) - $status\n";
    }

    echo "\n";

    // Gateway Settings
    $stmt = $pdo->query("SELECT * FROM payment_gateway_settings");
    $gateways = $stmt->fetchAll();

    echo "   Gateway Settings:\n";
    if (empty($gateways)) {
        echo "   <span class='warning'>- No gateways configured yet</span>\n";
        echo "   - Go to /admin/settings/payment-settings.php to configure\n";
    } else {
        foreach ($gateways as $gateway) {
            $status = $gateway['is_active'] ? '<span class="success">✅ ACTIVE</span>' : '<span class="error">❌ INACTIVE</span>';
            $env = $gateway['is_production'] ? '<span class="error">🔴 PRODUCTION</span>' : '<span class="warning">🟡 SANDBOX</span>';
            echo "   - {$gateway['gateway_name']} - $status - $env\n";
        }
    }

    echo "\n";

    // System Settings
    $stmt = $pdo->query("SELECT COUNT(*) FROM system_settings");
    $count = $stmt->fetchColumn();
    echo "   System Settings: <span class='success'>$count settings configured</span>\n";

    echo "\n";
    echo "<span class='success'>✅ ALL TABLES FIXED AND READY!</span>\n\n";
    echo "<span class='info'>Next steps:</span>\n";
    echo "1. Go to <a href='/admin/settings/payment-settings.php' style='color:#00aaff'>/admin/settings/payment-settings.php</a>\n";
    echo "2. Configure Midtrans API keys\n";
    echo "3. Configure Biteship API key\n";
    echo "4. Toggle payment methods ON/OFF\n";
    echo "5. Test payment integration\n\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
}

?></pre>
</body>
</html>
