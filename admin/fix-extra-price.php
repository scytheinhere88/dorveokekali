<?php
/**
 * FIX: Add extra_price column to product_variants table
 * Run this once by accessing: /admin/fix-extra-price.php
 */

require_once __DIR__ . '/../config.php';

// Simple auth check
if (!isset($_SESSION['admin_id'])) {
    die('Access denied. Please login as admin first.');
}

echo '<h2>Database Fix: Add extra_price column</h2>';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM product_variants LIKE 'extra_price'");
    $exists = $stmt->fetch();

    if (!$exists) {
        // Add the column
        $pdo->exec("ALTER TABLE product_variants ADD COLUMN extra_price DECIMAL(10,2) DEFAULT 0 AFTER stock");
        echo '<p style="color: green;">✅ Column extra_price added successfully!</p>';
    } else {
        echo '<p style="color: blue;">ℹ️ Column extra_price already exists</p>';
    }

    echo '<p style="color: green;"><strong>✅ Database fix complete!</strong></p>';
    echo '<p><a href="/admin/">Back to Admin</a></p>';
} catch (Exception $e) {
    echo '<p style="color: red;">❌ Error: ' . $e->getMessage() . '</p>';
}
