<?php
/**
 * ADMIN - VOUCHER MANAGEMENT  
 * Professional CRUD system with icon upload
 */
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    header('Location: /login.php');
    exit;
}

$page_title = 'Voucher Management';
include __DIR__ . '/../includes/admin-header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        if ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Voucher berhasil dihapus']);
        } elseif ($_POST['action'] === 'toggle_status') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE vouchers SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Status berhasil diubah']);
        } elseif ($_POST['action'] === 'assign_to_users') {
            $voucherId = (int)$_POST['voucher_id'];
            $targetType = $_POST['target_type'];
            $targetValue = $_POST['target_value'] ?? null;
            
            // Get users based on target
            $users = [];
            if ($targetType === 'all') {
                $stmt = $pdo->query("SELECT id FROM users WHERE 1=1");
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } elseif ($targetType === 'tier' && $targetValue) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE tier = ?");
                $stmt->execute([$targetValue]);
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } elseif ($targetType === 'referral') {
                $stmt = $pdo->query("SELECT id FROM users WHERE referral_code IS NOT NULL AND referral_code != ''");
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
            
            // Assign voucher to users
            $assigned = 0;
            foreach ($users as $userId) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO user_vouchers (user_id, voucher_id) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([$userId, $voucherId]);
                    $assigned++;
                } catch (Exception $e) {
                    // Skip duplicates
                }
            }
            
            echo json_encode(['success' => true, 'message' => "Voucher assigned to $assigned users"]);
        }
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Fetch all vouchers
$stmt = $pdo->query("
    SELECT v.*, 
           COUNT(DISTINCT uv.user_id) as assigned_users,
           COUNT(DISTINCT vu.id) as total_usage
    FROM vouchers v
    LEFT JOIN user_vouchers uv ON v.id = uv.voucher_id
    LEFT JOIN voucher_usage vu ON v.id = vu.voucher_id
    GROUP BY v.id
    ORDER BY v.created_at DESC
");
$vouchers = $stmt->fetchAll();
?>

<style>
/* Voucher-specific styles menggunakan global admin CSS structure */
.voucher-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.voucher-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    gap: 20px;
    align-items: start;
    border: 2px solid #E5E7EB;
    transition: all 0.3s;
}

.voucher-card:hover {
    border-color: #3B82F6;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
}

.voucher-icon {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    object-fit: cover;
    background: #F3F4F6;
    flex-shrink: 0;
}

.voucher-icon-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    flex-shrink: 0;
}

.voucher-content {
    flex: 1;
}

.voucher-card .voucher-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
}

.voucher-code {
    font-size: 24px;
    font-weight: 700;
    color: #1F2937;
    font-family: 'Courier New', monospace;
}

.voucher-type {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.type-discount {
    background: #DBEAFE;
    color: #1E40AF;
}

.type-free_shipping {
    background: #D1FAE5;
    color: #065F46;
}

.voucher-value {
    font-size: 20px;
    font-weight: 700;
    color: #3B82F6;
    margin: 8px 0;
}

.voucher-conditions {
    display: flex;
    gap: 20px;
    margin: 12px 0;
    font-size: 14px;
    color: #6B7280;
}

.voucher-stats {
    display: flex;
    gap: 24px;
    margin: 16px 0;
    padding: 12px 0;
    border-top: 1px solid #E5E7EB;
    font-size: 14px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.stat-label {
    color: #6B7280;
}

.stat-value {
    font-weight: 600;
    color: #1F2937;
}

.voucher-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.btn-action {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    border: 1px solid;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.btn-edit {
    border-color: #3B82F6;
    color: #3B82F6;
    text-decoration: none;
}

.btn-edit:hover {
    background: #3B82F6;
    color: white;
}

.btn-delete {
    border-color: #EF4444;
    color: #EF4444;
}

.btn-delete:hover {
    background: #EF4444;
    color: white;
}

.btn-toggle {
    border-color: #F59E0B;
    color: #F59E0B;
}

.btn-toggle:hover {
    background: #F59E0B;
    color: white;
}

.btn-assign {
    background: #10B981;
    border-color: #10B981;
    color: white;
}

.btn-assign:hover {
    background: #059669;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #D1FAE5;
    color: #065F46;
}

.status-inactive {
    background: #FEE2E2;
    color: #991B1B;
}

.status-expired {
    background: #F3F4F6;
    color: #6B7280;
}

.btn-create {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
    color: white;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s;
}

.btn-create:hover {
    transform: translateY(-2px);
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 12px;
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 20px;
}
</style>

<div class="voucher-header">
    <div>
        <h1>🎟️ Voucher Management</h1>
        <p style="color: #6B7280; margin-top: 4px;">Manage discount & free shipping vouchers</p>
    </div>
    <a href="/admin/vouchers/create.php" class="btn-create">
        <span>➕</span> Create New Voucher
    </a>
</div>

            <?php if (empty($vouchers)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎟️</div>
                    <h3>No Vouchers Yet</h3>
                    <p style="color: #6B7280; margin-top: 8px;">Create your first voucher to get started</p>
                    <a href="/admin/vouchers/create.php" class="btn-create" style="margin-top: 20px;">Create Voucher</a>
                </div>
            <?php else: ?>
                <?php foreach ($vouchers as $voucher): 
                    $isActive = $voucher['is_active'] == 1;
                    $isExpired = strtotime($voucher['valid_until']) < time();
                    $statusClass = $isExpired ? 'status-expired' : ($isActive ? 'status-active' : 'status-inactive');
                    $statusText = $isExpired ? 'Expired' : ($isActive ? 'Active' : 'Inactive');
                ?>
                <div class="voucher-card">
                    <?php if ($voucher['image']): ?>
                        <img src="/uploads/vouchers/<?= htmlspecialchars($voucher['image']) ?>" class="voucher-icon" alt="Voucher Icon">
                    <?php else: ?>
                        <div class="voucher-icon-placeholder">🎟️</div>
                    <?php endif; ?>
                    
                    <div class="voucher-content">
                        <div class="voucher-header">
                            <div>
                                <div class="voucher-code"><?= htmlspecialchars($voucher['code']) ?></div>
                                <div style="margin-top: 4px;">
                                    <span class="voucher-type type-<?= $voucher['type'] ?>">
                                        <?= $voucher['type'] === 'discount' ? '💰 Discount' : '🚚 Free Shipping' ?>
                                    </span>
                                    <span class="status-badge <?= $statusClass ?>" style="margin-left: 8px;">
                                        <?= $statusText ?>
                                    </span>
                                </div>
                            </div>
                            <div class="voucher-actions">
                                <button class="btn-action btn-assign" onclick="assignVoucher(<?= $voucher['id'] ?>, '<?= htmlspecialchars($voucher['code']) ?>')">
                                    📤 Assign
                                </button>
                                <a href="/admin/vouchers/edit.php?id=<?= $voucher['id'] ?>" class="btn-action btn-edit">
                                    ✏️ Edit
                                </a>
                                <button class="btn-action btn-toggle" onclick="toggleStatus(<?= $voucher['id'] ?>)">
                                    🔄 Toggle
                                </button>
                                <button class="btn-action btn-delete" onclick="deleteVoucher(<?= $voucher['id'] ?>)">
                                    🗑️ Delete
                                </button>
                            </div>
                        </div>

                        <?php if ($voucher['name']): ?>
                            <div style="font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 4px;">
                                <?= htmlspecialchars($voucher['name']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($voucher['description']): ?>
                            <div style="color: #6B7280; margin-bottom: 8px;">
                                <?= htmlspecialchars($voucher['description']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="voucher-value">
                            <?php if ($voucher['type'] === 'discount'): ?>
                                <?php if ($voucher['discount_type'] === 'percentage'): ?>
                                    <?= number_format($voucher['discount_value'], 0) ?>% OFF
                                    <?php if ($voucher['max_discount']): ?>
                                        (Max: Rp <?= number_format($voucher['max_discount'], 0, ',', '.') ?>)
                                    <?php endif; ?>
                                <?php else: ?>
                                    Rp <?= number_format($voucher['discount_value'], 0, ',', '.') ?> OFF
                                <?php endif; ?>
                            <?php else: ?>
                                FREE SHIPPING
                                <?php if ($voucher['discount_value']): ?>
                                    (Max: Rp <?= number_format($voucher['discount_value'], 0, ',', '.') ?>)
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="voucher-conditions">
                            <?php if ($voucher['min_purchase']): ?>
                                <div>📊 Min Purchase: <strong>Rp <?= number_format($voucher['min_purchase'], 0, ',', '.') ?></strong></div>
                            <?php endif; ?>
                            <div>🔢 Max Usage: <strong><?= $voucher['max_usage_per_user'] ?>x per user</strong></div>
                            <?php if ($voucher['total_usage_limit']): ?>
                                <div>🎯 Total Limit: <strong><?= $voucher['total_usage_limit'] ?></strong></div>
                            <?php endif; ?>
                        </div>

                        <div class="voucher-stats">
                            <div class="stat-item">
                                <span class="stat-label">👥 Assigned:</span>
                                <span class="stat-value"><?= $voucher['assigned_users'] ?> users</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">✅ Used:</span>
                                <span class="stat-value"><?= $voucher['total_usage'] ?> times</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">📅 Valid:</span>
                                <span class="stat-value">
                                    <?= date('d M Y', strtotime($voucher['valid_from'])) ?> - 
                                    <?= date('d M Y', strtotime($voucher['valid_until'])) ?>
                                </span>
                            </div>
                            <?php if ($voucher['target_type'] !== 'all'): ?>
                                <div class="stat-item">
                                    <span class="stat-label">🎯 Target:</span>
                                    <span class="stat-value"><?= ucfirst($voucher['target_type']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

<script>
    function deleteVoucher(id) {
        if (!confirm('Are you sure you want to delete this voucher?')) return;
        
        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        })
        .catch(e => alert('Error: ' + e.message));
    }

    function toggleStatus(id) {
        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=toggle_status&id=${id}`
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        })
        .catch(e => alert('Error: ' + e.message));
    }

    function assignVoucher(voucherId, voucherCode) {
        const targetType = prompt(`Assign voucher "${voucherCode}" to:\n\n1. all - All users\n2. tier - Specific tier (e.g. premium, gold)\n3. referral - Users with referrals\n\nEnter choice:`);
        
        if (!targetType || !['all', 'tier', 'referral'].includes(targetType)) {
            alert('Invalid choice');
            return;
        }
        
        let targetValue = null;
        if (targetType === 'tier') {
            targetValue = prompt('Enter tier name (e.g. premium, gold, silver):');
            if (!targetValue) return;
        }
        
        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=assign_to_users&voucher_id=${voucherId}&target_type=${targetType}&target_value=${targetValue || ''}`
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        })
        .catch(e => alert('Error: ' + e.message));
    }
    </script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>