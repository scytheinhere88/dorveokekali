<?php
require_once __DIR__ . '/config.php';

echo "<h2>Fixing wallet_transactions table...</h2>";

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM wallet_transactions LIKE 'admin_notes'");
    $exists = $stmt->fetch();

    if (!$exists) {
        echo "<p style='color: orange;'>⚠️ admin_notes column not found. Adding it...</p>";

        $pdo->exec("
            ALTER TABLE wallet_transactions
            ADD COLUMN admin_notes TEXT NULL AFTER description
        ");

        echo "<p style='color: green;'>✅ admin_notes column added successfully!</p>";
    } else {
        echo "<p style='color: green;'>✅ admin_notes column already exists!</p>";
    }

    // Show current structure
    echo "<h3>Current wallet_transactions structure:</h3>";
    $stmt = $pdo->query("DESCRIBE wallet_transactions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<p><strong>✅ All done! You can now go back to:</strong></p>";
    echo "<p><a href='/admin/users/manage-balance.php?id=6' style='display: inline-block; background: #3B82F6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>Manage Balance Page</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
