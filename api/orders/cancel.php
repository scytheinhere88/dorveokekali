<?php
/**
 * API: Cancel Pending Order
 */
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $orderId = (int)($_POST['order_id'] ?? 0);
    
    if (!$orderId) {
        throw new Exception('Order ID is required');
    }
    
    // Get order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Only allow cancel if pending
    if ($order['payment_status'] !== 'pending') {
        throw new Exception('Only pending orders can be cancelled');
    }
    
    // Update order status to cancelled
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'cancelled' WHERE id = ?");
    $stmt->execute([$orderId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
