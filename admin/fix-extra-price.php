<?php
/**
 * DATABASE FIX: Add extra_price column to product_variants table
 *
 * Run this ONCE by accessing: /admin/fix-extra-price.php
 *
 * This fixes the error: Column 'pv.extra_price' not found
 */

require_once __DIR__ . '/../config.php';

// Check if logged in as admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Access Denied</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
                margin: 0;
                padding: 20px;
            }
            .access-denied {
                background: white;
                padding: 48px;
                border-radius: 16px;
                text-align: center;
                max-width: 500px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            h1 {
                color: #EF4444;
                font-size: 48px;
                margin-bottom: 16px;
            }
            h2 {
                color: #1F2937;
                margin-bottom: 24px;
            }
            p {
                color: #6B7280;
                margin-bottom: 32px;
                line-height: 1.6;
            }
            a {
                display: inline-block;
                padding: 14px 32px;
                background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                transition: all 0.3s;
            }
            a:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            }
        </style>
    </head>
    <body>
        <div class="access-denied">
            <h1>🚫</h1>
            <h2>Access Denied</h2>
            <p>You need to be logged in as an administrator to access this page.</p>
            <a href="/admin/login.php">Login as Admin</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Database Fix - Add extra_price Column</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #F8F9FA;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #1F2937;
            margin-bottom: 30px;
        }
        .success {
            padding: 16px 20px;
            background: #D1FAE5;
            color: #065F46;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
        }
        .info {
            padding: 16px 20px;
            background: #DBEAFE;
            color: #1E40AF;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
        }
        .error {
            padding: 16px 20px;
            background: #FEE2E2;
            color: #DC2626;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
        }
        .details {
            background: #F9FAFB;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 32px;
            background: #1F2937;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn:hover {
            background: #374151;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Fix: Add extra_price Column</h1>

        <?php
        try {
            // Check if column exists
            $stmt = $pdo->query("SHOW COLUMNS FROM product_variants LIKE 'extra_price'");
            $exists = $stmt->fetch();

            if (!$exists) {
                // Add the column
                $pdo->exec("ALTER TABLE product_variants ADD COLUMN extra_price DECIMAL(10,2) DEFAULT 0 AFTER stock");

                echo '<div class="success">✅ SUCCESS! Column extra_price added successfully!</div>';
                echo '<div class="details">';
                echo 'ALTER TABLE product_variants ADD COLUMN extra_price DECIMAL(10,2) DEFAULT 0 AFTER stock';
                echo '</div>';
                echo '<p><strong>What was fixed:</strong></p>';
                echo '<ul>';
                echo '<li>Added <code>extra_price</code> column to <code>product_variants</code> table</li>';
                echo '<li>Type: DECIMAL(10,2) - allows prices up to 99,999,999.99</li>';
                echo '<li>Default value: 0</li>';
                echo '<li>Position: After <code>stock</code> column</li>';
                echo '</ul>';
                echo '<p><strong>Now you can:</strong></p>';
                echo '<ul>';
                echo '<li>✅ Add products to cart without error</li>';
                echo '<li>✅ View cart page without error</li>';
                echo '<li>✅ Proceed to checkout without error</li>';
                echo '</ul>';
            } else {
                echo '<div class="info">ℹ️ INFO: Column extra_price already exists</div>';
                echo '<p>The database is already fixed. No action needed.</p>';
                echo '<div class="details">';
                echo 'Column <code>extra_price</code> found in table <code>product_variants</code>';
                echo '</div>';
            }

            // Show current table structure
            echo '<h2 style="margin-top: 40px;">Current Table Structure:</h2>';
            echo '<div class="details">';
            $stmt = $pdo->query("DESCRIBE product_variants");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo '<table style="width: 100%; border-collapse: collapse;">';
            echo '<tr style="background: #E5E7EB; font-weight: bold;">';
            echo '<td style="padding: 8px;">Field</td>';
            echo '<td style="padding: 8px;">Type</td>';
            echo '<td style="padding: 8px;">Null</td>';
            echo '<td style="padding: 8px;">Default</td>';
            echo '</tr>';
            foreach ($columns as $col) {
                $highlight = $col['Field'] === 'extra_price' ? 'background: #FEF3C7;' : '';
                echo '<tr style="' . $highlight . '">';
                echo '<td style="padding: 8px;">' . htmlspecialchars($col['Field']) . '</td>';
                echo '<td style="padding: 8px;">' . htmlspecialchars($col['Type']) . '</td>';
                echo '<td style="padding: 8px;">' . htmlspecialchars($col['Null']) . '</td>';
                echo '<td style="padding: 8px;">' . htmlspecialchars($col['Default'] ?? 'NULL') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';

            echo '<div class="success" style="margin-top: 30px;">✅ Database fix complete!</div>';

        } catch (Exception $e) {
            echo '<div class="error">❌ ERROR: ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<p>Please check your database connection and try again.</p>';
        }
        ?>

        <a href="/admin/" class="btn">Back to Admin Panel</a>
        <a href="/" class="btn" style="background: #10B981;">Go to Homepage</a>
    </div>
</body>
</html>
