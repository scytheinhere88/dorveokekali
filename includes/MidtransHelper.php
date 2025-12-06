<?php
/**
 * MIDTRANS PAYMENT GATEWAY HELPER
 * Professional integration with Midtrans SNAP API
 */

class MidtransHelper {
    private $serverKey;
    private $clientKey;
    private $isProduction;
    private $apiUrl;
    
    public function __construct($pdo) {
        // Load settings from database
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM payment_settings WHERE setting_key LIKE 'midtrans_%'");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        $this->serverKey = $settings['midtrans_server_key'] ?? '';
        $this->clientKey = $settings['midtrans_client_key'] ?? '';
        $this->isProduction = ($settings['midtrans_is_production'] ?? '0') == '1';
        
        // Set API URL based on environment
        $this->apiUrl = $this->isProduction 
            ? 'https://api.midtrans.com' 
            : 'https://api.sandbox.midtrans.com';
    }
    
    /**
     * Create Snap Transaction for Topup
     */
    public function createTopupTransaction($topupId, $userId, $amount, $userDetails) {
        $orderId = 'TOPUP-' . $topupId . '-' . time();
        
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int)$amount
            ],
            'customer_details' => [
                'first_name' => $userDetails['name'] ?? 'Customer',
                'email' => $userDetails['email'] ?? '',
                'phone' => $userDetails['phone'] ?? ''
            ],
            'item_details' => [
                [
                    'id' => 'topup',
                    'price' => (int)$amount,
                    'quantity' => 1,
                    'name' => 'Wallet Top-up'
                ]
            ],
            'callbacks' => [
                'finish' => 'https://' . $_SERVER['HTTP_HOST'] . '/member/wallet.php?status=success'
            ]
        ];
        
        return $this->createSnapToken($params, $orderId);
    }
    
    /**
     * Create Snap Transaction for Order
     */
    public function createOrderTransaction($orderId, $orderData, $items, $userDetails) {
        $midtransOrderId = 'ORDER-' . $orderId . '-' . time();
        
        // Calculate total
        $subtotal = $orderData['subtotal'];
        $shippingCost = $orderData['shipping_cost'] ?? 0;
        $voucherDiscount = $orderData['voucher_discount'] ?? 0;
        $voucherFreeShipping = ($orderData['voucher_free_shipping'] ?? 0) ? $shippingCost : 0;
        
        $total = $subtotal + $shippingCost - $voucherDiscount - $voucherFreeShipping;
        $total = max(0, $total); // Ensure non-negative
        
        // Prepare item details
        $itemDetails = [];
        foreach ($items as $item) {
            $itemDetails[] = [
                'id' => 'ITEM-' . $item['id'],
                'price' => (int)$item['price'],
                'quantity' => (int)$item['qty'],
                'name' => substr($item['name'], 0, 50) // Midtrans limit
            ];
        }
        
        // Add shipping as item
        if ($shippingCost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int)$shippingCost,
                'quantity' => 1,
                'name' => 'Shipping Cost'
            ];
        }
        
        // Add discount as negative item
        if ($voucherDiscount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -(int)$voucherDiscount,
                'quantity' => 1,
                'name' => 'Voucher Discount'
            ];
        }
        
        if ($voucherFreeShipping > 0) {
            $itemDetails[] = [
                'id' => 'FREE-SHIPPING',
                'price' => -(int)$voucherFreeShipping,
                'quantity' => 1,
                'name' => 'Free Shipping Voucher'
            ];
        }
        
        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int)$total
            ],
            'customer_details' => [
                'first_name' => $userDetails['name'] ?? 'Customer',
                'email' => $userDetails['email'] ?? '',
                'phone' => $userDetails['phone'] ?? '',
                'billing_address' => [
                    'address' => $userDetails['address'] ?? '',
                    'city' => '',
                    'postal_code' => '',
                    'country_code' => 'IDN'
                ],
                'shipping_address' => [
                    'address' => $userDetails['address'] ?? '',
                    'city' => '',
                    'postal_code' => '',
                    'country_code' => 'IDN'
                ]
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => 'https://' . $_SERVER['HTTP_HOST'] . '/member/orders.php?status=success'
            ]
        ];
        
        return $this->createSnapToken($params, $midtransOrderId);
    }
    
    /**
     * Create Snap Token via API
     */
    private function createSnapToken($params, $orderId) {
        $url = $this->apiUrl . '/snap/v1/transactions';
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($this->serverKey . ':')
            ],
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode != 201 && $httpCode != 200) {
            throw new Exception('Midtrans API Error: ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['token'])) {
            throw new Exception('Snap token not found in response');
        }
        
        return [
            'snap_token' => $result['token'],
            'order_id' => $orderId,
            'redirect_url' => $result['redirect_url'] ?? null
        ];
    }
    
    /**
     * Verify notification signature
     */
    public function verifySignature($orderId, $statusCode, $grossAmount, $signatureKey) {
        $mySignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        return $mySignature === $signatureKey;
    }
    
    /**
     * Get transaction status from Midtrans
     */
    public function getTransactionStatus($orderId) {
        $url = $this->apiUrl . '/v2/' . $orderId . '/status';
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($this->serverKey . ':')
            ],
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode != 200) {
            throw new Exception('Failed to get transaction status');
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get client key for frontend
     */
    public function getClientKey() {
        return $this->clientKey;
    }
    
    /**
     * Get Snap.js URL based on environment
     */
    public function getSnapJsUrl() {
        return $this->isProduction 
            ? 'https://app.midtrans.com/snap/snap.js' 
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }
}
