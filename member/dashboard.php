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

// Tier Info
$current_tier = $user['current_tier'] ?? 'bronze';
$total_topup = floatval($user['total_topup'] ?? 0);
$tier_info = getTierInfo($current_tier);
$tier_progress = getProgressToNextTier($total_topup);

$page_title = 'My Dashboard - Dorve.id';
include __DIR__ . '/../includes/header.php';
?>

<style>
    /* GUARANTEED TO WORK - PROFESSIONAL MEMBER LAYOUT */
    * { box-sizing: border-box; margin: 0; padding: 0; }

    .prof-wrapper {
        display: flex;
        max-width: 1400px;
        margin: 100px auto 60px;
        padding: 0 40px;
        gap: 48px;
        align-items: flex-start;
    }

    /* SIDEBAR LEFT */
    .prof-sidebar {
        width: 280px;
        min-width: 280px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 120px;
        overflow: hidden;
    }

    .prof-sidebar-header {
        padding: 24px;
        background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        color: white;
        text-align: center;
    }

    .prof-sidebar-header h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .prof-sidebar-header p {
        font-size: 13px;
        opacity: 0.9;
    }

    .prof-nav {
        list-style: none;
        padding: 12px;
        margin: 0;
    }

    .prof-nav li {
        margin-bottom: 4px;
    }

    .prof-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        color: #4B5563;
        text-decoration: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .prof-nav a:hover {
        background: #F3F4F6;
        color: #1F2937;
    }

    .prof-nav a.active {
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: white;
        font-weight: 600;
    }

    .prof-nav .logout {
        border-top: 1px solid #E5E7EB;
        margin-top: 12px;
        padding-top: 16px;
    }

    .prof-nav .logout a {
        color: #EF4444;
    }

    /* CONTENT RIGHT */
    .prof-content {
        flex: 1;
        min-width: 0;
        background: white;
        border-radius: 20px;
        padding: 48px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    }

    .prof-content h1 {
        font-family: 'Playfair Display', serif;
        font-size: 40px;
        font-weight: 700;
        margin-bottom: 36px;
        color: #1F2937;
    }

    /* MOBILE */
    @media (max-width: 968px) {
        .prof-wrapper {
            flex-direction: column;
            padding: 0 20px;
            margin: 80px auto 40px;
            gap: 24px;
        }

        .prof-sidebar {
            width: 100%;
            position: relative;
            top: 0;
        }

        .prof-nav {
            display: flex;
            overflow-x: auto;
            gap: 8px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .prof-nav::-webkit-scrollbar {
            display: none;
        }

        .prof-nav li {
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .prof-nav a {
            white-space: nowrap;
            padding: 10px 16px;
            font-size: 13px;
        }

        .prof-nav .logout {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        .prof-content {
            padding: 32px 24px;
        }

        .prof-content h1 {
            font-size: 28px;
        }
    }

    /* TIER CARD - PREMIUM LUXURY */
    .tier-premium-card {
        background: linear-gradient(135deg, <?php echo $tier_info['color']; ?>15 0%, <?php echo $tier_info['color']; ?>30 100%);
        border: 3px solid <?php echo $tier_info['color']; ?>;
        border-radius: 24px;
        padding: 40px;
        margin-bottom: 48px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px <?php echo $tier_info['color']; ?>40;
    }

    .tier-premium-card::before {
        content: '<?php echo $tier_info['icon']; ?>';
        position: absolute;
        right: -40px;
        top: -40px;
        font-size: 200px;
        opacity: 0.08;
        transform: rotate(-15deg);
    }

    .tier-badge-large {
        display: inline-flex;
        align-items: center;
        gap: 16px;
        background: white;
        padding: 16px 32px;
        border-radius: 60px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        margin-bottom: 32px;
    }

    .tier-icon-huge {
        font-size: 48px;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    }

    .tier-name-large {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        font-weight: 800;
        color: #1F2937;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .tier-benefits-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .benefit-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        transition: transform 0.3s;
    }

    .benefit-card:hover {
        transform: translateY(-4px);
    }

    .benefit-icon-large {
        font-size: 36px;
        margin-bottom: 12px;
    }

    .benefit-title {
        font-size: 14px;
        color: #6B7280;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .benefit-value-large {
        font-size: 32px;
        font-weight: 800;
        color: #1F2937;
    }

    .tier-progress-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .progress-bar-container {
        width: 100%;
        height: 16px;
        background: #E5E7EB;
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, <?php echo $tier_info['color']; ?> 0%, <?php echo $tier_info['color']; ?>CC 100%);
        border-radius: 20px;
        transition: width 0.5s;
        box-shadow: 0 0 12px <?php echo $tier_info['color']; ?>60;
    }

    /* ALL TIERS SHOWCASE */
    .all-tiers-showcase {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 48px;
    }

    .tier-mini-card {
        padding: 32px 20px;
        border-radius: 20px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
        border: 3px solid transparent;
    }

    .tier-mini-card.active {
        transform: scale(1.05);
        border-color: #1F2937;
        box-shadow: 0 12px 40px rgba(0,0,0,0.2);
    }

    .tier-mini-card:hover {
        transform: translateY(-8px);
    }

    .tier-bronze-bg {
        background: linear-gradient(135deg, #CD7F32 0%, #E89F60 50%, #CD7F32 100%);
        box-shadow: 0 8px 24px rgba(205, 127, 50, 0.4);
    }

    .tier-silver-bg {
        background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 50%, #C0C0C0 100%);
        box-shadow: 0 8px 24px rgba(192, 192, 192, 0.4);
    }

    .tier-gold-bg {
        background: linear-gradient(135deg, #FFD700 0%, #FFF176 50%, #FFD700 100%);
        box-shadow: 0 8px 24px rgba(255, 215, 0, 0.5);
    }

    .tier-platinum-bg {
        background: linear-gradient(135deg, #E5E4E2 0%, #FFFFFF 25%, #B0C4DE 50%, #FFFFFF 75%, #E5E4E2 100%);
        box-shadow: 0 8px 32px rgba(176, 196, 222, 0.6);
    }

    .tier-mini-icon {
        font-size: 48px;
        margin-bottom: 16px;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    }

    .tier-mini-name {
        font-size: 20px;
        font-weight: 800;
        color: #1F2937;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .tier-mini-req {
        font-size: 12px;
        color: #2D2D2D;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .tier-mini-discount {
        background: rgba(255,255,255,0.4);
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 24px;
        font-weight: 800;
        color: #1F2937;
    }

    .active-badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: #1F2937;
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
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
        .tier-premium-card {
            padding: 24px;
        }

        .tier-name-large {
            font-size: 24px;
        }

        .tier-benefits-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .all-tiers-showcase {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
    }

    @media (max-width: 640px) {
        .all-tiers-showcase {
            grid-template-columns: 1fr;
        }

        .stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- PROFESSIONAL LAYOUT -->
<div class="prof-wrapper">

    <!-- SIDEBAR LEFT -->
    <aside class="prof-sidebar">
        <div class="prof-sidebar-header">
            <h3>Welcome back!</h3>
            <p><?php echo htmlspecialchars($user['name'] ?? $user['email']); ?></p>
        </div>

        <ul class="prof-nav">
            <li><a href="/member/dashboard.php" class="active">🏠 Dashboard</a></li>
            <li><a href="/member/orders.php">📦 My Orders</a></li>
            <li><a href="/member/wallet.php">💰 My Wallet</a></li>
            <li><a href="/member/address-book.php">📍 Address Book</a></li>
            <li><a href="/member/referral.php">👥 My Referrals</a></li>
            <li><a href="/member/vouchers/index.php">🎟️ My Vouchers</a></li>
            <li><a href="/member/reviews.php">⭐ My Reviews</a></li>
            <li><a href="/member/profile.php">👤 Edit Profile</a></li>
            <li><a href="/member/password.php">🔐 Change Password</a></li>
            <li class="logout"><a href="/auth/logout.php">🚪 Logout</a></li>
        </ul>
    </aside>

    <!-- CONTENT RIGHT -->
    <main class="prof-content">
        <h1>My Dashboard</h1>

        <!-- PREMIUM TIER CARD -->
        <div class="tier-premium-card">
            <div class="tier-badge-large">
                <span class="tier-icon-huge"><?php echo $tier_info['icon']; ?></span>
                <div>
                    <div class="tier-name-large"><?php echo $tier_info['name']; ?></div>
                    <div style="font-size: 14px; color: #6B7280; margin-top: 4px;">
                        Total Topup: <strong>Rp <?php echo number_format($total_topup, 0, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>

            <div class="tier-benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon-large">🎁</div>
                    <div class="benefit-title">Member Discount</div>
                    <div class="benefit-value-large"><?php echo $tier_info['discount']; ?>%</div>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon-large">💰</div>
                    <div class="benefit-title">Referral Commission</div>
                    <div class="benefit-value-large"><?php echo $tier_info['commission']; ?>%</div>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon-large">⭐</div>
                    <div class="benefit-title">Priority Level</div>
                    <div class="benefit-value-large"><?php
                        $priorities = ['bronze' => 'Basic', 'silver' => 'High', 'gold' => 'VIP', 'platinum' => 'Elite'];
                        echo $priorities[$current_tier];
                    ?></div>
                </div>
            </div>

            <?php if ($tier_progress['next_tier']):
                $next_tier_info = getTierInfo($tier_progress['next_tier']);
            ?>
            <div class="tier-progress-card">
                <div class="progress-header">
                    <div style="font-weight: 700; color: #1F2937;">
                        Next: <?php echo $next_tier_info['icon']; ?> <?php echo $next_tier_info['name']; ?>
                    </div>
                    <div style="font-weight: 700; color: <?php echo $tier_info['color']; ?>;">
                        <?php echo number_format($tier_progress['progress'], 1); ?>%
                    </div>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: <?php echo $tier_progress['progress']; ?>%"></div>
                </div>
                <div style="text-align: center; font-size: 14px; color: #6B7280;">
                    Topup <strong style="color: #1F2937;">Rp <?php echo number_format($tier_progress['needed'], 0, ',', '.'); ?></strong> lagi untuk unlock <?php echo $next_tier_info['name']; ?>!
                </div>
            </div>
            <?php else: ?>
            <div class="tier-progress-card" style="text-align: center; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white;">
                <div style="font-size: 20px; font-weight: 700;">🎉 Congratulations!</div>
                <div style="margin-top: 8px;">You've reached the highest tier. Enjoy maximum benefits!</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ALL TIERS SHOWCASE -->
        <h2 style="font-size: 28px; font-weight: 700; margin-bottom: 24px; text-align: center;">💎 Membership Tiers</h2>
        <div class="all-tiers-showcase">
            <?php
            $all_tiers = ['bronze', 'silver', 'gold', 'platinum'];
            foreach ($all_tiers as $tier_key):
                $tier_data = getTierInfo($tier_key);
                $is_current = $tier_key === $current_tier;
            ?>
            <div class="tier-mini-card tier-<?php echo $tier_key; ?>-bg <?php echo $is_current ? 'active' : ''; ?>">
                <?php if ($is_current): ?>
                    <div class="active-badge">⭐ Your Tier</div>
                <?php endif; ?>

                <div class="tier-mini-icon"><?php echo $tier_data['icon']; ?></div>
                <div class="tier-mini-name"><?php echo $tier_data['name']; ?></div>
                <div class="tier-mini-req">
                    <?php
                    if ($tier_data['min'] == 0) {
                        echo 'Starting Tier';
                    } else {
                        echo 'Rp ' . number_format($tier_data['min'], 0, ',', '.');
                    }
                    ?>
                </div>
                <?php if ($tier_data['discount'] > 0): ?>
                    <div class="tier-mini-discount"><?php echo $tier_data['discount']; ?>%</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
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
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
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
    </main>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
