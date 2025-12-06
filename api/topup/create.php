<?php
/**
 * API: Create Wallet Topup
 * Support Midtrans Payment Gateway
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
    $amount = floatval($_POST['amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? 'midtrans';
    
    if ($amount < 10000) {
        throw new Exception('Minimum topup amount is Rp 10.000');
    }
    
    if ($amount > 100000000) {
        throw new Exception('Maximum topup amount is Rp 100.000.000');
    }
    
    // Get user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Create topup record
    $stmt = $pdo->prepare("
        INSERT INTO wallet_topups (user_id, amount, payment_method, payment_status) 
        VALUES (?, ?, ?, 'pending')
    ");
    $stmt->execute([$userId, $amount, $paymentMethod]);
    $topupId = $pdo->lastInsertId();
    
    $response = ['success' => true, 'topup_id' => $topupId];
    
    // Handle Midtrans payment
    if ($paymentMethod === 'midtrans') {
        // Check if Midtrans is enabled
        $stmt = $pdo->prepare("SELECT setting_value FROM payment_settings WHERE setting_key = 'midtrans_enabled'");
        $stmt->execute();
        $enabled = $stmt->fetchColumn();
        
        if ($enabled != '1') {
            throw new Exception('Midtrans payment gateway is currently disabled');
        }
        
        $midtrans = new MidtransHelper($pdo);
        $snapData = $midtrans->createTopupTransaction($topupId, $userId, $amount, [
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? ''
        ]);
        
        // Update topup with snap token
        $stmt = $pdo->prepare("
            UPDATE wallet_topups 
            SET midtrans_order_id = ?, midtrans_snap_token = ?, expired_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
            WHERE id = ?
        ");
        $stmt->execute([$snapData['order_id'], $snapData['snap_token'], $topupId]);
        
        $response['snap_token'] = $snapData['snap_token'];
        $response['order_id'] = $snapData['order_id'];
    }
    // Handle Bank Transfer
    elseif ($paymentMethod === 'bank_transfer') {
        // Check if bank transfer is enabled
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
        
        $totalAmount = $amount + $uniqueCode;
        
        // Update topup
        $stmt = $pdo->prepare("
            UPDATE wallet_topups 
            SET unique_code = ?, amount = ?, expired_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
            WHERE id = ?
        ");
        $stmt->execute([$uniqueCode, $totalAmount, $topupId]);
        
        $response['unique_code'] = $uniqueCode;
        $response['total_amount'] = $totalAmount;
        $response['message'] = 'Please transfer exactly Rp ' . number_format($totalAmount, 0, ',', '.') . ' to complete your topup';
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}