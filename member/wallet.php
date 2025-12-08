<?php
require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user = getCurrentUser();

// Handle step parameter
$step = $_GET['step'] ?? 'list';
$txn_id = $_GET['txn_id'] ?? null;

// Check payment methods availability
$payment_enabled = [];
try {
    $stmt = $pdo->query("SELECT * FROM payment_methods WHERE is_active = 1");
    $payment_methods = $stmt->fetchAll();
    foreach ($payment_methods as $method) {
        $payment_enabled[$method['type']] = true;
    }
} catch (Exception $e) {
    $payment_enabled = [];
}

// Get Midtrans settings if enabled
if (isset($payment_enabled['midtrans']) && $payment_enabled['midtrans']) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM payment_gateway_settings WHERE gateway_name = 'midtrans' AND is_active = 1");
        $stmt->execute();
        $midtrans_settings = $stmt->fetch();

        if ($midtrans_settings) {
            define('MIDTRANS_CLIENT_KEY', $midtrans_settings['client_key']);
            define('MIDTRANS_SNAP_URL', $midtrans_settings['is_production'] ?
                'https://app.midtrans.com/snap/snap.js' :
                'https://app.sandbox.midtrans.com/snap/snap.js'
            );
        }
    } catch (Exception $e) {
        // Midtrans not configured
        $payment_enabled['midtrans'] = false;
    }
}

// Get bank accounts from database
try {
    $stmt = $pdo->query("SELECT * FROM bank_accounts WHERE is_active = 1 ORDER BY display_order ASC");
    $all_banks = $stmt->fetchAll();
    if (empty($all_banks)) {
        // Fallback if no active banks
        $all_banks = [
            ['id' => 1, 'bank_name' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'Dorve House', 'is_active' => 1],
            ['id' => 2, 'bank_name' => 'Mandiri', 'account_number' => '0987654321', 'account_name' => 'Dorve House', 'is_active' => 1],
        ];
    }
} catch (Exception $e) {
    // Fallback if table doesn't exist
    $all_banks = [
        ['id' => 1, 'bank_name' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'Dorve House', 'is_active' => 1],
        ['id' => 2, 'bank_name' => 'Mandiri', 'account_number' => '0987654321', 'account_name' => 'Dorve House', 'is_active' => 1],
    ];
}

// Get wallet transactions
$stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

// If step is 'confirm', get transaction details with bank info
$pending_txn = null;
if ($step === 'confirm' && $txn_id) {
    $stmt = $pdo->prepare("
        SELECT
            wt.*,
            ba.bank_name,
            ba.account_number,
            ba.account_name
        FROM wallet_transactions wt
        LEFT JOIN bank_accounts ba ON wt.bank_account_id = ba.id
        WHERE wt.id = ? AND wt.user_id = ?
    ");
    $stmt->execute([$txn_id, $_SESSION['user_id']]);
    $pending_txn = $stmt->fetch();
}

$page_title = 'My Wallet - Dorve';
$active_page = 'wallet';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/member-layout-horizontal.php';
?>

<style>

    .wallet-balance-card { background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%); color: var(--white); padding: 40px; border-radius: 16px; margin-bottom: 40px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); }
    .balance-label { font-size: 14px; opacity: 0.8; margin-bottom: 12px; letter-spacing: 1px; text-transform: uppercase; }
    .balance-amount { font-family: 'Playfair Display', serif; font-size: 48px; font-weight: 700; margin-bottom: 30px; }
    .wallet-actions { display: flex; gap: 16px; }
    .btn { padding: 14px 32px; border-radius: 8px; text-decoration: none; font-size: 15px; font-weight: 600; transition: all 0.3s; display: inline-block; text-align: center; border: none; cursor: pointer; }
    .btn-topup { background: var(--white); color: var(--charcoal); }
    .btn-topup:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(255,255,255,0.3); }

    .quick-topup { background: var(--white); padding: 30px; border-radius: 12px; margin-bottom: 40px; border: 1px solid rgba(0,0,0,0.08); }
    .quick-topup h3 { font-family: 'Playfair Display', serif; font-size: 24px; margin-bottom: 24px; }
    .topup-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .topup-option { padding: 20px; border: 2px solid rgba(0,0,0,0.1); border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.3s; }
    .topup-option:hover { border-color: var(--charcoal); background: var(--cream); }
    .topup-option.selected { border-color: var(--charcoal); background: var(--charcoal); color: var(--white); }
    .topup-amount { font-size: 20px; font-weight: 700; }
    .custom-amount { margin-bottom: 24px; }
    .custom-amount label { display: block; margin-bottom: 8px; font-weight: 600; }
    .custom-amount input { width: 100%; padding: 14px 16px; border: 1px solid rgba(0,0,0,0.15); border-radius: 8px; font-size: 15px; }

    .payment-section h4 { margin-bottom: 16px; font-weight: 600; }
    .bank-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .bank-card { padding: 20px; border: 2px solid rgba(0,0,0,0.1); border-radius: 8px; cursor: pointer; transition: all 0.3s; background: var(--white); }
    .bank-card:hover { border-color: var(--charcoal); background: var(--cream); }
    .bank-card.selected { border-color: var(--charcoal); background: var(--charcoal); color: var(--white); }
    .bank-card.disabled { opacity: 0.4; cursor: not-allowed; background: #f5f5f5; }
    .bank-card.disabled:hover { border-color: rgba(0,0,0,0.1); background: #f5f5f5; }
    .bank-name { font-weight: 700; margin-bottom: 4px; font-size: 16px; }
    .bank-number { font-family: monospace; font-size: 14px; opacity: 0.8; }

    .confirmation-card { background: var(--white); padding: 40px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); margin-bottom: 30px; }
    .amount-display { text-align: center; padding: 40px; background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%); border-radius: 12px; margin-bottom: 30px; }
    .amount-label { color: rgba(255,255,255,0.8); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
    .amount-value { color: var(--white); font-family: 'Playfair Display', serif; font-size: 48px; font-weight: 700; }
    .unique-code { color: #FCD34D; font-size: 24px; margin-top: 8px; }

    .transfer-details { background: var(--cream); padding: 24px; border-radius: 8px; margin-bottom: 24px; }
    .transfer-details h4 { margin-bottom: 16px; }
    .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(0,0,0,0.08); }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: var(--grey); }
    .detail-value { font-weight: 600; }

    .instructions { background: #FEF3C7; padding: 20px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #F59E0B; }
    .instructions h4 { margin-bottom: 12px; color: #92400E; }
    .instructions ol { margin-left: 20px; color: #78350F; }
    .instructions li { margin-bottom: 8px; }

    .upload-section { margin-top: 30px; }
    .upload-box { border: 2px dashed rgba(0,0,0,0.2); padding: 30px; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.3s; }
    .upload-box:hover { border-color: var(--charcoal); background: var(--cream); }
    .upload-box input { display: none; }
    .upload-label { font-weight: 600; margin-bottom: 8px; }
    .upload-hint { font-size: 14px; color: var(--grey); }

    .transactions-card { background: var(--white); padding: 30px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); }
    .transactions-card h3 { font-family: 'Playfair Display', serif; font-size: 24px; margin-bottom: 24px; }
    .transaction-item { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid rgba(0,0,0,0.05); }
    .transaction-item:last-child { border-bottom: none; }
    .transaction-info { flex: 1; margin-right: 20px; }
    .transaction-title { font-weight: 600; margin-bottom: 4px; }
    .transaction-date { font-size: 13px; color: var(--grey); }
    .transaction-amount { font-weight: 700; font-size: 18px; }
    .transaction-amount.credit { color: #10B981; }
    .transaction-status { padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-left: 12px; }
    .transaction-status.success { background: #D1FAE5; color: #065F46; }
    .transaction-status.pending { background: #FEF3C7; color: #92400E; }
    .transaction-status.rejected { background: #FEE2E2; color: #991B1B; }

    .alert { padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; }
    .alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #10B981; }
    .alert-error { background: #FEE2E2; color: #991B1B; border: 1px solid #EF4444; }
    .alert-info { background: #DBEAFE; color: #1E40AF; border: 1px solid #3B82F6; }

    .empty-state { text-align: center; padding: 60px 40px; color: var(--grey); }

    /* Modal */
    .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
    .modal-content { background-color: var(--white); margin: 10% auto; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    .modal-header { font-size: 24px; font-weight: 700; margin-bottom: 16px; }
    .modal-body { margin-bottom: 24px; line-height: 1.6; }
    .modal-buttons { display: flex; gap: 12px; }
    .modal-buttons button { flex: 1; padding: 12px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-modal-primary { background: var(--charcoal); color: var(--white); }
    .btn-modal-secondary { background: var(--cream); color: var(--charcoal); }

    @media (max-width: 1024px) {
        .member-layout { grid-template-columns: 1fr; gap: 40px; }
        .member-sidebar { position: static; }
    }

    /* Comprehensive Mobile Responsive Fixes */
    @media (max-width: 768px) {
        /* Page Title */
        .member-content h1 {
            font-size: 28px;
            margin-bottom: 24px;
        }

        /* Wallet Balance Card */
        .wallet-balance-card {
            padding: 24px;
            margin-bottom: 32px;
        }

        .balance-amount {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .balance-label {
            font-size: 12px;
        }

        .wallet-actions {
            flex-direction: column;
            gap: 12px;
        }

        .btn-topup {
            width: 100%;
            padding: 14px 20px;
            font-size: 14px;
            min-height: 44px;
        }

        /* Quick Topup Section */
        .quick-topup {
            padding: 20px;
            margin-bottom: 32px;
        }

        .quick-topup h3 {
            font-size: 20px;
            margin-bottom: 16px;
        }

        /* Topup Options Grid */
        .topup-options {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .topup-option {
            padding: 16px 12px;
            font-size: 14px;
            min-height: 60px;
        }

        .topup-amount {
            font-size: 16px;
        }

        /* Bank Selection Grid */
        .bank-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .bank-card {
            padding: 16px;
        }

        .bank-name {
            font-size: 16px;
        }

        .bank-number {
            font-size: 14px;
        }

        /* Transaction List */
        .transaction-item {
            flex-direction: column;
            align-items: flex-start;
            padding: 16px;
            gap: 12px;
        }

        .transaction-amount {
            font-size: 16px;
        }

        .transaction-date {
            font-size: 12px;
        }

        .transaction-status {
            font-size: 11px;
            padding: 4px 10px;
        }

        /* Form Inputs */
        .custom-amount input {
            width: 100%;
            padding: 12px 14px;
            font-size: 16px; /* Prevent iOS zoom */
            border-radius: 8px;
        }

        .custom-amount label {
            font-size: 13px;
            margin-bottom: 6px;
        }

        /* Confirmation Display */
        .confirmation-card {
            padding: 20px;
        }

        .amount-display {
            padding: 20px;
        }

        .amount-value {
            font-size: 32px;
        }

        .unique-code {
            font-size: 20px;
            word-break: break-all;
        }

        /* Upload Section */
        .upload-box {
            padding: 20px 16px;
        }

        /* Transactions Card */
        .transactions-card {
            padding: 20px;
        }

        .transactions-card h3 {
            font-size: 20px;
            margin-bottom: 20px;
        }

        /* Transfer Details */
        .transfer-details {
            padding: 20px;
        }

        /* Instructions */
        .instructions {
            padding: 16px;
        }

        /* Modal */
        .modal-content {
            width: 90%;
            margin: 20% auto;
            padding: 24px;
        }

        .modal-header {
            font-size: 20px;
        }

        .modal-buttons {
            flex-direction: column;
        }

        .modal-buttons button {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        /* Page Title */
        .member-content h1 {
            font-size: 24px;
        }

        /* Wallet Balance Card */
        .wallet-balance-card {
            padding: 20px;
        }

        .balance-amount {
            font-size: 32px;
        }

        /* Quick Topup Section */
        .quick-topup {
            padding: 16px;
        }

        /* Topup Grid - Keep 2 columns on smallest screens */
        .topup-options {
            grid-template-columns: repeat(2, 1fr);
        }

        /* Transaction List */
        .transaction-item {
            padding: 12px;
        }

        /* Confirmation Display */
        .confirmation-card {
            padding: 16px;
        }

        .amount-display {
            padding: 16px;
        }

        .amount-value {
            font-size: 28px;
        }

        .unique-code {
            font-size: 18px;
        }

        /* Transactions Card */
        .transactions-card {
            padding: 16px;
        }

        /* Transfer Details */
        .transfer-details {
            padding: 16px;
        }

        /* Instructions */
        .instructions {
            padding: 12px;
        }

        .instructions ol {
            margin-left: 16px;
        }
    }
</style>

<h1>My Wallet</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="wallet-balance-card">
            <div class="balance-label">Available Balance</div>
            <div class="balance-amount"><?php echo formatPrice($user['wallet_balance'] ?? 0); ?></div>
            <div class="wallet-actions">
                <button class="btn btn-topup" onclick="toggleTopup()">Top Up Wallet</button>
            </div>
        </div>

        <?php if ($step === 'list'): ?>
        <!-- STEP 1: Select Amount & Payment Method -->
        <div class="quick-topup" id="topupForm" style="display: none;">
            <h3>Top Up Your Wallet</h3>

            <form action="/member/process-topup.php" method="POST" id="topupFormElement">
                <div class="topup-options">
                    <div class="topup-option" onclick="selectAmount(50000)">
                        <div class="topup-amount">Rp 50K</div>
                    </div>
                    <div class="topup-option" onclick="selectAmount(100000)">
                        <div class="topup-amount">Rp 100K</div>
                    </div>
                    <div class="topup-option" onclick="selectAmount(250000)">
                        <div class="topup-amount">Rp 250K</div>
                    </div>
                    <div class="topup-option" onclick="selectAmount(500000)">
                        <div class="topup-amount">Rp 500K</div>
                    </div>
                    <div class="topup-option" onclick="selectAmount(1000000)">
                        <div class="topup-amount">Rp 1M</div>
                    </div>
                </div>

                <div class="custom-amount">
                    <label>Or Enter Custom Amount</label>
                    <input type="number" name="amount" id="customAmount" placeholder="Minimum Rp 10,000" min="10000" required>
                </div>

                <div class="payment-section">
                    <h4>Select Payment Method</h4>

                    <!-- Payment Method Selection -->
                    <div class="bank-grid" style="margin-bottom: 24px;">
                        <div class="bank-card" id="midtrans-method" onclick="selectPaymentMethod('midtrans', this)" style="cursor: pointer;">
                            <div class="bank-name">💳 Midtrans Payment</div>
                            <div style="font-size: 12px; opacity: 0.7; margin-top: 8px;">
                                QRIS, GoPay, OVO, ShopeePay, Credit Card, dll
                            </div>
                        </div>
                        <div class="bank-card" id="bank-method" onclick="selectPaymentMethod('bank_transfer', this)" style="cursor: pointer;">
                            <div class="bank-name">🏦 Bank Transfer</div>
                            <div style="font-size: 12px; opacity: 0.7; margin-top: 8px;">
                                Transfer manual ke rekening bank kami
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="selectedPaymentMethod" required>

                    <!-- Bank Selection (shown only for bank_transfer) -->
                    <div id="bank-selection-section" style="display: none;">
                        <h4>Select Destination Bank</h4>
                        <div class="bank-grid">
                            <?php foreach ($all_banks as $bank): ?>
                            <div class="bank-card <?php echo $bank['is_active'] ? '' : 'disabled'; ?>"
                                 data-bank-id="<?php echo $bank['id']; ?>"
                                 data-active="<?php echo $bank['is_active']; ?>"
                                 onclick="selectBank(this)">
                                <div class="bank-name"><?php echo htmlspecialchars($bank['bank_name']); ?></div>
                                <div class="bank-number"><?php echo htmlspecialchars($bank['account_number']); ?></div>
                                <div style="font-size: 12px; opacity: 0.7; margin-top: 4px;">
                                    a.n. <?php echo htmlspecialchars($bank['account_name']); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="bank_account_id" id="selectedBankId">
                    </div>
                </div>

                <button type="submit" class="btn" id="continueBtn" style="background: var(--charcoal); color: var(--white); width: 100%; opacity: 0.5; cursor: not-allowed;" disabled>
                    Select Payment Method First
                </button>
            </form>
        </div>

        <?php elseif ($step === 'confirm' && $pending_txn): ?>
        <!-- STEP 2: Show Transfer Details & Upload Proof -->
        <div class="confirmation-card">
            <div class="amount-display">
                <div class="amount-label">Transfer Amount</div>
                <div class="amount-value"><?php echo formatPrice($pending_txn['amount_original']); ?></div>
                <div class="unique-code">+ Kode Unik: Rp <?php echo number_format($pending_txn['unique_code'], 0, ',', '.'); ?></div>
                <div class="amount-value" style="font-size: 36px; margin-top: 12px;">
                    = <?php echo formatPrice($pending_txn['amount']); ?>
                </div>
            </div>

            <div class="instructions">
                <h4>⚠️ Instruksi Transfer</h4>
                <ol>
                    <li>Transfer <strong>TEPAT</strong> sejumlah <strong><?php echo formatPrice($pending_txn['amount']); ?></strong></li>
                    <li>Ke rekening bank yang tertera di bawah</li>
                    <li>Kode unik digunakan untuk verifikasi otomatis</li>
                    <li>Upload bukti transfer setelah melakukan pembayaran</li>
                    <li>Saldo akan ditambahkan setelah admin approve</li>
                </ol>
            </div>

            <div class="transfer-details">
                <h4>Detail Transfer</h4>
                <?php if (!empty($pending_txn['bank_name'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Bank</span>
                    <span class="detail-value"><?php echo htmlspecialchars($pending_txn['bank_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nomor Rekening</span>
                    <span class="detail-value"><?php echo htmlspecialchars($pending_txn['account_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Atas Nama</span>
                    <span class="detail-value"><?php echo htmlspecialchars($pending_txn['account_name']); ?></span>
                </div>
                <?php else: ?>
                <div style="padding: 20px; background: #FEF2F2; border-radius: 8px; color: #B91C1C; margin-bottom: 16px;">
                    ⚠️ <strong>Bank information not available.</strong><br>
                    Please contact admin or select a different bank account.
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label">Jumlah Transfer</span>
                    <span class="detail-value" style="color: #EF4444; font-size: 18px;">
                        <?php echo formatPrice($pending_txn['amount']); ?>
                    </span>
                </div>
            </div>

            <form action="/member/process-topup.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_proof">
                <input type="hidden" name="txn_id" value="<?php echo $pending_txn['id']; ?>">

                <div class="upload-section">
                    <label class="upload-label">Upload Bukti Transfer (Required)</label>
                    <div class="upload-box" onclick="document.getElementById('proofFile').click()">
                        <input type="file" id="proofFile" name="proof" accept="image/*" required onchange="showFileName(this)">
                        <div class="upload-label">📤 Klik untuk upload gambar</div>
                        <div class="upload-hint" id="fileNameDisplay">Format: JPG, PNG (Max 5MB)</div>
                    </div>
                </div>

                <button type="submit" class="btn" style="background: var(--charcoal); color: var(--white); width: 100%; margin-top: 24px;">
                    Sudah Bayar & Submit
                </button>
            </form>

            <div style="text-align: center; margin-top: 16px;">
                <a href="/member/wallet.php" style="color: var(--grey); text-decoration: underline;">
                    Batal & Kembali
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Transaction History -->
        <div class="transactions-card">
            <h3>Transaction History</h3>
            <?php if (empty($transactions)): ?>
                <div class="empty-state">
                    <p>No transactions yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($transactions as $txn): ?>
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <div class="transaction-title">Wallet Top Up</div>
                            <div class="transaction-date">
                                <?php echo date('d M Y, H:i', strtotime($txn['created_at'])); ?>
                                <?php if ($txn['description']): ?>
                                    - <?php echo htmlspecialchars($txn['description']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="transaction-amount credit">
                                + <?php echo formatPrice($txn['amount_original'] ?? $txn['amount']); ?>
                            </div>
                            <span class="transaction-status <?php echo $txn['payment_status']; ?>">
                                <?php echo strtoupper($txn['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

<?php include __DIR__ . '/../includes/member-layout-horizontal-end.php'; ?>

<!-- Modal for Unavailable Banks -->
<div id="bankModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">⚠️ Bank Tidak Tersedia</div>
        <div class="modal-body">
            Bank yang Anda pilih saat ini tidak tersedia. Silakan gunakan metode pembayaran lain yang tersedia atau pilih bank yang aktif.
        </div>
        <div class="modal-buttons">
            <button class="btn-modal-primary" onclick="closeModal()">OK, Mengerti</button>
        </div>
    </div>
</div>

<script>
// Load Midtrans Snap if available
<?php if (isset($payment_enabled['midtrans']) && $payment_enabled['midtrans']): ?>
const midtransScriptUrl = '<?php echo MIDTRANS_SNAP_URL; ?>';
const script = document.createElement('script');
script.src = midtransScriptUrl;
script.setAttribute('data-client-key', '<?php echo MIDTRANS_CLIENT_KEY; ?>');
document.head.appendChild(script);
<?php endif; ?>

function toggleTopup() {
    const form = document.getElementById('topupForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function selectAmount(amount) {
    document.getElementById('customAmount').value = amount;
    document.querySelectorAll('.topup-option').forEach(opt => opt.classList.remove('selected'));
    event.target.closest('.topup-option').classList.add('selected');
}

function selectPaymentMethod(method, element) {
    // Update selection UI
    document.getElementById('midtrans-method').classList.remove('selected');
    document.getElementById('bank-method').classList.remove('selected');
    element.classList.add('selected');

    // Update hidden field
    document.getElementById('selectedPaymentMethod').value = method;

    // Show/hide bank selection
    const bankSection = document.getElementById('bank-selection-section');
    const continueBtn = document.getElementById('continueBtn');

    if (method === 'bank_transfer') {
        bankSection.style.display = 'block';
        continueBtn.textContent = 'Select Bank to Continue';
        continueBtn.disabled = true;
        continueBtn.style.opacity = '0.5';
        continueBtn.style.cursor = 'not-allowed';
    } else if (method === 'midtrans') {
        bankSection.style.display = 'none';
        continueBtn.textContent = 'Continue with Midtrans';
        continueBtn.disabled = false;
        continueBtn.style.opacity = '1';
        continueBtn.style.cursor = 'pointer';
    }
}

function selectBank(element) {
    const isActive = element.getAttribute('data-active') === '1';

    if (!isActive) {
        document.getElementById('bankModal').style.display = 'block';
        return;
    }

    // Update selection UI within bank section only
    document.querySelectorAll('#bank-selection-section .bank-card').forEach(card => card.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('selectedBankId').value = element.getAttribute('data-bank-id');

    // Enable continue button
    const continueBtn = document.getElementById('continueBtn');
    continueBtn.textContent = 'Continue to Payment';
    continueBtn.disabled = false;
    continueBtn.style.opacity = '1';
    continueBtn.style.cursor = 'pointer';
}

function closeModal() {
    document.getElementById('bankModal').style.display = 'none';
}

function showFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('fileNameDisplay').textContent = '✓ ' + input.files[0].name;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('bankModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Handle form submission
document.getElementById('topupFormElement')?.addEventListener('submit', async function(e) {
    const paymentMethod = document.getElementById('selectedPaymentMethod').value;
    const amount = document.getElementById('customAmount').value;

    if (!amount || amount < 10000) {
        alert('Minimum topup amount is Rp 10,000');
        e.preventDefault();
        return;
    }

    // For Midtrans, use API and show Snap popup
    if (paymentMethod === 'midtrans') {
        e.preventDefault();

        const continueBtn = document.getElementById('continueBtn');
        continueBtn.disabled = true;
        continueBtn.textContent = 'Processing...';

        try {
            const formData = new FormData();
            formData.append('amount', amount);
            formData.append('payment_method', 'midtrans');

            const response = await fetch('/api/topup/create.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success && data.snap_token) {
                // Show Midtrans Snap popup
                if (typeof snap !== 'undefined') {
                    snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            window.location.href = '/member/wallet.php?success=1';
                        },
                        onPending: function(result) {
                            window.location.href = '/member/wallet.php?pending=1';
                        },
                        onError: function(result) {
                            alert('Payment failed. Please try again.');
                            continueBtn.disabled = false;
                            continueBtn.textContent = 'Continue with Midtrans';
                        },
                        onClose: function() {
                            continueBtn.disabled = false;
                            continueBtn.textContent = 'Continue with Midtrans';
                        }
                    });
                } else {
                    alert('Midtrans is not available. Please try again later.');
                    continueBtn.disabled = false;
                    continueBtn.textContent = 'Continue with Midtrans';
                }
            } else {
                alert('Error: ' + (data.error || 'Failed to create topup'));
                continueBtn.disabled = false;
                continueBtn.textContent = 'Continue with Midtrans';
            }
        } catch (error) {
            console.error('Topup error:', error);
            alert('An error occurred. Please try again.');
            continueBtn.disabled = false;
            continueBtn.textContent = 'Continue with Midtrans';
        }
    }
    // For bank_transfer, submit normally (will handle by process-topup.php)
    // form will submit normally, no preventDefault
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
