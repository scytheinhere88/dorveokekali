<?php
/**
 * FIX SITE_SETTINGS TABLE
 * Add missing setting_key and setting_value columns
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
    <title>Fix Settings Table</title>
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
    <h1>🔧 FIX SITE_SETTINGS TABLE</h1>
    <pre><?php

try {
    echo "Starting site_settings table fix...\n\n";

    // STEP 1: Check if table exists
    echo "STEP 1: Checking if table exists...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");

    if ($stmt->rowCount() == 0) {
        echo "<span class='warning'>⚠️ Table site_settings does NOT exist!</span>\n";
        echo "<span class='info'>Creating table now...</span>\n\n";

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `site_settings` (
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

    // STEP 2: Get current structure
    echo "STEP 2: Checking table structure...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM site_settings");
    $existing_columns = [];
    while ($row = $stmt->fetch()) {
        $existing_columns[] = $row['Field'];
    }

    echo "<span class='info'>Current columns:</span>\n";
    foreach ($existing_columns as $col) {
        echo "  - $col\n";
    }
    echo "\n";

    // STEP 3: Add missing columns
    echo "STEP 3: Adding missing columns...\n";

    $columns_to_add = [
        'setting_key' => 'VARCHAR(100) NOT NULL',
        'setting_value' => 'TEXT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];

    $added_count = 0;
    $exists_count = 0;

    foreach ($columns_to_add as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            try {
                $pdo->exec("ALTER TABLE site_settings ADD COLUMN `$column` $definition");
                echo "<span class='success'>✅ Added: $column</span>\n";
                $existing_columns[] = $column;
                $added_count++;
            } catch (PDOException $e) {
                echo "<span class='warning'>⚠️ Could not add $column: " . $e->getMessage() . "</span>\n";
            }
        } else {
            echo "<span class='info'>ℹ️ Exists: $column</span>\n";
            $exists_count++;
        }
    }

    // STEP 4: Add unique index on setting_key if not exists
    echo "\nSTEP 4: Adding unique index on setting_key...\n";
    try {
        $pdo->exec("ALTER TABLE site_settings ADD UNIQUE INDEX `unique_setting_key` (`setting_key`)");
        echo "<span class='success'>✅ Unique index added</span>\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<span class='info'>ℹ️ Unique index already exists</span>\n";
        } else {
            echo "<span class='warning'>⚠️ Could not add unique index: " . $e->getMessage() . "</span>\n";
        }
    }

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ TABLE STRUCTURE FIXED!</span>\n";
    echo "<span class='success'>Added: $added_count columns</span>\n";
    echo "<span class='success'>Already exists: $exists_count columns</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    // STEP 5: Show final structure
    echo "Final Table Structure:\n";
    echo "<span class='info'>====================</span>\n";
    $stmt = $pdo->query("DESCRIBE site_settings");
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $stmt->fetch()) {
        $important = ($row['Field'] === 'setting_key' || $row['Field'] === 'setting_value') ? ' <span class="warning">← CRITICAL</span>' : '';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "$important</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "</tr>";
    }
    echo "</table>\n";

    // STEP 6: Show current settings
    echo "\nCurrent Settings Data:\n";
    echo "<span class='info'>====================</span>\n";
    $stmt = $pdo->query("SELECT * FROM site_settings");
    $data = $stmt->fetchAll();

    if (empty($data)) {
        echo "<span class='warning'>⚠️ No settings saved yet. This is OK.</span>\n";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Setting Key</th><th>Setting Value</th></tr>";
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['setting_key']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($row['setting_value'], 0, 50)) . (strlen($row['setting_value']) > 50 ? '...' : '') . "</td>";
            echo "</tr>";
        }
        echo "</table>\n";
    }

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ ALL DONE!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    echo "<span class='info'>Next steps:</span>\n";
    echo "1. Go to: <a href='/admin/settings/index.php' style='color: #00aaff;'>/admin/settings/index.php</a>\n";
    echo "2. Scroll to 'General Settings'\n";
    echo "3. Enter WhatsApp Admin Number (e.g., 628123456789)\n";
    echo "4. Click 'Save System Settings'\n";
    echo "5. ✅ Should work now!\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    echo "\n<span class='info'>Stack trace:</span>\n";
    echo "<span class='error'>" . htmlspecialchars($e->getTraceAsString()) . "</span>\n";
}

?></pre>
</body>
</html>
