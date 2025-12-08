<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/review-helper.php';

if (!isAdmin()) {
    redirect('/admin/login.php');
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $review_id = (int)$_GET['id'];
    
    // Get product_id before deleting
    $stmt = $pdo->prepare("SELECT product_id FROM product_reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();
    
    if ($review) {
        // Delete review (cascade will delete media)
        $stmt = $pdo->prepare("DELETE FROM product_reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        
        // Update product rating
        updateProductRating($review['product_id']);
        
        $_SESSION['success'] = 'Review berhasil dihapus!';
    }
    
    redirect('/admin/reviews/');
}

// Handle hide/show action
if (isset($_GET['action']) && in_array($_GET['action'], ['hide', 'show']) && isset($_GET['id'])) {
    $review_id = (int)$_GET['id'];
    $status = $_GET['action'] === 'hide' ? 'hidden' : 'published';
    
    $stmt = $pdo->prepare("UPDATE product_reviews SET status = ? WHERE id = ?");
    $stmt->execute([$status, $review_id]);
    
    // Get product_id and update rating
    $stmt = $pdo->prepare("SELECT product_id FROM product_reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();
    if ($review) {
        updateProductRating($review['product_id']);
    }
    
    $_SESSION['success'] = 'Status review berhasil diubah!';
    redirect('/admin/reviews/');
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$product_filter = (int)($_GET['product_id'] ?? 0);
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($filter === 'published') {
    $where[] = "r.status = 'published'";
} elseif ($filter === 'hidden') {
    $where[] = "r.status = 'hidden'";
} elseif ($filter === 'admin') {
    $where[] = "r.created_by_admin = 1";
} elseif ($filter === 'user') {
    $where[] = "r.created_by_admin = 0";
}

if ($product_filter) {
    $where[] = "r.product_id = ?";
    $params[] = $product_filter;
}

if ($search) {
    $where[] = "(r.review_text LIKE ? OR r.reviewer_name LIKE ? OR p.name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get reviews
$stmt = $pdo->prepare("
    SELECT r.*, p.name as product_name, p.image as product_image,
           u.name as user_name
    FROM product_reviews r
    LEFT JOIN products p ON r.product_id = p.id
    LEFT JOIN users u ON r.user_id = u.id
    $whereClause
    ORDER BY r.created_at DESC
    LIMIT 50
");
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Get products for filter
$stmt = $pdo->query("SELECT id, name FROM products ORDER BY name");
$products = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = 'hidden' THEN 1 ELSE 0 END) as hidden,
        SUM(CASE WHEN created_by_admin = 1 THEN 1 ELSE 0 END) as admin_reviews,
        AVG(rating) as avg_rating
    FROM product_reviews
");
$stats = $stmt->fetch();

$page_title = 'Reviews Management - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #E5E7EB;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6B7280;
        }
        
        .filters {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            gap: 8px;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: 1px solid #E5E7EB;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .filter-btn.active {
            background: #667EEA;
            color: white;
            border-color: #667EEA;
        }
        
        select, input[type="text"] {
            padding: 8px 12px;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .review-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
            border: 1px solid #E5E7EB;
        }
        
        .review-header {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .product-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .review-meta h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .rating {
            color: #FBBF24;
            font-size: 18px;
        }
        
        .review-text {
            margin: 16px 0;
            line-height: 1.6;
            color: #374151;
        }
        
        .review-media {
            display: flex;
            gap: 12px;
            margin: 16px 0;
        }
        
        .media-thumb {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .review-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #E5E7EB;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: #D1FAE5; color: #065F46; }
        .badge-warning { background: #FEF3C7; color: #92400E; }
        .badge-info { background: #DBEAFE; color: #1E40AF; }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #6EE7B7;
        }
</style>

<main class="admin-main">
    <div class="page-header">
        <div>
            <h1>⭐ Reviews Management</h1>
            <p>Manage customer reviews and create promotional reviews</p>
        </div>
        <a href="/admin/reviews/create.php" class="btn btn-primary">+ Create New Review</a>
    </div>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Reviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['published'] ?></div>
                <div class="stat-label">Published</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['hidden'] ?></div>
                <div class="stat-label">Hidden</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['admin_reviews'] ?></div>
                <div class="stat-label">Admin Reviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['avg_rating'], 1) ?>★</div>
                <div class="stat-label">Avg Rating</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <a href="?filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">All</a>
                <a href="?filter=published" class="filter-btn <?= $filter === 'published' ? 'active' : '' ?>">Published</a>
                <a href="?filter=hidden" class="filter-btn <?= $filter === 'hidden' ? 'active' : '' ?>">Hidden</a>
                <a href="?filter=admin" class="filter-btn <?= $filter === 'admin' ? 'active' : '' ?>">Admin Reviews</a>
                <a href="?filter=user" class="filter-btn <?= $filter === 'user' ? 'active' : '' ?>">User Reviews</a>
            </div>
            
            <select onchange="window.location.href='?product_id='+this.value">
                <option value="">Filter by Product</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id'] ?>" <?= $product_filter == $product['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($product['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <form method="GET" style="display: flex; gap: 8px;">
                <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
        
        <!-- Reviews List -->
        <?php if (empty($reviews)): ?>
            <div style="text-align: center; padding: 60px; background: white; border-radius: 12px;">
                <div style="font-size: 64px; margin-bottom: 16px;">📝</div>
                <h3>Belum Ada Review</h3>
                <p style="color: #6B7280; margin-top: 8px;">Buat review pertama untuk produk Anda!</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <img src="/uploads/products/<?= htmlspecialchars($review['product_image']) ?>" class="product-thumb" alt="Product">
                        <div class="review-meta" style="flex: 1;">
                            <h3><?= htmlspecialchars($review['product_name']) ?></h3>
                            <div class="rating"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></div>
                            <div style="font-size: 13px; color: #6B7280; margin-top: 4px;">
                                By <strong><?= htmlspecialchars($review['reviewer_name']) ?></strong>
                                <?php if ($review['created_by_admin']): ?>
                                    <span class="badge badge-info">Admin Review</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Verified Purchase</span>
                                <?php endif; ?>
                                <?php if ($review['status'] === 'hidden'): ?>
                                    <span class="badge badge-warning">Hidden</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 12px; color: #9CA3AF; margin-top: 4px;">
                                <?= date('d M Y H:i', strtotime($review['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="review-text">
                        <?= nl2br(htmlspecialchars($review['review_text'])) ?>
                    </div>
                    
                    <?php
                    // Get media
                    $stmt = $pdo->prepare("SELECT * FROM review_media WHERE review_id = ?");
                    $stmt->execute([$review['id']]);
                    $media = $stmt->fetchAll();
                    
                    if ($media):
                    ?>
                        <div class="review-media">
                            <?php foreach ($media as $m): ?>
                                <?php if ($m['media_type'] === 'image'): ?>
                                    <img src="<?= htmlspecialchars($m['file_path']) ?>" class="media-thumb" alt="Review">
                                <?php else: ?>
                                    <video src="<?= htmlspecialchars($m['file_path']) ?>" class="media-thumb" controls></video>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="review-actions">
                        <?php if ($review['status'] === 'published'): ?>
                            <a href="?action=hide&id=<?= $review['id'] ?>" class="btn btn-warning" onclick="return confirm('Hide review ini?')">🙈 Hide</a>
                        <?php else: ?>
                            <a href="?action=show&id=<?= $review['id'] ?>" class="btn btn-success">👁️ Show</a>
                        <?php endif; ?>
                        <a href="?action=delete&id=<?= $review['id'] ?>" class="btn btn-danger" onclick="return confirm('Hapus review ini? Tidak bisa di-undo!')">🗑️ Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
