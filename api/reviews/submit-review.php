<?php
/**
 * API: Submit Product Review
 * POST request to submit review with photos & video
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/review-helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $order_id = (int)($_POST['order_id'] ?? 0);
    $product_id = (int)($_POST['product_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');
    $reviewer_name = trim($_POST['reviewer_name'] ?? '');
    
    // Validation
    if (!$order_id || !$product_id) {
        throw new Exception('Order ID dan Product ID wajib diisi.');
    }
    
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Rating harus antara 1-5 bintang.');
    }
    
    if (empty($review_text) || strlen($review_text) > 3000) {
        throw new Exception('Review text wajib diisi (max 1000 kata).');
    }
    
    if (empty($reviewer_name)) {
        throw new Exception('Nama reviewer wajib diisi.');
    }
    
    // Check if user can review this order
    if (!canUserReviewOrder($order_id, $user_id)) {
        throw new Exception('Anda belum bisa review order ini. Pastikan pesanan sudah diterima.');
    }
    
    // Check if already reviewed
    if (isOrderItemReviewed($order_id, $product_id)) {
        throw new Exception('Anda sudah memberikan review untuk produk ini.');
    }
    
    // Get order_item_id
    $stmt = $pdo->prepare("
        SELECT id FROM order_items
        WHERE order_id = ? AND product_id = ?
        LIMIT 1
    ");
    $stmt->execute([$order_id, $product_id]);
    $orderItem = $stmt->fetch();
    
    if (!$orderItem) {
        throw new Exception('Order item tidak ditemukan.');
    }
    
    // Insert review
    $stmt = $pdo->prepare("
        INSERT INTO product_reviews (
            order_id, order_item_id, product_id, user_id,
            rating, review_text, reviewer_name,
            is_verified_purchase, created_by_admin, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 'published')
    ");
    $stmt->execute([
        $order_id, $orderItem['id'], $product_id, $user_id,
        $rating, $review_text, $reviewer_name
    ]);
    
    $review_id = $pdo->lastInsertId();
    
    // Upload photos (max 3)
    $uploadedPhotos = [];
    if (!empty($_FILES['photos'])) {
        $photoCount = min(count($_FILES['photos']['name']), 3);
        
        for ($i = 0; $i < $photoCount; $i++) {
            if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['photos']['name'][$i],
                    'type' => $_FILES['photos']['type'][$i],
                    'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                    'size' => $_FILES['photos']['size'][$i]
                ];
                
                try {
                    $uploaded = uploadReviewMedia($file, 'image');
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO review_media (review_id, media_type, file_path, file_size)
                        VALUES (?, 'image', ?, ?)
                    ");
                    $stmt->execute([$review_id, $uploaded['filepath'], $uploaded['filesize']]);
                    
                    $uploadedPhotos[] = $uploaded['filepath'];
                } catch (Exception $e) {
                    // Log error but continue
                    error_log('Photo upload error: ' . $e->getMessage());
                }
            }
        }
    }
    
    // Upload video (max 1)
    $uploadedVideo = null;
    if (!empty($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploaded = uploadReviewMedia($_FILES['video'], 'video');
            
            $stmt = $pdo->prepare("
                INSERT INTO review_media (review_id, media_type, file_path, file_size)
                VALUES (?, 'video', ?, ?)
            ");
            $stmt->execute([$review_id, $uploaded['filepath'], $uploaded['filesize']]);
            
            $uploadedVideo = $uploaded['filepath'];
        } catch (Exception $e) {
            error_log('Video upload error: ' . $e->getMessage());
        }
    }
    
    // Update product rating
    updateProductRating($product_id);
    
    // Create reward voucher if rating >= 4
    $voucher = null;
    if ($rating >= 4) {
        $user = getCurrentUser();
        $voucher = createReviewRewardVoucher($user_id, $user['name']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Review berhasil dikirim!',
        'review_id' => $review_id,
        'rating' => $rating,
        'photos_uploaded' => count($uploadedPhotos),
        'video_uploaded' => $uploadedVideo ? true : false,
        'voucher' => $voucher
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
