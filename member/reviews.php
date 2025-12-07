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
</style>

<div class="member-layout">
    <?php include __DIR__ . '/../includes/member-sidebar.php'; ?>

    <div class="member-content">
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
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
