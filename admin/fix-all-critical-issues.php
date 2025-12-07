<?php
require_once __DIR__ . '/../config.php';

if (!isAdmin()) die('Unauthorized');

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Fix All Critical Issues</title>";
echo "<style>
    body { font-family: 'Inter', sans-serif; max-width: 1200px; margin: 40px auto; padding: 30px; background: #f5f5f5; }
    .container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #1a1a1a; margin-bottom: 10px; }
    .subtitle { color: #666; margin-bottom: 30px; }
    .success { color: #10B981; padding: 12px 16px; background: #D1FAE5; border-radius: 6px; margin: 10px 0; border-left: 4px solid #10B981; }
    .error { color: #EF4444; padding: 12px 16px; background: #FEE2E2; border-radius: 6px; margin: 10px 0; border-left: 4px solid #EF4444; }
    .section { margin: 30px 0; padding: 20px; background: #f9fafb; border-radius: 8px; }
    .section h2 { color: #1a1a1a; margin-bottom: 15px; font-size: 20px; }
</style></head><body><div class='container'>";

echo "<h1>🔧 Fix All Critical Issues</h1>";
echo "<p class='subtitle'>Fixing orders page, cart, and database schema...</p>";

$fixCount = 0;
$errorCount = 0;

// ===================================================
// FIX 1: Create order_addresses table if missing
// ===================================================
echo "<div class='section'>";
echo "<h2>1️⃣ Fix Order Addresses Table</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_addresses'");

    if ($stmt->rowCount() == 0) {
        // Create order_addresses table
        $pdo->exec("
            CREATE TABLE order_addresses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                type ENUM('billing', 'shipping') NOT NULL DEFAULT 'shipping',
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                email VARCHAR(255) NULL,
                address TEXT NOT NULL,
                city VARCHAR(100) NOT NULL,
                province VARCHAR(100) NOT NULL,
                postal_code VARCHAR(10) NULL,
                district VARCHAR(100) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                INDEX idx_order (order_id),
                INDEX idx_type (type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Order shipping and billing addresses'
        ");
        echo "<p class='success'>✓ Created order_addresses table</p>";

        // Migrate existing addresses from orders table if needed
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'shipping_address_id'");
        if ($stmt->rowCount() > 0) {
            $pdo->exec("
                INSERT INTO order_addresses (order_id, type, name, phone, email, address, city, province, postal_code, district)
                SELECT
                    o.id,
                    'shipping',
                    a.name,
                    a.phone,
                    u.email,
                    a.address,
                    a.city,
                    a.province,
                    a.postal_code,
                    a.district
                FROM orders o
                LEFT JOIN addresses a ON o.shipping_address_id = a.id
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.shipping_address_id IS NOT NULL
                AND NOT EXISTS (SELECT 1 FROM order_addresses WHERE order_id = o.id)
            ");
            echo "<p class='success'>✓ Migrated existing addresses to order_addresses</p>";
        }

        $fixCount++;
    } else {
        echo "<p class='success'>✓ order_addresses table already exists</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    $errorCount++;
}

echo "</div>";

// ===================================================
// FIX 2: Ensure all required columns exist in orders table
// ===================================================
echo "<div class='section'>";
echo "<h2>2️⃣ Fix Orders Table Schema</h2>";

try {
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = array_column($stmt->fetchAll(), 'Field');

    $requiredColumns = [
        'fulfillment_status' => "ENUM('new', 'waiting_print', 'waiting_pickup', 'in_transit', 'delivered', 'cancelled') DEFAULT 'new' AFTER payment_status",
        'tracking_number' => "VARCHAR(100) NULL AFTER fulfillment_status"
    ];

    foreach ($requiredColumns as $col => $definition) {
        if (!in_array($col, $columns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN $col $definition");
            echo "<p class='success'>✓ Added $col column to orders table</p>";
            $fixCount++;
        }
    }

    echo "<p class='success'>✓ Orders table schema is correct</p>";

} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    $errorCount++;
}

echo "</div>";

// ===================================================
// FIX 3: Ensure biteship_shipments table exists
// ===================================================
echo "<div class='section'>";
echo "<h2>3️⃣ Fix Biteship Shipments Table</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'biteship_shipments'");

    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE biteship_shipments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL UNIQUE,
                waybill_id VARCHAR(100) NULL,
                courier_company VARCHAR(50) NULL,
                courier_service_name VARCHAR(100) NULL,
                courier_tracking_id VARCHAR(100) NULL,
                label_print_batch_id INT NULL,
                shipment_status VARCHAR(50) DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                INDEX idx_waybill (waybill_id),
                INDEX idx_status (shipment_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p class='success'>✓ Created biteship_shipments table</p>";
        $fixCount++;
    } else {
        echo "<p class='success'>✓ biteship_shipments table already exists</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    $errorCount++;
}

echo "</div>";

// ===================================================
// SUMMARY
// ===================================================
echo "<div class='section'>";
echo "<h2>📊 Summary</h2>";
echo "<p><strong>Fixes Applied:</strong> $fixCount</p>";
echo "<p><strong>Errors:</strong> $errorCount</p>";

if ($errorCount == 0) {
    echo "<p class='success'><strong>✅ All critical database issues have been fixed!</strong></p>";
    echo "<ul>";
    echo "<li>✓ Admin orders page should now work</li>";
    echo "<li>✓ Cart page should now work</li>";
    echo "<li>✓ All required tables and columns are in place</li>";
    echo "</ul>";
} else {
    echo "<p class='warning'><strong>⚠️ Some issues could not be fixed. Please check the errors above.</strong></p>";
}

echo "</div>";

echo "<a href='/admin/orders/index.php' style='display: inline-block; padding: 12px 24px; background: #1a1a1a; color: white; text-decoration: none; border-radius: 6px; margin: 10px 10px 10px 0;'>Test Orders Page</a>";
echo "<a href='/pages/cart.php' style='display: inline-block; padding: 12px 24px; background: #1a1a1a; color: white; text-decoration: none; border-radius: 6px; margin: 10px;'>Test Cart Page</a>";
echo "<a href='/admin/index.php' style='display: inline-block; padding: 12px 24px; background: #E5E7EB; color: #1a1a1a; text-decoration: none; border-radius: 6px; margin: 10px;'>Back to Dashboard</a>";

echo "</div></body></html>";
?>
