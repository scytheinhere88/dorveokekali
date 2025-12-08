<?php
require_once __DIR__ . '/../../config.php';
if (!isAdmin()) redirect('/admin/login.php');

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['deposit_id'])) {
    $deposit_id = $_POST['deposit_id'];
    $action = $_POST['action'];
    $admin_notes = $_POST['admin_notes'] ?? '';

    if ($action === 'approve') {
        // Get transaction details
        $stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE id = ?");
        $stmt->execute([$deposit_id]);
        $txn = $stmt->fetch();

        if ($txn && $txn['status'] === 'pending') {
            try {
                $pdo->beginTransaction();

                // Update user balance
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$txn['amount_original'] ?? $txn['amount'], $txn['user_id']]);

                // Update transaction status
                $stmt = $pdo->prepare("UPDATE wallet_transactions SET status = 'approved', admin_notes = ?, payment_status = 'success', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$admin_notes, $deposit_id]);

                $pdo->commit();
                $_SESSION['success'] = 'Deposit approved! User balance updated.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error'] = 'Error approving deposit: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE wallet_transactions SET status = 'rejected', admin_notes = ?, payment_status = 'failed', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$admin_notes, $deposit_id]);
        $_SESSION['success'] = 'Deposit rejected.';
    }
    redirect('/admin/deposits/');
}

// Get all wallet topup transactions
$stmt = $pdo->query("
    SELECT
        wt.*,
        u.name as user_name,
        u.email as user_email,
        u.phone as user_phone,
        ba.bank_name,
        ba.account_number as bank_account_number,
        ba.account_name as bank_account_name
    FROM wallet_transactions wt
    JOIN users u ON wt.user_id = u.id
    LEFT JOIN bank_accounts ba ON wt.bank_account_id = ba.id
    WHERE wt.type = 'topup' OR wt.type = 'deposit'
    ORDER BY wt.created_at DESC
");
$deposits = $stmt->fetchAll();

$pending = array_filter($deposits, fn($d) => $d['status'] === 'pending');
$approved = array_filter($deposits, fn($d) => $d['status'] === 'approved');
$rejected = array_filter($deposits, fn($d) => $d['status'] === 'rejected');

$page_title = 'Kelola Deposit Wallet - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
    .deposit-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; }
    .deposit-modal.active { display: flex; }
    .deposit-modal-content { background: white; border-radius: 12px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; padding: 0; }
    .deposit-modal-header { padding: 24px; border-bottom: 1px solid #E5E7EB; display: flex; justify-content: space-between; align-items: center; }
    .deposit-modal-body { padding: 24px; }
    .deposit-modal-close { cursor: pointer; font-size: 24px; color: #6B7280; }
    .deposit-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
    .deposit-detail-item { padding: 16px; background: #F9FAFB; border-radius: 8px; }
    .deposit-detail-label { font-size: 12px; color: #6B7280; margin-bottom: 4px; text-transform: uppercase; font-weight: 600; }
    .deposit-detail-value { font-size: 16px; color: #111827; font-weight: 600; }
    .proof-image-container { text-align: center; margin: 24px 0; }
    .proof-image { max-width: 100%; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .amount-breakdown { background: linear-gradient(135deg, #1F2937 0%, #111827 100%); color: white; padding: 24px; border-radius: 12px; margin-bottom: 24px; }
    .amount-breakdown-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .amount-breakdown-row:last-child { border-bottom: none; font-size: 18px; font-weight: 700; }
    .status-badge { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display: inline-block; }
    .status-pending { background: #FEF3C7; color: #92400E; }
    .status-approved { background: #ECFDF5; color: #059669; }
    .status-rejected { background: #FEF2F2; color: #DC2626; }
    .action-buttons { display: flex; gap: 12px; margin-top: 24px; }
    .action-buttons button { flex: 1; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .btn-approve { background: #059669; color: white; border: none; }
    .btn-reject { background: #DC2626; color: white; border: none; }
    .deposit-card { background: white; border: 1px solid #E5E7EB; border-radius: 12px; padding: 20px; margin-bottom: 16px; cursor: pointer; transition: all 0.3s; }
    .deposit-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-color: #D1D5DB; }
    .deposit-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
    .deposit-card-customer { display: flex; align-items: center; gap: 12px; }
    .deposit-card-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; }
    .deposit-card-info { flex: 1; }
    .deposit-card-name { font-weight: 700; font-size: 16px; margin-bottom: 4px; }
    .deposit-card-email { font-size: 13px; color: #6B7280; }
    .deposit-card-amount { text-align: right; }
    .deposit-card-amount-label { font-size: 12px; color: #6B7280; margin-bottom: 4px; }
    .deposit-card-amount-value { font-size: 24px; font-weight: 700; color: #111827; }
    .deposit-card-details { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding-top: 16px; border-top: 1px solid #F3F4F6; }
    .deposit-card-detail { }
    .deposit-card-detail-label { font-size: 11px; color: #9CA3AF; margin-bottom: 4px; text-transform: uppercase; font-weight: 600; }
    .deposit-card-detail-value { font-size: 14px; color: #374151; font-weight: 600; }
    .tabs { display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 2px solid #E5E7EB; }
    .tab { padding: 12px 24px; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; font-weight: 600; color: #6B7280; }
    .tab.active { color: #111827; border-bottom-color: #111827; }
</style>

<div class="header">
    <h1>💰 Kelola Deposit Wallet</h1>
    <p style="color: #6B7280; margin-top: 8px;">Verifikasi dan kelola deposit member</p>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div style="background: #ECFDF5; color: #059669; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        ✓ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div style="background: #FEF2F2; color: #DC2626; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        ✗ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo count($pending); ?></div>
        <div class="stat-label">⏳ Pending Deposits</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo count($approved); ?></div>
        <div class="stat-label">✅ Approved Deposits</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo count($rejected); ?></div>
        <div class="stat-label">❌ Rejected Deposits</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">Rp <?php echo number_format(array_sum(array_map(fn($d) => $d['amount_original'] ?? $d['amount'], $pending)), 0, ',', '.'); ?></div>
        <div class="stat-label">💵 Total Pending Amount</div>
    </div>
</div>

<div class="tabs">
    <div class="tab active" onclick="filterDeposits('all')">All (<?php echo count($deposits); ?>)</div>
    <div class="tab" onclick="filterDeposits('pending')">Pending (<?php echo count($pending); ?>)</div>
    <div class="tab" onclick="filterDeposits('approved')">Approved (<?php echo count($approved); ?>)</div>
    <div class="tab" onclick="filterDeposits('rejected')">Rejected (<?php echo count($rejected); ?>)</div>
</div>

<div id="deposits-container">
    <?php if (empty($deposits)): ?>
        <div style="text-align: center; padding: 60px; color: #9CA3AF;">
            <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
            <div style="font-size: 18px; font-weight: 600;">Belum ada deposit</div>
            <div style="font-size: 14px; margin-top: 8px;">Deposit member akan muncul di sini</div>
        </div>
    <?php else: ?>
        <?php foreach ($deposits as $deposit): ?>
            <div class="deposit-card" data-status="<?php echo $deposit['status']; ?>" onclick="showDepositDetail(<?php echo htmlspecialchars(json_encode($deposit)); ?>)">
                <div class="deposit-card-header">
                    <div class="deposit-card-customer">
                        <div class="deposit-card-avatar">
                            <?php echo strtoupper(substr($deposit['user_name'], 0, 1)); ?>
                        </div>
                        <div class="deposit-card-info">
                            <div class="deposit-card-name"><?php echo htmlspecialchars($deposit['user_name']); ?></div>
                            <div class="deposit-card-email"><?php echo htmlspecialchars($deposit['user_email']); ?></div>
                            <?php if ($deposit['user_phone']): ?>
                                <div class="deposit-card-email">📱 <?php echo htmlspecialchars($deposit['user_phone']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="deposit-card-amount">
                        <div class="deposit-card-amount-label">Total Transfer</div>
                        <div class="deposit-card-amount-value">Rp <?php echo number_format($deposit['amount'], 0, ',', '.'); ?></div>
                        <?php if ($deposit['unique_code']): ?>
                            <div style="font-size: 12px; color: #9CA3AF; margin-top: 4px;">
                                Base: Rp <?php echo number_format($deposit['amount_original'], 0, ',', '.'); ?> + <?php echo $deposit['unique_code']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="deposit-card-details">
                    <div class="deposit-card-detail">
                        <div class="deposit-card-detail-label">📅 Date</div>
                        <div class="deposit-card-detail-value"><?php echo date('d M Y H:i', strtotime($deposit['created_at'])); ?></div>
                    </div>
                    <div class="deposit-card-detail">
                        <div class="deposit-card-detail-label">🏦 Bank</div>
                        <div class="deposit-card-detail-value"><?php echo htmlspecialchars($deposit['bank_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="deposit-card-detail">
                        <div class="deposit-card-detail-label">📋 Status</div>
                        <div>
                            <span class="status-badge status-<?php echo $deposit['status']; ?>">
                                <?php echo strtoupper($deposit['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Deposit Detail Modal -->
<div id="depositModal" class="deposit-modal">
    <div class="deposit-modal-content">
        <div class="deposit-modal-header">
            <h2 style="margin: 0;">Detail Deposit</h2>
            <span class="deposit-modal-close" onclick="closeDepositModal()">&times;</span>
        </div>
        <div class="deposit-modal-body" id="depositModalBody">
            <!-- Content will be injected via JavaScript -->
        </div>
    </div>
</div>

<script>
let currentFilter = 'all';

function filterDeposits(status) {
    currentFilter = status;
    const cards = document.querySelectorAll('.deposit-card');
    const tabs = document.querySelectorAll('.tab');

    tabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');

    cards.forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function showDepositDetail(deposit) {
    const modal = document.getElementById('depositModal');
    const modalBody = document.getElementById('depositModalBody');

    let statusClass = 'status-' + deposit.status;
    let statusLabel = deposit.status.toUpperCase();

    let html = `
        <div class="amount-breakdown">
            <div style="text-align: center; margin-bottom: 16px;">
                <div style="font-size: 14px; opacity: 0.8; margin-bottom: 8px;">TOTAL YANG HARUS DITRANSFER</div>
                <div style="font-size: 36px; font-weight: 700;">Rp ${Number(deposit.amount).toLocaleString('id-ID')}</div>
            </div>
            ${deposit.unique_code ? `
            <div class="amount-breakdown-row">
                <span>Amount Original</span>
                <span>Rp ${Number(deposit.amount_original).toLocaleString('id-ID')}</span>
            </div>
            <div class="amount-breakdown-row">
                <span>Kode Unik Verifikasi</span>
                <span>+ ${deposit.unique_code}</span>
            </div>
            <div class="amount-breakdown-row">
                <span>Total Transfer</span>
                <span>Rp ${Number(deposit.amount).toLocaleString('id-ID')}</span>
            </div>
            ` : ''}
        </div>

        <div class="deposit-detail-grid">
            <div class="deposit-detail-item">
                <div class="deposit-detail-label">👤 Customer</div>
                <div class="deposit-detail-value">${deposit.user_name}</div>
                <div style="font-size: 13px; color: #6B7280; margin-top: 4px;">${deposit.user_email}</div>
                ${deposit.user_phone ? `<div style="font-size: 13px; color: #6B7280; margin-top: 4px;">📱 ${deposit.user_phone}</div>` : ''}
            </div>
            <div class="deposit-detail-item">
                <div class="deposit-detail-label">📅 Transaction Date</div>
                <div class="deposit-detail-value">${new Date(deposit.created_at).toLocaleString('id-ID')}</div>
            </div>
            <div class="deposit-detail-item">
                <div class="deposit-detail-label">🏦 Bank Tujuan</div>
                <div class="deposit-detail-value">${deposit.bank_name || 'N/A'}</div>
                ${deposit.bank_account_number ? `<div style="font-size: 13px; color: #6B7280; margin-top: 4px;">${deposit.bank_account_number}</div>` : ''}
                ${deposit.bank_account_name ? `<div style="font-size: 13px; color: #6B7280; margin-top: 4px;">a.n. ${deposit.bank_account_name}</div>` : ''}
            </div>
            <div class="deposit-detail-item">
                <div class="deposit-detail-label">📋 Status</div>
                <div><span class="status-badge ${statusClass}">${statusLabel}</span></div>
            </div>
            <div class="deposit-detail-item">
                <div class="deposit-detail-label">🔖 Reference ID</div>
                <div class="deposit-detail-value">${deposit.reference_id || 'N/A'}</div>
            </div>
            <div class="deposit-detail-item">
                <div class="deposit-detail-label">💳 Payment Method</div>
                <div class="deposit-detail-value">${deposit.payment_method || 'Bank Transfer'}</div>
            </div>
        </div>

        ${deposit.proof_image ? `
        <div style="margin: 24px 0;">
            <div style="font-weight: 600; margin-bottom: 12px; font-size: 16px;">📸 Bukti Transfer:</div>
            <div class="proof-image-container">
                <img src="${deposit.proof_image}" alt="Bukti Transfer" class="proof-image" onclick="window.open('${deposit.proof_image}', '_blank')">
                <div style="margin-top: 12px;">
                    <a href="${deposit.proof_image}" target="_blank" style="color: #2563EB; text-decoration: underline;">Buka gambar di tab baru</a>
                </div>
            </div>
        </div>
        ` : '<div style="padding: 24px; background: #FEF2F2; color: #DC2626; border-radius: 8px; text-align: center;">⚠️ Bukti transfer belum diupload</div>'}

        ${deposit.admin_notes ? `
        <div style="padding: 16px; background: #F9FAFB; border-radius: 8px; margin-top: 16px;">
            <div style="font-weight: 600; margin-bottom: 8px;">📝 Admin Notes:</div>
            <div style="color: #6B7280;">${deposit.admin_notes}</div>
        </div>
        ` : ''}

        ${deposit.status === 'pending' && deposit.proof_image ? `
        <form method="POST" onsubmit="return confirm('Approve deposit ini? Saldo member akan ditambah Rp ${Number(deposit.amount_original || deposit.amount).toLocaleString('id-ID')}')">
            <input type="hidden" name="deposit_id" value="${deposit.id}">
            <input type="hidden" name="action" value="approve">
            <div style="margin-top: 24px;">
                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Admin Notes (Optional):</label>
                <textarea name="admin_notes" rows="3" style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 14px;" placeholder="Add notes for this transaction..."></textarea>
            </div>
            <div class="action-buttons">
                <button type="submit" class="btn-approve">✅ APPROVE & ADD BALANCE</button>
            </div>
        </form>
        <form method="POST" style="margin-top: 12px;" onsubmit="return confirm('Reject deposit ini?')">
            <input type="hidden" name="deposit_id" value="${deposit.id}">
            <input type="hidden" name="action" value="reject">
            <div style="margin-bottom: 12px;">
                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Reject Reason (Required):</label>
                <textarea name="admin_notes" rows="2" required style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 14px;" placeholder="Why are you rejecting this deposit?"></textarea>
            </div>
            <button type="submit" class="btn-reject" style="width: 100%;">❌ REJECT DEPOSIT</button>
        </form>
        ` : ''}
    `;

    modalBody.innerHTML = html;
    modal.classList.add('active');
}

function closeDepositModal() {
    document.getElementById('depositModal').classList.remove('active');
}

// Close modal when clicking outside
document.getElementById('depositModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDepositModal();
    }
});
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
