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
        .stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
    }

    @media (max-width: 640px) {
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
    </main>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
