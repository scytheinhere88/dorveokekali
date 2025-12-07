<?php
/**
 * SQL Migration: Add missing columns to products table
 * Run this ONCE to fix the database structure
 */
require_once __DIR__ . '/../config.php';

if (!isAdmin()) {
    die('Access denied');
}

echo "<h2>Products Table Migration</h2>";
echo "<pre>";

try {
    // Check current table structure
    $stmt = $pdo->query("DESCRIBE products");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Current columns:\n";
    print_r($columns);
    echo "\n";

    $changes = [];

    // Add description column if not exists
    if (!in_array('description', $columns)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT NULL AFTER name");
        $changes[] = "✓ Added 'description' column";
    } else {
        echo "✓ 'description' column already exists\n";
    }

    // Add discount_percent column if not exists
    if (!in_array('discount_percent', $columns)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN discount_percent DECIMAL(5,2) DEFAULT 0 AFTER price");
        $changes[] = "✓ Added 'discount_percent' column";
    } else {
        echo "✓ 'discount_percent' column already exists\n";
    }

    // Convert existing discount_price to discount_percent if discount_price exists
    if (in_array('discount_price', $columns) && in_array('discount_percent', $columns)) {
        // Calculate discount_percent from discount_price for existing products
        $pdo->exec("
            UPDATE products
            SET discount_percent = ROUND(((price - discount_price) / price) * 100, 2)
            WHERE discount_price IS NOT NULL AND discount_price > 0 AND price > 0
        ");
        $changes[] = "✓ Converted discount_price to discount_percent for existing products";
    }

    if (!empty($changes)) {
        echo "\n=== CHANGES MADE ===\n";
        foreach ($changes as $change) {
            echo "$change\n";
        }
    }

    echo "\n=== SUCCESS ===\n";
    echo "Products table structure updated successfully!\n";
    echo "\nYou can now delete this file: /admin/fix-products-table.php\n";

} catch (PDOException $e) {
    echo "\n=== ERROR ===\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>

<a href="/admin/products/index.php" style="display: inline-block; margin-top: 20px; padding: 12px 24px; background: #1A1A1A; color: white; text-decoration: none; border-radius: 6px;">
    Back to Products
</a>
