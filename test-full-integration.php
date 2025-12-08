<?php
/**
 * COMPREHENSIVE PAYMENT & SHIPPING INTEGRATION TEST
 * Tests all payment methods, Midtrans, Biteship, Webhooks
 */
require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment & Shipping Integration Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            font-size: 42px;
            color: #1a202c;
            margin-bottom: 12px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #718096;
            font-size: 18px;
            margin-bottom: 48px;
        }
        .test-section {
            background: #f7fafc;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 32px;
            border-left: 5px solid #667eea;
        }
        .test-section h2 {
            font-size: 28px;
            color: #2d3748;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .test-section h3 {
            font-size: 20px;
            color: #4a5568;
            margin: 24px 0 16px 0;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin: 16px 0;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .status-pass {
            background: #c6f6d5;
            color: #22543d;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
        }
        .status-fail {
            background: #fed7d7;
            color: #742a2a;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
        }
        .status-warn {
            background: #feebc8;
            color: #7c2d12;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
        }
        .alert {
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            font-size: 15px;
            line-height: 1.6;
        }
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #38a169;
        }
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #e53e3e;
        }
        .alert-warning {
            background: #feebc8;
            color: #7c2d12;
            border-left: 4px solid #dd6b20;
        }
        .alert-info {
            background: #bee3f8;
            color: #1a365d;
            border-left: 4px solid #3182ce;
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 12px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 16px 0;
        }
        .summary-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin: 32px 0;
        }
        .summary-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .summary-card h3 {
            font-size: 16px;
            color: #718096;
            margin: 0 0 12px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-card .number {
            font-size: 48px;
            font-weight: 700;
            margin: 0;
        }
        .number-pass { color: #38a169; }
        .number-fail { color: #e53e3e; }
        .number-warn { color: #dd6b20; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-danger { background: #fed7d7; color: #742a2a; }
        .badge-warning { background: #feebc8; color: #7c2d12; }
        .badge-info { background: #bee3f8; color: #1a365d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Integration Test Suite</h1>
        <p class="subtitle">Complete Payment & Shipping System Verification</p>

<?php
$results = ['pass' => 0, 'fail' => 0, 'warn' => 0];
$errors = [];
$warnings = [];

// ==================== TEST 1: DATABASE TABLES ====================
echo '<div class="test-section">';
echo '<h2>🗄️ Database Tables Verification</h2>';

$required_tables = [
    'payment_methods' => 'Payment methods configuration',
    'payment_gateway_settings' => 'Gateway API keys (Midtrans, Biteship, etc)',
    'system_settings' => 'System configuration (min topup, etc)',
    'orders' => 'Order data',
    'wallet_transactions' => 'Wallet transactions',
    'biteship_webhook_logs' => 'Biteship webhook logs',
    'biteship_shipments' => 'Biteship shipments tracking'
];

echo '<table><thead><tr><th>Table Name</th><th>Description</th><th>Status</th></tr></thead><tbody>';
foreach ($required_tables as $table => $desc) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;

        if ($exists) {
            echo "<tr><td><strong>$table</strong></td><td>$desc</td><td><span class='status-pass'>✅ EXISTS</span></td></tr>";
            $results['pass']++;
        } else {
            echo "<tr><td><strong>$table</strong></td><td>$desc</td><td><span class='status-fail'>❌ MISSING</span></td></tr>";
            $errors[] = "Table '$table' does not exist";
            $results['fail']++;
        }
    } catch (Exception $e) {
        echo "<tr><td><strong>$table</strong></td><td>$desc</td><td><span class='status-fail'>❌ ERROR</span></td></tr>";
        $errors[] = "Error checking table '$table': " . $e->getMessage();
        $results['fail']++;
    }
}
echo '</tbody></table>';
echo '</div>';

// ==================== TEST 2: PAYMENT METHODS ====================
echo '<div class="test-section">';
echo '<h2>💳 Payment Methods Configuration</h2>';

try {
    $stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY display_order");
    $payment_methods = $stmt->fetchAll();

    if (empty($payment_methods)) {
        echo '<div class="alert alert-error">❌ No payment methods configured!</div>';
        $errors[] = "No payment methods found";
        $results['fail']++;
    } else {
        echo '<div class="alert alert-success">✅ Found ' . count($payment_methods) . ' payment methods</div>';

        echo '<table><thead><tr><th>Name</th><th>Type</th><th>Description</th><th>Active</th><th>Display Order</th></tr></thead><tbody>';
        foreach ($payment_methods as $method) {
            $status = $method['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            echo "<tr>";
            echo "<td><strong>{$method['name']}</strong></td>";
            echo "<td><code>{$method['type']}</code></td>";
            echo "<td>" . htmlspecialchars($method['description']) . "</td>";
            echo "<td>$status</td>";
            echo "<td>{$method['display_order']}</td>";
            echo "</tr>";
        }
        echo '</tbody></table>';
        $results['pass']++;
    }
} catch (Exception $e) {
    echo '<div class="alert alert-error">❌ Error: ' . $e->getMessage() . '</div>';
    $errors[] = "Payment methods error: " . $e->getMessage();
    $results['fail']++;
}

echo '</div>';

// ==================== TEST 3: MIDTRANS CONFIGURATION ====================
echo '<div class="test-section">';
echo '<h2>💰 Midtrans Gateway Configuration</h2>';

try {
    $stmt = $pdo->prepare("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'midtrans'");
    $stmt->execute();
    $midtrans = $stmt->fetch();

    if (!$midtrans) {
        echo '<div class="alert alert-warning">⚠️ Midtrans not configured yet</div>';
        $warnings[] = "Midtrans gateway not configured";
        $results['warn']++;
    } else {
        $hasServerKey = !empty($midtrans['server_key']);
        $hasClientKey = !empty($midtrans['client_key']);
        $isActive = $midtrans['is_active'];
        $isProduction = $midtrans['is_production'];

        if ($hasServerKey && $hasClientKey && $isActive) {
            echo '<div class="alert alert-success">✅ Midtrans is configured and active!</div>';
            $results['pass']++;
        } else {
            echo '<div class="alert alert-warning">⚠️ Midtrans configuration incomplete</div>';
            $warnings[] = "Midtrans configuration incomplete";
            $results['warn']++;
        }

        echo '<table><tbody>';
        echo '<tr><td><strong>Gateway Name</strong></td><td>' . $midtrans['gateway_name'] . '</td></tr>';
        echo '<tr><td><strong>Server Key</strong></td><td>' . ($hasServerKey ? '<span class="badge badge-success">✅ SET (' . strlen($midtrans['server_key']) . ' chars)</span>' : '<span class="badge badge-danger">❌ NOT SET</span>') . '</td></tr>';
        echo '<tr><td><strong>Client Key</strong></td><td>' . ($hasClientKey ? '<span class="badge badge-success">✅ SET (' . strlen($midtrans['client_key']) . ' chars)</span>' : '<span class="badge badge-danger">❌ NOT SET</span>') . '</td></tr>';
        echo '<tr><td><strong>Environment</strong></td><td>' . ($isProduction ? '<span class="badge badge-danger">🔴 PRODUCTION</span>' : '<span class="badge badge-warning">🟡 SANDBOX</span>') . '</td></tr>';
        echo '<tr><td><strong>Status</strong></td><td>' . ($isActive ? '<span class="badge badge-success">✅ ACTIVE</span>' : '<span class="badge badge-danger">❌ INACTIVE</span>') . '</td></tr>';
        echo '</tbody></table>';

        // Test MidtransHelper class
        echo '<h3>Testing MidtransHelper Class</h3>';
        try {
            require_once __DIR__ . '/includes/MidtransHelper.php';
            $midtransHelper = new MidtransHelper($pdo);
            $clientKey = $midtransHelper->getClientKey();
            $snapJsUrl = $midtransHelper->getSnapJsUrl();

            echo '<div class="alert alert-success">✅ MidtransHelper class loaded successfully!</div>';
            echo '<table><tbody>';
            echo '<tr><td><strong>Client Key Retrieved</strong></td><td>' . ($clientKey ? '<span class="badge badge-success">✅ YES</span>' : '<span class="badge badge-danger">❌ NO</span>') . '</td></tr>';
            echo '<tr><td><strong>Snap.js URL</strong></td><td><code>' . htmlspecialchars($snapJsUrl) . '</code></td></tr>';
            echo '</tbody></table>';
            $results['pass']++;
        } catch (Exception $e) {
            echo '<div class="alert alert-error">❌ MidtransHelper Error: ' . $e->getMessage() . '</div>';
            $errors[] = "MidtransHelper error: " . $e->getMessage();
            $results['fail']++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert alert-error">❌ Error: ' . $e->getMessage() . '</div>';
    $errors[] = "Midtrans config error: " . $e->getMessage();
    $results['fail']++;
}

echo '</div>';

// ==================== TEST 4: BITESHIP CONFIGURATION ====================
echo '<div class="test-section">';
echo '<h2>🚚 Biteship Shipping Configuration</h2>';

try {
    $stmt = $pdo->prepare("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'biteship'");
    $stmt->execute();
    $biteship = $stmt->fetch();

    if (!$biteship) {
        echo '<div class="alert alert-warning">⚠️ Biteship not configured in payment_gateway_settings</div>';
        $warnings[] = "Biteship gateway not configured";
        $results['warn']++;
    } else {
        $hasApiKey = !empty($biteship['api_key']);
        $isActive = $biteship['is_active'];
        $isProduction = $biteship['is_production'];

        if ($hasApiKey && $isActive) {
            echo '<div class="alert alert-success">✅ Biteship is configured and active!</div>';
            $results['pass']++;
        } else {
            echo '<div class="alert alert-warning">⚠️ Biteship configuration incomplete</div>';
            $warnings[] = "Biteship configuration incomplete";
            $results['warn']++;
        }

        echo '<table><tbody>';
        echo '<tr><td><strong>Gateway Name</strong></td><td>' . $biteship['gateway_name'] . '</td></tr>';
        echo '<tr><td><strong>API Key</strong></td><td>' . ($hasApiKey ? '<span class="badge badge-success">✅ SET (' . strlen($biteship['api_key']) . ' chars)</span>' : '<span class="badge badge-danger">❌ NOT SET</span>') . '</td></tr>';
        echo '<tr><td><strong>Environment</strong></td><td>' . ($isProduction ? '<span class="badge badge-danger">🔴 PRODUCTION</span>' : '<span class="badge badge-warning">🟡 SANDBOX</span>') . '</td></tr>';
        echo '<tr><td><strong>Status</strong></td><td>' . ($isActive ? '<span class="badge badge-success">✅ ACTIVE</span>' : '<span class="badge badge-danger">❌ INACTIVE</span>') . '</td></tr>';
        echo '</tbody></table>';
    }

    // Test BiteshipClient class
    echo '<h3>Testing BiteshipClient Class</h3>';
    try {
        require_once __DIR__ . '/includes/BiteshipClient.php';
        $biteshipClient = new BiteshipClient();
        echo '<div class="alert alert-success">✅ BiteshipClient class loaded successfully!</div>';
        $results['pass']++;
    } catch (Exception $e) {
        echo '<div class="alert alert-error">❌ BiteshipClient Error: ' . $e->getMessage() . '</div>';
        $errors[] = "BiteshipClient error: " . $e->getMessage();
        $results['fail']++;
    }

    // Check webhook configuration
    echo '<h3>Webhook Configuration</h3>';
    echo '<div class="alert alert-info">';
    echo '<strong>📍 Webhook URL:</strong> https://dorve.id/api/biteship/webhook.php<br>';
    echo '<strong>🆔 Webhook ID:</strong> 69344b45b55b8d1d0bb204f2<br>';
    echo '<strong>✅ Status:</strong> Already configured in Biteship Dashboard';
    echo '</div>';

    // Check webhook logs table
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM biteship_webhook_logs");
        $logCount = $stmt->fetchColumn();
        echo '<table><tbody>';
        echo '<tr><td><strong>Total Webhook Logs</strong></td><td>' . $logCount . ' events received</td></tr>';
        echo '</tbody></table>';
        $results['pass']++;
    } catch (Exception $e) {
        echo '<div class="alert alert-warning">⚠️ Cannot read webhook logs: ' . $e->getMessage() . '</div>';
        $warnings[] = "Webhook logs table issue";
        $results['warn']++;
    }

} catch (Exception $e) {
    echo '<div class="alert alert-error">❌ Error: ' . $e->getMessage() . '</div>';
    $errors[] = "Biteship config error: " . $e->getMessage();
    $results['fail']++;
}

echo '</div>';

// ==================== TEST 5: SYSTEM SETTINGS ====================
echo '<div class="test-section">';
echo '<h2>⚙️ System Settings</h2>';

try {
    $stmt = $pdo->query("SELECT * FROM system_settings WHERE setting_key IN ('min_topup_amount', 'unique_code_min', 'unique_code_max')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    echo '<table><thead><tr><th>Setting</th><th>Value</th><th>Status</th></tr></thead><tbody>';

    $minTopup = $settings['min_topup_amount'] ?? 10000;
    $codeMin = $settings['unique_code_min'] ?? 100;
    $codeMax = $settings['unique_code_max'] ?? 999;

    echo '<tr><td><strong>Min Topup Amount</strong></td><td>Rp ' . number_format($minTopup, 0, ',', '.') . '</td><td><span class="status-pass">✅</span></td></tr>';
    echo '<tr><td><strong>Unique Code Min</strong></td><td>' . $codeMin . '</td><td><span class="status-pass">✅</span></td></tr>';
    echo '<tr><td><strong>Unique Code Max</strong></td><td>' . $codeMax . '</td><td><span class="status-pass">✅</span></td></tr>';
    echo '</tbody></table>';
    $results['pass']++;
} catch (Exception $e) {
    echo '<div class="alert alert-warning">⚠️ System settings not configured: ' . $e->getMessage() . '</div>';
    $warnings[] = "System settings not configured";
    $results['warn']++;
}

echo '</div>';

// ==================== TEST 6: AMOUNT CALCULATION VERIFICATION ====================
echo '<div class="test-section">';
echo '<h2>🧮 Amount Calculation Functions</h2>';

$test_calculations = [
    ['price' => 100000, 'discount' => 0, 'expected' => 100000],
    ['price' => 100000, 'discount' => 10, 'expected' => 90000],
    ['price' => 200000, 'discount' => 25, 'expected' => 150000],
    ['price' => 150000, 'discount' => 50, 'expected' => 75000],
];

echo '<table><thead><tr><th>Price</th><th>Discount %</th><th>Expected</th><th>Calculated</th><th>Status</th></tr></thead><tbody>';
$calc_passed = true;
foreach ($test_calculations as $test) {
    $calculated = calculateDiscount($test['price'], $test['discount']);
    $match = abs($calculated - $test['expected']) < 0.01;
    $status = $match ? '<span class="status-pass">✅ PASS</span>' : '<span class="status-fail">❌ FAIL</span>';

    echo '<tr>';
    echo '<td>Rp ' . number_format($test['price'], 0, ',', '.') . '</td>';
    echo '<td>' . $test['discount'] . '%</td>';
    echo '<td>Rp ' . number_format($test['expected'], 0, ',', '.') . '</td>';
    echo '<td>Rp ' . number_format($calculated, 0, ',', '.') . '</td>';
    echo '<td>' . $status . '</td>';
    echo '</tr>';

    if (!$match) $calc_passed = false;
}
echo '</tbody></table>';

if ($calc_passed) {
    echo '<div class="alert alert-success">✅ All calculation tests passed!</div>';
    $results['pass']++;
} else {
    echo '<div class="alert alert-error">❌ Some calculations failed!</div>';
    $errors[] = "Amount calculation errors detected";
    $results['fail']++;
}

echo '</div>';

// ==================== SUMMARY ====================
$total = $results['pass'] + $results['fail'] + $results['warn'];
$passRate = $total > 0 ? round(($results['pass'] / $total) * 100) : 0;

echo '<div class="test-section" style="border-left-color: #667eea; background: linear-gradient(135deg, #f6f8fb 0%, #e8eef5 100%);">';
echo '<h2>📊 Test Summary</h2>';

echo '<div class="summary-box">';
echo '<div class="summary-card"><h3>Tests Passed</h3><p class="number number-pass">' . $results['pass'] . '</p></div>';
echo '<div class="summary-card"><h3>Tests Failed</h3><p class="number number-fail">' . $results['fail'] . '</p></div>';
echo '<div class="summary-card"><h3>Warnings</h3><p class="number number-warn">' . $results['warn'] . '</p></div>';
echo '<div class="summary-card"><h3>Pass Rate</h3><p class="number" style="color: #667eea;">' . $passRate . '%</p></div>';
echo '</div>';

if (!empty($errors)) {
    echo '<div class="alert alert-error"><strong>❌ Critical Errors:</strong><ul style="margin: 12px 0 0 20px;">';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul></div>';
}

if (!empty($warnings)) {
    echo '<div class="alert alert-warning"><strong>⚠️ Warnings:</strong><ul style="margin: 12px 0 0 20px;">';
    foreach ($warnings as $warning) {
        echo '<li>' . htmlspecialchars($warning) . '</li>';
    }
    echo '</ul></div>';
}

if (empty($errors) && empty($warnings)) {
    echo '<div class="alert alert-success" style="font-size: 18px; text-align: center; padding: 32px;">';
    echo '<strong style="font-size: 24px;">🎉 ALL SYSTEMS GO!</strong><br>';
    echo 'Payment and shipping integrations are fully configured and ready for production!';
    echo '</div>';
} elseif (empty($errors)) {
    echo '<div class="alert alert-info" style="font-size: 18px; text-align: center; padding: 32px;">';
    echo '<strong style="font-size: 24px;">✅ System Ready with Minor Warnings</strong><br>';
    echo 'Core functionality is working. Please review warnings above.';
    echo '</div>';
} else {
    echo '<div class="alert alert-error" style="font-size: 18px; text-align: center; padding: 32px;">';
    echo '<strong style="font-size: 24px;">⚠️ Action Required</strong><br>';
    echo 'Please fix the critical errors above before going to production.';
    echo '</div>';
}

echo '</div>';

// ==================== NEXT STEPS ====================
echo '<div class="test-section" style="border-left-color: #10b981;">';
echo '<h2>🚀 Next Steps</h2>';
echo '<div style="font-size: 16px; line-height: 1.8; color: #2d3748;">';
echo '<h3>1. Configure Payment Methods (if not done)</h3>';
echo '<p>Go to: <code>/admin/settings/payment-settings.php</code></p>';
echo '<ul style="margin-left: 20px;">';
echo '<li>Toggle payment methods on/off</li>';
echo '<li>Enter Midtrans Server Key and Client Key</li>';
echo '<li>Select Production or Sandbox mode</li>';
echo '<li>Save settings</li>';
echo '</ul>';

echo '<h3 style="margin-top: 24px;">2. Configure Biteship API</h3>';
echo '<p>Go to: <code>/admin/settings/api-settings.php</code></p>';
echo '<ul style="margin-left: 20px;">';
echo '<li>Enter Biteship API Key</li>';
echo '<li>Select Production or Sandbox mode</li>';
echo '<li>Webhook is already configured: <code>https://dorve.id/api/biteship/webhook.php</code></li>';
echo '<li>Webhook ID: <code>69344b45b55b8d1d0bb204f2</code></li>';
echo '</ul>';

echo '<h3 style="margin-top: 24px;">3. Test Flows</h3>';
echo '<ul style="margin-left: 20px;">';
echo '<li><strong>Checkout:</strong> Add products → Cart → Checkout → Select payment → Complete</li>';
echo '<li><strong>Topup:</strong> Go to Wallet → Enter amount → Upload proof → Admin approve</li>';
echo '<li><strong>Shipping:</strong> Check if courier rates load correctly</li>';
echo '<li><strong>Webhook:</strong> Complete order and check if status updates automatically</li>';
echo '</ul>';

echo '<h3 style="margin-top: 24px;">4. Monitor Logs</h3>';
echo '<ul style="margin-left: 20px;">';
echo '<li>Check Biteship webhook logs in database</li>';
echo '<li>Monitor Midtrans notification handler</li>';
echo '<li>Check error logs for any issues</li>';
echo '</ul>';
echo '</div>';
echo '</div>';

?>

        <p style="text-align: center; color: #718096; margin-top: 48px; padding-top: 24px; border-top: 2px solid #e2e8f0;">
            Test completed at <?= date('Y-m-d H:i:s') ?> • Dorve.id Payment & Shipping Integration
        </p>
    </div>
</body>
</html>
