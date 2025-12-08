<?php
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    redirect('/admin/login.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'save_midtrans') {
            // Get existing gateway settings or create new
            $stmt = $pdo->prepare("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'midtrans'");
            $stmt->execute();
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE payment_gateway_settings 
                    SET server_key = ?, client_key = ?, merchant_id = ?, is_production = ?, is_active = ?
                    WHERE gateway_name = 'midtrans'
                ");
                $stmt->execute([
                    $_POST['midtrans_server_key'] ?? '',
                    $_POST['midtrans_client_key'] ?? '',
                    $_POST['midtrans_merchant_id'] ?? '',
                    isset($_POST['midtrans_production']) ? 1 : 0,
                    isset($_POST['midtrans_active']) ? 1 : 0
                ]);
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO payment_gateway_settings (gateway_name, server_key, client_key, merchant_id, is_production, is_active)
                    VALUES ('midtrans', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['midtrans_server_key'] ?? '',
                    $_POST['midtrans_client_key'] ?? '',
                    $_POST['midtrans_merchant_id'] ?? '',
                    isset($_POST['midtrans_production']) ? 1 : 0,
                    isset($_POST['midtrans_active']) ? 1 : 0
                ]);
            }
            
            $success = 'Midtrans settings saved successfully!';
        } elseif (isset($_POST['action']) && $_POST['action'] === 'save_biteship') {
            $stmt = $pdo->prepare("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'biteship'");
            $stmt->execute();
            $existing = $stmt->fetch();

            if ($existing) {
                $stmt = $pdo->prepare("
                    UPDATE payment_gateway_settings
                    SET server_key = ?, is_production = ?, is_active = ?
                    WHERE gateway_name = 'biteship'
                ");
                $stmt->execute([
                    $_POST['biteship_api_key'] ?? '',
                    isset($_POST['biteship_production']) ? 1 : 0,
                    isset($_POST['biteship_active']) ? 1 : 0
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO payment_gateway_settings (gateway_name, server_key, is_production, is_active)
                    VALUES ('biteship', ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['biteship_api_key'] ?? '',
                    isset($_POST['biteship_production']) ? 1 : 0,
                    isset($_POST['biteship_active']) ? 1 : 0
                ]);
            }

            $success = 'Biteship settings saved successfully!';
        }
    } catch (Exception $e) {
        $error = 'Error saving settings: ' . $e->getMessage();
    }
}

$stmt = $pdo->query("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'midtrans'");
$midtrans_settings = $stmt->fetch();

$stmt = $pdo->query("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'biteship'");
$biteship_settings = $stmt->fetch();

$page_title = 'API Settings - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<main class="admin-main">
    <div class="page-header">
        <h1>⚙️ API Integrations</h1>
        <p>Manage third-party API integrations for payment, shipping, and more</p>
    </div>

    <?php if ($success): ?>
        <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            ✓ <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            ✗ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <h2>Midtrans Payment Gateway</h2>
        <form method="POST">
            <input type="hidden" name="action" value="save_midtrans">
            
            <div class="form-group">
                <label>Server Key</label>
                <input type="text" name="midtrans_server_key" value="<?= htmlspecialchars($midtrans_settings['server_key'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Client Key</label>
                <input type="text" name="midtrans_client_key" value="<?= htmlspecialchars($midtrans_settings['client_key'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Merchant ID (Optional)</label>
                <input type="text" name="midtrans_merchant_id" value="<?= htmlspecialchars($midtrans_settings['merchant_id'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="midtrans_production" <?= ($midtrans_settings['is_production'] ?? 0) ? 'checked' : '' ?>>
                    Production Mode
                </label>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="midtrans_active" <?= ($midtrans_settings['is_active'] ?? 0) ? 'checked' : '' ?>>
                    Enable Midtrans
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">💾 Save Midtrans Settings</button>
        </form>
    </div>

    <div class="card" style="margin-top: 32px;">
        <h2>🚚 Biteship Shipping API</h2>
        <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
            Configure Biteship API for real-time shipping rates and tracking. Get your API key from
            <a href="https://biteship.com" target="_blank" style="color: #667eea;">biteship.com</a>
        </p>

        <form method="POST">
            <input type="hidden" name="action" value="save_biteship">

            <div class="form-group">
                <label>API Key</label>
                <input type="text" name="biteship_api_key" value="<?= htmlspecialchars($biteship_settings['server_key'] ?? '') ?>" placeholder="biteship_live_...">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="biteship_production" <?= ($biteship_settings['is_production'] ?? 0) ? 'checked' : '' ?>>
                    Production Mode (Live API Key)
                </label>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="biteship_active" <?= ($biteship_settings['is_active'] ?? 0) ? 'checked' : '' ?>>
                    Enable Biteship Shipping
                </label>
            </div>

            <button type="submit" class="btn btn-primary">💾 Save Biteship Settings</button>
        </form>
    </div>

    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-top: 32px;">
        <h3 style="margin: 0 0 12px 0; font-size: 16px;">📋 Integration Status</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-top: 16px;">
            <div style="background: white; padding: 16px; border-radius: 6px; border-left: 4px solid <?= ($midtrans_settings['is_active'] ?? 0) ? '#28a745' : '#dc3545' ?>;">
                <div style="font-weight: 600; margin-bottom: 4px;">Midtrans Payment</div>
                <div style="font-size: 14px; color: #666;">
                    Status: <strong style="color: <?= ($midtrans_settings['is_active'] ?? 0) ? '#28a745' : '#dc3545' ?>;">
                        <?= ($midtrans_settings['is_active'] ?? 0) ? '✓ Active' : '✗ Inactive' ?>
                    </strong>
                </div>
                <div style="font-size: 14px; color: #666;">
                    Mode: <?= ($midtrans_settings['is_production'] ?? 0) ? 'Production' : 'Sandbox' ?>
                </div>
            </div>

            <div style="background: white; padding: 16px; border-radius: 6px; border-left: 4px solid <?= ($biteship_settings['is_active'] ?? 0) ? '#28a745' : '#dc3545' ?>;">
                <div style="font-weight: 600; margin-bottom: 4px;">Biteship Shipping</div>
                <div style="font-size: 14px; color: #666;">
                    Status: <strong style="color: <?= ($biteship_settings['is_active'] ?? 0) ? '#28a745' : '#dc3545' ?>;">
                        <?= ($biteship_settings['is_active'] ?? 0) ? '✓ Active' : '✗ Inactive' ?>
                    </strong>
                </div>
                <div style="font-size: 14px; color: #666;">
                    Mode: <?= ($biteship_settings['is_production'] ?? 0) ? 'Production' : 'Test' ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
