<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/auth-check.php';

// Save settings
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
            
            $_SESSION['success'] = 'Midtrans settings saved successfully!';
        }
        
        header('Location: /admin/settings/api-settings.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error saving settings: ' . $e->getMessage();
    }
}

// Get current settings
$stmt = $pdo->query("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'midtrans'");
$midtrans_settings = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<div class="content-wrapper">
    <h1>API Settings</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
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
            
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
