<?php
/**
 * MIGRATION: Create user_addresses Table
 *
 * Purpose: Store multiple shipping addresses per user for checkout
 */

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Unauthorized - Admin access required');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create User Addresses Table - Migration</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .info { color: #0ff; }
        pre { background: #000; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🚀 User Addresses Table Migration</h1>
    <pre><?php

try {
    echo "Starting migration...\n\n";

    $stmt = $pdo->query("SHOW TABLES LIKE 'user_addresses'");
    $tableExists = $stmt->fetch();

    if ($tableExists) {
        echo "<span class='success'>✅ Table 'user_addresses' already exists!</span>\n";
    } else {
        echo "<span class='info'>📝 Creating table 'user_addresses'...</span>\n";

        $sql = "CREATE TABLE user_addresses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            label VARCHAR(100) NOT NULL COMMENT 'e.g., Home, Office',
            recipient_name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            address TEXT NOT NULL,
            latitude DECIMAL(10, 8) NULL,
            longitude DECIMAL(11, 8) NULL,
            is_default TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_user_id (user_id),
            KEY idx_is_default (is_default)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo->exec($sql);

        echo "<span class='success'>✅ Table created successfully!</span>\n";
    }

    echo "\n";
    echo "<span class='info'>📊 Table structure:</span>\n";
    echo "─────────────────────────────\n";

    $stmt = $pdo->query("DESCRIBE user_addresses");
    while ($row = $stmt->fetch()) {
        $nullable = $row['Null'] === 'YES' ? '(nullable)' : '(NOT NULL)';
        $default = $row['Default'] ? "default: {$row['Default']}" : '';
        echo "• {$row['Field']}: {$row['Type']} $nullable $default\n";
    }

    echo "\n<span class='success'>✅ MIGRATION COMPLETED!</span>\n\n";
    echo "<span class='info'>What this enables:</span>\n";
    echo "1. Multiple shipping addresses per user\n";
    echo "2. Default address selection\n";
    echo "3. GPS coordinates for accurate shipping\n";
    echo "4. Recipient info (name, phone) per address\n";
    echo "5. Seamless checkout with address dropdown\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

?></pre>
    <br>
    <a href="/admin" style="color: #0ff;">← Back to Admin</a>
</body>
</html>
