<?php
/**
 * API: Check for New Orders (Unpaid/Unprinted)
 * Returns latest order info for sound notification
 */
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $lastId = (int)($_GET['last_id'] ?? 0);
    
    // Check for new paid orders that haven't been printed
    $stmt = $pdo->prepare("
        SELECT id, order_number, created_at 
        FROM orders 
        WHERE id > ? 
        AND payment_status = 'paid' 
        AND (printed_at IS NULL OR printed_at = '')
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$lastId]);
    $newOrder = $stmt->fetch();
    
    if ($newOrder) {
        echo json_encode([
            'success' => true,
            'has_new' => true,
            'latest_id' => $newOrder['id'],
            'order_number' => $newOrder['order_number'],
            'created_at' => $newOrder['created_at']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_new' => false
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}