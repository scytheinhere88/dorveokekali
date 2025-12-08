<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/review-helper.php';

if (!isAdmin()) {
    redirect('/admin/login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $review_text = trim($_POST['review_text'] ?? '');
        $reviewer_name = trim($_POST['reviewer_name'] ?? 'Admin');
        
        if (!$product_id) {
            throw new Exception('Pilih produk terlebih dahulu.');
        }
        
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Rating harus antara 1-5 bintang.');
        }
        
        if (empty($review_text)) {
            throw new Exception('Review text wajib diisi.');
        }
        
        // Insert review
        $stmt = $pdo->prepare("
            INSERT INTO product_reviews (
                product_id, rating, review_text, reviewer_name,
                is_verified_purchase, created_by_admin, status
            ) VALUES (?, ?, ?, ?, 0, 1, 'published')
        ");
        $stmt->execute([$product_id, $rating, $review_text, $reviewer_name]);
        $review_id = $pdo->lastInsertId();
        
        // Upload photos
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
                        $stmt = $pdo->prepare("INSERT INTO review_media (review_id, media_type, file_path, file_size) VALUES (?, 'image', ?, ?)");
                        $stmt->execute([$review_id, $uploaded['filepath'], $uploaded['filesize']]);
                    } catch (Exception $e) {
                        // Continue on error
                    }
                }
            }
        }
        
        // Update product rating
        updateProductRating($product_id);
        
        $_SESSION['success'] = 'Review berhasil dibuat!';
        redirect('/admin/reviews/');
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$stmt = $pdo->query("SELECT id, name, image FROM products ORDER BY name");
$products = $stmt->fetchAll();

$page_title = 'Create Fake Review - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
        h1 { font-size: 28px; margin-bottom: 32px; }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            font-size: 15px;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .star-rating {
            display: flex;
            gap: 12px;
            font-size: 32px;
            cursor: pointer;
        }
        
        .star {
            color: #D1D5DB;
            transition: color 0.2s;
        }
        
        .star.active { color: #FBBF24; }
        
        .btn {
            padding: 14px 28px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #6B7280;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
        }
        
        .product-preview {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: #F9FAFB;
            border-radius: 8px;
            margin-top: 12px;
        }
        
        .product-preview img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>

<main class="admin-main">
    <div class="page-header">
        <div>
            <h1>✍️ Create Fake Review</h1>
            <p>Create a promotional review as a guest user</p>
        </div>
        <a href="/admin/reviews/index.php" class="btn btn-secondary">← Back to Reviews</a>
    </div>

    <div class="card">
        <h2 style="margin-bottom: 24px;">Review Details</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Pilih Produk *</label>
                    <select name="product_id" required onchange="showProductPreview(this)">
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" data-image="/uploads/products/<?= htmlspecialchars($product['image']) ?>" data-name="<?= htmlspecialchars($product['name']) ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="productPreview"></div>
                </div>
                
                <div class="form-group">
                    <label>Rating *</label>
                    <input type="hidden" name="rating" id="ratingInput" value="0">
                    <div class="star-rating" id="starRating">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Nama Reviewer</label>
                    <input type="text" name="reviewer_name" value="Admin" placeholder="Nama yang akan ditampilkan">
                </div>
                
                <div class="form-group">
                    <label>Review Text *</label>
                    <textarea name="review_text" required placeholder="Tulis review yang profesional dan membantu..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Upload Foto (Max 3)</label>
                    <input type="file" name="photos[]" accept="image/*" multiple>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 32px;">
                    <button type="submit" class="btn btn-primary">✓ Buat Review</button>
                    <a href="/admin/reviews/" class="btn btn-secondary">Batal</a>
                </div>
            </form>
    </div>
</main>

<script>
    let selectedRating = 0;
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('ratingInput');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.dataset.rating);
            ratingInput.value = selectedRating;
            updateStars();
        });
        
        star.addEventListener('mouseenter', function() {
            highlightStars(parseInt(this.dataset.rating));
        });
    });
    
    document.getElementById('starRating').addEventListener('mouseleave', updateStars);
    
    function highlightStars(rating) {
        stars.forEach((star, index) => {
            star.classList.toggle('active', index < rating);
        });
    }
    
    function updateStars() {
        highlightStars(selectedRating);
    }
    
    function showProductPreview(select) {
        const option = select.options[select.selectedIndex];
        const preview = document.getElementById('productPreview');
        
        if (option.value) {
            preview.innerHTML = `
                <div class="product-preview">
                    <img src="${option.dataset.image}" alt="Product">
                    <div>
                        <strong>${option.dataset.name}</strong>
                        <div style="font-size: 13px; color: #6B7280; margin-top: 4px;">Review akan muncul di product detail ini</div>
                    </div>
                </div>
            `;
        } else {
            preview.innerHTML = '';
        }
    }
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
