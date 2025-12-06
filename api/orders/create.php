<?php
/**
 * API: Create Order with Payment
 * Support: Wallet, Midtrans, Bank Transfer
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/MidtransHelper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $paymentMethod = $_POST['payment_method'] ?? 'wallet';
    $shippingMethodId = (int)($_POST['shipping_method'] ?? 0);
    $voucherCodes = $_POST['voucher_codes'] ?? '';
    $voucherDiscount = floatval($_POST['voucher_discount'] ?? 0);
    $voucherFreeShipping = (int)($_POST['voucher_free_shipping'] ?? 0);
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Get user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Get cart items
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.price, pv.size, pv.color 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        LEFT JOIN product_variants pv ON ci.variant_id = pv.id 
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();
    
    if (empty($cartItems)) {
        throw new Exception('Cart is empty');
    }
    
    // Calculate subtotal
    $subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cartItems));
    
    // Get shipping cost
    $shippingCost = 0;
    if ($shippingMethodId > 0) {
        $stmt = $pdo->prepare("SELECT cost FROM shipping_methods WHERE id = ? AND is_active = 1");
        $stmt->execute([$shippingMethodId]);
        $shippingCost = floatval($stmt->fetchColumn());
    }
    
    // Apply voucher discounts
    $finalShippingCost = $voucherFreeShipping ? 0 : $shippingCost;
    $total = $subtotal + $finalShippingCost - $voucherDiscount;
    $total = max(0, $total);
    
    // Generate order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Create order with expiry time (1 hour)
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, order_number, subtotal, shipping_cost, voucher_discount, 
            voucher_free_shipping, voucher_codes, total_payable_amount, 
            payment_method, payment_status, shipping_status, fulfillment_status,
            expired_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', 'pending', DATE_ADD(NOW(), INTERVAL 1 HOUR))
    ");
    $stmt->execute([
        $userId, $orderNumber, $subtotal, $shippingCost, $voucherDiscount,
        $voucherFreeShipping, $voucherCodes, $total,
        $paymentMethod
    ]);
    $orderId = $pdo->lastInsertId();
    
    // Create order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, variant_id, quantity, price) 
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($cartItems as $item) {
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['variant_id'],
            $item['qty'],
            $item['price']
        ]);
    }
    
    $response = [
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'total' => $total
    ];
    
    // Handle payment method
    if ($paymentMethod === 'wallet') {
        // Check wallet balance
        if ($user['wallet_balance'] < $total) {
            throw new Exception('Insufficient wallet balance. Please topup your wallet.');
        }
        
        // Deduct wallet
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        $stmt->execute([$total, $userId]);
        
        // Update order status
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'paid', paid_at = NOW(), fulfillment_status = 'processing' 
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        
        // Record voucher usage
        if (!empty($voucherCodes)) {
            $codes = explode(',', $voucherCodes);
            foreach ($codes as $code) {
                $stmt = $pdo->prepare("SELECT id FROM vouchers WHERE code = ?");
                $stmt->execute([trim($code)]);
                $voucherId = $stmt->fetchColumn();
                
                if ($voucherId) {
                    $stmt = $pdo->prepare("
                        INSERT INTO voucher_usage (order_id, user_id, voucher_id, voucher_code, voucher_type, discount_amount) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$orderId, $userId, $voucherId, trim($code), 'discount', $voucherDiscount]);
                    
                    // Update voucher usage count
                    $pdo->exec("UPDATE vouchers SET total_used = total_used + 1 WHERE id = $voucherId");
                    $pdo->exec("
                        INSERT INTO user_vouchers (user_id, voucher_id, usage_count) 
                        VALUES ($userId, $voucherId, 1) 
                        ON DUPLICATE KEY UPDATE usage_count = usage_count + 1
                    ");
                }
            }
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        $response['message'] = 'Order created and paid successfully!';
        
    } elseif ($paymentMethod === 'midtrans') {
        // Check if enabled
        $stmt = $pdo->prepare("SELECT setting_value FROM payment_settings WHERE setting_key = 'midtrans_enabled'");
        $stmt->execute();
        $enabled = $stmt->fetchColumn();
        
        if ($enabled != '1') {
            throw new Exception('Midtrans payment gateway is currently disabled');
        }
        
        $midtrans = new MidtransHelper($pdo);
        $snapData = $midtrans->createOrderTransaction($orderId, [
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'voucher_discount' => $voucherDiscount,
            'voucher_free_shipping' => $voucherFreeShipping
        ], $cartItems, [
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
            'address' => $user['address'] ?? ''
        ]);
        
        // Update order with Midtrans data
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET midtrans_order_id = ?, midtrans_snap_token = ? 
            WHERE id = ?
        ");
        $stmt->execute([$snapData['order_id'], $snapData['snap_token'], $orderId]);
        
        $response['snap_token'] = $snapData['snap_token'];
        $response['midtrans_order_id'] = $snapData['order_id'];
        
    } elseif ($paymentMethod === 'bank_transfer') {
        // Check if enabled
        $stmt = $pdo->prepare("SELECT setting_value FROM payment_settings WHERE setting_key = 'bank_transfer_enabled'");
        $stmt->execute();
        $enabled = $stmt->fetchColumn();
        
        if ($enabled != '1') {
            throw new Exception('Bank transfer is currently disabled');
        }
        
        // Generate unique code
        $stmt = $pdo->prepare("SELECT setting_value FROM payment_settings WHERE setting_key IN ('unique_code_min', 'unique_code_max')");
        $stmt->execute();
        $codes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $minCode = (int)($codes['unique_code_min'] ?? 1);
        $maxCode = (int)($codes['unique_code_max'] ?? 999);
        $uniqueCode = rand($minCode, $maxCode);
        
        $totalWithCode = $total + $uniqueCode;
        
        // Update order
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET unique_code = ?, total_payable_amount = ? 
            WHERE id = ?
        ");
        $stmt->execute([$uniqueCode, $totalWithCode, $orderId]);
        
        $response['unique_code'] = $uniqueCode;
        $response['total_with_code'] = $totalWithCode;
    }
    
    $pdo->commit();
    echo json_encode($response);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}