<?php
require_once 'config.php';

echo 'UPLOAD_URL: ' . UPLOAD_URL . PHP_EOL;
echo 'UPLOAD_PATH: ' . UPLOAD_PATH . PHP_EOL;
echo 'SITE_URL: ' . SITE_URL . PHP_EOL;

// Check products table
$stmt = $pdo->query('SELECT id, name, image FROM products LIMIT 5');
echo PHP_EOL . '=== Products table (image column) ===' . PHP_EOL;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo 'ID: ' . $row['id'] . ' | Name: ' . $row['name'] . ' | Image: ' . ($row['image'] ?? 'NULL') . PHP_EOL;
}

// Check product_images table
try {
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM product_images');
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo PHP_EOL . '=== Product Images table ===' . PHP_EOL;
    echo 'Total rows: ' . $count['total'] . PHP_EOL;

    if ($count['total'] > 0) {
        $stmt = $pdo->query('SELECT pi.id, pi.product_id, pi.image_path, pi.is_primary FROM product_images pi LIMIT 5');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo 'ID: ' . $row['id'] . ' | Product: ' . $row['product_id'] . ' | Path: ' . $row['image_path'] . ' | Primary: ' . $row['is_primary'] . PHP_EOL;
        }
    }
} catch (PDOException $e) {
    echo 'Product_images table does not exist or error: ' . $e->getMessage() . PHP_EOL;
}

// Check what files actually exist
echo PHP_EOL . '=== Checking uploads directory ===' . PHP_EOL;
if (is_dir(UPLOAD_PATH . 'products')) {
    $files = scandir(UPLOAD_PATH . 'products');
    echo 'Files in ' . UPLOAD_PATH . 'products:' . PHP_EOL;
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo '  - ' . $file . PHP_EOL;
        }
    }
} else {
    echo 'Products directory does not exist at: ' . UPLOAD_PATH . 'products' . PHP_EOL;
}
