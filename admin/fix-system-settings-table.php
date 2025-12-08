<?php
/**
 * FIX SYSTEM_SETTINGS TABLE
 * Fix key_name -> setting_key column name mismatch
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
    <title>Fix System Settings Table</title>
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
    <h1>🔧 FIX SYSTEM_SETTINGS TABLE</h1>
    <pre><?php

try {
    echo "Starting system_settings table fix...\n\n";

    // STEP 1: Check if table exists
    echo "STEP 1: Checking if table exists...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_settings'");

    if ($stmt->rowCount() == 0) {
        echo "<span class='warning'>⚠️ Table system_settings does NOT exist!</span>\n";
        echo "<span class='info'>Creating table with correct structure...</span>\n\n";

        $pdo->exec("
            CREATE TABLE `system_settings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `setting_key` VARCHAR(100) UNIQUE NOT NULL,
                `setting_value` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        echo "<span class='success'>✅ Table created successfully!</span>\n\n";
        echo "<span class='info'>Next: Go to /admin/settings/payment-settings.php and save WhatsApp number</span>\n";
        exit;
    } else {
        echo "<span class='success'>✅ Table exists</span>\n\n";
    }

    // STEP 2: Check current columns
    echo "STEP 2: Checking table columns...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM system_settings");
    $columns = [];
    while ($row = $stmt->fetch()) {
        $columns[$row['Field']] = $row;
    }

    echo "<span class='info'>Current columns:</span>\n";
    foreach ($columns as $name => $info) {
        echo "  - $name ({$info['Type']})\n";
    }
    echo "\n";

    // STEP 3: Check for key_name column (OLD name)
    echo "STEP 3: Checking for column name mismatch...\n";

    $has_key_name = isset($columns['key_name']);
    $has_setting_key = isset($columns['setting_key']);

    if ($has_key_name && !$has_setting_key) {
        echo "<span class='warning'>⚠️ Found OLD column name: 'key_name'</span>\n";
        echo "<span class='info'>Need to rename to: 'setting_key'</span>\n\n";

        // Rename column
        echo "Renaming column: key_name → setting_key...\n";
        $pdo->exec("ALTER TABLE system_settings CHANGE COLUMN `key_name` `setting_key` VARCHAR(100) NOT NULL");
        echo "<span class='success'>✅ Column renamed successfully!</span>\n\n";

        // Check if unique index exists on setting_key
        echo "Adding unique index on setting_key...\n";
        try {
            $pdo->exec("ALTER TABLE system_settings ADD UNIQUE INDEX `unique_setting_key` (`setting_key`)");
            echo "<span class='success'>✅ Unique index added</span>\n\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "<span class='info'>ℹ️ Unique index already exists</span>\n\n";
            } else {
                echo "<span class='warning'>⚠️ Could not add unique index: " . $e->getMessage() . "</span>\n\n";
            }
        }

    } elseif (!$has_key_name && $has_setting_key) {
        echo "<span class='success'>✅ Column 'setting_key' already exists (correct!)</span>\n\n";
    } elseif ($has_key_name && $has_setting_key) {
        echo "<span class='warning'>⚠️ Both 'key_name' and 'setting_key' exist!</span>\n";
        echo "<span class='info'>This is unusual. Dropping 'key_name' column...</span>\n\n";

        $pdo->exec("ALTER TABLE system_settings DROP COLUMN `key_name`");
        echo "<span class='success'>✅ Dropped 'key_name' column</span>\n\n";
    } else {
        echo "<span class='error'>❌ Neither 'key_name' nor 'setting_key' exists!</span>\n";
        echo "<span class='info'>Adding 'setting_key' column...</span>\n\n";

        $pdo->exec("ALTER TABLE system_settings ADD COLUMN `setting_key` VARCHAR(100) NOT NULL AFTER `id`");
        $pdo->exec("ALTER TABLE system_settings ADD UNIQUE INDEX `unique_setting_key` (`setting_key`)");
        echo "<span class='success'>✅ Column added successfully!</span>\n\n";
    }

    // STEP 4: Ensure setting_value column exists
    echo "STEP 4: Checking for 'setting_value' column...\n";
    if (!isset($columns['setting_value'])) {
        echo "<span class='warning'>⚠️ Column 'setting_value' missing!</span>\n";
        echo "<span class='info'>Adding column...</span>\n\n";

        $pdo->exec("ALTER TABLE system_settings ADD COLUMN `setting_value` TEXT NULL");
        echo "<span class='success'>✅ Column added successfully!</span>\n\n";
    } else {
        echo "<span class='success'>✅ Column 'setting_value' exists</span>\n\n";
    }

    // STEP 5: Ensure timestamp columns exist
    echo "STEP 5: Checking timestamp columns...\n";

    if (!isset($columns['created_at'])) {
        $pdo->exec("ALTER TABLE system_settings ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "<span class='success'>✅ Added 'created_at' column</span>\n";
    }

    if (!isset($columns['updated_at'])) {
        $pdo->exec("ALTER TABLE system_settings ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "<span class='success'>✅ Added 'updated_at' column</span>\n";
    }

    echo "\n";

    // STEP 6: Show final structure
    echo "STEP 6: Final table structure:\n";
    echo "<span class='info'>====================</span>\n";
    $stmt = $pdo->query("DESCRIBE system_settings");
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch()) {
        $highlight = ($row['Field'] === 'setting_key' || $row['Field'] === 'setting_value') ? ' style="background: #004400;"' : '';
        echo "<tr$highlight>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>\n\n";

    // STEP 7: Show current data
    echo "STEP 7: Current data in table:\n";
    echo "<span class='info'>====================</span>\n";
    $stmt = $pdo->query("SELECT * FROM system_settings");
    $data = $stmt->fetchAll();

    if (empty($data)) {
        echo "<span class='warning'>⚠️ No data in table yet. This is OK.</span>\n\n";
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
        echo "</table>\n\n";
    }

    echo "<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ ALL DONE!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    echo "<span class='info'>What you fixed:</span>\n";
    echo "1. ✅ Table structure verified\n";
    echo "2. ✅ Column 'key_name' renamed to 'setting_key' (if needed)\n";
    echo "3. ✅ Column 'setting_value' verified\n";
    echo "4. ✅ Unique index on 'setting_key'\n";
    echo "5. ✅ Timestamp columns added\n\n";

    echo "<span class='info'>Next steps:</span>\n";
    echo "1. Go to: <a href='/admin/settings/payment-settings.php' style='color: #00aaff;'>/admin/settings/payment-settings.php</a>\n";
    echo "2. Scroll to WhatsApp settings section\n";
    echo "3. Enter WhatsApp Admin Number\n";
    echo "4. Click Save\n";
    echo "5. ✅ Should work now!\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    echo "\n<span class='info'>Stack trace:</span>\n";
    echo "<span class='error'>" . htmlspecialchars($e->getTraceAsString()) . "</span>\n";
}

?></pre>
</body>
</html>
