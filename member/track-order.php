<?php
require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$orderId = intval($_GET['id'] ?? 0);

// Verify order belongs to user
$stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

// Redirect to tracking API
header('Location: /api/tracking/get-status.php?order_id=' . $orderId);
exit;
?>