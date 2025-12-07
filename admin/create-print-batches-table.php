<?php
/**
 * MIGRATION: Create print_batches Table
 *
 * Purpose: Track all label printing batches for audit trail and reprint functionality
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
    <title>Create Print Batches Table - Migration</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .info { color: #0ff; }
        pre { background: #000; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🚀 Print Batches Table Migration</h1>
    <pre><?php

try {
    echo "Starting migration...\n\n";

    // Check if table already exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'print_batches'");
    $tableExists = $stmt->fetch();

    if ($tableExists) {
        echo "<span class='success'>✅ Table 'print_batches' already exists!</span>\n";
    } else {
        echo "<span class='info'>📝 Creating table 'print_batches'...</span>\n";

        $sql = "CREATE TABLE print_batches (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            batch_code VARCHAR(50) NOT NULL UNIQUE,
            printed_by_admin_id INT UNSIGNED NOT NULL,
            total_orders INT UNSIGNED NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_batch_code (batch_code),
            KEY idx_printed_by (printed_by_admin_id),
            KEY idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo->exec($sql);

        echo "<span class='success'>✅ Table created successfully!</span>\n";
    }

    echo "\n";
    echo "<span class='info'>📊 Table structure:</span>\n";
    echo "─────────────────────────────\n";

    $stmt = $pdo->query("DESCRIBE print_batches");
    while ($row = $stmt->fetch()) {
        $nullable = $row['Null'] === 'YES' ? '(nullable)' : '(NOT NULL)';
        $default = $row['Default'] ? "default: {$row['Default']}" : '';
        echo "• {$row['Field']}: {$row['Type']} $nullable $default\n";
    }

    echo "\n<span class='success'>✅ MIGRATION COMPLETED!</span>\n\n";
    echo "<span class='info'>What this enables:</span>\n";
    echo "1. Track all print batches with unique codes\n";
    echo "2. Record who printed which batch\n";
    echo "3. Count total orders per batch\n";
    echo "4. Query print history by date/admin\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

?></pre>
    <br>
    <a href="/admin/orders" style="color: #0ff;">← Back to Orders</a>
</body>
</html>
