<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';

echo "<h2>Checking Database Tables</h2>";

try {
    // Check for order_addresses table
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_addresses'");
    if ($stmt->rowCount() > 0) {
        echo "✅ order_addresses table EXISTS<br>";
    } else {
        echo "❌ order_addresses table MISSING<br>";
    }

    // Check addresses table structure
    echo "<h3>Addresses Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE addresses");
    echo "<pre>";
    print_r($stmt->fetchAll());
    echo "</pre>";

    // Test cart query
    echo "<h3>Testing Cart Query:</h3>";
    $session_id = session_id();
    $stmt = $pdo->prepare("SELECT ci.*, p.name, p.slug, p.price, p.discount_percent,
                           pi.image_path, pv.color, pv.size, pv.extra_price
                           FROM cart_items ci
                           JOIN products p ON ci.product_id = p.id
                           LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                           LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                           WHERE ci.session_id = ?");
    $stmt->execute([$session_id]);
    echo "✅ Cart query works!<br>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
