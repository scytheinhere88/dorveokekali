<?php
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    redirect('/admin/login.php');
}

$tier_id = intval($_GET['id'] ?? 0);

try {
    // Check if tier exists
    $stmt = $pdo->prepare("SELECT * FROM commission_tiers WHERE id = ?");
    $stmt->execute([$tier_id]);
    $tier = $stmt->fetch();

    if (!$tier) {
        $_SESSION['error'] = 'Commission tier not found!';
        redirect('/admin/referrals/index.php');
    }

    // Delete the tier
    $stmt = $pdo->prepare("DELETE FROM commission_tiers WHERE id = ?");
    $stmt->execute([$tier_id]);

    $_SESSION['success'] = 'Commission tier deleted successfully!';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error deleting commission tier: ' . $e->getMessage();
}

redirect('/admin/referrals/index.php');
?>
