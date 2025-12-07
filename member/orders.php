<?php
require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user = getCurrentUser();

// Auto-cancel expired orders
$pdo->exec("
    UPDATE orders 
    SET payment_status = 'expired' 
    WHERE payment_status = 'pending' 
    AND expired_at IS NOT NULL 
    AND expired_at < NOW()
");

// Get pending orders (unpaid, not expired)
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    AND payment_status = 'pending' 
    AND (expired_at IS NULL OR expired_at > NOW())
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$pendingOrders = $stmt->fetchAll();

// Get all other orders
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    AND (payment_status != 'pending' OR payment_status = 'expired')
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$page_title = 'Pesanan Saya - Cek Status & Riwayat Belanja | Dorve House';
$page_description = 'Lihat semua pesanan baju wanita Anda di Dorve House. Cek status pengiriman, detail pesanan, dan riwayat transaksi. Track pesanan dengan mudah.';
include __DIR__ . '/../includes/header.php';
?>

<style>
    .member-content h1 {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        margin-bottom: 40px;
    }

    .order-card {
        background: var(--white);
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 24px;
        transition: all 0.3s;
    }

    .order-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .order-number {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .order-date {
        font-size: 14px;
        color: var(--grey);
    }

    .order-status {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .status-badge {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-paid {
        background: #D4EDDA;
        color: #155724;
    }

    .status-pending {
        background: #FFF3CD;
        color: #856404;
    }

    .status-delivered {
        background: #D4EDDA;
        color: #155724;
    }

    .status-processing {
        background: #D1ECF1;
        color: #0C5460;
    }

    .order-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .detail-item {
        font-size: 14px;
    }

    .detail-label {
        color: var(--grey);
        margin-bottom: 4px;
    }

    .detail-value {
        font-weight: 600;
        color: var(--charcoal);
    }

    .order-total {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        font-weight: 600;
        color: var(--charcoal);
    }

    .order-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
        display: inline-block;
        text-align: center;
    }

    .btn-primary {
        background: var(--charcoal);
        color: var(--white);
    }

    .btn-secondary {
        background: var(--white);
        color: var(--charcoal);
        border: 1px solid rgba(0,0,0,0.15);
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .empty-state {
        text-align: center;
        padding: 80px 40px;
    }

    .empty-state h3 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        margin-bottom: 16px;
    }

    .empty-state p {
        color: var(--grey);
        margin-bottom: 30px;
    }

    /* ========================================
       MOBILE RESPONSIVE STYLES
       ======================================== */

    /* Tablet and below (768px) */
    @media (max-width: 768px) {
        /* Page Title & Typography */
        .member-content h1 {
            font-size: 28px !important;
            margin-bottom: 24px !important;
        }

        .section-title {
            font-size: 22px !important;
            margin-bottom: 20px !important;
        }

        /* Pending Orders Section - Override inline styles */
        .member-content > div[style*="background: linear-gradient"] {
            padding: 16px !important;
        }

        .member-content > div[style*="background: linear-gradient"] h2 {
            font-size: 20px !important;
            margin-bottom: 12px !important;
        }

        .member-content > div[style*="background: linear-gradient"] h2 span {
            font-size: 12px !important;
            padding: 3px 8px !important;
            margin-left: 4px !important;
        }

        .member-content > div[style*="background: linear-gradient"] p {
            font-size: 13px !important;
            margin-bottom: 16px !important;
        }

        /* Pending Order Card Inline Styles */
        .order-card[style*="border: 2px solid #F59E0B"] > div[style*="position: absolute"] {
            font-size: 12px !important;
            padding: 6px 12px !important;
        }

        .order-card div[style*="font-size: 18px"] {
            font-size: 16px !important;
        }

        .order-card div[style*="display: flex"][style*="gap: 12px"][style*="margin-top: 16px"] {
            flex-direction: column !important;
        }

        .order-card div[style*="display: flex"][style*="gap: 12px"][style*="margin-top: 16px"] button {
            width: 100% !important;
        }

        /* Copy Button Inline Style Override */
        button[style*="padding: 4px 12px"][onclick*="copyOrderId"] {
            font-size: 11px !important;
            padding: 4px 10px !important;
        }

        /* Order Cards - Stack and reduce padding */
        .order-card {
            padding: 20px;
            margin-bottom: 16px;
        }

        .order-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .order-items {
            flex-direction: column;
            gap: 12px;
        }

        /* Order Actions - Full width buttons */
        .order-actions {
            flex-direction: column;
            width: 100%;
            gap: 8px;
        }

        .order-actions .btn {
            width: 100%;
            min-height: 44px;
            padding: 12px;
            font-size: 14px;
        }

        /* Pending Orders Banner */
        .pending-banner {
            padding: 16px;
            font-size: 14px;
        }

        .pending-banner h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        /* Order Status & Badges */
        .order-status {
            font-size: 11px;
            padding: 4px 10px;
            flex-wrap: wrap;
        }

        .status-badge {
            font-size: 11px;
            padding: 4px 10px;
        }

        .order-number {
            font-size: 16px;
        }

        .order-date {
            font-size: 12px;
        }

        /* Copy Button & Order ID */
        .copy-btn {
            font-size: 12px;
            padding: 6px 12px;
            min-height: 36px;
        }

        .order-id-display {
            font-size: 14px;
            word-break: break-all;
        }

        /* Countdown Timer */
        .countdown-timer {
            font-size: 14px;
            padding: 8px 12px;
        }

        /* Empty State */
        .empty-state {
            padding: 40px 20px;
        }

        .empty-state h3 {
            font-size: 24px;
        }

        /* Modal Responsive */
        .modal-content {
            width: 95%;
            padding: 20px;
        }

        .bank-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        /* Order Details Grid */
        .order-details {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        /* Order Total */
        .order-total {
            font-size: 20px;
        }

        /* Bank Transfer Modal - Mobile Responsive */
        .bank-modal > div {
            width: 95% !important;
            padding: 24px !important;
            max-width: none !important;
        }

        .bank-modal h2 {
            font-size: 22px !important;
            margin-bottom: 20px !important;
        }

        .bank-modal div[style*="font-size: 36px"] {
            font-size: 28px !important;
        }

        /* Review Modal - Mobile Responsive */
        .review-modal > div {
            width: 95% !important;
            padding: 20px !important;
        }

        .review-modal h2 {
            font-size: 20px !important;
            margin-bottom: 20px !important;
        }

        .review-modal div[style*="display: flex"][style*="gap: 16px"] {
            flex-direction: column !important;
            align-items: stretch !important;
        }

        .review-modal img {
            width: 100% !important;
            height: auto !important;
            max-width: 200px !important;
        }

        .review-modal a[href*="write-review"] {
            width: 100% !important;
            text-align: center !important;
        }

        /* Complete Success Modal */
        .member-content div[style*="position: fixed"][style*="rgba(0,0,0,0.7)"] > div {
            width: 90% !important;
            padding: 32px 20px !important;
        }

        .member-content div[style*="position: fixed"] h2 {
            font-size: 24px !important;
        }

        .member-content div[style*="position: fixed"] div[style*="display: flex"] button {
            padding: 12px !important;
            font-size: 14px !important;
        }

        /* Order Header Inline Elements */
        .order-header > div > div[style*="display: flex"] {
            flex-wrap: wrap !important;
            gap: 8px !important;
        }

        /* Detail Items */
        .detail-item .detail-label {
            font-size: 12px;
        }

        .detail-item .detail-value {
            font-size: 14px;
        }
    }

    /* Mobile phones (480px and below) */
    @media (max-width: 480px) {
        /* Page Title */
        .member-content h1 {
            font-size: 24px !important;
            margin-bottom: 20px !important;
        }

        /* Order Cards - Further reduce padding */
        .order-card {
            padding: 16px !important;
            margin-bottom: 12px !important;
        }

        /* Pending Banner */
        .pending-banner {
            padding: 12px !important;
        }

        /* Pending Orders Section - Further reduce for small phones */
        .member-content > div[style*="background: linear-gradient"] {
            padding: 12px !important;
            margin-bottom: 20px !important;
        }

        .member-content > div[style*="background: linear-gradient"] h2 {
            font-size: 18px !important;
        }

        .member-content > div[style*="background: linear-gradient"] h2 span {
            font-size: 11px !important;
            padding: 2px 6px !important;
        }

        /* Empty State */
        .empty-state {
            padding: 30px 16px !important;
        }

        .empty-state h3 {
            font-size: 20px !important;
        }

        /* Order Number */
        .order-number {
            font-size: 14px !important;
        }

        /* Order Date */
        .order-date {
            font-size: 11px !important;
        }

        /* Countdown Timer */
        .countdown-timer {
            font-size: 11px !important;
            padding: 5px 8px !important;
        }

        .order-card[style*="border: 2px solid #F59E0B"] > div[style*="position: absolute"] {
            font-size: 11px !important;
            padding: 5px 8px !important;
        }

        /* Order Total */
        .order-total {
            font-size: 18px !important;
        }

        /* Status Badges */
        .status-badge {
            font-size: 10px !important;
            padding: 3px 8px !important;
        }

        /* Buttons */
        .btn {
            font-size: 13px !important;
            padding: 10px !important;
            min-height: 40px !important;
        }

        /* Copy Button */
        button[style*="padding: 4px 12px"][onclick*="copyOrderId"] {
            font-size: 10px !important;
            padding: 3px 8px !important;
        }

        /* Bank Modal - Small phones */
        .bank-modal > div {
            padding: 20px !important;
        }

        .bank-modal h2 {
            font-size: 20px !important;
        }

        .bank-modal div[style*="font-size: 36px"] {
            font-size: 24px !important;
        }

        .bank-modal button, .bank-modal a {
            font-size: 13px !important;
            padding: 12px !important;
        }

        /* Review Modal - Small phones */
        .review-modal > div {
            padding: 16px !important;
        }

        .review-modal h2 {
            font-size: 18px !important;
        }

        /* Order Details - Smaller text */
        .detail-item .detail-label {
            font-size: 11px;
        }

        .detail-item .detail-value {
            font-size: 13px;
        }

        /* Tracking Number */
        .detail-value[style*="font-family: 'Courier New'"] {
            font-size: 12px !important;
            word-break: break-all;
        }

        /* Order Actions Gap */
        .order-actions {
            gap: 6px !important;
        }
    }
</style>

<div class="member-layout">
    <?php include __DIR__ . '/../includes/member-sidebar.php'; ?>

    <div class="member-content">
        <h1>My Orders</h1>

        <!-- PENDING ORDERS SECTION -->
        <?php if (!empty($pendingOrders)): ?>
        <div style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-radius: 12px; padding: 24px; margin-bottom: 32px; border: 2px solid #F59E0B;">
            <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 8px; color: #92400E;">
                ⏰ Pending Payment Orders
                <span style="background: #EF4444; color: white; padding: 4px 12px; border-radius: 12px; font-size: 14px; margin-left: 8px;">
                    <?= count($pendingOrders) ?>
                </span>
            </h2>
            <p style="color: #92400E; margin-bottom: 20px;">Complete your payment before the order expires</p>
            
            <?php foreach ($pendingOrders as $order): 
                $timeLeft = strtotime($order['expired_at']) - time();
                $minutesLeft = floor($timeLeft / 60);
                $secondsLeft = $timeLeft % 60;
            ?>
            <div class="order-card" style="background: white; border: 2px solid #F59E0B; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; background: #EF4444; color: white; padding: 8px 16px; border-radius: 0 0 0 12px; font-weight: 700; font-size: 14px;" 
                     data-order-id="<?= $order['id'] ?>" 
                     data-expires="<?= strtotime($order['expired_at']) ?>" 
                     class="countdown-timer">
                    ⏰ <?= sprintf('%02d:%02d', $minutesLeft, $secondsLeft) ?>
                </div>
                
                <div class="order-header">
                    <div>
                        <div style="font-size: 14px; color: #6B7280; margin-bottom: 4px;">
                            Order #<?= htmlspecialchars($order['order_number']) ?>
                        </div>
                        <div style="font-size: 18px; font-weight: 700; color: #1F2937;">
                            Rp <?= number_format($order['total_payable_amount'], 0, ',', '.') ?>
                        </div>
                        <div style="font-size: 13px; color: #6B7280; margin-top: 4px;">
                            <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 16px;">
                    <button onclick="resumePayment(<?= $order['id'] ?>, '<?= $order['payment_method'] ?>')" 
                            class="btn" 
                            style="flex: 1; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; border: none; font-weight: 700; padding: 14px;">
                        💳 Bayar Sekarang
                    </button>
                    <button onclick="if(confirm('Cancel this order?')) cancelOrder(<?= $order['id'] ?>)" 
                            class="btn" 
                            style="background: white; color: #EF4444; border: 2px solid #EF4444;">
                        ✕ Cancel
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ALL ORDERS -->
        <?php if (empty($orders) && empty($pendingOrders)): ?>
            <div class="empty-state">
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders. Start shopping to see your orders here.</p>
                <a href="/pages/all-products.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                                <button onclick="copyOrderId('<?php echo htmlspecialchars($order['order_number']); ?>')" 
                                        style="padding: 4px 12px; background: #E5E7EB; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; color: #374151;"
                                        title="Copy Order ID">
                                    📋 Copy ID
                                </button>
                            </div>
                            <div class="order-date">Ordered on <?php echo date('F d, Y', strtotime($order['created_at'])); ?></div>
                            <div style="font-size: 12px; color: #9CA3AF; margin-top: 4px;">
                                Order ID: <code style="background: #F3F4F6; padding: 2px 6px; border-radius: 4px; font-weight: 600;">#<?php echo $order['id']; ?></code>
                            </div>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                            <span class="status-badge status-<?php echo $order['shipping_status']; ?>">
                                <?php echo ucfirst($order['shipping_status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-details">
                        <div class="detail-item">
                            <div class="detail-label">Payment Method</div>
                            <div class="detail-value"><?php echo ucfirst($order['payment_method']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Shipping Address</div>
                            <div class="detail-value"><?php echo htmlspecialchars(explode(',', $order['shipping_address'])[0]); ?></div>
                        </div>
                        <?php if ($order['tracking_number']): ?>
                        <div class="detail-item">
                            <div class="detail-label">Tracking Number</div>
                            <div class="detail-value" style="font-family: 'Courier New', monospace; color: #0066cc;">
                                <?php echo htmlspecialchars($order['tracking_number']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <div class="detail-label">Total Amount</div>
                            <div class="order-total"><?php echo formatPrice($order['total_amount']); ?></div>
                        </div>
                    </div>

                    <div class="order-actions">
                        <a href="/member/order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">View Details</a>
                        
                        <?php if ($order['tracking_number'] || $order['delivery_status']): ?>
                            <button onclick="openTrackingModal(<?php echo $order['id']; ?>)" class="btn btn-secondary" style="background: #10B981; color: white;">
                                📦 Track Paket
                            </button>
                        <?php endif; ?>
                        
                        <?php 
                        // Show "Terima Pesanan" button if paid and delivered/shipped but not completed
                        if ($order['payment_status'] === 'paid' && !$order['completed_at'] && 
                            ($order['delivery_status'] === 'delivered' || $order['delivery_status'] === 'courier_picked_up')): 
                        ?>
                            <button onclick="completeOrder(<?php echo $order['id']; ?>)" 
                                    class="btn" 
                                    id="completeBtn<?php echo $order['id']; ?>"
                                    style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; border: none; font-weight: 600;">
                                ✅ Terima Pesanan
                            </button>
                        <?php endif; ?>
                        
                        <?php 
                        // Show review button if order completed and can review
                        if ($order['completed_at'] && $order['can_review'] == 1):
                            // Get order items to check which products can be reviewed
                            $stmt = $pdo->prepare("
                                SELECT oi.*, p.name as product_name,
                                       (SELECT COUNT(*) FROM product_reviews WHERE order_id = ? AND product_id = oi.product_id) as has_review
                                FROM order_items oi
                                JOIN products p ON oi.product_id = p.id
                                WHERE oi.order_id = ?
                            ");
                            $stmt->execute([$order['id'], $order['id']]);
                            $orderItems = $stmt->fetchAll();
                            
                            $hasUnreviewedItems = false;
                            foreach ($orderItems as $item) {
                                if ($item['has_review'] == 0) {
                                    $hasUnreviewedItems = true;
                                    break;
                                }
                            }
                            
                            if ($hasUnreviewedItems):
                        ?>
                            <button onclick="showReviewOptions(<?php echo $order['id']; ?>)" 
                                    class="btn" 
                                    style="background: #FBBF24; color: #92400E; border: none; font-weight: 600;">
                                ⭐ Tulis Review
                            </button>
                        <?php 
                            endif;
                        endif; 
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/tracking-modal.php'; ?>

<!-- Load Midtrans Snap.js -->
<?php
require_once __DIR__ . '/../includes/MidtransHelper.php';
$midtrans = new MidtransHelper($pdo);
?>
<script src="<?= $midtrans->getSnapJsUrl() ?>" data-client-key="<?= $midtrans->getClientKey() ?>"></script>

<script>
function copyOrderId(orderNumber) {
    navigator.clipboard.writeText(orderNumber).then(() => {
        alert('✅ Order ID copied! Anda bisa kasih ini ke admin untuk konfirmasi order: ' + orderNumber);
    }).catch(err => {
        alert('❌ Gagal copy: ' + err.message);
    });
}

// Countdown Timer for Pending Orders
const countdownTimers = document.querySelectorAll('.countdown-timer');
countdownTimers.forEach(timer => {
    const expiresAt = parseInt(timer.dataset.expires);
    
    const interval = setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const timeLeft = expiresAt - now;
        
        if (timeLeft <= 0) {
            clearInterval(interval);
            timer.textContent = '❌ EXPIRED';
            timer.style.background = '#991B1B';
            // Auto reload to update status
            setTimeout(() => location.reload(), 2000);
            return;
        }
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timer.textContent = `⏰ ${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        // Change color when less than 5 minutes
        if (timeLeft < 300) {
            timer.style.background = '#DC2626';
        }
    }, 1000);
});

// Resume Payment Function
function resumePayment(orderId, paymentMethod) {
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = '⏳ Loading...';
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    
    fetch('/api/orders/resume-payment.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.error || 'Failed to resume payment');
        }
        
        if (data.paid) {
            alert('✅ ' + data.message);
            location.reload();
            return;
        }
        
        if (paymentMethod === 'wallet') {
            alert('✅ Payment successful!');
            location.reload();
            
        } else if (paymentMethod === 'midtrans') {
            // Open Snap popup
            window.snap.pay(data.snap_token, {
                onSuccess: function(result) {
                    alert('✅ Payment successful!');
                    location.reload();
                },
                onPending: function(result) {
                    alert('⏳ Payment pending. Please complete your payment.');
                    location.reload();
                },
                onError: function(result) {
                    alert('❌ Payment failed. Please try again.');
                    btn.disabled = false;
                    btn.textContent = '💳 Bayar Sekarang';
                },
                onClose: function() {
                    btn.disabled = false;
                    btn.textContent = '💳 Bayar Sekarang';
                }
            });
            
        } else if (paymentMethod === 'bank_transfer') {
            // Show bank transfer modal
            showBankTransferModal(data);
            btn.disabled = false;
            btn.textContent = '💳 Bayar Sekarang';
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        btn.disabled = false;
        btn.textContent = '💳 Bayar Sekarang';
    });
}

// Cancel Order
function cancelOrder(orderId) {
    fetch('/api/orders/cancel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${orderId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Order cancelled');
            location.reload();
        } else {
            alert('❌ ' + (data.error || 'Failed to cancel order'));
        }
    })
    .catch(e => alert('Error: ' + e.message));
}

// Bank Transfer Modal (same as checkout)
function showBankTransferModal(data) {
    const modal = document.createElement('div');
    modal.className = 'bank-modal';
    modal.style.cssText = 'display: flex; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center;';
    modal.innerHTML = `
        <div style="background: white; border-radius: 20px; max-width: 600px; width: 90%; padding: 40px;">
            <h2 style="font-size: 28px; font-weight: 700; margin-bottom: 24px; text-align: center;">🏦 Transfer Instructions</h2>
            
            <div style="background: #F3F4F6; padding: 24px; border-radius: 12px; text-align: center; margin-bottom: 24px;">
                <div style="font-size: 16px; color: #6B7280; margin-bottom: 8px;">Please transfer</div>
                <div style="font-size: 36px; color: #667EEA; font-weight: 700; margin: 12px 0;">
                    Rp ${formatNumber(data.total_with_code)}
                </div>
                <div style="font-size: 14px; color: #6B7280;">Including unique code: <strong>${data.unique_code}</strong></div>
            </div>

            <div id="bankListModal">Loading banks...</div>

            <div style="margin-top: 24px; text-align: center;">
                <a href="https://wa.me/6281377378859?text=Halo%20Admin,%20saya%20sudah%20transfer%20untuk%20order%20${data.order_number}" 
                   target="_blank" style="display: inline-block; padding: 14px 28px; background: #25D366; color: white; text-decoration: none; border-radius: 10px; font-weight: 600;">
                    📱 Contact Admin via WhatsApp
                </a>
            </div>

            <button onclick="this.parentElement.parentElement.remove()" style="width: 100%; padding: 14px; margin-top: 16px; background: #1A1A1A; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                Got It
            </button>
        </div>
    `;
    document.body.appendChild(modal);
    
    // Load banks
    fetch('/api/payment/get-banks.php')
        .then(r => r.json())
        .then(banks => {
            const bankList = modal.querySelector('#bankListModal');
            bankList.innerHTML = banks.map(bank => `
                <div style="padding: 16px; border: 2px solid #E5E7EB; border-radius: 12px; margin-bottom: 12px;">
                    <div style="font-weight: 700; font-size: 16px;">${bank.bank_name}</div>
                    <div style="font-family: 'Courier New', monospace; font-size: 18px; color: #667EEA; font-weight: 700; margin: 8px 0;">
                        ${bank.account_number}
                    </div>
                    <div style="color: #6B7280; font-size: 14px;">${bank.account_name}</div>
                </div>
            `).join('');
        });
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}
</script>

<!-- Complete Order & Review Functions -->
<script>
// Complete Order Function
async function completeOrder(orderId) {
    if (!confirm('Konfirmasi bahwa Anda sudah menerima pesanan ini?')) {
        return;
    }
    
    const btn = document.getElementById('completeBtn' + orderId);
    btn.disabled = true;
    btn.textContent = '⏳ Processing...';
    
    try {
        const formData = new FormData();
        formData.append('order_id', orderId);
        
        const response = await fetch('/api/orders/complete-order.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success modal
            showCompleteSuccessModal(orderId);
        } else {
            alert('Error: ' + result.message);
            btn.disabled = false;
            btn.textContent = '✅ Terima Pesanan';
        }
    } catch (error) {
        alert('Terjadi kesalahan. Silakan coba lagi.');
        btn.disabled = false;
        btn.textContent = '✅ Terima Pesanan';
    }
}

function showCompleteSuccessModal(orderId) {
    const modal = document.createElement('div');
    modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999;';
    modal.innerHTML = `
        <div style="background: white; border-radius: 20px; padding: 48px; max-width: 500px; text-align: center;">
            <div style="font-size: 64px; margin-bottom: 16px;">✅</div>
            <h2 style="font-size: 28px; margin-bottom: 16px;">Pesanan Diterima!</h2>
            <p style="color: #6B7280; margin-bottom: 24px;">
                Terima kasih! Pesanan Anda telah ditandai sebagai diterima.<br>
                Bagaimana pengalaman Anda dengan produk kami?
            </p>
            <div style="display: flex; gap: 12px;">
                <button onclick="window.location.href=window.location.href" style="flex: 1; padding: 14px; background: #6B7280; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Nanti Saja
                </button>
                <button onclick="showReviewOptions(${orderId})" style="flex: 1; padding: 14px; background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%); color: #92400E; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    ⭐ Tulis Review Sekarang
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Show Review Options Function
async function showReviewOptions(orderId) {
    // Close any existing modals
    document.querySelectorAll('.review-modal').forEach(m => m.remove());
    
    try {
        const response = await fetch(`/api/orders/get-reviewable-items.php?order_id=${orderId}`);
        const result = await response.json();
        
        if (!result.success || !result.items || result.items.length === 0) {
            alert('Tidak ada produk yang bisa direview.');
            return;
        }
        
        const items = result.items;
        
        const modal = document.createElement('div');
        modal.className = 'review-modal';
        modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999; overflow-y: auto;';
        
        let itemsHtml = '';
        items.forEach(item => {
            itemsHtml += `
                <div style="display: flex; gap: 16px; padding: 20px; background: #F9FAFB; border-radius: 12px; margin-bottom: 12px; align-items: center;">
                    <img src="/uploads/products/${item.product_image}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" alt="${item.product_name}">
                    <div style="flex: 1;">
                        <h4 style="margin-bottom: 4px;">${item.product_name}</h4>
                        <p style="font-size: 13px; color: #6B7280;">Qty: ${item.quantity}</p>
                    </div>
                    <a href="/member/write-review.php?order_id=${orderId}&product_id=${item.product_id}" 
                       style="padding: 10px 20px; background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px;">
                        ✍️ Review
                    </a>
                </div>
            `;
        });
        
        modal.innerHTML = `
            <div style="background: white; border-radius: 20px; padding: 32px; max-width: 600px; width: 90%;">
                <h2 style="font-size: 24px; margin-bottom: 24px; text-align: center;">⭐ Pilih Produk untuk Direview</h2>
                <div style="max-height: 400px; overflow-y: auto;">
                    ${itemsHtml}
                </div>
                <button onclick="this.closest('.review-modal').remove()" 
                        style="width: 100%; padding: 14px; background: #6B7280; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 16px;">
                    Tutup
                </button>
            </div>
        `;
        
        document.body.appendChild(modal);
    } catch (error) {
        alert('Terjadi kesalahan. Silakan coba lagi.');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
