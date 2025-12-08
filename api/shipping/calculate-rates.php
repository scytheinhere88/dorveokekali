<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/BiteshipClient.php';

header('Content-Type: application/json');

// Enable error logging
error_log("Calculate Rates API Called at " . date('Y-m-d H:i:s'));

try {
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Calculate Rates Input: " . json_encode($input));

    // Get destination from input
    $destination = [
        'postal_code' => $input['postal_code'] ?? null,
        'area_id' => $input['area_id'] ?? null,
        'latitude' => $input['latitude'] ?? null,
        'longitude' => $input['longitude'] ?? null
    ];

    // Remove null values
    $destination = array_filter($destination, function($v) { return $v !== null; });

    if (empty($destination)) {
        throw new Exception('No destination information provided (postal_code, area_id, or lat/lng required)');
    }

    // Get items
    $items = $input['items'] ?? [];

    if (empty($items)) {
        throw new Exception('No items provided');
    }

    // Get store origin from system_settings (try multiple tables for compatibility)
    $storeSettings = [];

    // Try system_settings first
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'store_%'");
        while ($row = $stmt->fetch()) {
            $storeSettings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        error_log("Could not query system_settings: " . $e->getMessage());
    }

    // Try site_settings if system_settings is empty
    if (empty($storeSettings)) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'store_%'");
            while ($row = $stmt->fetch()) {
                $storeSettings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Could not query site_settings: " . $e->getMessage());
        }
    }

    // Set origin with fallback to Jakarta postal code
    $origin = [
        'postal_code' => $storeSettings['store_postal_code'] ?? '12190'
    ];

    error_log("Origin: " . json_encode($origin));
    error_log("Destination: " . json_encode($destination));

    // Initialize Biteship client
    $client = new BiteshipClient();

    // Get courier codes from settings
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'biteship_default_couriers'");
        $stmt->execute();
        $courierCodes = $stmt->fetchColumn();
    } catch (Exception $e) {
        $courierCodes = null;
    }

    if (!$courierCodes) {
        $courierCodes = 'jne,jnt,sicepat,anteraja,idexpress,ninja';
    }

    error_log("Courier Codes: " . $courierCodes);

    // Get rates
    $result = $client->getRates($origin, $destination, $items, $courierCodes);

    error_log("Biteship API Response: " . json_encode($result));
    
    if ($result['success']) {
        $pricing = $result['data']['pricing'] ?? [];
        
        // Format response - ONLY AVAILABLE COURIERS
        $rates = [];
        $unavailableCouriers = [];
        
        foreach ($pricing as $rate) {
            $price = floatval($rate['price'] ?? 0);
            $courierCompany = $rate['courier_company'] ?? '';
            $courierService = $rate['courier_service_name'] ?? '';
            
            // Only include couriers with valid price (available)
            if ($price > 0 && !empty($courierCompany)) {
                $rates[] = [
                    'courier_company' => $courierCompany,
                    'courier_name' => $rate['courier_name'] ?? '',
                    'courier_service_name' => $courierService,
                    'rate_id' => $rate['rate_id'] ?? '',
                    'price' => $price,
                    'duration' => $rate['duration'] ?? '',
                    'description' => $rate['description'] ?? '',
                    'available' => true
                ];
            } else {
                // Track unavailable couriers for logging
                $unavailableCouriers[] = $courierCompany . ' - ' . $courierService;
            }
        }
        
        // Sort by price (cheapest first)
        usort($rates, function($a, $b) {
            return $a['price'] - $b['price'];
        });
        
        echo json_encode([
            'success' => true,
            'rates' => $rates,
            'total_available' => count($rates),
            'unavailable_couriers' => $unavailableCouriers, // For debugging
            'message' => count($rates) > 0 ? 'Rates available' : 'No couriers available for this area'
        ]);
    } else {
        throw new Exception($result['error'] ?? 'Failed to get shipping rates');
    }
    
} catch (Exception $e) {
    error_log("Calculate Rates Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}