<?php
/**
 * API: Mark Order as Printed
 */
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $orderId = (int)($_POST['order_id'] ?? 0);
    
    if (!$orderId) {
        throw new Exception('Order ID is required');
    }
    
    // Update order
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET printed_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$orderId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order marked as printed'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}