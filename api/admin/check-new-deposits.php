<?php
/**
 * API: Check for New Deposits (Pending Confirmation)
 * Returns latest deposit info for sound notification
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
    
    // Check for new pending deposits
    $stmt = $pdo->prepare("
        SELECT id, user_id, amount, created_at 
        FROM wallet_topups 
        WHERE id > ? 
        AND payment_status = 'pending' 
        AND payment_method = 'bank_transfer'
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$lastId]);
    $newDeposit = $stmt->fetch();
    
    if ($newDeposit) {
        echo json_encode([
            'success' => true,
            'has_new' => true,
            'latest_id' => $newDeposit['id'],
            'amount' => $newDeposit['amount'],
            'created_at' => $newDeposit['created_at']
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