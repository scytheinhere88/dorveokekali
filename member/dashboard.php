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

$page_title = 'Akun Saya - Dashboard Member Dorve House | Kelola Pesanan & Profil';
$page_description = 'Dashboard akun member Dorve. Lihat riwayat pesanan, status pengiriman, wallet, dan kelola profil Anda. Belanja baju wanita online jadi lebih mudah.';
include __DIR__ . '/../includes/header.php';
?>

<style>
    .member-content h1 {
        font-family: 'Playfair Display', serif;
        font-size: 42px;
        margin-bottom: 40px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 24px;
        margin-bottom: 60px;
    }

    .stat-card {
        padding: 30px;
        background: var(--cream);
        border-radius: 8px;
        text-align: center;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 600;
        color: var(--charcoal);
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 14px;
        color: var(--grey);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        margin-bottom: 30px;
    }

    .order-list {
        border-top: 1px solid rgba(0,0,0,0.08);
    }

    .order-item {
        padding: 24px 0;
        border-bottom: 1px solid rgba(0,0,0,0.08);
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 24px;
    }

    .order-number {
        font-weight: 600;
        margin-bottom: 8px;
    }

    .order-date {
        font-size: 13px;
        color: var(--grey);
        margin-bottom: 8px;
    }

    .order-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-pending {
        background: #FFF3CD;
        color: #856404;
    }

    .status-paid {
        background: #D4EDDA;
        color: #155724;
    }

    .status-shipped {
        background: #D1ECF1;
        color: #0C5460;
    }

    .order-total {
        text-align: right;
    }

    .order-price {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .view-order-btn {
        padding: 8px 20px;
        background: var(--charcoal);
        color: var(--white);
        text-decoration: none;
        font-size: 12px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 968px) {
        .order-item {
            grid-template-columns: 1fr;
        }
    }

    /* Mobile Responsive Fixes */
    @media (max-width: 768px) {
        .member-content h1 {
            font-size: 28px;
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 22px;
            margin-bottom: 20px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .stat-value {
            font-size: 28px;
        }

        .stat-label {
            font-size: 12px;
        }

        .stat-card {
            padding: 20px;
        }

        .order-item {
            padding: 16px 0;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .order-total {
            text-align: left;
            margin-top: 0;
        }

        .order-price {
            font-size: 18px;
        }

        .order-number {
            font-size: 16px;
        }
    }

    @media (max-width: 480px) {
        .member-content h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .stat-card {
            padding: 16px;
        }
    }
</style>

<div class="member-layout">
    <?php include __DIR__ . '/../includes/member-sidebar.php'; ?>

    <div class="member-content">
        <h1>My Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $order_count; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-value">
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND payment_status = 'paid'");
                    $stmt->execute([$_SESSION['user_id']]);
                    echo $stmt->fetch()['count'];
                    ?>
                </div>
                <div class="stat-label">Completed</div>
            </div>

            <div class="stat-card">
                <div class="stat-value">
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reviews WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    echo $stmt->fetch()['count'];
                    ?>
                </div>
                <div class="stat-label">Reviews Written</div>
            </div>
        </div>

        <!-- Tier Status Section -->
        <?php
        $current_tier = $user['current_tier'] ?? 'bronze';
        $total_topup = floatval($user['total_topup'] ?? 0);

        // Tier requirements
        $tiers = [
            'bronze' => ['name' => 'Bronze', 'min' => 0, 'max' => 999999, 'next' => 'silver', 'next_min' => 1000000, 'discount' => '0%', 'benefits' => ['Akses ke semua produk', 'Customer support standard']],
            'silver' => ['name' => 'Silver', 'min' => 1000000, 'max' => 4999999, 'next' => 'gold', 'next_min' => 5000000, 'discount' => '5%', 'benefits' => ['Diskon 5% setiap pembelian', 'Priority customer support', 'Akses flash sale eksklusif']],
            'gold' => ['name' => 'Gold', 'min' => 5000000, 'max' => 9999999, 'next' => 'platinum', 'next_min' => 10000000, 'discount' => '10%', 'benefits' => ['Diskon 10% setiap pembelian', 'Free shipping semua pesanan', 'Priority customer support', 'Akses early bird sale']],
            'platinum' => ['name' => 'Platinum', 'min' => 10000000, 'max' => PHP_INT_MAX, 'next' => null, 'next_min' => 0, 'discount' => '15%', 'benefits' => ['Diskon 15% setiap pembelian', 'Free shipping & packing premium', 'Dedicated account manager', 'Akses koleksi eksklusif', 'Birthday special gift']]
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
        ?>

        <div style="background: linear-gradient(135deg, #1A1A1A 0%, #3A3A3A 100%); padding: 40px; border-radius: 12px; margin: 48px 0; color: white;" class="tier-status-section">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; align-items: center;" class="tier-status-grid">
                <div>
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                        <div style="font-size: 48px;">
                            <?php
                            $tier_icons = ['bronze' => '🥉', 'silver' => '🥈', 'gold' => '🥇', 'platinum' => '💎'];
                            echo $tier_icons[$current_tier];
                            ?>
                        </div>
                        <div>
                            <h2 style="font-family: 'Playfair Display', serif; font-size: 36px; margin-bottom: 4px; text-transform: capitalize;"><?php echo $tier_info['name']; ?> Member</h2>
                            <p style="opacity: 0.8; font-size: 14px;">Total Topup: Rp <?php echo number_format($total_topup, 0, ',', '.'); ?></p>
                        </div>
                    </div>

                    <?php if ($next_tier): ?>
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                                <span>Progress ke <?php echo $tiers[$next_tier]['name']; ?></span>
                                <span><?php echo number_format($progress_percent, 1); ?>%</span>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); height: 12px; border-radius: 20px; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #D4C5B9 0%, #F5E6D3 100%); height: 100%; width: <?php echo $progress_percent; ?>%; transition: width 0.5s;"></div>
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
                        <a href="/member/wallet.php" style="display: inline-block; padding: 14px 32px; background: white; color: #1A1A1A; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; letter-spacing: 0.5px; text-transform: uppercase;">Topup Sekarang</a>
                    </div>
                </div>

                <div style="background: rgba(255,255,255,0.1); padding: 30px; border-radius: 8px;">
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

        <!-- All Tiers Info -->
        <style>
            .tier-card {
                padding: 32px;
                border-radius: 16px;
                position: relative;
                overflow: hidden;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                cursor: pointer;
            }
            
            .tier-card::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
                opacity: 0;
                transition: opacity 0.4s ease;
            }
            
            .tier-card:hover::before {
                opacity: 1;
                animation: shimmer 2s infinite;
            }
            
            .tier-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            }
            
            @keyframes shimmer {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .tier-bronze {
                background: linear-gradient(135deg, #CD7F32 0%, #E89F60 50%, #CD7F32 100%);
                border: 2px solid rgba(205, 127, 50, 0.5);
                box-shadow: 0 8px 24px rgba(205, 127, 50, 0.3);
            }
            
            .tier-silver {
                background: linear-gradient(135deg, #C0C0C0 0%, #E8E8E8 50%, #A8A8A8 100%);
                border: 2px solid rgba(192, 192, 192, 0.6);
                box-shadow: 0 8px 24px rgba(192, 192, 192, 0.4);
            }
            
            .tier-gold {
                background: linear-gradient(135deg, #FFD700 0%, #FFED4E 50%, #FFAA00 100%);
                border: 2px solid rgba(255, 215, 0, 0.6);
                box-shadow: 0 8px 24px rgba(255, 215, 0, 0.5);
            }
            
            .tier-platinum {
                background: linear-gradient(135deg, #E5E4E2 0%, #FFFFFF 25%, #B0C4DE 50%, #FFFFFF 75%, #E5E4E2 100%);
                border: 2px solid rgba(176, 196, 222, 0.8);
                box-shadow: 0 8px 32px rgba(176, 196, 222, 0.6);
                position: relative;
                overflow: hidden;
            }
            
            .tier-platinum::after {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: linear-gradient(
                    45deg,
                    transparent 30%,
                    rgba(255, 255, 255, 0.5) 50%,
                    transparent 70%
                );
                animation: shine 3s infinite;
            }
            
            @keyframes shine {
                0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
                100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            }
            
            .tier-active {
                border: 3px solid #1A1A1A !important;
                box-shadow: 0 12px 48px rgba(0,0,0,0.3), inset 0 0 0 2px rgba(255,255,255,0.2) !important;
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
                z-index: 2;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            }
            
            .tier-icon-wrapper {
                font-size: 56px;
                margin-bottom: 16px;
                filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
                animation: float 3s ease-in-out infinite;
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
            }
            
            .tier-discount-badge {
                text-align: center;
                padding: 20px;
                background: rgba(255, 255, 255, 0.3);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                margin-bottom: 20px;
                border: 1px solid rgba(255, 255, 255, 0.5);
            }
            
            .tier-benefit-item {
                padding: 10px 0;
                padding-left: 28px;
                position: relative;
                font-size: 13px;
                line-height: 1.6;
                color: #1A1A1A;
                font-weight: 500;
            }
            
            .tier-benefit-item::before {
                content: '✓';
                position: absolute;
                left: 0;
                top: 10px;
                width: 20px;
                height: 20px;
                background: rgba(255, 255, 255, 0.4);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 12px;
            }
            
            @media (max-width: 1200px) {
                .tier-cards-grid { grid-template-columns: repeat(2, 1fr) !important; }
            }

            @media (max-width: 768px) {
                .tier-cards-grid {
                    grid-template-columns: 1fr !important;
                    gap: 20px !important;
                }

                .tier-icon-wrapper {
                    font-size: 40px !important;
                }
            }
        </style>

        <style>
            /* Tier Status Section Responsive */
            @media (max-width: 768px) {
                .tier-status-section {
                    padding: 24px !important;
                    margin: 32px 0 !important;
                }

                .tier-status-grid {
                    grid-template-columns: 1fr !important;
                    gap: 24px !important;
                }

                .tier-status-section h2 {
                    font-size: 28px !important;
                }

                .tier-status-section p {
                    font-size: 13px !important;
                }
            }

            @media (max-width: 480px) {
                .tier-status-section {
                    padding: 20px !important;
                }

                .tier-status-section h2 {
                    font-size: 24px !important;
                }
            }

            /* Empty State Responsive */
            .empty-state-container {
                text-align: center;
                padding: 60px 20px;
                background: var(--cream);
                border-radius: 8px;
            }

            @media (max-width: 768px) {
                .empty-state-container {
                    padding: 40px 20px;
                }

                .tier-cards-section {
                    padding: 24px !important;
                }

                .tier-cards-section h2 {
                    font-size: 28px !important;
                }

                .tier-cards-section p {
                    font-size: 14px !important;
                }
            }

            @media (max-width: 480px) {
                .empty-state-container {
                    padding: 30px 16px;
                }

                .tier-cards-section {
                    padding: 20px !important;
                }

                .tier-cards-section h2 {
                    font-size: 24px !important;
                }
            }
        </style>

        <div style="background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%); padding: 48px; border-radius: 20px; margin-bottom: 48px; box-shadow: 0 4px 16px rgba(0,0,0,0.06);" class="tier-cards-section">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 36px; text-align: center; margin-bottom: 16px; color: #1A1A1A;">💎 Membership Tiers</h2>
            <p style="text-align: center; color: #6B6B6B; margin-bottom: 48px; font-size: 16px;">Semakin banyak topup, semakin besar benefit yang Anda dapatkan</p>

            <div class="tier-cards-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px;">
                <?php foreach ($tiers as $tier_key => $tier_data): ?>
                    <div class="tier-card tier-<?php echo $tier_key; ?> <?php echo $tier_key === $current_tier ? 'tier-active' : ''; ?>">
                        <?php if ($tier_key === $current_tier): ?>
                            <div class="tier-badge">⭐ Your Tier</div>
                        <?php endif; ?>

                        <div style="text-align: center; margin-bottom: 24px; position: relative; z-index: 1;">
                            <div class="tier-icon-wrapper">
                                <?php echo $tier_icons[$tier_key]; ?>
                            </div>
                            <h3 style="font-size: 26px; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; color: #1A1A1A; letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <?php echo $tier_data['name']; ?>
                            </h3>
                            <p style="font-size: 13px; color: #2D2D2D; font-weight: 600; background: rgba(255,255,255,0.3); padding: 4px 12px; border-radius: 20px; display: inline-block;">
                                <?php if ($tier_key === 'platinum'): ?>
                                    ≥ Rp 10,000,000
                                <?php else: ?>
                                    Rp <?php echo number_format($tier_data['min'], 0, ',', '.'); ?> - <?php echo number_format($tier_data['max'], 0, ',', '.'); ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if ($tier_data['discount'] != '0%'): ?>
                            <div class="tier-discount-badge">
                                <div style="font-size: 32px; font-weight: 800; color: #1A1A1A; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <?php echo $tier_data['discount']; ?>
                                </div>
                                <div style="font-size: 11px; color: #1A1A1A; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;">
                                    Auto Discount
                                </div>
                            </div>
                        <?php endif; ?>

                        <ul style="list-style: none; padding: 0; margin: 0; position: relative; z-index: 1;">
                            <?php foreach ($tier_data['benefits'] as $benefit): ?>
                                <li class="tier-benefit-item">
                                    <?php echo htmlspecialchars($benefit); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <h2 class="section-title">Recent Orders</h2>

        <?php if (empty($recent_orders)): ?>
            <div class="empty-state-container">
                <p style="color: var(--grey); margin-bottom: 24px;">You haven't placed any orders yet.</p>
                <a href="/pages/all-products.php" style="display: inline-block; padding: 14px 32px; background: var(--charcoal); color: var(--white); text-decoration: none; border-radius: 4px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="order-list">
                <?php foreach ($recent_orders as $order): ?>
                    <div class="order-item">
                        <div>
                            <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                            <div class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                            <span class="order-status status-<?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                        <div class="order-total">
                            <div class="order-price"><?php echo formatPrice($order['total_amount']); ?></div>
                            <a href="/member/order-detail.php?id=<?php echo $order['id']; ?>" class="view-order-btn">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 40px;">
                <a href="/member/orders.php" style="color: var(--charcoal); text-decoration: none; font-weight: 500;">View All Orders →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
