<?php
require_once __DIR__ . '/../config.php';

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

// Tier calculations
$current_tier = $user['current_tier'] ?? 'bronze';
$total_topup = floatval($user['total_topup'] ?? 0);

$tiers = [
    'bronze' => ['name' => 'Bronze', 'icon' => '🥉', 'min' => 0, 'max' => 999999, 'next' => 'silver', 'next_min' => 1000000, 'discount' => '0%', 'commission' => '3%', 'benefits' => ['Akses ke semua produk', 'Customer support standard', 'Komisi referral 3%']],
    'silver' => ['name' => 'Silver', 'icon' => '🥈', 'min' => 1000000, 'max' => 4999999, 'next' => 'gold', 'next_min' => 5000000, 'discount' => '5%', 'commission' => '5%', 'benefits' => ['Diskon 5% setiap pembelian', 'Priority customer support', 'Akses flash sale eksklusif', 'Komisi referral 5%']],
    'gold' => ['name' => 'Gold', 'icon' => '🥇', 'min' => 5000000, 'max' => 9999999, 'next' => 'platinum', 'next_min' => 10000000, 'discount' => '10%', 'commission' => '8%', 'benefits' => ['Diskon 10% setiap pembelian', 'Free shipping semua pesanan', 'Priority customer support', 'Akses early bird sale', 'Komisi referral 8%']],
    'platinum' => ['name' => 'Platinum', 'icon' => '💎', 'min' => 10000000, 'max' => PHP_INT_MAX, 'next' => null, 'next_min' => 0, 'discount' => '15%', 'commission' => '10%', 'benefits' => ['Diskon 15% setiap pembelian', 'Free shipping & packing premium', 'Dedicated account manager', 'Akses koleksi eksklusif', 'Birthday special gift', 'Komisi referral 10%']]
];

$tier_info = $tiers[$current_tier];
$next_tier = $tier_info['next'];
$progress_percent = 0;

if ($next_tier) {
    $current_min = $tier_info['min'];
    $next_min = $tier_info['next_min'];
    $progress = $total_topup - $current_min;
    $range = $next_min - $current_min;
    $progress_percent = min(100, ($progress / $range) * 100);
} else {
    $progress_percent = 100;
}

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

    /* Tier Section */
    .tier-section {
        background: linear-gradient(135deg, #1A1A1A 0%, #3A3A3A 100%);
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 48px;
        color: white;
    }

    .tier-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 40px;
        align-items: center;
    }

    .tier-badge-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .tier-icon-large {
        font-size: 48px;
    }

    .tier-title {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        margin-bottom: 4px;
        text-transform: capitalize;
    }

    .tier-progress {
        margin-bottom: 16px;
    }

    .progress-bar {
        background: rgba(255,255,255,0.2);
        height: 12px;
        border-radius: 20px;
        overflow: hidden;
        margin: 8px 0;
    }

    .progress-fill {
        background: linear-gradient(90deg, #D4C5B9 0%, #F5E6D3 100%);
        height: 100%;
        transition: width 0.5s;
    }

    .tier-benefits-box {
        background: rgba(255,255,255,0.1);
        padding: 30px;
        border-radius: 12px;
    }

    /* All Tiers Cards */
    .all-tiers-section {
        background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
        padding: 48px;
        border-radius: 20px;
        margin-bottom: 48px;
    }

    .tier-cards-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .tier-card {
        padding: 32px 24px;
        border-radius: 16px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
    }

    .tier-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }

    .tier-bronze {
        background: linear-gradient(135deg, #CD7F32 0%, #E89F60 100%);
        border: 2px solid rgba(205, 127, 50, 0.5);
    }

    .tier-silver {
        background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 100%);
        border: 2px solid rgba(192, 192, 192, 0.6);
    }

    .tier-gold {
        background: linear-gradient(135deg, #FFD700 0%, #FFED4E 100%);
        border: 2px solid rgba(255, 215, 0, 0.6);
    }

    .tier-platinum {
        background: linear-gradient(135deg, #E5E4E2 0%, #FFFFFF 50%, #E5E4E2 100%);
        border: 2px solid rgba(176, 196, 222, 0.8);
    }

    .tier-active {
        border: 3px solid #1A1A1A !important;
        box-shadow: 0 12px 48px rgba(0,0,0,0.3);
    }

    .tier-badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        color: white;
        padding: 6px 20px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
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
        .tier-section {
            padding: 24px;
        }

        .tier-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .tier-title {
            font-size: 28px;
        }

        .all-tiers-section {
            padding: 24px;
        }

        .tier-cards-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
    }

    @media (max-width: 640px) {
        .tier-cards-grid {
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

        <!-- TIER STATUS -->
        <div class="tier-section">
            <div class="tier-grid">
                <div>
                    <div class="tier-badge-header">
                        <span class="tier-icon-large"><?php echo $tier_info['icon']; ?></span>
                        <div>
                            <h2 class="tier-title"><?php echo $tier_info['name']; ?> Member</h2>
                            <p style="opacity: 0.8; font-size: 14px;">Total Topup: Rp <?php echo number_format($total_topup, 0, ',', '.'); ?></p>
                        </div>
                    </div>

                    <?php if ($next_tier): ?>
                        <div class="tier-progress">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                                <span>Progress to <?php echo $tiers[$next_tier]['name']; ?></span>
                                <span><?php echo number_format($progress_percent, 1); ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%"></div>
                            </div>
                            <p style="font-size: 13px; opacity: 0.8; margin-top: 8px;">
                                Topup Rp <?php echo number_format($tier_info['next_min'] - $total_topup, 0, ',', '.'); ?> lagi untuk naik ke <?php echo $tiers[$next_tier]['name']; ?>!
                            </p>
                        </div>
                    <?php else: ?>
                        <div style="background: rgba(212, 197, 185, 0.2); padding: 16px; border-radius: 8px; border-left: 4px solid #D4C5B9;">
                            <p style="font-size: 14px; margin: 0;">🎉 Selamat! Anda sudah mencapai tier tertinggi!</p>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 24px;">
                        <a href="/member/wallet.php" style="display: inline-block; padding: 14px 32px; background: white; color: #1A1A1A; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                            Topup Sekarang
                        </a>
                    </div>
                </div>

                <div class="tier-benefits-box">
                    <h3 style="font-size: 18px; margin-bottom: 20px; font-weight: 600;">Benefit Tier Anda</h3>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($tier_info['benefits'] as $benefit): ?>
                            <li style="padding: 10px 0; padding-left: 28px; position: relative; font-size: 14px; line-height: 1.5;">
                                <span style="position: absolute; left: 0; top: 10px;">✓</span>
                                <?php echo htmlspecialchars($benefit); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($tier_info['discount'] != '0%'): ?>
                        <div style="margin-top: 20px; padding: 16px; background: rgba(212, 197, 185, 0.3); border-radius: 6px; text-align: center;">
                            <div style="font-size: 32px; font-weight: 700; font-family: 'Playfair Display', serif;"><?php echo $tier_info['discount']; ?></div>
                            <div style="font-size: 12px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px;">Auto Discount</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ALL TIERS -->
        <div class="all-tiers-section">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 36px; text-align: center; margin-bottom: 16px; color: #1A1A1A;">💎 Membership Tiers</h2>
            <p style="text-align: center; color: #6B6B6B; margin-bottom: 32px; font-size: 16px;">Semakin banyak topup, semakin besar benefit yang Anda dapatkan</p>

            <div class="tier-cards-grid">
                <?php foreach ($tiers as $tier_key => $tier_data): ?>
                    <div class="tier-card tier-<?php echo $tier_key; ?> <?php echo $tier_key === $current_tier ? 'tier-active' : ''; ?>">
                        <?php if ($tier_key === $current_tier): ?>
                            <div class="tier-badge">⭐ Your Tier</div>
                        <?php endif; ?>

                        <div style="font-size: 56px; margin-bottom: 16px;"><?php echo $tier_data['icon']; ?></div>
                        <h3 style="font-size: 24px; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; color: #1A1A1A;">
                            <?php echo $tier_data['name']; ?>
                        </h3>
                        <p style="font-size: 13px; color: #2D2D2D; font-weight: 600; margin-bottom: 16px;">
                            <?php if ($tier_key === 'platinum'): ?>
                                ≥ Rp 10,000,000
                            <?php else: ?>
                                Rp <?php echo number_format($tier_data['min'], 0, ',', '.'); ?>+
                            <?php endif; ?>
                        </p>

                        <?php if ($tier_data['discount'] != '0%'): ?>
                            <div style="background: rgba(255,255,255,0.3); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                                <div style="font-size: 28px; font-weight: 800; color: #1A1A1A;"><?php echo $tier_data['discount']; ?></div>
                                <div style="font-size: 11px; color: #1A1A1A; font-weight: 700; text-transform: uppercase;">Discount</div>
                            </div>
                        <?php endif; ?>

                        <ul style="list-style: none; padding: 0; margin: 0; text-align: left;">
                            <?php foreach ($tier_data['benefits'] as $idx => $benefit): ?>
                                <?php if ($idx < 3): ?>
                                    <li style="padding: 6px 0; padding-left: 24px; position: relative; font-size: 12px; color: #1A1A1A; font-weight: 500;">
                                        <span style="position: absolute; left: 0;">✓</span>
                                        <?php echo htmlspecialchars($benefit); ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
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
