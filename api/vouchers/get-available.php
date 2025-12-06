<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $cartTotal = floatval($_GET['cart_total'] ?? 0);
    
    // Get all available vouchers for user
    $stmt = $pdo->prepare("
        SELECT v.*,
               COALESCE(uv.usage_count, 0) as user_usage_count,
               CASE 
                   WHEN v.min_purchase <= ? THEN 1
                   ELSE 0
               END as is_eligible,
               CASE 
                   WHEN v.total_usage_limit IS NOT NULL AND v.total_used >= v.total_usage_limit THEN 1
                   ELSE 0
               END as is_limit_reached
        FROM vouchers v
        LEFT JOIN user_vouchers uv ON v.id = uv.voucher_id AND uv.user_id = ?
        WHERE v.is_active = 1
          AND v.valid_from <= NOW()
          AND v.valid_until >= NOW()
          AND (v.total_usage_limit IS NULL OR v.total_used < v.total_usage_limit)
          AND (uv.usage_count IS NULL OR uv.usage_count < v.max_usage_per_user)
        ORDER BY v.type DESC, v.min_purchase ASC
    ");
    $stmt->execute([$cartTotal, $userId]);
    $vouchers = $stmt->fetchAll();
    
    // Separate by type
    $freeShipping = [];
    $discount = [];
    
    foreach ($vouchers as $voucher) {
        $voucherData = [
            'id' => $voucher['id'],
            'code' => $voucher['code'],
            'name' => $voucher['name'],
            'description' => $voucher['description'],
            'image' => $voucher['image'],
            'type' => $voucher['type'],
            'discount_type' => $voucher['discount_type'],
            'discount_value' => floatval($voucher['discount_value']),
            'max_discount' => floatval($voucher['max_discount'] ?? 0),
            'min_purchase' => floatval($voucher['min_purchase']),
            'terms_conditions' => $voucher['terms_conditions'],
            'usage_count' => intval($voucher['user_usage_count']),
            'max_usage' => intval($voucher['max_usage_per_user']),
            'is_eligible' => (bool)$voucher['is_eligible'],
            'valid_until' => $voucher['valid_until']
        ];
        
        if ($voucher['type'] === 'free_shipping') {
            $freeShipping[] = $voucherData;
        } else {
            $discount[] = $voucherData;
        }
    }
    
    echo json_encode([
        'success' => true,
        'vouchers' => [
            'free_shipping' => $freeShipping,
            'discount' => $discount
        ],
        'total' => count($vouchers)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}