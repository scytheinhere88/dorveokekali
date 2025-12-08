<?php
/**
 * SETUP BITESHIP INTEGRATION
 * Configure Biteship API key and store settings
 */
require_once __DIR__ . '/../config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

$biteship_api_key = 'biteship_live.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiRG9ydmUuaWQiLCJ1c2VySWQiOiI2OTI4NDVhNDM4MzQ5ZjAyZjdhM2VhNDgiLCJpYXQiOjE3NjQ2NTYwMjV9.xmkeeT2ghfHPe7PItX5HJ0KptlC5xbIhL1TlHWn6S1U';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Biteship Integration</title>
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
    <h1>🚀 SETUP BITESHIP INTEGRATION</h1>
    <pre><?php

try {
    echo "Starting Biteship integration setup...\n\n";

    // STEP 1: Check/Create payment_gateway_settings table
    echo "STEP 1: Setting up payment_gateway_settings table...\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `payment_gateway_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `gateway_name` VARCHAR(50) NOT NULL UNIQUE,
            `api_key` TEXT,
            `api_secret` TEXT,
            `merchant_id` VARCHAR(100),
            `client_id` TEXT,
            `client_secret` TEXT,
            `server_key` TEXT,
            `client_key` TEXT,
            `is_production` TINYINT(1) DEFAULT 0,
            `is_active` TINYINT(1) DEFAULT 1,
            `webhook_url` VARCHAR(255),
            `webhook_secret` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "<span class='success'>✅ payment_gateway_settings table ready</span>\n\n";

    // STEP 2: Insert/Update Biteship API Key
    echo "STEP 2: Configuring Biteship API Key...\n";

    $stmt = $pdo->prepare("SELECT id FROM payment_gateway_settings WHERE gateway_name = 'biteship'");
    $stmt->execute();
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("
            UPDATE payment_gateway_settings
            SET api_key = ?,
                is_production = 1,
                is_active = 1,
                updated_at = NOW()
            WHERE gateway_name = 'biteship'
        ");
        $stmt->execute([$biteship_api_key]);
        echo "<span class='success'>✅ Biteship API key UPDATED</span>\n";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO payment_gateway_settings
            (gateway_name, api_key, is_production, is_active)
            VALUES ('biteship', ?, 1, 1)
        ");
        $stmt->execute([$biteship_api_key]);
        echo "<span class='success'>✅ Biteship API key INSERTED</span>\n";
    }

    echo "<span class='info'>API Key: " . substr($biteship_api_key, 0, 30) . "...</span>\n";
    echo "<span class='info'>Environment: PRODUCTION (Live)</span>\n";
    echo "<span class='info'>Status: ACTIVE</span>\n\n";

    // STEP 3: Setup system_settings table
    echo "STEP 3: Setting up system_settings table...\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `system_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) UNIQUE NOT NULL,
            `setting_value` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "<span class='success'>✅ system_settings table ready</span>\n\n";

    // STEP 4: Configure store settings
    echo "STEP 4: Configuring store settings...\n";

    $store_settings = [
        'store_name' => 'Dorve.id Official Store',
        'store_phone' => '+62-813-7737-8859',
        'store_address' => 'Jalan Raya Store No. 123',
        'store_city' => 'Jakarta',
        'store_province' => 'DKI Jakarta',
        'store_postal_code' => '12190',
        'store_country' => 'ID',
        'biteship_environment' => 'production',
        'biteship_default_couriers' => 'jne,jnt,sicepat,anteraja,idexpress,ninja'
    ];

    foreach ($store_settings as $key => $value) {
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()
        ");
        $stmt->execute([$key, $value, $value]);
        echo "<span class='success'>✅ $key = $value</span>\n";
    }

    echo "\n";

    // STEP 5: Test Biteship API Connection
    echo "STEP 5: Testing Biteship API Connection...\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.biteship.com/v1/maps/areas?countries=ID&input=Jakarta&type=single');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $biteship_api_key,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "<span class='success'>✅ API Connection: SUCCESS</span>\n";
        echo "<span class='info'>HTTP Code: 200 OK</span>\n";
        $data = json_decode($response, true);
        if (isset($data['areas']) && count($data['areas']) > 0) {
            echo "<span class='success'>✅ API Response: Valid (" . count($data['areas']) . " areas found)</span>\n";
        }
    } else {
        echo "<span class='error'>❌ API Connection: FAILED</span>\n";
        echo "<span class='error'>HTTP Code: $httpCode</span>\n";
        echo "<span class='warning'>Response: " . substr($response, 0, 200) . "</span>\n";
    }

    echo "\n";

    // STEP 6: Display configuration summary
    echo "<span class='info'>========================================</span>\n";
    echo "<span class='success'>✅ BITESHIP INTEGRATION COMPLETE!</span>\n";
    echo "<span class='info'>========================================</span>\n\n";

    echo "Configuration Summary:\n";
    echo "----------------------\n";
    echo "Gateway Name:    biteship\n";
    echo "API Key:         " . substr($biteship_api_key, 0, 40) . "...\n";
    echo "Environment:     PRODUCTION (Live)\n";
    echo "Status:          ACTIVE\n";
    echo "Store Postal:    12190 (Jakarta)\n";
    echo "Store City:      Jakarta\n";
    echo "Couriers:        JNE, JNT, SiCepat, AnterAja, ID Express, Ninja\n\n";

    echo "<span class='info'>What's been configured:</span>\n";
    echo "1. ✅ payment_gateway_settings table created\n";
    echo "2. ✅ Biteship API key configured (LIVE)\n";
    echo "3. ✅ system_settings table created\n";
    echo "4. ✅ Store settings configured\n";
    echo "5. ✅ Default couriers set\n";
    echo "6. ✅ API connection tested\n\n";

    echo "<span class='info'>Next steps:</span>\n";
    echo "1. Go to checkout page\n";
    echo "2. Select shipping address\n";
    echo "3. ✅ Shipping methods should appear!\n\n";

    echo "<span class='warning'>IMPORTANT: If store postal code is wrong, update in:</span>\n";
    echo "- /admin/settings/payment-settings.php\n";
    echo "- Change 'store_postal_code' to your actual store postal code\n\n";

    // STEP 7: Show current gateway settings
    echo "STEP 7: Current gateway settings in database:\n";
    $stmt = $pdo->query("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'biteship'");
    $biteship_config = $stmt->fetch();

    if ($biteship_config) {
        echo "<span class='success'>Gateway: {$biteship_config['gateway_name']}</span>\n";
        echo "<span class='success'>API Key: " . substr($biteship_config['api_key'], 0, 40) . "...</span>\n";
        echo "<span class='success'>Production: " . ($biteship_config['is_production'] ? 'YES' : 'NO') . "</span>\n";
        echo "<span class='success'>Active: " . ($biteship_config['is_active'] ? 'YES' : 'NO') . "</span>\n";
    }

} catch (PDOException $e) {
    echo "<span class='error'>❌ DATABASE ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
} catch (Exception $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

?></pre>
</body>
</html>
