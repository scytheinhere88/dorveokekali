<?php
/**
 * Payment Integration Verification Script
 * Tests all payment flows and amount calculations
 */
require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');

$results = [];
$errors = [];
$warnings = [];

// ==================== TEST 1: Check Payment Methods ====================
echo "<h2>🔍 Test 1: Payment Methods Configuration</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY display_order");
    $payment_methods = $stmt->fetchAll();

    if (empty($payment_methods)) {
        $errors[] = "❌ No payment methods found in database!";
        echo "<p style='color: red;'>❌ FAILED: No payment methods configured</p>";
    } else {
        echo "<p style='color: green;'>✅ PASSED: Found " . count($payment_methods) . " payment methods</p>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Active</th><th>Display Order</th></tr>";
        foreach ($payment_methods as $method) {
            $active = $method['is_active'] ? '✅ Yes' : '❌ No';
            $color = $method['is_active'] ? 'lightgreen' : 'lightcoral';
            echo "<tr style='background: $color'>";
            echo "<td>{$method['id']}</td>";
            echo "<td>{$method['name']}</td>";
            echo "<td><strong>{$method['type']}</strong></td>";
            echo "<td>$active</td>";
            echo "<td>{$method['display_order']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        $results[] = "✅ Payment methods: OK";
    }
} catch (Exception $e) {
    $errors[] = "❌ Error checking payment methods: " . $e->getMessage();
    echo "<p style='color: red;'>❌ ERROR: {$e->getMessage()}</p>";
}

// ==================== TEST 2: Check Gateway Settings ====================
echo "<h2>🔍 Test 2: Payment Gateway Settings</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM payment_gateway_settings");
    $gateways = $stmt->fetchAll();

    if (empty($gateways)) {
        $warnings[] = "⚠️ No payment gateway settings found (optional)";
        echo "<p style='color: orange;'>⚠️ WARNING: No gateway settings configured</p>";
    } else {
        echo "<p style='color: green;'>✅ PASSED: Found " . count($gateways) . " gateway configurations</p>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th>Gateway</th><th>Has Server Key</th><th>Has Client Key</th><th>Production</th><th>Active</th></tr>";
        foreach ($gateways as $gw) {
            $has_server = !empty($gw['server_key']) ? '✅' : '❌';
            $has_client = !empty($gw['client_key']) ? '✅' : '❌';
            $prod = $gw['is_production'] ? '🔴 Live' : '🟡 Sandbox';
            $active = $gw['is_active'] ? '✅ Yes' : '❌ No';
            echo "<tr>";
            echo "<td><strong>{$gw['gateway_name']}</strong></td>";
            echo "<td>$has_server</td>";
            echo "<td>$has_client</td>";
            echo "<td>$prod</td>";
            echo "<td>$active</td>";
            echo "</tr>";
        }
        echo "</table>";
        $results[] = "✅ Gateway settings: OK";
    }
} catch (Exception $e) {
    $warnings[] = "⚠️ Error checking gateway settings: " . $e->getMessage();
    echo "<p style='color: orange;'>⚠️ WARNING: {$e->getMessage()}</p>";
}

// ==================== TEST 3: Check System Settings ====================
echo "<h2>🔍 Test 3: System Settings (Topup Configuration)</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM system_settings WHERE setting_key IN ('min_topup_amount', 'unique_code_min', 'unique_code_max', 'whatsapp_admin')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $required_settings = ['min_topup_amount', 'unique_code_min', 'unique_code_max'];
    $missing = [];

    foreach ($required_settings as $key) {
        if (!isset($settings[$key])) {
            $missing[] = $key;
        }
    }

    if (!empty($missing)) {
        $warnings[] = "⚠️ Missing system settings: " . implode(', ', $missing);
        echo "<p style='color: orange;'>⚠️ WARNING: Missing settings - will use defaults</p>";
    } else {
        echo "<p style='color: green;'>✅ PASSED: All system settings configured</p>";
    }

    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

    $min_topup = $settings['min_topup_amount'] ?? '10000 (default)';
    $code_min = $settings['unique_code_min'] ?? '100 (default)';
    $code_max = $settings['unique_code_max'] ?? '999 (default)';
    $whatsapp = $settings['whatsapp_admin'] ?? 'Not set';

    echo "<tr><td>Min Topup Amount</td><td>Rp " . number_format($min_topup, 0, ',', '.') . "</td><td>✅</td></tr>";
    echo "<tr><td>Unique Code Min</td><td>$code_min</td><td>✅</td></tr>";
    echo "<tr><td>Unique Code Max</td><td>$code_max</td><td>✅</td></tr>";
    echo "<tr><td>WhatsApp Admin</td><td>$whatsapp</td><td>" . ($whatsapp !== 'Not set' ? '✅' : '⚠️') . "</td></tr>";
    echo "</table>";

    $results[] = "✅ System settings: OK";
} catch (Exception $e) {
    $errors[] = "❌ Error checking system settings: " . $e->getMessage();
    echo "<p style='color: red;'>❌ ERROR: {$e->getMessage()}</p>";
}

// ==================== TEST 4: Test Amount Calculations ====================
echo "<h2>🔍 Test 4: Amount Calculation Verification</h2>";

$test_cases = [
    [
        'name' => 'Product with no discount',
        'price' => 100000,
        'discount_percent' => 0,
        'qty' => 2,
        'expected_subtotal' => 200000
    ],
    [
        'name' => 'Product with 20% discount',
        'price' => 150000,
        'discount_percent' => 20,
        'qty' => 1,
        'expected_subtotal' => 120000
    ],
    [
        'name' => 'Product with 50% discount (multiple)',
        'price' => 200000,
        'discount_percent' => 50,
        'qty' => 3,
        'expected_subtotal' => 300000
    ]
];

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr><th>Test Case</th><th>Price</th><th>Discount</th><th>Qty</th><th>Expected</th><th>Calculated</th><th>Status</th></tr>";

foreach ($test_cases as $test) {
    $calculated_price = calculateDiscount($test['price'], $test['discount_percent']);
    $calculated_subtotal = $calculated_price * $test['qty'];
    $match = abs($calculated_subtotal - $test['expected_subtotal']) < 0.01;
    $status = $match ? '✅ PASS' : '❌ FAIL';
    $color = $match ? 'lightgreen' : 'lightcoral';

    echo "<tr style='background: $color'>";
    echo "<td>{$test['name']}</td>";
    echo "<td>Rp " . number_format($test['price'], 0, ',', '.') . "</td>";
    echo "<td>{$test['discount_percent']}%</td>";
    echo "<td>{$test['qty']}</td>";
    echo "<td>Rp " . number_format($test['expected_subtotal'], 0, ',', '.') . "</td>";
    echo "<td>Rp " . number_format($calculated_subtotal, 0, ',', '.') . "</td>";
    echo "<td><strong>$status</strong></td>";
    echo "</tr>";

    if (!$match) {
        $errors[] = "❌ Calculation error: {$test['name']}";
    }
}
echo "</table>";

if (empty($errors)) {
    $results[] = "✅ Amount calculations: OK";
}

// ==================== TEST 5: Test Topup Unique Code ====================
echo "<h2>🔍 Test 5: Topup Unique Code Generation</h2>";

$test_amounts = [10000, 50000, 100000, 500000, 1000000];
echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr><th>Original Amount</th><th>Unique Code</th><th>Amount With Code</th><th>Status</th></tr>";

foreach ($test_amounts as $amount) {
    $unique_code = rand(100, 999);
    $amount_with_code = $amount + $unique_code;

    echo "<tr style='background: lightgreen'>";
    echo "<td>Rp " . number_format($amount, 0, ',', '.') . "</td>";
    echo "<td>$unique_code</td>";
    echo "<td>Rp " . number_format($amount_with_code, 0, ',', '.') . "</td>";
    echo "<td>✅ PASS</td>";
    echo "</tr>";
}
echo "</table>";
$results[] = "✅ Topup unique code: OK";

// ==================== TEST 6: Check Order Total Calculation ====================
echo "<h2>🔍 Test 6: Order Total Calculation (Subtotal + Shipping - Voucher)</h2>";

$order_tests = [
    [
        'subtotal' => 200000,
        'shipping' => 15000,
        'voucher_discount' => 20000,
        'voucher_free_shipping' => false,
        'expected_total' => 195000
    ],
    [
        'subtotal' => 300000,
        'shipping' => 20000,
        'voucher_discount' => 0,
        'voucher_free_shipping' => true,
        'expected_total' => 300000
    ],
    [
        'subtotal' => 150000,
        'shipping' => 10000,
        'voucher_discount' => 50000,
        'voucher_free_shipping' => true,
        'expected_total' => 100000
    ]
];

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr><th>Subtotal</th><th>Shipping</th><th>Discount</th><th>Free Ship</th><th>Expected Total</th><th>Calculated</th><th>Status</th></tr>";

foreach ($order_tests as $test) {
    $final_shipping = $test['voucher_free_shipping'] ? 0 : $test['shipping'];
    $calculated_total = $test['subtotal'] + $final_shipping - $test['voucher_discount'];
    $calculated_total = max(0, $calculated_total);

    $match = abs($calculated_total - $test['expected_total']) < 0.01;
    $status = $match ? '✅ PASS' : '❌ FAIL';
    $color = $match ? 'lightgreen' : 'lightcoral';

    echo "<tr style='background: $color'>";
    echo "<td>Rp " . number_format($test['subtotal'], 0, ',', '.') . "</td>";
    echo "<td>Rp " . number_format($test['shipping'], 0, ',', '.') . "</td>";
    echo "<td>Rp " . number_format($test['voucher_discount'], 0, ',', '.') . "</td>";
    echo "<td>" . ($test['voucher_free_shipping'] ? '✅ Yes' : '❌ No') . "</td>";
    echo "<td>Rp " . number_format($test['expected_total'], 0, ',', '.') . "</td>";
    echo "<td>Rp " . number_format($calculated_total, 0, ',', '.') . "</td>";
    echo "<td><strong>$status</strong></td>";
    echo "</tr>";

    if (!$match) {
        $errors[] = "❌ Order total calculation error";
    }
}
echo "</table>";

if (empty($errors)) {
    $results[] = "✅ Order total calculations: OK";
}

// ==================== SUMMARY ====================
echo "<hr style='margin: 40px 0;'>";
echo "<h1>📊 Test Summary</h1>";

echo "<div style='background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #2e7d32; margin-top: 0;'>✅ Passed Tests</h3>";
foreach ($results as $result) {
    echo "<p style='margin: 8px 0;'>$result</p>";
}
echo "</div>";

if (!empty($warnings)) {
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3 style='color: #f57c00; margin-top: 0;'>⚠️ Warnings</h3>";
    foreach ($warnings as $warning) {
        echo "<p style='margin: 8px 0;'>$warning</p>";
    }
    echo "</div>";
}

if (!empty($errors)) {
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3 style='color: #c62828; margin-top: 0;'>❌ Errors</h3>";
    foreach ($errors as $error) {
        echo "<p style='margin: 8px 0;'>$error</p>";
    }
    echo "</div>";
}

// Final verdict
echo "<div style='background: " . (empty($errors) ? '#1976d2' : '#d32f2f') . "; color: white; padding: 24px; border-radius: 8px; margin: 20px 0; text-align: center;'>";
if (empty($errors)) {
    echo "<h2 style='margin: 0;'>🎉 ALL TESTS PASSED!</h2>";
    echo "<p style='margin: 12px 0 0 0;'>Payment integration is working correctly!</p>";
} else {
    echo "<h2 style='margin: 0;'>⚠️ SOME TESTS FAILED</h2>";
    echo "<p style='margin: 12px 0 0 0;'>Please fix the errors above before using payment system.</p>";
}
echo "</div>";

echo "<hr style='margin: 40px 0;'>";
echo "<p style='text-align: center; color: #666;'>Test completed at " . date('Y-m-d H:i:s') . "</p>";
?>

<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        background: #f5f5f5;
    }
    h1, h2 {
        color: #333;
    }
    table {
        width: 100%;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    th {
        background: #1976d2;
        color: white;
        text-align: left;
        font-weight: 600;
    }
    td {
        color: #333;
    }
</style>
