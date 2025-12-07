<?php
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn() || getCurrentUser()['role'] !== 'admin') {
    redirect('/admin/login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $min_topup = floatval($_POST['min_topup'] ?? 0);
    $max_topup = !empty($_POST['max_topup']) ? floatval($_POST['max_topup']) : null;
    $commission_percent = floatval($_POST['commission_percent'] ?? 0);
    $free_shipping_vouchers = intval($_POST['free_shipping_vouchers'] ?? 0);

    if (empty($name)) {
        $error = 'Tier name is required!';
    } elseif ($commission_percent < 0 || $commission_percent > 100) {
        $error = 'Commission percentage must be between 0 and 100!';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO commission_tiers (name, min_topup, max_topup, commission_percent, free_shipping_vouchers)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $min_topup, $max_topup, $commission_percent, $free_shipping_vouchers]);

            $_SESSION['success'] = 'Commission tier created successfully!';
            redirect('/admin/referrals/index.php');
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$page_title = 'Add Commission Tier';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
.page-header { margin-bottom: 32px; }
.page-header h1 { font-size: 32px; color: #1A1A1A; margin-bottom: 8px; font-weight: 700; }
.page-header p { color: #6B7280; font-size: 15px; }
.form-container { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); max-width: 800px; }
.form-group { margin-bottom: 24px; }
label { display: block; margin-bottom: 8px; font-weight: 600; color: #1A1A1A; font-size: 15px; }
.help-text { font-size: 13px; color: #6B7280; margin-top: 4px; }
input[type="text"], input[type="number"] {
    width: 100%; padding: 12px 16px; border: 2px solid #E5E7EB; border-radius: 8px;
    font-size: 15px; font-family: 'Inter', sans-serif; transition: all 0.3s;
}
input:focus { outline: none; border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.btn { padding: 12px 28px; border-radius: 8px; font-size: 15px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; border: none; }
.btn-primary { background: #3B82F6; color: white; }
.btn-primary:hover { background: #2563EB; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
.btn-secondary { background: #E5E7EB; color: #4B5563; }
.btn-secondary:hover { background: #D1D5DB; }
.button-group { display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 2px solid #F0F0F0; }
.alert { padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; }
.alert-error { background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; }
.info-box {
    background: #EFF6FF; border: 2px solid #BFDBFE; border-radius: 12px; padding: 24px; margin-bottom: 24px;
}
.info-box h3 { font-size: 16px; color: #1E40AF; margin-bottom: 12px; }
.info-box ul { margin-left: 20px; line-height: 1.8; color: #1E3A8A; }
</style>

<div class="page-header">
    <h1>Add Commission Tier</h1>
    <p>Create a new referral commission tier</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="info-box">
    <h3>How Commission Tiers Work:</h3>
    <ul>
        <li>Commission is based on the <strong>referred user's FIRST topup amount</strong></li>
        <li>Referrer receives commission % + free shipping vouchers</li>
        <li>Reward is only paid when referred user completes their first topup</li>
        <li>Set Min Topup = 0 for the lowest tier</li>
        <li>Leave Max Topup empty for unlimited (highest tier)</li>
    </ul>
</div>

<div class="form-container">
    <form method="POST">
        <div class="form-group">
            <label for="name">Tier Name *</label>
            <input type="text" id="name" name="name" required placeholder="e.g., Tier 1: Under 500K">
            <div class="help-text">Descriptive name for this commission tier</div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="min_topup">Min Topup Amount (Rp) *</label>
                <input type="number" id="min_topup" name="min_topup" min="0" step="1000" required placeholder="0">
                <div class="help-text">Minimum first topup amount for this tier</div>
            </div>

            <div class="form-group">
                <label for="max_topup">Max Topup Amount (Rp)</label>
                <input type="number" id="max_topup" name="max_topup" min="0" step="1000" placeholder="Leave empty for unlimited">
                <div class="help-text">Maximum first topup amount (empty = no limit)</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="commission_percent">Commission Percentage (%) *</label>
                <input type="number" id="commission_percent" name="commission_percent" min="0" max="100" step="0.01" required placeholder="5.00">
                <div class="help-text">Commission percentage (e.g., 5 for 5%)</div>
            </div>

            <div class="form-group">
                <label for="free_shipping_vouchers">Free Shipping Vouchers *</label>
                <input type="number" id="free_shipping_vouchers" name="free_shipping_vouchers" min="0" step="1" required placeholder="1">
                <div class="help-text">Number of free shipping vouchers to give</div>
            </div>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">Create Tier</button>
            <a href="/admin/referrals/index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
