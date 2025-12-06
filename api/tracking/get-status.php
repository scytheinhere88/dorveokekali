<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/BiteshipClient.php';

header('Content-Type: application/json');

try {
    $orderId = intval($_GET['order_id'] ?? 0);
    
    if (!$orderId) {
        throw new Exception('Order ID required');
    }
    
    // Get order dengan shipment data
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_number,
            o.fulfillment_status,
            o.created_at,
            bs.waybill_id,
            bs.courier_company,
            bs.courier_name,
            bs.courier_service_name,
            bs.status as biteship_status,
            bs.delivery_date,
            bs.shipping_cost,
            bs.weight_kg,
            oa.city as destination_city,
            oa.province as destination_province
        FROM orders o
        LEFT JOIN biteship_shipments bs ON o.id = bs.order_id
        LEFT JOIN order_addresses oa ON o.id = oa.order_id AND oa.type = 'shipping'
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Get tracking history from Biteship
    $trackingHistory = [];
    if ($order['waybill_id'] && $order['courier_company']) {
        try {
            $client = new BiteshipClient();
            $result = $client->trackOrder($order['waybill_id'], $order['courier_company']);
            
            if ($result['success']) {
                if (isset($result['data']['history'])) {
                    $trackingHistory = $result['data']['history'];
                }
                // Additional tracking info
                if (isset($result['data']['status'])) {
                    $order['biteship_status'] = $result['data']['status'];
                }
            }
        } catch (Exception $e) {
            // Continue without live tracking
        }
    }
    
    // Format status untuk display
    $statusMap = [
        'new' => ['label' => 'Pesanan Baru', 'icon' => '🆕', 'color' => '#3B82F6'],
        'waiting_print' => ['label' => 'Siap Dikirim', 'icon' => '📦', 'color' => '#8B5CF6'],
        'waiting_pickup' => ['label' => 'Menunggu Pickup Kurir', 'icon' => '🚚', 'color' => '#F59E0B'],
        'in_transit' => ['label' => 'Dalam Perjalanan', 'icon' => '🚚', 'color' => '#10B981'],
        'delivered' => ['label' => 'Terkirim', 'icon' => '✅', 'color' => '#10B981'],
        'cancelled' => ['label' => 'Dibatalkan', 'icon' => '❌', 'color' => '#EF4444']
    ];
    
    $currentStatus = $statusMap[$order['fulfillment_status']] ?? ['label' => 'Unknown', 'icon' => '❓', 'color' => '#6B7280'];
    
    echo json_encode([
        'success' => true,
        'order' => [
            'id' => $order['id'],
            'order_number' => $order['order_number'],
            'created_at' => $order['created_at'],
            'status' => $order['fulfillment_status'],
            'status_display' => $currentStatus,
            'waybill_id' => $order['waybill_id'],
            'courier' => [
                'company' => $order['courier_company'],
                'name' => $order['courier_name'],
                'service' => $order['courier_service_name']
            ],
            'destination' => [
                'city' => $order['destination_city'],
                'province' => $order['destination_province']
            ],
            'shipping_cost' => floatval($order['shipping_cost'] ?? 0),
            'weight_kg' => floatval($order['weight_kg'] ?? 0),
            'delivery_date' => $order['delivery_date']
        ],
        'tracking_history' => $trackingHistory,
        'has_tracking' => !empty($order['waybill_id'])
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}