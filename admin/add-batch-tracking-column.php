<?php
/**
 * MIGRATION: Add Batch Tracking Column to biteship_shipments
 *
 * Purpose: Add label_print_batch_id column for tracking which batch a label was printed in
 * This enables audit trail and reprint management for long-term operations
 */

require_once __DIR__ . '/../config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Unauthorized - Admin access required');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Batch Tracking - Migration</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .info { color: #0ff; }
        pre { background: #000; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🚀 Batch Tracking Migration</h1>
    <pre><?php

try {
    echo "Starting migration...\n\n";

    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM biteship_shipments LIKE 'label_print_batch_id'");
    $columnExists = $stmt->fetch();

    if ($columnExists) {
        echo "<span class='success'>✅ Column 'label_print_batch_id' already exists!</span>\n";
    } else {
        echo "<span class='info'>📝 Adding column 'label_print_batch_id' to biteship_shipments...</span>\n";

        $sql = "ALTER TABLE biteship_shipments
                ADD COLUMN label_print_batch_id INT UNSIGNED NULL DEFAULT NULL,
                ADD KEY idx_label_print_batch (label_print_batch_id)";

        $pdo->exec($sql);

        echo "<span class='success'>✅ Column added successfully!</span>\n";
    }

    echo "\n";
    echo "<span class='info'>📊 Current table structure:</span>\n";
    echo "─────────────────────────────\n";

    $stmt = $pdo->query("DESCRIBE biteship_shipments");
    while ($row = $stmt->fetch()) {
        $nullable = $row['Null'] === 'YES' ? '(nullable)' : '(NOT NULL)';
        $default = $row['Default'] ? "default: {$row['Default']}" : '';
        echo "• {$row['Field']}: {$row['Type']} $nullable $default\n";
    }

    echo "\n<span class='success'>✅ MIGRATION COMPLETED!</span>\n\n";
    echo "<span class='info'>Next steps:</span>\n";
    echo "1. Code will automatically track batches when printing\n";
    echo "2. You can query print history by batch_id\n";
    echo "3. Reprint specific batches when needed\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

?></pre>
    <br>
    <a href="/admin/orders" style="color: #0ff;">← Back to Orders</a>
</body>
</html>
