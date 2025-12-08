<?php
/**
 * FIX WALLET TRANSACTIONS TABLE
 * Add missing proof_image column
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
    <title>Fix Wallet Transactions Table</title>
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
    <h1>🔧 FIX WALLET TRANSACTIONS TABLE</h1>
    <pre><?php

try {
    echo "Starting wallet_transactions table fix...\n\n";

    // STEP 1: Check if table exists
    echo "STEP 1: Checking if table exists...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'wallet_transactions'");
    if ($stmt->rowCount() == 0) {
        echo "<span class='error'>❌ Table wallet_transactions does NOT exist!</span>\n";
        echo "<span class='info'>Please run /admin/fix-tables.php first</span>\n";
        exit;
    }
    echo "<span class='success'>✅ Table exists</span>\n\n";

    // STEP 2: Check existing columns
    echo "STEP 2: Checking table structure...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM wallet_transactions");
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
        'proof_image' => 'VARCHAR(255) NULL AFTER reference_id',
        'admin_notes' => 'TEXT NULL AFTER proof_image'
    ];

    foreach ($columns_to_add as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            try {
                $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN `$column` $definition");
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

    // STEP 4: Show final structure
    echo "Final Table Structure:\n";
    echo "<span class='info'>====================</span>\n";
    $stmt = $pdo->query("DESCRIBE wallet_transactions");
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $stmt->fetch()) {
        $important = $row['Field'] === 'proof_image' ? ' <span class="warning">← NEEDED FOR UPLOAD</span>' : '';
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "$important</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "</tr>";
    }
    echo "</table>\n";

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ ALL DONE!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    echo "<span class='info'>Next steps:</span>\n";
    echo "1. Upload bukti transfer should work now!\n";
    echo "2. Test by creating a topup and uploading proof\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    echo "\n<span class='info'>Stack trace:</span>\n";
    echo "<span class='error'>" . htmlspecialchars($e->getTraceAsString()) . "</span>\n";
}

?></pre>
</body>
</html>
