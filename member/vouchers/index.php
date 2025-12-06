<?php
require_once __DIR__ . '/../../config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Get all available vouchers for this user
$stmt = $pdo->prepare("
    SELECT v.*,
           COALESCE(uv.usage_count, 0) as usage_count,
           CASE 
               WHEN v.total_usage_limit IS NOT NULL AND v.total_used >= v.total_usage_limit THEN 1
               ELSE 0
           END as is_limit_reached
    FROM vouchers v
    LEFT JOIN user_vouchers uv ON v.id = uv.voucher_id AND uv.user_id = ?
    WHERE v.is_active = 1
      AND v.valid_from <= NOW()
      AND v.valid_until >= NOW()
    ORDER BY v.type DESC, v.discount_value DESC
");
$stmt->execute([$userId]);
$allVouchers = $stmt->fetchAll();

// Categorize vouchers
$discountVouchers = [];
$shippingVouchers = [];

foreach ($allVouchers as $voucher) {
    if ($voucher['type'] === 'free_shipping') {
        $shippingVouchers[] = $voucher;
    } else {
        $discountVouchers[] = $voucher;
    }
}

$page_title = 'My Vouchers - Dorve.id';
include __DIR__ . '/../../includes/header.php';
?>

<style>
    .member-content h1 {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        margin-bottom: 8px;
    }
    
    .page-description {
        color: #6B7280;
        margin-bottom: 32px;
    }
    
    .voucher-section {
        margin-bottom: 48px;
    }
    
    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #E5E7EB;
    }
    
    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
    }
    
    .section-count {
        background: #667EEA;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }
    
    .voucher-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 24px;
    }
    
    .voucher-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        border: 2px solid transparent;
        transition: all 0.3s;
        position: relative;
    }
    
    .voucher-card:hover {
        border-color: #667EEA;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
        transform: translateY(-4px);
    }
    
    .voucher-card.discount {
        border-left: 4px solid #F59E0B;
    }
    
    .voucher-card.shipping {
        border-left: 4px solid #10B981;
    }
    
    .voucher-header {
        padding: 24px;
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: white;
        position: relative;
    }
    
    .voucher-header.discount-header {
        background: linear-gradient(135deg, #F59E0B 0%, #EF4444 100%);
    }
    
    .voucher-header.shipping-header {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    }
    
    .voucher-type {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.9;
        margin-bottom: 8px;
    }
    
    .voucher-code {
        font-size: 24px;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        margin-bottom: 8px;
        letter-spacing: 2px;
    }
    
    .voucher-name {
        font-size: 16px;
        opacity: 0.95;
    }
    
    .voucher-body {
        padding: 24px;
    }
    
    .voucher-value {
        font-size: 36px;
        font-weight: 700;
        color: #667EEA;
        margin-bottom: 16px;
    }
    
    .voucher-value.discount-value {
        color: #F59E0B;
    }
    
    .voucher-value.shipping-value {
        color: #10B981;
    }
    
    .voucher-details {
        font-size: 14px;
        color: #6B7280;
        line-height: 1.6;
    }
    
    .voucher-detail-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    
    .voucher-footer {
        padding: 16px 24px;
        background: #F9FAFB;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .voucher-expiry {
        font-size: 12px;
        color: #6B7280;
    }
    
    .btn-copy {
        padding: 8px 20px;
        background: #667EEA;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-copy:hover {
        background: #5568D3;
        transform: scale(1.05);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        border: 2px dashed #E5E7EB;
    }
    
    .empty-icon {
        font-size: 64px;
        margin-bottom: 16px;
    }
    
    @media (max-width: 768px) {
        .voucher-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="member-layout">
    <?php include __DIR__ . '/../../includes/member-sidebar.php'; ?>
    
    <div class="member-content">
        <h1>🎫 My Vouchers</h1>
        <p class="page-description">Save money with exclusive vouchers and deals</p>
        
        <!-- Discount Vouchers Section -->
        <div class="voucher-section">
            <div class="section-header">
                <span style="font-size: 32px;">💰</span>
                <h2 class="section-title">Discount Vouchers</h2>
                <span class="section-count"><?= count($discountVouchers) ?></span>
            </div>
            
            <?php if (empty($discountVouchers)): ?>
                <div class="empty-state">
                    <div class="empty-icon">💸</div>
                    <h3>No Discount Vouchers Available</h3>
                    <p style="color: #6B7280; margin-top: 8px;">Check back later for exclusive deals!</p>
                </div>
            <?php else: ?>
                <div class="voucher-grid">
                    <?php foreach ($discountVouchers as $voucher): ?>
                        <div class="voucher-card discount">
                            <div class="voucher-header discount-header">
                                <div class="voucher-type">💰 Discount Voucher</div>
                                <div class="voucher-code"><?= htmlspecialchars($voucher['code']) ?></div>
                                <div class="voucher-name"><?= htmlspecialchars($voucher['name']) ?></div>
                            </div>
                            
                            <div class="voucher-body">
                                <div class="voucher-value discount-value">
                                    <?php if ($voucher['discount_type'] === 'percentage'): ?>
                                        <?= $voucher['discount_value'] ?>% OFF
                                    <?php else: ?>
                                        Rp <?= number_format($voucher['discount_value'], 0, ',', '.') ?> OFF
                                    <?php endif; ?>
                                </div>
                                
                                <div class="voucher-details">
                                    <?php if ($voucher['min_purchase'] > 0): ?>
                                        <div class="voucher-detail-item">
                                            <span>📦</span>
                                            <span>Min. purchase: Rp <?= number_format($voucher['min_purchase'], 0, ',', '.') ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($voucher['max_discount'] > 0): ?>
                                        <div class="voucher-detail-item">
                                            <span>🔝</span>
                                            <span>Max. discount: Rp <?= number_format($voucher['max_discount'], 0, ',', '.') ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($voucher['usage_limit_per_user']): ?>
                                        <div class="voucher-detail-item">
                                            <span>🎯</span>
                                            <span>Used: <?= $voucher['usage_count'] ?> / <?= $voucher['usage_limit_per_user'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="voucher-footer">
                                <div class="voucher-expiry">
                                    ⏰ Valid until <?= date('d M Y', strtotime($voucher['valid_until'])) ?>
                                </div>
                                <button onclick="copyCode('<?= $voucher['code'] ?>')" class="btn-copy">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Free Shipping Vouchers Section -->
        <div class="voucher-section">
            <div class="section-header">
                <span style="font-size: 32px;">🚚</span>
                <h2 class="section-title">Free Shipping Vouchers</h2>
                <span class="section-count" style="background: #10B981;"><?= count($shippingVouchers) ?></span>
            </div>
            
            <?php if (empty($shippingVouchers)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
                    <h3>No Shipping Vouchers Available</h3>
                    <p style="color: #6B7280; margin-top: 8px;">Free shipping vouchers will appear here when available</p>
                </div>
            <?php else: ?>
                <div class="voucher-grid">
                    <?php foreach ($shippingVouchers as $voucher): ?>
                        <div class="voucher-card shipping">
                            <div class="voucher-header shipping-header">
                                <div class="voucher-type">🚚 Free Shipping</div>
                                <div class="voucher-code"><?= htmlspecialchars($voucher['code']) ?></div>
                                <div class="voucher-name"><?= htmlspecialchars($voucher['name']) ?></div>
                            </div>
                            
                            <div class="voucher-body">
                                <div class="voucher-value shipping-value">
                                    FREE SHIPPING
                                </div>
                                
                                <div class="voucher-details">
                                    <?php if ($voucher['min_purchase'] > 0): ?>
                                        <div class="voucher-detail-item">
                                            <span>📦</span>
                                            <span>Min. purchase: Rp <?= number_format($voucher['min_purchase'], 0, ',', '.') ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($voucher['max_discount'] > 0): ?>
                                        <div class="voucher-detail-item">
                                            <span>🔝</span>
                                            <span>Max. shipping cost: Rp <?= number_format($voucher['max_discount'], 0, ',', '.') ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($voucher['usage_limit_per_user']): ?>
                                        <div class="voucher-detail-item">
                                            <span>🎯</span>
                                            <span>Used: <?= $voucher['usage_count'] ?> / <?= $voucher['usage_limit_per_user'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="voucher-footer">
                                <div class="voucher-expiry">
                                    ⏰ Valid until <?= date('d M Y', strtotime($voucher['valid_until'])) ?>
                                </div>
                                <button onclick="copyCode('<?= $voucher['code'] ?>')" class="btn-copy" style="background: #10B981;">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('✅ Voucher code "' + code + '" copied to clipboard!');
    }).catch(err => {
        alert('❌ Failed to copy: ' + err);
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
