<?php
/**
 * FIX DATABASE TABLES
 * Creates missing tables and fixes column names
 */
require_once __DIR__ . '/config.php';

echo "🔧 FIXING DATABASE TABLES...\n\n";

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

    echo "   ✅ payment_gateway_settings table created\n\n";

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

    echo "   ✅ system_settings table created\n\n";

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

    echo "   ✅ site_settings table created\n\n";

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

    echo "   ✅ Default settings inserted\n\n";

    // ==================== MIGRATE OLD payment_settings DATA ====================
    echo "5. Checking for old payment_settings data...\n";

    try {
        $stmt = $pdo->query("SELECT * FROM payment_settings LIMIT 1");
        $oldSettings = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($oldSettings) {
            echo "   Found old data, migrating...\n";

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
                echo "   ✅ Midtrans data migrated\n";
            }
        } else {
            echo "   No old data found\n";
        }
    } catch (Exception $e) {
        echo "   No payment_settings table found (this is OK)\n";
    }

    echo "\n";

    // ==================== UPDATE payment_methods TABLE ====================
    echo "6. Updating payment_methods table...\n";

    try {
        // Check if 'type' column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM payment_methods LIKE 'type'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE payment_methods ADD COLUMN `type` VARCHAR(50) AFTER `name`");
            echo "   ✅ Added 'type' column\n";
        }

        // Check if 'description' column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM payment_methods LIKE 'description'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE payment_methods ADD COLUMN `description` TEXT AFTER `type`");
            echo "   ✅ Added 'description' column\n";
        }

        // Update existing records with proper type
        $pdo->exec("UPDATE payment_methods SET type = 'wallet' WHERE name LIKE '%wallet%' OR name LIKE '%Dorve%'");
        $pdo->exec("UPDATE payment_methods SET type = 'midtrans' WHERE name LIKE '%midtrans%' OR name LIKE '%QRIS%' OR name LIKE '%Credit Card%'");
        $pdo->exec("UPDATE payment_methods SET type = 'bank_transfer' WHERE name LIKE '%bank%' OR name LIKE '%transfer%'");
        $pdo->exec("UPDATE payment_methods SET type = 'paypal' WHERE name LIKE '%paypal%'");

        echo "   ✅ payment_methods table updated\n";
    } catch (Exception $e) {
        echo "   ⚠️ Warning: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // ==================== VERIFY TABLES ====================
    echo "7. Verifying tables...\n\n";

    $tables = ['payment_methods', 'payment_gateway_settings', 'system_settings', 'site_settings'];

    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ $table exists\n";

            // Show column count
            $stmt = $pdo->query("SHOW COLUMNS FROM $table");
            $colCount = $stmt->rowCount();
            echo "      ($colCount columns)\n";
        } else {
            echo "   ❌ $table MISSING!\n";
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
        $status = $method['is_active'] ? '✅ ACTIVE' : '❌ INACTIVE';
        $type = $method['type'] ?? 'unknown';
        echo "   - {$method['name']} ({$type}) - $status\n";
    }

    echo "\n";

    // Gateway Settings
    $stmt = $pdo->query("SELECT * FROM payment_gateway_settings");
    $gateways = $stmt->fetchAll();

    echo "   Gateway Settings:\n";
    if (empty($gateways)) {
        echo "   - No gateways configured yet\n";
        echo "   - Go to /admin/settings/payment-settings.php to configure\n";
    } else {
        foreach ($gateways as $gateway) {
            $status = $gateway['is_active'] ? '✅ ACTIVE' : '❌ INACTIVE';
            $env = $gateway['is_production'] ? '🔴 PRODUCTION' : '🟡 SANDBOX';
            echo "   - {$gateway['gateway_name']} - $status - $env\n";
        }
    }

    echo "\n";

    // System Settings
    $stmt = $pdo->query("SELECT COUNT(*) FROM system_settings");
    $count = $stmt->fetchColumn();
    echo "   System Settings: $count settings configured\n";

    echo "\n";
    echo "✅ ALL TABLES FIXED AND READY!\n\n";
    echo "Next steps:\n";
    echo "1. Go to /admin/settings/payment-settings.php\n";
    echo "2. Configure Midtrans API keys\n";
    echo "3. Configure Biteship API key\n";
    echo "4. Toggle payment methods ON/OFF\n";
    echo "5. Test payment integration\n\n";

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
