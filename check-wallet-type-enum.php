<?php
require_once __DIR__ . '/config.php';

echo "<h2>Checking wallet_transactions type column...</h2>";

try {
    // Get column definition
    $stmt = $pdo->query("SHOW COLUMNS FROM wallet_transactions WHERE Field = 'type'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h3>Current 'type' column definition:</h3>";
    echo "<pre>";
    print_r($column);
    echo "</pre>";

    echo "<h3>Current type values in database:</h3>";
    $stmt = $pdo->query("SELECT DISTINCT type FROM wallet_transactions");
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($types as $type) {
        echo "<li>" . htmlspecialchars($type) . "</li>";
    }
    echo "</ul>";

    // Check if we need to fix
    $type_def = $column['Type'];
    echo "<h3>Type Definition: <code>$type_def</code></h3>";

    if (strpos($type_def, 'deposit') === false || strpos($type_def, 'withdrawal') === false) {
        echo "<p style='color: red;'>⚠️ ENUM needs to be updated!</p>";
        echo "<p><a href='/fix-wallet-type-enum.php' style='background: #EF4444; color: white; padding: 12px 24px; display: inline-block; text-decoration: none; border-radius: 6px;'>🔧 Fix ENUM Values Now</a></p>";
    } else {
        echo "<p style='color: green;'>✅ ENUM looks good!</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
