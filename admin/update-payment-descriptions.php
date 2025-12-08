<?php
/**
 * UPDATE PAYMENT METHOD DESCRIPTIONS
 * Make them more informative for users
 */
require_once __DIR__ . '/../config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Payment Descriptions</title>
    <style>
        body { font-family: monospace; padding: 40px; background: #1a1a1a; color: #00ff00; }
        .success { color: #00ff00; }
        .info { color: #00aaff; }
        pre { background: #000; padding: 20px; border-radius: 8px; }
        h1 { color: #ffffff; }
    </style>
</head>
<body>
    <h1>💳 UPDATE PAYMENT DESCRIPTIONS</h1>
    <pre><?php

try {
    echo "Updating payment method descriptions...\n\n";

    // Check if 'code' column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM payment_methods LIKE 'code'");
    $has_code_column = $stmt->rowCount() > 0;

    if (!$has_code_column) {
        echo "<span class='info'>⚠️ 'code' column not found. Will only use 'type' column for matching.</span>\n\n";
    }

    // Update Bank Transfer
    if ($has_code_column) {
        $stmt = $pdo->prepare("
            UPDATE payment_methods
            SET description = 'Transfer ke rekening bank kami (BCA, Mandiri, BNI). Pilih bank setelah place order.'
            WHERE type = 'bank_transfer' OR code = 'bank_transfer' OR code = 'BANK_TRANSFER' OR name LIKE '%bank%'
        ");
    } else {
        $stmt = $pdo->prepare("
            UPDATE payment_methods
            SET description = 'Transfer ke rekening bank kami (BCA, Mandiri, BNI). Pilih bank setelah place order.'
            WHERE type = 'bank_transfer' OR name LIKE '%bank%'
        ");
    }
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "<span class='success'>✅ Bank Transfer description updated ($count rows)</span>\n";
    echo "   New: 'Transfer ke rekening bank kami (BCA, Mandiri, BNI). Pilih bank setelah place order.'\n\n";

    // Update Midtrans
    if ($has_code_column) {
        $stmt = $pdo->prepare("
            UPDATE payment_methods
            SET description = 'Bayar dengan QRIS, E-Wallet, atau Kartu Kredit (akan muncul pilihan lengkap setelah place order)'
            WHERE type = 'midtrans' OR code = 'midtrans' OR code = 'MIDTRANS' OR name LIKE '%midtrans%'
        ");
    } else {
        $stmt = $pdo->prepare("
            UPDATE payment_methods
            SET description = 'Bayar dengan QRIS, E-Wallet, atau Kartu Kredit (akan muncul pilihan lengkap setelah place order)'
            WHERE type = 'midtrans' OR name LIKE '%midtrans%'
        ");
    }
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "<span class='success'>✅ Midtrans description updated ($count rows)</span>\n";
    echo "   New: 'Bayar dengan QRIS, E-Wallet, atau Kartu Kredit (akan muncul pilihan lengkap setelah place order)'\n\n";

    // Update Wallet
    if ($has_code_column) {
        $stmt = $pdo->prepare("
            UPDATE payment_methods
            SET description = 'Bayar menggunakan saldo Dorve Wallet Anda'
            WHERE type = 'wallet' OR code = 'wallet' OR code = 'WALLET' OR name LIKE '%wallet%'
        ");
    } else {
        $stmt = $pdo->prepare("
            UPDATE payment_methods
            SET description = 'Bayar menggunakan saldo Dorve Wallet Anda'
            WHERE type = 'wallet' OR name LIKE '%wallet%'
        ");
    }
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "<span class='success'>✅ Wallet description updated ($count rows)</span>\n";
    echo "   New: 'Bayar menggunakan saldo Dorve Wallet Anda'\n\n";

    echo "\n<span class='success'>========================================</span>\n";
    echo "<span class='success'>✅ ALL DESCRIPTIONS UPDATED!</span>\n";
    echo "<span class='success'>========================================</span>\n\n";

    echo "<span class='info'>Changes will be visible immediately on checkout page.</span>\n";
    echo "<span class='info'>No cache clearing needed.</span>\n";

} catch (PDOException $e) {
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
}

?></pre>
</body>
</html>
