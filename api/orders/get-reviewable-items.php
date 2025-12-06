<?php
/**
 * API: Get Reviewable Items for an Order
 */
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $order_id = (int)($_GET['order_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    
    if (!$order_id) {
        throw new Exception('Order ID tidak valid.');
    }
    
    // Check if order belongs to user and can be reviewed
    $stmt = $pdo->prepare("
        SELECT id, can_review, completed_at
        FROM orders
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order tidak ditemukan.');
    }
    
    if (!$order['can_review'] || !$order['completed_at']) {
        throw new Exception('Order ini belum bisa direview.');
    }
    
    // Get order items that haven't been reviewed
    $stmt = $pdo->prepare("
        SELECT 
            oi.product_id,
            oi.quantity,
            p.name as product_name,
            p.image as product_image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
        AND NOT EXISTS (
            SELECT 1 FROM product_reviews
            WHERE order_id = ? AND product_id = oi.product_id
        )
    ");
    $stmt->execute([$order_id, $order_id]);
    $items = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
