<?php
require_once 'config.php';

echo "<h2>Fixing Image Paths in Database</h2>";
echo "<p>Removing /uploads/ prefix from paths...</p>";

try {
    // Fix product_images table
    $stmt = $pdo->query("SELECT id, image_path FROM product_images WHERE image_path LIKE '/uploads/%' OR image_path LIKE 'uploads/%'");
    $images = $stmt->fetchAll();

    $fixed_images = 0;
    foreach ($images as $image) {
        $old_path = $image['image_path'];

        // Remove /uploads/ or uploads/ prefix
        $new_path = preg_replace('#^/?uploads/#', '', $old_path);

        if ($old_path !== $new_path) {
            $update = $pdo->prepare("UPDATE product_images SET image_path = ? WHERE id = ?");
            $update->execute([$new_path, $image['id']]);
            $fixed_images++;

            echo "<div style='padding: 8px; margin: 4px 0; background: #e3f2fd; border-left: 3px solid #2196f3;'>";
            echo "<strong>Image ID {$image['id']}</strong><br>";
            echo "OLD: <code style='background:#ffebee;padding:2px 6px;'>{$old_path}</code><br>";
            echo "NEW: <code style='background:#c8e6c9;padding:2px 6px;'>{$new_path}</code>";
            echo "</div>";
        }
    }

    echo "<hr>";

    // Fix products table
    $stmt = $pdo->query("SELECT id, image FROM products WHERE image LIKE '/uploads/%' OR image LIKE 'uploads/%'");
    $products = $stmt->fetchAll();

    $fixed_products = 0;
    foreach ($products as $product) {
        $old_path = $product['image'];

        // Remove /uploads/ or uploads/ prefix
        $new_path = preg_replace('#^/?uploads/#', '', $old_path);

        if ($old_path !== $new_path) {
            $update = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
            $update->execute([$new_path, $product['id']]);
            $fixed_products++;

            echo "<div style='padding: 8px; margin: 4px 0; background: #fff3e0; border-left: 3px solid #ff9800;'>";
            echo "<strong>Product ID {$product['id']}</strong><br>";
            echo "OLD: <code style='background:#ffebee;padding:2px 6px;'>{$old_path}</code><br>";
            echo "NEW: <code style='background:#c8e6c9;padding:2px 6px;'>{$new_path}</code>";
            echo "</div>";
        }
    }

    echo "<hr>";
    echo "<h3 style='color: #4caf50;'>✓ Done!</h3>";
    echo "<p><strong>Fixed {$fixed_images} product images</strong></p>";
    echo "<p><strong>Fixed {$fixed_products} main product images</strong></p>";

    // Show current UPLOAD_URL
    echo "<hr>";
    echo "<h3>Current UPLOAD_URL Configuration:</h3>";
    echo "<code style='background:#e3f2fd;padding:10px;display:block;'>" . UPLOAD_URL . "</code>";

    // Show example final URL
    if ($fixed_images > 0 || $fixed_products > 0) {
        echo "<h3>Example Final URLs:</h3>";
        $stmt = $pdo->query("SELECT image_path FROM product_images LIMIT 1");
        $example = $stmt->fetch();
        if ($example) {
            echo "<p>Database path: <code style='background:#fff3e0;padding:4px 8px;'>{$example['image_path']}</code></p>";
            echo "<p>Final URL: <code style='background:#c8e6c9;padding:4px 8px;'>" . UPLOAD_URL . $example['image_path'] . "</code></p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 {
    margin-top: 30px;
}
code {
    font-family: 'Courier New', monospace;
    font-size: 14px;
}
</style>
