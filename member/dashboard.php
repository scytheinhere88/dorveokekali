<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/tier-helper.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user = getCurrentUser();

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$order_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_orders = $stmt->fetchAll();

$current_tier = $user['current_tier'] ?? 'bronze';
$total_topup = floatval($user['total_topup'] ?? 0);
$tier_info = getTierInfo($current_tier);
$tier_progress = getProgressToNextTier($total_topup);

$page_title = 'My Dashboard - Dorve.id';
include __DIR__ . '/../includes/header.php';
?>

<style>
    .member-content h1 {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        margin-bottom: 40px;
    }

    /* Tier Card */
    .tier-card {
        background: linear-gradient(135deg, <?php echo $tier_info['color']; ?>15 0%, <?php echo $tier_info['color']; ?>25 100%);
        border: 2px solid <?php echo $tier_info['color']; ?>;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }

    .tier-card::before {
        content: '<?php echo $tier_info['icon']; ?>';
        position: absolute;
        right: -20px;
        top: -20px;
        font-size: 150px;
        opacity: 0.1;
    }

    .tier-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .tier-badge {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: white;
        padding: 12px 24px;
        border-radius: 50px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .tier-icon {
        font-size: 32px;
    }

    .tier-name {
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
    }

    .tier-benefits {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .benefit-item {
        background: white;
        padding: 16px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .benefit-icon {
        font-size: 24px;
    }

    .benefit-text {
        flex: 1;
    }

    .benefit-label {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 4px;
    }

    .benefit-value {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .tier-progress-section {
        background: white;
        padding: 20px;
        border-radius: 12px;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 14px;
        color: #6B7280;
    }

    .progress-bar {
        width: 100%;
        height: 12px;
        background: #E5E7EB;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, <?php echo $tier_info['color']; ?> 0%, <?php echo $tier_info['color']; ?>CC 100%);
        border-radius: 10px;
        transition: width 0.3s;
    }

    .progress-info {
        font-size: 13px;
        color: #6B7280;
        text-align: center;
    }

    /* All Tiers Section */
    .all-tiers-section {
        background: white;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 40px;
        border: 1px solid #E5E7EB;
    }

    .all-tiers-section h2 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 24px;
        text-align: center;
    }

    .tiers-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .tier-item {
        background: #F9FAFB;
        border: 2px solid #E5E7EB;
        border-radius: 12px;
        padding: 24px 16px;
        text-align: center;
        transition: all 0.3s;
    }

    .tier-item.current {
        border-color: var(--tier-color);
        background: linear-gradient(135deg, var(--tier-color)15 0%, var(--tier-color)25 100%);
        transform: scale(1.05);
    }

    .tier-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }

    .tier-item-icon {
        font-size: 48px;
        margin-bottom: 12px;
    }

    .tier-item-name {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .tier-item-requirement {
        font-size: 12px;
        color: #6B7280;
        margin-bottom: 16px;
    }

    .tier-item-benefits {
        text-align: left;
        font-size: 12px;
    }

    .tier-item-benefits div {
        margin-bottom: 6px;
        color: #4B5563;
    }

    /* Stats */
    .stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 48px;
    }

    .stat {
        background: #F9FAFB;
        padding: 32px 24px;
        border-radius: 16px;
        text-align: center;
        border: 1px solid #E5E7EB;
    }

    .stat-value {
        font-size: 42px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 13px;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    @media (max-width: 968px) {
        .member-content h1 {
            font-size: 28px;
            margin-bottom: 24px;
        }

        .tier-card {
            padding: 24px 20px;
        }

        .tier-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }

        .tier-benefits {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .tiers-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
    }

    @media (max-width: 640px) {
        .stats {
            grid-template-columns: 1fr;
        }

        .tiers-grid {
            grid-template-columns: 1fr;
        }

        .tier-item-icon {
            font-size: 36px;
        }
    }
</style>

<div class="member-layout">
    <?php include __DIR__ . '/../includes/member-sidebar.php'; ?>

    <div class="member-content">
        <h1>My Dashboard</h1>

        <!-- Current Tier Card -->
        <div class="tier-card">
            <div class="tier-header">
                <div class="tier-badge">
                    <span class="tier-icon"><?php echo $tier_info['icon']; ?></span>
                    <span class="tier-name"><?php echo $tier_info['name']; ?> Member</span>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 14px; color: #6B7280;">Total Top-up</div>
                    <div style="font-size: 24px; font-weight: 700;">Rp <?php echo number_format($total_topup, 0, ',', '.'); ?></div>
                </div>
            </div>

            <div class="tier-benefits">
                <div class="benefit-item">
                    <span class="benefit-icon">🎁</span>
                    <div class="benefit-text">
                        <div class="benefit-label">Member Discount</div>
                        <div class="benefit-value"><?php echo $tier_info['discount']; ?>%</div>
                    </div>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">💰</span>
                    <div class="benefit-text">
                        <div class="benefit-label">Referral Commission</div>
                        <div class="benefit-value"><?php echo $tier_info['commission']; ?>%</div>
                    </div>
                </div>
            </div>

            <?php if ($tier_progress['next_tier']): ?>
            <div class="tier-progress-section">
                <div class="progress-label">
                    <span>Progress to <?php echo getTierInfo($tier_progress['next_tier'])['name']; ?></span>
                    <span><?php echo number_format($tier_progress['progress'], 1); ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $tier_progress['progress']; ?>%"></div>
                </div>
                <div class="progress-info">
                    Top-up Rp <?php echo number_format($tier_progress['needed'], 0, ',', '.'); ?> more to unlock <?php echo getTierInfo($tier_progress['next_tier'])['icon']; ?> <?php echo getTierInfo($tier_progress['next_tier'])['name']; ?> tier!
                </div>
            </div>
            <?php else: ?>
            <div class="tier-progress-section">
                <div style="text-align: center; padding: 12px; color: #10B981; font-weight: 600;">
                    🎉 You've reached the highest tier! Enjoy maximum benefits!
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- All Tiers Overview -->
        <div class="all-tiers-section">
            <h2>Membership Tiers</h2>
            <div class="tiers-grid">
                <?php
                $all_tiers = ['bronze', 'silver', 'gold', 'platinum'];
                foreach ($all_tiers as $tier_key):
                    $tier_data = getTierInfo($tier_key);
                    $is_current = $tier_key === $current_tier;
                ?>
                <div class="tier-item <?php echo $is_current ? 'current' : ''; ?>" style="--tier-color: <?php echo $tier_data['color']; ?>">
                    <div class="tier-item-icon"><?php echo $tier_data['icon']; ?></div>
                    <div class="tier-item-name"><?php echo $tier_data['name']; ?></div>
                    <div class="tier-item-requirement">
                        <?php
                        if ($tier_data['min'] == 0) {
                            echo 'Starting tier';
                        } else {
                            echo 'Rp ' . number_format($tier_data['min'], 0, ',', '.') . '+';
                        }
                        ?>
                    </div>
                    <div class="tier-item-benefits">
                        <div>🎁 <?php echo $tier_data['discount']; ?>% Discount</div>
                        <div>💰 <?php echo $tier_data['commission']; ?>% Commission</div>
                    </div>
                    <?php if ($is_current): ?>
                    <div style="margin-top: 12px; padding: 6px 12px; background: white; border-radius: 20px; font-weight: 600; font-size: 11px;">
                        YOUR TIER
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat">
                <div class="stat-value"><?php echo $order_count; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>

            <div class="stat">
                <div class="stat-value">
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND payment_status = 'paid'");
                    $stmt->execute([$_SESSION['user_id']]);
                    echo $stmt->fetch()['count'];
                    ?>
                </div>
                <div class="stat-label">Completed</div>
            </div>

            <div class="stat">
                <div class="stat-value">
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reviews WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    echo $stmt->fetch()['count'];
                    ?>
                </div>
                <div class="stat-label">Reviews</div>
            </div>
        </div>

        <!-- Recent Orders -->
        <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 24px;">Recent Orders</h2>

        <?php if (!empty($recent_orders)): ?>
            <?php foreach ($recent_orders as $order): ?>
            <div style="padding: 20px 0; border-bottom: 1px solid #E5E7EB;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600; margin-bottom: 6px;">#<?php echo $order['order_number']; ?></div>
                        <div style="font-size: 13px; color: #6B7280;"><?php echo date('d M Y', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 18px; font-weight: 700; margin-bottom: 12px;">
                            Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                        </div>
                        <a href="/member/order-detail.php?id=<?php echo $order['id']; ?>"
                           style="display: inline-block; padding: 10px 24px; background: #1F2937; color: white; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 600;">
                            View Order
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: #6B7280;">
                <h3 style="margin-bottom: 8px;">No orders yet</h3>
                <p>Start shopping to see your orders here!</p>
                <a href="/" style="display: inline-block; margin-top: 20px; padding: 12px 32px; background: #1F2937; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Shop Now
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
