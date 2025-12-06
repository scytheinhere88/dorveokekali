<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $voucherCode = trim($_POST['voucher_code'] ?? '');
    $cartTotal = floatval($_POST['cart_total'] ?? 0);
    $userId = $_SESSION['user_id'];
    
    if (empty($voucherCode)) {
        throw new Exception('Kode voucher tidak boleh kosong');
    }
    
    // Get voucher details
    $stmt = $pdo->prepare("
        SELECT v.*,
               COALESCE(uv.usage_count, 0) as user_usage_count,
               CASE 
                   WHEN v.total_usage_limit IS NOT NULL AND v.total_used >= v.total_usage_limit THEN 1
                   ELSE 0
               END as is_limit_reached
        FROM vouchers v
        LEFT JOIN user_vouchers uv ON v.id = uv.voucher_id AND uv.user_id = ?
        WHERE v.code = ? AND v.is_active = 1
    ");
    $stmt->execute([$userId, $voucherCode]);
    $voucher = $stmt->fetch();
    
    if (!$voucher) {
        throw new Exception('Voucher tidak valid atau sudah tidak aktif');
    }
    
    // Check validity period
    $now = date('Y-m-d H:i:s');
    if ($now < $voucher['valid_from']) {
        throw new Exception('Voucher belum dapat digunakan. Berlaku mulai: ' . date('d M Y', strtotime($voucher['valid_from'])));
    }
    if ($now > $voucher['valid_until']) {
        throw new Exception('Voucher sudah kadaluarsa');
    }
    
    // Check minimum purchase
    if ($cartTotal < $voucher['min_purchase']) {
        throw new Exception('Minimum pembelian Rp ' . number_format($voucher['min_purchase'], 0, ',', '.') . ' untuk menggunakan voucher ini');
    }
    
    // Check usage limit per user
    if ($voucher['user_usage_count'] >= $voucher['max_usage_per_user']) {
        throw new Exception('Anda sudah mencapai batas penggunaan voucher ini');
    }
    
    // Check total usage limit
    if ($voucher['is_limit_reached']) {
        throw new Exception('Voucher sudah habis digunakan');
    }
    
    // Calculate discount
    $discountAmount = 0;
    if ($voucher['type'] === 'discount') {
        if ($voucher['discount_type'] === 'percentage') {
            $discountAmount = ($cartTotal * $voucher['discount_value']) / 100;
        } else {
            $discountAmount = $voucher['discount_value'];
        }
        
        // Apply max discount
        if ($voucher['max_discount'] && $discountAmount > $voucher['max_discount']) {
            $discountAmount = $voucher['max_discount'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'voucher' => [
            'id' => $voucher['id'],
            'code' => $voucher['code'],
            'name' => $voucher['name'],
            'type' => $voucher['type'],
            'discount_amount' => $discountAmount,
            'max_discount' => $voucher['max_discount'],
            'description' => $voucher['description']
        ],
        'message' => 'Voucher valid!'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}