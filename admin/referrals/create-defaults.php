<?php
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    redirect('/admin/login.php');
}

try {
    // Check if any tiers already exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM commission_tiers");
    $result = $stmt->fetch();

    if ($result['count'] > 0) {
        $_SESSION['error'] = 'Commission tiers already exist! Delete existing tiers first if you want to recreate defaults.';
        redirect('/admin/referrals/index.php');
    }

    // Create default tiers
    $pdo->exec("
        INSERT INTO commission_tiers (name, min_topup, max_topup, commission_percent, free_shipping_vouchers) VALUES
        ('Tier 1: Under 500K', 0, 499999, 3.00, 1),
        ('Tier 2: 500K - 1M', 500000, 999999, 4.00, 2),
        ('Tier 3: 1M - 5M', 1000000, 4999999, 5.00, 2),
        ('Tier 4: 5M+', 5000000, NULL, 6.00, 3)
    ");

    $_SESSION['success'] = 'Default commission tiers created successfully!';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error creating default tiers: ' . $e->getMessage();
}

redirect('/admin/referrals/index.php');
?>
