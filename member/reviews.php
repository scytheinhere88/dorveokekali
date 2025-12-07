<?php
require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user = getCurrentUser();

$stmt = $pdo->prepare("
    SELECT r.*, p.name as product_name, p.slug as product_slug
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reviews = $stmt->fetchAll();

$page_title = 'My Reviews - Dorve';
include __DIR__ . '/../includes/header.php';
?>

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    .prof-wrapper {
        display: flex;
        max-width: 1400px;
        margin: 100px auto 60px;
        padding: 0 40px;
        gap: 48px;
        align-items: flex-start;
    }

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

    .member-content h1 { font-family: 'Playfair Display', serif; font-size: 36px; margin-bottom: 40px; }
    .review-card { background: var(--white); border: 1px solid rgba(0,0,0,0.08); border-radius: 8px; padding: 30px; margin-bottom: 24px; }
    .review-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; }
    .review-product { font-weight: 600; font-size: 18px; margin-bottom: 8px; }
    .review-date { font-size: 14px; color: var(--grey); }
    .star-rating { color: #FFB800; font-size: 18px; margin-bottom: 16px; }
    .review-text { line-height: 1.8; color: var(--grey); }
    .review-status { padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .status-approved { background: #D4EDDA; color: #155724; }
    .status-pending { background: #FFF3CD; color: #856404; }
    .empty-state { text-align: center; padding: 80px 40px; }
    .empty-state h3 { font-family: 'Playfair Display', serif; font-size: 28px; margin-bottom: 16px; }

    /* ===== MOBILE RESPONSIVE ===== */

    /* Tablet (768px and below) */
    @media (max-width: 768px) {
        .member-content h1 {
            font-size: 28px;
            margin-bottom: 24px;
        }

        .form-card {
            padding: 20px;
            max-width: 100%;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 14px;
            font-size: 16px; /* Prevent iOS zoom */
            border-radius: 8px;
        }

        .form-group label {
            font-size: 13px;
            margin-bottom: 6px;
        }

        .btn {
            width: 100%;
            padding: 14px 20px;
            font-size: 14px;
            min-height: 44px;
        }

        .alert {
            font-size: 13px;
            padding: 12px;
        }

        .review-card {
            padding: 20px;
            margin-bottom: 16px;
        }

        .review-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .product-name {
            font-size: 14px;
        }

        .review-status {
            font-size: 11px;
            padding: 4px 10px;
        }

        .star-rating {
            font-size: 16px;
        }

        .review-text {
            font-size: 13px;
            line-height: 1.6;
        }

        .review-date {
            font-size: 12px;
        }

        .empty-state {
            padding: 40px 20px;
        }
    }

    /* Mobile Phone (480px and below) */
    @media (max-width: 480px) {
        .member-content h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-card {
            padding: 16px;
        }

        .form-group input,
        .form-group textarea {
            padding: 10px 12px;
        }

        .btn {
            padding: 12px 16px;
            font-size: 13px;
        }

        .review-card {
            padding: 16px;
        }

        .empty-state {
            padding: 30px 16px;
        }
    }
</style>

<div class="prof-wrapper">
    <aside class="prof-sidebar">
        <div class="prof-sidebar-header">
            <h3>Welcome back!</h3>
            <p><?php echo htmlspecialchars($user['name'] ?? $user['email']); ?></p>
        </div>

        <ul class="prof-nav">
            <li><a href="/member/dashboard.php">🏠 Dashboard</a></li>
            <li><a href="/member/orders.php">📦 My Orders</a></li>
            <li><a href="/member/wallet.php">💰 My Wallet</a></li>
            <li><a href="/member/address-book.php">📍 Address Book</a></li>
            <li><a href="/member/referral.php">👥 My Referrals</a></li>
            <li><a href="/member/vouchers/index.php">🎟️ My Vouchers</a></li>
            <li><a href="/member/reviews.php" class="active">⭐ My Reviews</a></li>
            <li><a href="/member/profile.php">👤 Edit Profile</a></li>
            <li><a href="/member/password.php">🔐 Change Password</a></li>
            <li class="logout"><a href="/auth/logout.php">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="prof-content">
        <h1>My Reviews</h1>

        <?php if (empty($reviews)): ?>
            <div class="empty-state">
                <h3>No Reviews Yet</h3>
                <p>You haven't written any reviews. Purchase products to leave reviews.</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div>
                            <div class="review-product">
                                <a href="/pages/product-detail.php?slug=<?php echo $review['product_slug']; ?>" style="color: inherit; text-decoration: none;">
                                    <?php echo htmlspecialchars($review['product_name']); ?>
                                </a>
                            </div>
                            <div class="review-date"><?php echo date('F d, Y', strtotime($review['created_at'])); ?></div>
                        </div>
                        <span class="review-status status-<?php echo $review['is_approved'] ? 'approved' : 'pending'; ?>">
                            <?php echo $review['is_approved'] ? 'Published' : 'Pending Approval'; ?>
                        </span>
                    </div>

                    <div class="star-rating">
                        <?php for ($i = 0; $i < $review['rating']; $i++): ?>⭐<?php endfor; ?>
                        <?php for ($i = $review['rating']; $i < 5; $i++): ?>☆<?php endfor; ?>
                    </div>

                    <div class="review-text">
                        <?php echo htmlspecialchars($review['comment']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
