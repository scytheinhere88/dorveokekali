<?php
require_once __DIR__ . '/config.php';

try {
    $stmt = $pdo->query("DESCRIBE wallet_transactions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "wallet_transactions columns:\n";
    echo str_repeat('-', 50) . "\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }

    // Check if admin_notes exists
    $has_admin_notes = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'admin_notes') {
            $has_admin_notes = true;
            break;
        }
    }

    if (!$has_admin_notes) {
        echo "\n❌ MISSING: admin_notes column!\n";
        echo "Need to add this column.\n";
    } else {
        echo "\n✅ admin_notes column exists\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
