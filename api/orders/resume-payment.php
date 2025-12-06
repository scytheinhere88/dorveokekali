<?php
/**
 * API: Resume Payment for Existing Order
 * Get existing snap token or create new one
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
    $orderId = (int)($_POST['order_id'] ?? 0);
    
    if (!$orderId) {
        throw new Exception('Order ID is required');
    }
    
    // Get order details
    $stmt = $pdo->prepare("
        SELECT o.*, u.name, u.email, u.phone, u.address
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Check if already paid
    if ($order['payment_status'] === 'paid') {
        throw new Exception('Order already paid');
    }
    
    // Check if expired
    if ($order['expired_at'] && strtotime($order['expired_at']) < time()) {
        // Auto cancel expired order
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'expired' WHERE id = ?");
        $stmt->execute([$orderId]);
        throw new Exception('Order has expired. Please create a new order.');
    }
    
    $response = [
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $order['order_number'],
        'total' => $order['total_payable_amount'],
        'payment_method' => $order['payment_method'],
        'expired_at' => $order['expired_at']
    ];
    
    // Handle payment method
    if ($order['payment_method'] === 'wallet') {
        // Check wallet balance again
        $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $walletBalance = floatval($stmt->fetchColumn());
        
        if ($walletBalance < $order['total_payable_amount']) {
            throw new Exception('Insufficient wallet balance. Please topup first.');
        }
        
        // Process wallet payment now
        $pdo->beginTransaction();
        
        // Deduct wallet
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        $stmt->execute([$order['total_payable_amount'], $userId]);
        
        // Update order status
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'paid', paid_at = NOW(), fulfillment_status = 'processing' 
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        
        // Record voucher usage if not done
        if (!empty($order['voucher_codes'])) {
            $codes = explode(',', $order['voucher_codes']);
            foreach ($codes as $code) {
                $stmt = $pdo->prepare("SELECT id FROM vouchers WHERE code = ?");
                $stmt->execute([trim($code)]);
                $voucherId = $stmt->fetchColumn();
                
                if ($voucherId) {
                    $stmt = $pdo->prepare("
                        INSERT IGNORE INTO voucher_usage (order_id, user_id, voucher_id, voucher_code, voucher_type, discount_amount) 
                        VALUES (?, ?, ?, ?, 'discount', ?)
                    ");
                    $stmt->execute([$orderId, $userId, $voucherId, trim($code), $order['voucher_discount']]);
                    
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
        
        $pdo->commit();
        
        $response['message'] = 'Payment successful!';
        $response['paid'] = true;
        
    } elseif ($order['payment_method'] === 'midtrans') {
        // Check if snap token already exists
        if ($order['midtrans_snap_token']) {
            // Return existing snap token
            $response['snap_token'] = $order['midtrans_snap_token'];
            $response['midtrans_order_id'] = $order['midtrans_order_id'];
        } else {
            // Generate new snap token (if somehow missing)
            $midtrans = new MidtransHelper($pdo);
            
            // Get order items
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name, p.price 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll();
            
            $snapData = $midtrans->createOrderTransaction($orderId, [
                'subtotal' => $order['subtotal'],
                'shipping_cost' => $order['shipping_cost'],
                'voucher_discount' => $order['voucher_discount'],
                'voucher_free_shipping' => $order['voucher_free_shipping']
            ], $items, [
                'name' => $order['name'],
                'email' => $order['email'],
                'phone' => $order['phone'] ?? '',
                'address' => $order['address'] ?? ''
            ]);
            
            // Update order with new snap token
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET midtrans_order_id = ?, midtrans_snap_token = ? 
                WHERE id = ?
            ");
            $stmt->execute([$snapData['order_id'], $snapData['snap_token'], $orderId]);
            
            $response['snap_token'] = $snapData['snap_token'];
            $response['midtrans_order_id'] = $snapData['order_id'];
        }
        
    } elseif ($order['payment_method'] === 'bank_transfer') {
        // Return bank transfer info
        $response['unique_code'] = $order['unique_code'];
        $response['total_with_code'] = $order['total_payable_amount'];
        $response['message'] = 'Please complete your bank transfer';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
