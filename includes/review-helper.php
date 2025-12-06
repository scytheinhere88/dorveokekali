<?php
/**
 * Review System Helper Functions
 */

/**
 * Calculate & Update Product Rating
 */
function updateProductRating($product_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as total
        FROM product_reviews
        WHERE product_id = ? AND status = 'published'
    ");
    $stmt->execute([$product_id]);
    $result = $stmt->fetch();
    
    $avgRating = $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
    $totalReviews = $result['total'] ?? 0;
    
    $stmt = $pdo->prepare("
        UPDATE products 
        SET average_rating = ?, total_reviews = ?
        WHERE id = ?
    ");
    $stmt->execute([$avgRating, $totalReviews, $product_id]);
    
    return ['average_rating' => $avgRating, 'total_reviews' => $totalReviews];
}

/**
 * Create Thank You Voucher for Review (Rating >= 4)
 */
function createReviewRewardVoucher($user_id, $username) {
    global $pdo;
    
    // Generate unique voucher code
    $code = 'TERIMA-' . strtoupper(substr(uniqid(), -6));
    
    $stmt = $pdo->prepare("
        INSERT INTO vouchers (
            code, type, value, max_value, min_purchase,
            start_date, end_date, is_active, terms, created_at
        ) VALUES (
            ?, 'percentage', 10, 20000, 250000,
            NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), 1,
            'Voucher reward untuk review produk. Diskon 10% (max Rp 20.000) dengan min. belanja Rp 250.000. Berlaku 14 hari.',
            NOW()
        )
    ");
    $stmt->execute([$code]);
    $voucher_id = $pdo->lastInsertId();
    
    // Assign voucher to user
    $stmt = $pdo->prepare("
        INSERT INTO user_vouchers (user_id, voucher_id, is_used, created_at)
        VALUES (?, ?, 0, NOW())
    ");
    $stmt->execute([$user_id, $voucher_id]);
    
    return [
        'code' => $code,
        'voucher_id' => $voucher_id,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+14 days'))
    ];
}

/**
 * Check if user can review an order
 */
function canUserReviewOrder($order_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT can_review, completed_at
        FROM orders
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    return $order && $order['can_review'] == 1 && $order['completed_at'];
}

/**
 * Check if order item already reviewed
 */
function isOrderItemReviewed($order_id, $product_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id FROM product_reviews
        WHERE order_id = ? AND product_id = ?
    ");
    $stmt->execute([$order_id, $product_id]);
    return $stmt->fetch() ? true : false;
}

/**
 * Get reviews for a product
 */
function getProductReviews($product_id, $limit = 10, $offset = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as user_name
        FROM product_reviews r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ? AND r.status = 'published'
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$product_id, $limit, $offset]);
    $reviews = $stmt->fetchAll();
    
    // Get media for each review
    foreach ($reviews as &$review) {
        $stmt = $pdo->prepare("
            SELECT * FROM review_media
            WHERE review_id = ?
            ORDER BY media_type, created_at
        ");
        $stmt->execute([$review['id']]);
        $review['media'] = $stmt->fetchAll();
    }
    
    return $reviews;
}

/**
 * Upload review media (photos/videos)
 */
function uploadReviewMedia($file, $type = 'image') {
    $maxSize = 16 * 1024 * 1024; // 16MB
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File terlalu besar. Max 16MB.');
    }
    
    $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/quicktime'];
    
    if ($type === 'image' && !in_array($file['type'], $allowedImageTypes)) {
        throw new Exception('Format gambar tidak valid. Gunakan JPG, PNG, atau WebP.');
    }
    
    if ($type === 'video' && !in_array($file['type'], $allowedVideoTypes)) {
        throw new Exception('Format video tidak valid. Gunakan MP4 atau WebM.');
    }
    
    $uploadDir = __DIR__ . '/../uploads/reviews/' . ($type === 'image' ? 'photos' : 'videos');
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Gagal upload file.');
    }
    
    return [
        'filename' => $filename,
        'filepath' => '/uploads/reviews/' . ($type === 'image' ? 'photos' : 'videos') . '/' . $filename,
        'filesize' => $file['size']
    ];
}

/**
 * Get review statistics for product
 */
function getReviewStats($product_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            AVG(rating) as average,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM product_reviews
        WHERE product_id = ? AND status = 'published'
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}
