<?php
/**
 * FIX ALL SETTINGS TABLES
 * Fix site_settings, system_settings, referral_settings
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
    <title>Fix All Settings Tables</title>
    <style>
        body { font-family: monospace; padding: 40px; background: #1a1a1a; color: #00ff00; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #00aaff; }
        .warning { color: #ffaa00; }
        pre { background: #000; padding: 20px; border-radius: 8px; }
        h1, h2 { color: #ffffff; }
        table { border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px 12px; text-align: left; border: 1px solid #444; }
        th { background: #333; color: #fff; }
    </style>
</head>
<body>
    <h1>🔧 FIX ALL SETTINGS TABLES</h1>
    <pre><?php

function fixSettingsTable($pdo, $table_name, $purpose) {
    echo "\n<span class='info'>========================================</span>\n";
    echo "<span class='info'>Processing: $table_name</span>\n";
    echo "<span class='info'>Purpose: $purpose</span>\n";
    echo "<span class='info'>========================================</span>\n\n";

    try {
        // Check if table exists
        echo "Checking if table exists...\n";
        $stmt = $pdo->query("SHOW TABLES LIKE '$table_name'");

        if ($stmt->rowCount() == 0) {
            echo "<span class='warning'>⚠️ Table $table_name does NOT exist!</span>\n";
            echo "<span class='info'>Creating table now...</span>\n";

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `$table_name` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
                    `setting_value` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            echo "<span class='success'>✅ Table created successfully!</span>\n\n";
        } else {
            echo "<span class='success'>✅ Table exists</span>\n\n";
        }

        // Get current structure
        echo "Checking table structure...\n";
        $stmt = $pdo->query("SHOW COLUMNS FROM $table_name");
        $existing_columns = [];
        while ($row = $stmt->fetch()) {
            $existing_columns[] = $row['Field'];
        }

        // Add missing columns
        $columns_to_add = [
            'setting_key' => 'VARCHAR(100) NOT NULL',
            'setting_value' => 'TEXT NULL',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ];

        $added = 0;
        foreach ($columns_to_add as $column => $definition) {
            if (!in_array($column, $existing_columns)) {
                try {
                    $pdo->exec("ALTER TABLE $table_name ADD COLUMN `$column` $definition");
                    echo "<span class='success'>✅ Added: $column</span>\n";
                    $existing_columns[] = $column;
                    $added++;
                } catch (PDOException $e) {
                    echo "<span class='warning'>⚠️ Could not add $column: " . $e->getMessage() . "</span>\n";
                }
            }
        }

        if ($added == 0) {
            echo "<span class='info'>ℹ️ All columns exist</span>\n";
        }

        // Add unique index
        if (in_array('setting_key', $existing_columns)) {
            try {
                $pdo->exec("ALTER TABLE $table_name ADD UNIQUE INDEX `unique_setting_key` (`setting_key`)");
                echo "<span class='success'>✅ Unique index added</span>\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate key name') !== false || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    echo "<span class='info'>ℹ️ Unique index already exists</span>\n";
                }
            }
        }

        // Show current data count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table_name");
        $count = $stmt->fetch()['count'];
        echo "<span class='info'>Current records: $count</span>\n";

        echo "<span class='success'>✅ $table_name is ready!</span>\n";

    } catch (PDOException $e) {
        echo "<span class='error'>❌ ERROR with $table_name: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    }
}

try {
    echo "Starting comprehensive settings tables fix...\n\n";

    // Fix all settings tables
    fixSettingsTable($pdo, 'site_settings', 'General store settings (WhatsApp, currency, etc)');
    fixSettingsTable($pdo, 'system_settings', 'System-wide settings');
    fixSettingsTable($pdo, 'referral_settings', 'Referral program settings');

    echo "\n\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ ALL SETTINGS TABLES FIXED!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    // Show summary
    echo "Summary:\n";
    echo "<span class='info'>====================</span>\n";

    $tables = ['site_settings', 'system_settings', 'referral_settings'];
    echo "<table>";
    echo "<tr><th>Table Name</th><th>Exists</th><th>Has setting_key</th><th>Has setting_value</th><th>Records</th></tr>";

    foreach ($tables as $table) {
        $exists = false;
        $has_key = false;
        $has_value = false;
        $count = 0;

        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->rowCount() > 0;

            if ($exists) {
                $stmt = $pdo->query("SHOW COLUMNS FROM $table");
                $columns = [];
                while ($row = $stmt->fetch()) {
                    $columns[] = $row['Field'];
                }
                $has_key = in_array('setting_key', $columns);
                $has_value = in_array('setting_value', $columns);

                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch()['count'];
            }
        } catch (PDOException $e) {
            // Error checking table
        }

        echo "<tr>";
        echo "<td>$table</td>";
        echo "<td>" . ($exists ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . "</td>";
        echo "<td>" . ($has_key ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . "</td>";
        echo "<td>" . ($has_value ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . "</td>";
        echo "<td>$count</td>";
        echo "</tr>";
    }
    echo "</table>\n";

    echo "\n<span class='info'>What you can do now:</span>\n";
    echo "1. ✅ Save WhatsApp number in General Settings\n";
    echo "2. ✅ Save payment gateway settings\n";
    echo "3. ✅ Save referral program settings\n";
    echo "4. ✅ All settings will work without errors\n\n";

    echo "<span class='info'>Go to:</span>\n";
    echo "- <a href='/admin/settings/index.php' style='color: #00aaff;'>General Settings</a>\n";
    echo "- <a href='/admin/settings/payment-settings.php' style='color: #00aaff;'>Payment Settings</a>\n";
    echo "- <a href='/admin/settings/referral-settings.php' style='color: #00aaff;'>Referral Settings</a>\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ FATAL ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

?></pre>
</body>
</html>
