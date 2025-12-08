<?php
require_once __DIR__ . '/config.php';

echo "<h2>Fixing wallet_transactions type ENUM...</h2>";

try {
    // Get current column definition
    $stmt = $pdo->query("SHOW COLUMNS FROM wallet_transactions WHERE Field = 'type'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h3>Current Definition:</h3>";
    echo "<code>" . htmlspecialchars($column['Type']) . "</code><br><br>";

    // Update ENUM to include all needed values
    echo "<p>Updating ENUM to include: topup, deposit, withdrawal, purchase, refund, referral_bonus, admin_adjustment</p>";

    $pdo->exec("
        ALTER TABLE wallet_transactions
        MODIFY COLUMN type ENUM(
            'topup',
            'deposit',
            'withdrawal',
            'purchase',
            'refund',
            'referral_bonus',
            'admin_adjustment'
        ) NOT NULL
    ");

    echo "<p style='color: green; font-weight: bold;'>✅ ENUM updated successfully!</p>";

    // Verify
    $stmt = $pdo->query("SHOW COLUMNS FROM wallet_transactions WHERE Field = 'type'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h3>New Definition:</h3>";
    echo "<code>" . htmlspecialchars($column['Type']) . "</code><br><br>";

    echo "<hr>";
    echo "<p><strong>✅ All done! Now you can add/deduct balance without errors.</strong></p>";
    echo "<p><a href='/admin/users/manage-balance.php?id=6' style='display: inline-block; background: #3B82F6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;'>Go to Manage Balance</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please run this SQL manually in phpMyAdmin:</p>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
    echo "ALTER TABLE wallet_transactions
MODIFY COLUMN type ENUM(
    'topup',
    'deposit',
    'withdrawal',
    'purchase',
    'refund',
    'referral_bonus',
    'admin_adjustment'
) NOT NULL;";
    echo "</pre>";
}
?>
