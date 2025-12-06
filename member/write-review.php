<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/review-helper.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user = getCurrentUser();
$order_id = (int)($_GET['order_id'] ?? 0);
$product_id = (int)($_GET['product_id'] ?? 0);

if (!$order_id || !$product_id) {
    redirect('/member/orders.php');
}

// Check if user can review
if (!canUserReviewOrder($order_id, $_SESSION['user_id'])) {
    $_SESSION['error'] = 'Anda belum bisa review order ini.';
    redirect('/member/orders.php');
}

// Check if already reviewed
if (isOrderItemReviewed($order_id, $product_id)) {
    $_SESSION['error'] = 'Anda sudah memberikan review untuk produk ini.';
    redirect('/member/orders.php');
}

// Get product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/member/orders.php');
}

$page_title = 'Tulis Review - ' . $product['name'];
include __DIR__ . '/../includes/header.php';
?>

<style>
    .review-container {
        max-width: 800px;
        margin: 80px auto;
        padding: 0 20px;
    }
    
    .review-card {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }
    
    .product-info {
        display: flex;
        gap: 20px;
        padding: 24px;
        background: #F9FAFB;
        border-radius: 12px;
        margin-bottom: 32px;
    }
    
    .product-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .product-details h3 {
        font-size: 18px;
        margin-bottom: 8px;
    }
    
    .rating-section {
        text-align: center;
        padding: 32px 0;
        border-bottom: 1px solid #E5E7EB;
        margin-bottom: 32px;
    }
    
    .rating-section h2 {
        font-size: 20px;
        margin-bottom: 16px;
    }
    
    .star-rating {
        display: inline-flex;
        gap: 12px;
        font-size: 48px;
        cursor: pointer;
    }
    
    .star {
        color: #D1D5DB;
        transition: all 0.2s;
    }
    
    .star.active,
    .star:hover {
        color: #FBBF24;
    }
    
    .form-group {
        margin-bottom: 24px;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #374151;
    }
    
    .form-input,
    .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #E5E7EB;
        border-radius: 8px;
        font-size: 15px;
        transition: border-color 0.2s;
    }
    
    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #667EEA;
    }
    
    .form-textarea {
        min-height: 150px;
        resize: vertical;
    }
    
    .char-count {
        text-align: right;
        font-size: 13px;
        color: #6B7280;
        margin-top: 4px;
    }
    
    .upload-section {
        background: #F9FAFB;
        border: 2px dashed #D1D5DB;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        margin-bottom: 24px;
    }
    
    .upload-section h4 {
        margin-bottom: 12px;
    }
    
    .upload-hint {
        font-size: 13px;
        color: #6B7280;
        margin-bottom: 16px;
    }
    
    .preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }
    
    .preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .preview-item img,
    .preview-item video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .remove-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(0,0,0,0.7);
        color: white;
        border: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
    }
    
    .submit-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
    }
    
    .submit-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<div class="review-container">
    <div class="review-card">
        <h1 style="font-size: 28px; margin-bottom: 24px; text-align: center;">✍️ Tulis Review Produk</h1>
        
        <div class="product-info">
            <img src="/uploads/products/<?= htmlspecialchars($product['image']) ?>" class="product-image" alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="product-details">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p style="color: #6B7280; font-size: 14px;">Bagaimana pengalaman Anda dengan produk ini?</p>
            </div>
        </div>
        
        <form id="reviewForm" enctype="multipart/form-data">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <input type="hidden" id="ratingInput" name="rating" value="0">
            
            <!-- Rating Stars -->
            <div class="rating-section">
                <h2>Berikan Rating</h2>
                <div class="star-rating" id="starRating">
                    <span class="star" data-rating="1">★</span>
                    <span class="star" data-rating="2">★</span>
                    <span class="star" data-rating="3">★</span>
                    <span class="star" data-rating="4">★</span>
                    <span class="star" data-rating="5">★</span>
                </div>
                <p id="ratingText" style="margin-top: 12px; color: #6B7280;">Klik bintang untuk memberikan rating</p>
            </div>
            
            <!-- Reviewer Name -->
            <div class="form-group">
                <label class="form-label">Nama Anda *</label>
                <input type="text" name="reviewer_name" class="form-input" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            
            <!-- Review Text -->
            <div class="form-group">
                <label class="form-label">Review Anda *</label>
                <textarea name="review_text" class="form-textarea" id="reviewText" placeholder="Ceritakan pengalaman Anda dengan produk ini..." required maxlength="3000"></textarea>
                <div class="char-count"><span id="charCount">0</span> / 1000 kata</div>
            </div>
            
            <!-- Photo Upload -->
            <div class="upload-section">
                <h4>📸 Upload Foto (Opsional)</h4>
                <p class="upload-hint">Max 3 foto • Format: JPG, PNG, WebP • Max 16MB per file</p>
                <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple style="display: none;">
                <button type="button" onclick="document.getElementById('photoInput').click()" class="submit-btn" style="width: auto; padding: 12px 24px; background: #10B981; margin-top: 0;">
                    Pilih Foto
                </button>
                <div class="preview-container" id="photoPreview"></div>
            </div>
            
            <!-- Video Upload -->
            <div class="upload-section">
                <h4>🎥 Upload Video (Opsional)</h4>
                <p class="upload-hint">Max 1 video • Format: MP4, WebM • Max 16MB • Durasi max 1 menit</p>
                <input type="file" id="videoInput" name="video" accept="video/*" style="display: none;">
                <button type="button" onclick="document.getElementById('videoInput').click()" class="submit-btn" style="width: auto; padding: 12px 24px; background: #3B82F6; margin-top: 0;">
                    Pilih Video
                </button>
                <div class="preview-container" id="videoPreview"></div>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                ✓ Kirim Review
            </button>
        </form>
    </div>
</div>

<script>
let selectedRating = 0;
let selectedPhotos = [];
let selectedVideo = null;

const ratingTexts = {
    1: '😞 Sangat Buruk',
    2: '😕 Kurang Memuaskan',
    3: '😐 Cukup',
    4: '😊 Bagus!',
    5: '🤩 Luar Biasa!'
};

// Star Rating
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('ratingInput');
const ratingText = document.getElementById('ratingText');

stars.forEach(star => {
    star.addEventListener('click', function() {
        selectedRating = parseInt(this.dataset.rating);
        ratingInput.value = selectedRating;
        updateStars();
        ratingText.textContent = ratingTexts[selectedRating];
        ratingText.style.fontSize = '18px';
        ratingText.style.fontWeight = '600';
    });
    
    star.addEventListener('mouseenter', function() {
        const rating = parseInt(this.dataset.rating);
        highlightStars(rating);
    });
});

document.getElementById('starRating').addEventListener('mouseleave', updateStars);

function highlightStars(rating) {
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

function updateStars() {
    highlightStars(selectedRating);
}

// Character Count
const reviewText = document.getElementById('reviewText');
const charCount = document.getElementById('charCount');

reviewText.addEventListener('input', function() {
    const words = this.value.trim().split(/\s+/).filter(w => w.length > 0);
    charCount.textContent = words.length;
    if (words.length > 1000) {
        charCount.style.color = '#EF4444';
    } else {
        charCount.style.color = '#6B7280';
    }
});

// Photo Upload
const photoInput = document.getElementById('photoInput');
const photoPreview = document.getElementById('photoPreview');

photoInput.addEventListener('change', function() {
    const files = Array.from(this.files).slice(0, 3);
    photoPreview.innerHTML = '';
    
    files.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-btn" onclick="removePhoto(${index})">×</button>
            `;
            photoPreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});

function removePhoto(index) {
    const dt = new DataTransfer();
    const files = Array.from(photoInput.files);
    files.forEach((file, i) => {
        if (i !== index) dt.items.add(file);
    });
    photoInput.files = dt.files;
    photoInput.dispatchEvent(new Event('change'));
}

// Video Upload
const videoInput = document.getElementById('videoInput');
const videoPreview = document.getElementById('videoPreview');

videoInput.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    
    videoPreview.innerHTML = '';
    const reader = new FileReader();
    reader.onload = function(e) {
        const div = document.createElement('div');
        div.className = 'preview-item';
        div.innerHTML = `
            <video src="${e.target.result}" controls></video>
            <button type="button" class="remove-btn" onclick="removeVideo()">×</button>
        `;
        videoPreview.appendChild(div);
    };
    reader.readAsDataURL(file);
});

function removeVideo() {
    videoInput.value = '';
    videoPreview.innerHTML = '';
}

// Form Submit
const form = document.getElementById('reviewForm');
const submitBtn = document.getElementById('submitBtn');

form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (selectedRating === 0) {
        alert('Mohon berikan rating terlebih dahulu!');
        return;
    }
    
    const formData = new FormData(this);
    
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Mengirim...';
    
    try {
        const response = await fetch('/api/reviews/submit-review.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (result.voucher) {
                showRewardModal(result.voucher);
            } else {
                showThankYouModal();
            }
        } else {
            alert('Error: ' + result.message);
            submitBtn.disabled = false;
            submitBtn.textContent = '✓ Kirim Review';
        }
    } catch (error) {
        alert('Terjadi kesalahan. Silakan coba lagi.');
        submitBtn.disabled = false;
        submitBtn.textContent = '✓ Kirim Review';
    }
});

function showRewardModal(voucher) {
    const modal = document.createElement('div');
    modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999;';
    modal.innerHTML = `
        <div style="background: white; border-radius: 20px; padding: 48px; max-width: 500px; text-align: center;">
            <div style="font-size: 64px; margin-bottom: 16px;">🎁</div>
            <h2 style="font-size: 28px; margin-bottom: 16px;">Terima Kasih!</h2>
            <p style="color: #6B7280; margin-bottom: 24px;">Review Anda sangat berarti bagi kami. Sebagai apresiasi:</p>
            <div style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); color: white; padding: 24px; border-radius: 12px; margin-bottom: 24px;">
                <p style="font-size: 14px; margin-bottom: 8px;">Kode Voucher Anda:</p>
                <p style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">${voucher.code}</p>
                <p style="font-size: 13px; margin-top: 12px; opacity: 0.9;">Diskon 10% (max Rp 20.000) • Min. belanja Rp 250.000</p>
            </div>
            <p style="font-size: 14px; color: #6B7280; margin-bottom: 24px;">Voucher berlaku 14 hari & sudah otomatis masuk ke menu Voucher Anda.</p>
            <button onclick="window.location.href='/member/orders.php'" style="width: 100%; padding: 14px; background: #10B981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">OK, Mengerti!</button>
        </div>
    `;
    document.body.appendChild(modal);
}

function showThankYouModal() {
    const modal = document.createElement('div');
    modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999;';
    modal.innerHTML = `
        <div style="background: white; border-radius: 20px; padding: 48px; max-width: 500px; text-align: center;">
            <div style="font-size: 64px; margin-bottom: 16px;">🙏</div>
            <h2 style="font-size: 28px; margin-bottom: 16px;">Terima Kasih!</h2>
            <p style="color: #6B7280; margin-bottom: 24px;">Review Anda sangat membantu kami untuk terus meningkatkan kualitas produk dan layanan.</p>
            <button onclick="window.location.href='/member/orders.php'" style="width: 100%; padding: 14px; background: #667EEA; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Kembali ke Orders</button>
        </div>
    `;
    document.body.appendChild(modal);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
