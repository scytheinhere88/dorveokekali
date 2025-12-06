<?php
/**
 * Update Print Status After Printing
 * Called via AJAX after labels are printed
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$batchId = intval($data['batch_id'] ?? 0);

if ($batchId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid batch ID']);
    exit;
}

try {
    // Update all orders in this batch
    $stmt = $pdo->prepare("
        UPDATE orders o
        INNER JOIN biteship_shipments s ON o.id = s.order_id
        SET o.fulfillment_status = 'waiting_pickup'
        WHERE s.label_print_batch_id = ?
    ");
    $stmt->execute([$batchId]);
    
    $affected = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Updated $affected order(s) to waiting_pickup status",
        'affected' => $affected
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
