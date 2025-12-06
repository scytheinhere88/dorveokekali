<?php
/**
 * API: Complete Order (User clicked "Terima Pesanan")
 */
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $user_id = $_SESSION['user_id'];
    
    if (!$order_id) {
        throw new Exception('Order ID tidak valid.');
    }
    
    // Check if order belongs to user
    $stmt = $pdo->prepare("
        SELECT id, payment_status, delivery_status, completed_at
        FROM orders
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order tidak ditemukan.');
    }
    
    if ($order['completed_at']) {
        throw new Exception('Order sudah ditandai sebagai selesai.');
    }
    
    if ($order['payment_status'] !== 'paid') {
        throw new Exception('Order belum dibayar.');
    }
    
    // Update order status
    $stmt = $pdo->prepare("
        UPDATE orders
        SET delivery_status = 'completed',
            completed_at = NOW(),
            can_review = 1
        WHERE id = ?
    ");
    $stmt->execute([$order_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil ditandai sebagai diterima!',
        'can_review' => true
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
