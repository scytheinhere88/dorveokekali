<?php
/**
 * VERIFY PAYMENT GATEWAY SETTINGS TABLE
 * Check table structure and fix if needed
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
    <title>Verify Payment Gateway Table</title>
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
    <h1>🔍 VERIFY PAYMENT GATEWAY TABLE</h1>
    <pre><?php

try {
    echo "Checking payment_gateway_settings table...\n\n";

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'payment_gateway_settings'");
    if ($stmt->rowCount() == 0) {
        echo "<span class='error'>❌ Table payment_gateway_settings does NOT exist!</span>\n";
        echo "<span class='info'>Creating table now...</span>\n\n";

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

        echo "<span class='success'>✅ Table created successfully!</span>\n\n";
    } else {
        echo "<span class='success'>✅ Table exists</span>\n\n";
    }

    // Show table structure
    echo "Table Structure:\n";
    echo "<span class='info'>====================</span>\n";

    $stmt = $pdo->query("DESCRIBE payment_gateway_settings");
    $columns = $stmt->fetchAll();

    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>\n";

    // Check for required columns
    echo "\nChecking Required Columns:\n";
    echo "<span class='info'>====================</span>\n";

    $required_columns = ['gateway_name', 'server_key', 'client_key', 'merchant_id', 'is_production', 'is_active'];
    $existing_columns = array_column($columns, 'Field');

    foreach ($required_columns as $col) {
        if (in_array($col, $existing_columns)) {
            echo "<span class='success'>✅ $col exists</span>\n";
        } else {
            echo "<span class='error'>❌ $col MISSING!</span>\n";
        }
    }

    // Show current data
    echo "\n\nCurrent Data:\n";
    echo "<span class='info'>====================</span>\n";

    $stmt = $pdo->query("SELECT * FROM payment_gateway_settings");
    $data = $stmt->fetchAll();

    if (empty($data)) {
        echo "<span class='warning'>⚠️ No data yet. This is OK - you can add settings from admin panel.</span>\n";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Gateway</th><th>Server Key</th><th>Client Key</th><th>Merchant ID</th><th>Production</th><th>Active</th></tr>";
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['gateway_name']) . "</td>";
            echo "<td>" . (empty($row['server_key']) ? '<span class="error">Not set</span>' : '<span class="success">Set (' . strlen($row['server_key']) . ' chars)</span>') . "</td>";
            echo "<td>" . (empty($row['client_key']) ? '<span class="error">Not set</span>' : '<span class="success">Set (' . strlen($row['client_key']) . ' chars)</span>') . "</td>";
            echo "<td>" . htmlspecialchars($row['merchant_id'] ?? 'Not set') . "</td>";
            echo "<td>" . ($row['is_production'] ? '<span class="warning">PRODUCTION</span>' : '<span class="info">Sandbox</span>') . "</td>";
            echo "<td>" . ($row['is_active'] ? '<span class="success">Active</span>' : '<span class="error">Inactive</span>') . "</td>";
            echo "</tr>";
        }
        echo "</table>\n";
    }

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ VERIFICATION COMPLETE!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    echo "<span class='info'>You can now go to:</span>\n";
    echo "<a href='/admin/settings/payment-settings.php' style='color: #00aaff;'>/admin/settings/payment-settings.php</a>\n";
    echo "<span class='info'>to configure your Midtrans settings.</span>\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    echo "\n<span class='info'>Stack trace:</span>\n";
    echo "<span class='error'>" . htmlspecialchars($e->getTraceAsString()) . "</span>\n";
}

?></pre>
</body>
</html>
