<?php
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) die('Unauthorized');

echo "<h2>Setting up banners table...</h2>\n";

try {
    // Check if banners table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'banners'")->fetchAll();
    
    if (empty($tables)) {
        echo "<p>Creating banners table...</p>";
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS banners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                subtitle VARCHAR(255) NULL,
                image_url VARCHAR(500) NOT NULL,
                link_url VARCHAR(500) NULL,
                display_order INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_order (display_order),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p style='color: green;'>✓ Table created successfully!</p>";
        
        // Create uploads directory
        $upload_dir = __DIR__ . '/../../uploads/banners/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
            echo "<p style='color: green;'>✓ Created uploads/banners/ directory</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ banners table already exists</p>";
    }
    
    echo "<h2 style='color: green;'>✓ Setup complete!</h2>";
    echo "<p><a href='/admin/promotion/index.php'>← Go to Promotions</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>