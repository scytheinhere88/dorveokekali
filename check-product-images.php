<?php
require_once 'config.php';

echo "<h2>Check Product Images Database</h2>";

// Get all products
$stmt = $pdo->query("SELECT id, name, image FROM products ORDER BY id DESC LIMIT 10");
$products = $stmt->fetchAll();

echo "<style>
body { font-family: -apple-system, sans-serif; padding: 20px; background: #f5f5f5; }
table { width: 100%; border-collapse: collapse; background: white; margin: 20px 0; }
th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
th { background: #667eea; color: white; }
.ok { background: #c8e6c9; }
.warning { background: #fff3cd; }
.error { background: #ffcdd2; }
img { max-width: 100px; height: auto; }
</style>";

foreach ($products as $product) {
    echo "<h3>Product ID: {$product['id']} - {$product['name']}</h3>";

    // Main image
    echo "<p><strong>Main Image in products table:</strong> <code>{$product['image']}</code></p>";

    // Get all images from product_images
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$product['id']]);
    $images = $stmt->fetchAll();

    echo "<p><strong>Total images in product_images table:</strong> " . count($images) . "</p>";

    if (count($images) > 0) {
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Image Path</th>
                <th>Is Primary</th>
                <th>Sort Order</th>
                <th>Full URL</th>
                <th>Preview</th>
                <th>Status</th>
              </tr>";

        foreach ($images as $img) {
            $full_url = UPLOAD_URL . $img['image_path'];

            // Check if path looks correct
            $status_class = 'ok';
            $status_text = 'OK';

            if (strpos($img['image_path'], '/uploads/') !== false || strpos($img['image_path'], 'uploads/') === 0) {
                $status_class = 'error';
                $status_text = 'PATH ERROR - contains /uploads/';
            } elseif (strpos($img['image_path'], 'products/') !== 0) {
                $status_class = 'warning';
                $status_text = 'WARNING - should start with products/';
            }

            echo "<tr class='{$status_class}'>";
            echo "<td>{$img['id']}</td>";
            echo "<td><code>{$img['image_path']}</code></td>";
            echo "<td>" . ($img['is_primary'] ? 'YES' : 'No') . "</td>";
            echo "<td>{$img['sort_order']}</td>";
            echo "<td><small>{$full_url}</small></td>";
            echo "<td><img src='{$full_url}' onerror=\"this.src='https://via.placeholder.com/100x100?text=404'\"></td>";
            echo "<td><strong>{$status_text}</strong></td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p style='color: red;'><strong>⚠️ NO IMAGES FOUND in product_images table!</strong></p>";
    }

    echo "<hr>";
}

echo "<h3>Database Configuration:</h3>";
echo "<p><strong>UPLOAD_URL:</strong> <code>" . UPLOAD_URL . "</code></p>";
echo "<p><strong>Example correct path:</strong> <code>products/image.jpg</code></p>";
echo "<p><strong>Example correct full URL:</strong> <code>" . UPLOAD_URL . "products/image.jpg</code></p>";
?>
