<?php
/**
 * Temporary Setup Runner
 * Executes normalization and Biteship setup scripts
 * DELETE THIS FILE AFTER SETUP IS COMPLETE
 */

require_once __DIR__ . '/../config.php';

if (!isAdmin()) {
    die('Unauthorized');
}

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Setup Runner</title>";
echo "<style>
body { font-family: 'Segoe UI', Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 30px; background: #F5F7FA; }
h1 { color: #1F2937; font-size: 28px; margin-bottom: 10px; }
.subtitle { color: #6B7280; font-size: 14px; margin-bottom: 30px; }
.step { background: white; padding: 25px; border-radius: 12px; margin: 20px 0; border: 2px solid #E5E7EB; }
.step h2 { color: #374151; font-size: 20px; margin: 0 0 15px; }
.success { color: #10B981; font-weight: 600; }
.error { color: #EF4444; font-weight: 600; }
.info { background: #DBEAFE; padding: 15px; border-radius: 8px; border-left: 4px solid #3B82F6; margin: 15px 0; }
.btn { display: inline-block; padding: 12px 24px; background: #3B82F6; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 20px; }
.btn:hover { background: #2563EB; }
</style></head><body>";

echo "<h1>🔧 Dorve.id Setup Runner</h1>";
echo "<p class='subtitle'>Menjalankan script normalisasi database dan setup Biteship integration</p>";

// STEP 1: Normalize Settings Table
echo "<div class='step'>";
echo "<h2>📋 Step 1: Normalizing Settings Table</h2>";

try {
    $stmt = $pdo->query("DESCRIBE settings");
    $columns = array_column($stmt->fetchAll(), 'Field');
    
    echo "<p>Current columns: " . implode(', ', $columns) . "</p>";
    
    if (in_array('value', $columns) && !in_array('setting_value', $columns)) {
        echo "<p>Migrating 'value' → 'setting_value'...</p>";
        $pdo->exec("ALTER TABLE settings CHANGE COLUMN `value` `setting_value` TEXT");
        echo "<p class='success'>✓ Column renamed successfully</p>";
        
    } elseif (in_array('setting_value', $columns) && in_array('value', $columns)) {
        echo "<p>Both columns exist. Merging data...</p>";
        $pdo->exec("UPDATE settings SET setting_value = `value` WHERE setting_value IS NULL OR setting_value = ''");
        echo "<p class='success'>✓ Data merged</p>";
        
        $pdo->exec("ALTER TABLE settings DROP COLUMN `value`");
        echo "<p class='success'>✓ Old 'value' column dropped</p>";
        
    } elseif (in_array('setting_value', $columns)) {
        echo "<p class='success'>✓ Already using 'setting_value'. No migration needed.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// STEP 2: Run Biteship Setup
echo "<div class='step'>";
echo "<h2>🚚 Step 2: Setting up Biteship Integration</h2>";

try {
    // Update orders table
    $stmt = $pdo->query("DESCRIBE orders");
    $orderColumns = array_column($stmt->fetchAll(), 'Field');
    
    $columnsToAdd = [
        'fulfillment_status' => "ALTER TABLE orders ADD COLUMN fulfillment_status ENUM('new', 'waiting_print', 'waiting_pickup', 'in_transit', 'delivered', 'cancelled', 'returned') DEFAULT 'new' AFTER payment_status",
        'shipping_courier' => "ALTER TABLE orders ADD COLUMN shipping_courier VARCHAR(100) NULL AFTER fulfillment_status",
        'shipping_service' => "ALTER TABLE orders ADD COLUMN shipping_service VARCHAR(100) NULL AFTER shipping_courier",
        'shipping_cost' => "ALTER TABLE orders ADD COLUMN shipping_cost DECIMAL(15,2) DEFAULT 0 AFTER shipping_service",
        'tracking_number' => "ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(255) NULL AFTER shipping_cost",
        'notes' => "ALTER TABLE orders ADD COLUMN notes TEXT NULL AFTER tracking_number"
    ];
    
    foreach ($columnsToAdd as $colName => $sql) {
        if (!in_array($colName, $orderColumns)) {
            $pdo->exec($sql);
            echo "<p class='success'>✓ Added '$colName' to orders table</p>";
        }
    }
    
    if (!in_array('tracking_number', $orderColumns)) {
        $pdo->exec("ALTER TABLE orders ADD INDEX idx_tracking (tracking_number)");
    }
    
    // Create tables
    $tables = [
        'order_addresses' => "CREATE TABLE IF NOT EXISTS order_addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            type ENUM('billing', 'shipping') NOT NULL,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            address_line TEXT NOT NULL,
            district VARCHAR(255),
            city VARCHAR(255) NOT NULL,
            province VARCHAR(255) NOT NULL,
            postal_code VARCHAR(20) NOT NULL,
            country VARCHAR(5) DEFAULT 'ID',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order (order_id),
            INDEX idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'biteship_shipments' => "CREATE TABLE IF NOT EXISTS biteship_shipments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            biteship_order_id VARCHAR(255) NOT NULL UNIQUE,
            courier_company VARCHAR(100) NOT NULL,
            courier_name VARCHAR(100) NOT NULL,
            courier_service_name VARCHAR(100) NOT NULL,
            courier_service_code VARCHAR(100) NULL,
            rate_id VARCHAR(255) NULL,
            shipping_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
            insurance_cost DECIMAL(15,2) DEFAULT 0,
            status VARCHAR(50) DEFAULT 'pending',
            waybill_id VARCHAR(255) NULL,
            label_print_batch_id INT NULL,
            pickup_code VARCHAR(50) NULL,
            delivery_date DATETIME NULL,
            pickup_time VARCHAR(100) NULL,
            destination_province VARCHAR(255),
            destination_city VARCHAR(255),
            destination_postal_code VARCHAR(20),
            origin_province VARCHAR(255),
            origin_city VARCHAR(255),
            origin_postal_code VARCHAR(20),
            weight_kg DECIMAL(10,2) DEFAULT 0,
            raw_response TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_order (order_id),
            INDEX idx_biteship_id (biteship_order_id),
            INDEX idx_waybill (waybill_id),
            INDEX idx_status (status),
            INDEX idx_batch (label_print_batch_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'biteship_webhook_logs' => "CREATE TABLE IF NOT EXISTS biteship_webhook_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event VARCHAR(100) NOT NULL,
            biteship_order_id VARCHAR(255) NULL,
            payload TEXT NOT NULL,
            processed TINYINT(1) DEFAULT 0,
            error_message TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event (event),
            INDEX idx_biteship_id (biteship_order_id),
            INDEX idx_processed (processed)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'print_batches' => "CREATE TABLE IF NOT EXISTS print_batches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            batch_code VARCHAR(50) NOT NULL UNIQUE,
            printed_by_admin_id INT NOT NULL,
            printed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            total_orders INT DEFAULT 0,
            notes TEXT NULL,
            INDEX idx_batch_code (batch_code),
            INDEX idx_admin (printed_by_admin_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables as $tableName => $sql) {
        $pdo->exec($sql);
        echo "<p class='success'>✓ Table '$tableName' ready</p>";
    }
    
    // Insert/Update Biteship settings with USER'S API KEY
    $settings = [
        'biteship_enabled' => '1',
        'biteship_api_key' => 'biteship_live.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiRG9ydmUuaWQiLCJ1c2VySWQiOiI2OTI4NDVhNDM4MzQ5ZjAyZjdhM2VhNDgiLCJpYXQiOjE3NjQ2NTYwMjV9.xmkeeT2ghfHPe7PItX5HJ0KptlC5xbIhL1TlHWn6S1U',
        'biteship_environment' => 'production',
        'biteship_webhook_secret' => '',
        'biteship_default_couriers' => 'jne,jnt,sicepat,anteraja,idexpress',
        'store_name' => 'Dorve.id Official Store',
        'store_phone' => '+62-813-7737-8859',
        'store_address' => 'Jakarta, Indonesia',
        'store_city' => 'Jakarta Selatan',
        'store_province' => 'DKI Jakarta',
        'store_postal_code' => '12345',
        'store_country' => 'ID'
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, $value]);
    }
    echo "<p class='success'>✓ Biteship settings configured</p>";
    
    echo "<div class='info'>";
    echo "<p style='margin: 0 0 10px; font-weight: 600; color: #1E40AF;'>📋 Webhook URL untuk Biteship Dashboard:</p>";
    echo "<code style='background: white; padding: 8px 12px; display: block; border-radius: 6px; font-size: 13px;'>https://dorve.id/api/biteship/webhook.php</code>";
    echo "<p style='margin: 10px 0 0; font-size: 13px; color: #1E40AF;'>Copy URL ini ke: <strong>Biteship Dashboard → Settings → Webhooks</strong></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Summary
echo "<div class='step' style='background: #F0FDF4; border-color: #10B981;'>";
echo "<h2 style='color: #10B981;'>✅ Setup Complete!</h2>";
echo "<p>Database berhasil dinormalisasi dan integrasi Biteship siap digunakan.</p>";
echo "<p style='margin-top: 20px;'><strong>Next Steps:</strong></p>";
echo "<ol style='line-height: 2;'>";
echo "<li>Verifikasi settings di <a href='/admin/settings/api-settings.php' class='btn' style='display: inline; padding: 4px 12px; font-size: 14px;'>API Settings</a></li>";
echo "<li>Test koneksi Biteship dari admin panel</li>";
echo "<li>Configure webhook di Biteship Dashboard</li>";
echo "<li><strong style='color: #DC2626;'>DELETE FILE INI (/admin/run-setup.php) setelah selesai!</strong></li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
