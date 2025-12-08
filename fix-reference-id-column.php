<?php
require_once __DIR__ . '/config.php';

echo "<h2>Fixing reference_id column type...</h2>";

try {
    // Check current column type
    $stmt = $pdo->query("SHOW COLUMNS FROM wallet_transactions WHERE Field = 'reference_id'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h3>Current reference_id column:</h3>";
    echo "<pre>";
    print_r($column);
    echo "</pre>";

    $current_type = $column['Type'];
    echo "<p>Current Type: <code>$current_type</code></p>";

    // If it's INT, we need to change it to VARCHAR
    if (strpos(strtolower($current_type), 'int') !== false) {
        echo "<p style='color: orange;'>⚠️ Column is INTEGER, changing to VARCHAR to support text references...</p>";

        $pdo->exec("
            ALTER TABLE wallet_transactions
            MODIFY COLUMN reference_id VARCHAR(100) NULL
        ");

        echo "<p style='color: green; font-weight: bold;'>✅ reference_id column updated to VARCHAR(100)!</p>";
    } else {
        echo "<p style='color: green;'>✅ reference_id is already VARCHAR/TEXT type!</p>";
    }

    // Verify
    $stmt = $pdo->query("SHOW COLUMNS FROM wallet_transactions WHERE Field = 'reference_id'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h3>New Definition:</h3>";
    echo "<code>" . htmlspecialchars($column['Type']) . "</code><br><br>";

    echo "<hr>";
    echo "<p><strong>✅ Fixed! Now try adding/deducting balance again.</strong></p>";
    echo "<p><a href='/admin/users/manage-balance.php?id=6' style='display: inline-block; background: #3B82F6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>Go to Manage Balance</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please run this SQL manually:</p>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
    echo "ALTER TABLE wallet_transactions
MODIFY COLUMN reference_id VARCHAR(100) NULL;";
    echo "</pre>";
}
?>
