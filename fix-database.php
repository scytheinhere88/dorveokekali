<?php
require_once __DIR__ . '/config.php';

echo "Adding extra_price column to product_variants...\n";

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM product_variants LIKE 'extra_price'");
    $exists = $stmt->fetch();

    if (!$exists) {
        // Add the column
        $pdo->exec("ALTER TABLE product_variants ADD COLUMN extra_price DECIMAL(10,2) DEFAULT 0 AFTER stock");
        echo "✅ Column extra_price added successfully!\n";
    } else {
        echo "ℹ️  Column extra_price already exists\n";
    }

    echo "\n✅ Database fix complete!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
