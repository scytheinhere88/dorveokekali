<?php
/**
 * FULL SYSTEM DEBUG - DORVE.ID
 * Comprehensive system check untuk seluruh website
 * Access: https://dorve.id/debug-system.php (admin only)
 */

require_once __DIR__ . '/config.php';

if (!isAdmin()) {
    die('❌ Admin access required');
}

// Start output buffering
ob_start();

$errors = [];
$warnings = [];
$success = [];

// SECTION 1: PHP Configuration
echo "<h2>📋 PHP Configuration</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>Post Max Size: " . ini_get('post_max_size') . "</li>";
echo "<li>Memory Limit: " . ini_get('memory_limit') . "</li>";
echo "<li>Max Execution Time: " . ini_get('max_execution_time') . "s</li>";
echo "</ul>";

if (ini_get('upload_max_filesize') < '16M') {
    $warnings[] = 'Upload max filesize should be at least 16M for review images/videos';
}

// SECTION 2: Database Check
echo "<h2>💾 Database Status</h2>";
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>✅ Connected to database</p>";
    echo "<p>Total Tables: " . count($tables) . "</p>";
    
    $required_tables = [
        'users', 'products', 'categories', 'orders', 'order_items',
        'banners', 'vouchers', 'user_vouchers', 'product_reviews',
        'review_media', 'user_addresses', 'wallet_topups'
    ];
    
    echo "<h3>Required Tables Check:</h3><ul>";
    foreach ($required_tables as $table) {
        if (in_array($table, $tables)) {
            echo "<li>✅ $table</li>";
            $success[] = "Table $table exists";
        } else {
            echo "<li>❌ $table - MISSING!</li>";
            $errors[] = "Missing table: $table";
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Database Error: " . $e->getMessage() . "</p>";
    $errors[] = 'Database connection failed: ' . $e->getMessage();
}

// SECTION 3: File Structure Check
echo "<h2>📁 File Structure Check</h2>";
$required_dirs = [
    '/uploads/products',
    '/uploads/reviews/photos',
    '/uploads/reviews/videos',
    '/includes',
    '/api',
    '/admin',
    '/member',
    '/pages'
];

echo "<ul>";
foreach ($required_dirs as $dir) {
    $full_path = __DIR__ . $dir;
    if (file_exists($full_path) && is_dir($full_path)) {
        $writable = is_writable($full_path) ? '✅ Writable' : '⚠️ Not writable';
        echo "<li>✅ $dir - $writable</li>";
        if (!is_writable($full_path)) {
            $warnings[] = "Directory not writable: $dir";
        }
    } else {
        echo "<li>❌ $dir - MISSING!</li>";
        $errors[] = "Missing directory: $dir";
    }
}
echo "</ul>";

// SECTION 4: Pages Check
echo "<h2>📄 Pages Check</h2>";
$pages_to_check = [
    'Homepage' => '/index.php',
    'All Products' => '/pages/all-products.php',
    'Product Detail' => '/pages/product-detail.php',
    'Checkout' => '/pages/checkout.php',
    'Login' => '/auth/login.php',
    'Register' => '/auth/register.php',
    'Member Dashboard' => '/member/dashboard.php',
    'Member Orders' => '/member/orders.php',
    'Write Review' => '/member/write-review.php',
    'Admin Dashboard' => '/admin/index.php',
    'Admin Products' => '/admin/products/index.php',
    'Admin Reviews' => '/admin/reviews/index.php'
];

echo "<ul>";
foreach ($pages_to_check as $name => $path) {
    $full_path = __DIR__ . $path;
    if (file_exists($full_path)) {
        echo "<li>✅ $name</li>";
    } else {
        echo "<li>❌ $name - FILE MISSING!</li>";
        $errors[] = "Missing page: $name ($path)";
    }
}
echo "</ul>";

// SECTION 5: API Endpoints Check
echo "<h2>🔌 API Endpoints Check</h2>";
$api_endpoints = [
    '/api/reviews/submit-review.php',
    '/api/orders/complete-order.php',
    '/api/orders/get-reviewable-items.php',
    '/api/orders/create.php',
    '/api/topup/create.php'
];

echo "<ul>";
foreach ($api_endpoints as $endpoint) {
    $full_path = __DIR__ . $endpoint;
    if (file_exists($full_path)) {
        echo "<li>✅ $endpoint</li>";
    } else {
        echo "<li>⚠️ $endpoint - Missing (may not be needed)</li>";
        $warnings[] = "API endpoint missing: $endpoint";
    }
}
echo "</ul>";

// SECTION 6: Include Files Check
echo "<h2>📚 Include Files Check</h2>";
$includes = [
    '/includes/header.php',
    '/includes/footer.php',
    '/includes/review-helper.php',
    '/includes/email-helper.php',
    '/includes/seo-helper.php',
    '/includes/global-responsive.css',
    '/includes/homepage-sections.php'
];

echo "<ul>";
foreach ($includes as $inc) {
    $full_path = __DIR__ . $inc;
    if (file_exists($full_path)) {
        echo "<li>✅ $inc</li>";
    } else {
        echo "<li>⚠️ $inc - Missing</li>";
        $warnings[] = "Include file missing: $inc";
    }
}
echo "</ul>";

// SECTION 7: Responsive CSS Check
echo "<h2>📱 Responsive Design Check</h2>";
$responsive_file = __DIR__ . '/includes/global-responsive.css';
if (file_exists($responsive_file)) {
    echo "<p>✅ Global responsive CSS exists</p>";
    $size = filesize($responsive_file);
    echo "<p>File size: " . number_format($size) . " bytes</p>";
} else {
    echo "<p>❌ Global responsive CSS missing!</p>";
    $errors[] = 'Responsive CSS file missing';
}

// SECTION 8: Products & Reviews Stats
echo "<h2>📊 Content Statistics</h2>";
try {
    // Check if products table has is_active column
    $stmt = $pdo->query("DESCRIBE products");
    $product_columns = array_column($stmt->fetchAll(), 'Field');
    $has_is_active = in_array('is_active', $product_columns);
    
    if ($has_is_active) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    }
    $total_products = $stmt->fetchColumn();
    echo "<p>Total Products: <strong>$total_products</strong></p>";
    
    // Reviews
    if (in_array('product_reviews', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'published'");
        $total_reviews = $stmt->fetchColumn();
        echo "<p>Total Published Reviews: <strong>$total_reviews</strong></p>";
    } else {
        echo "<p>Total Published Reviews: <strong>0</strong> (table not exists)</p>";
    }
    
    // Orders
    if (in_array('orders', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'");
        $total_orders = $stmt->fetchColumn();
        echo "<p>Total Paid Orders: <strong>$total_orders</strong></p>";
    } else {
        echo "<p>Total Paid Orders: <strong>0</strong> (table not exists)</p>";
    }
    
    // Users
    $stmt = $pdo->query("DESCRIBE users");
    $user_columns = array_column($stmt->fetchAll(), 'Field');
    $user_has_is_active = in_array('is_active', $user_columns);
    
    if ($user_has_is_active) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1");
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    }
    $total_users = $stmt->fetchColumn();
    echo "<p>Total Users: <strong>$total_users</strong></p>";
    
    if ($total_products == 0) {
        $warnings[] = 'No products in database';
    }
} catch (Exception $e) {
    echo "<p>❌ Error fetching stats: " . $e->getMessage() . "</p>";
}

// SECTION 9: Summary
echo "<h2>📋 Summary</h2>";
echo "<div style='padding: 20px; background: #F3F4F6; border-radius: 8px;'>";
echo "<p><strong>✅ Success: </strong>" . count($success) . " items</p>";
echo "<p><strong>⚠️ Warnings: </strong>" . count($warnings) . " items</p>";
echo "<p><strong>❌ Errors: </strong>" . count($errors) . " items</p>";
echo "</div>";

if (!empty($errors)) {
    echo "<h3 style='color: #DC2626;'>❌ Critical Errors:</h3><ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

if (!empty($warnings)) {
    echo "<h3 style='color: #F59E0B;'>⚠️ Warnings:</h3><ul>";
    foreach ($warnings as $warning) {
        echo "<li>$warning</li>";
    }
    echo "</ul>";
}

if (empty($errors) && empty($warnings)) {
    echo "<div style='padding: 24px; background: #D1FAE5; border-radius: 8px; text-align: center; margin: 20px 0;'>";
    echo "<h2 style='color: #065F46; margin: 0;'>🎉 All Systems Go!</h2>";
    echo "<p style='color: #065F46; margin: 8px 0 0;'>No errors or warnings detected. System is healthy!</p>";
    echo "</div>";
}

$content = ob_get_clean();

// Output HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System Debug - Dorve.ID</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #F9FAFB;
        }
        h1 {
            color: #1F2937;
            border-bottom: 3px solid #3B82F6;
            padding-bottom: 16px;
        }
        h2 {
            color: #374151;
            margin-top: 40px;
            padding: 12px;
            background: white;
            border-left: 4px solid #3B82F6;
        }
        h3 {
            color: #4B5563;
        }
        ul {
            line-height: 1.8;
        }
        li {
            margin: 8px 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        code {
            background: #F3F4F6;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 System Debug - Dorve.ID</h1>
        <p style="color: #6B7280;">Generated: <?= date('Y-m-d H:i:s') ?></p>
        <hr>
        <?= $content ?>
        <hr style="margin: 40px 0;">
        <p style="text-align: center; color: #9CA3AF; font-size: 14px;">Debug System © Dorve.ID</p>
    </div>
</body>
</html>
