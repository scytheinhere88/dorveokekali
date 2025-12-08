<?php
/**
 * HOMEPAGE LUXURY SECTIONS
 * Include this file in index.php for upgraded sections
 */

// This file contains 3 luxury sections:
// 1. Hero Slider (from banners table)
// 2. Category Marquee (auto-scrolling)
// 3. Enhanced Featured Products
?>

<!-- ========== HERO BANNER SLIDER (8-10 BANNERS FROM ADMIN) ========== -->
<?php if (!empty($slider_banners)): ?>
<section class="hero-slider-container">
    <?php foreach ($slider_banners as $index => $banner): ?>
        <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
            <img src="<?= htmlspecialchars($banner['image_url']) ?>" 
                 alt="<?= htmlspecialchars($banner['title'] ?? 'Banner Promosi Dorve.id') ?>" 
                 class="hero-slide-image">
            <div class="hero-slide-overlay"></div>
            <div class="hero-slide-content">
                <?php if (!empty($banner['title'])): ?>
                    <h1 class="hero-slide-title"><?= htmlspecialchars($banner['title']) ?></h1>
                <?php endif; ?>
                <?php if (!empty($banner['subtitle'])): ?>
                    <p class="hero-slide-subtitle"><?= htmlspecialchars($banner['subtitle']) ?></p>
                <?php endif; ?>
                <?php if (!empty($banner['cta_text']) && !empty($banner['link_url'])): ?>
                    <a href="<?= htmlspecialchars($banner['link_url']) ?>" class="hero-slide-cta">
                        <?= htmlspecialchars($banner['cta_text']) ?>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- Slider Navigation Dots -->
    <?php if (count($slider_banners) > 1): ?>
        <div class="slider-nav">
            <?php foreach ($slider_banners as $index => $banner): ?>
                <span class="slider-dot <?= $index === 0 ? 'active' : '' ?>" 
                      data-slide="<?= $index ?>" 
                      onclick="goToSlide(<?= $index ?>)"
                      aria-label="Go to slide <?= $index + 1 ?>"></span>
            <?php endforeach; ?>
        </div>
        
        <!-- Slider Arrows -->
        <div class="slider-arrows">
            <button class="slider-arrow slider-arrow-left" onclick="prevSlide()" aria-label="Previous slide">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="slider-arrow slider-arrow-right" onclick="nextSlide()" aria-label="Next slide">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
        
        <!-- Progress Bar -->
        <div class="slider-progress">
            <div class="slider-progress-bar"></div>
        </div>
    <?php endif; ?>
</section>

<script>
let currentSlide = 0;
const totalSlides = <?= count($slider_banners) ?>;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.slider-dot');
const progressBar = document.querySelector('.slider-progress-bar');
let sliderInterval;
const slideDuration = 6000; // 6 seconds per slide

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[index].classList.add('active');
    if (dots[index]) dots[index].classList.add('active');
    currentSlide = index;
    
    // Reset progress bar
    if (progressBar) {
        progressBar.style.animation = 'none';
        setTimeout(() => {
            progressBar.style.animation = `slideProgress ${slideDuration}ms linear`;
        }, 10);
    }
}

function nextSlide() {
    let next = (currentSlide + 1) % totalSlides;
    showSlide(next);
    resetInterval();
}

function prevSlide() {
    let prev = (currentSlide - 1 + totalSlides) % totalSlides;
    showSlide(prev);
    resetInterval();
}

function goToSlide(index) {
    showSlide(index);
    resetInterval();
}

function resetInterval() {
    if (sliderInterval) clearInterval(sliderInterval);
    if (totalSlides > 1) {
        sliderInterval = setInterval(nextSlide, slideDuration);
    }
}

// Auto-play slider
if (totalSlides > 1) {
    resetInterval();
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') prevSlide();
    if (e.key === 'ArrowRight') nextSlide();
});
</script>

<style>
@keyframes slideProgress {
    0% { width: 0; }
    100% { width: 100%; }
}
</style>
<?php else: ?>
<!-- Default Hero Banner (if no banners in admin) -->
<section class="hero-slider-container">
    <div class="hero-slide active">
        <img src="/public/images/Dorve1.png" alt="Dorve.id - Fashion Online Terpercaya" class="hero-slide-image">
        <div class="hero-slide-overlay"></div>
        <div class="hero-slide-content">
            <h1 class="hero-slide-title">Dorve.id - Fashion Online Terpercaya</h1>
            <p class="hero-slide-subtitle">Koleksi Fashion Pria, Wanita & Couple Terlengkap di Indonesia</p>
            <a href="/pages/all-products.php" class="hero-slide-cta">
                Belanja Sekarang
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 8px;">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== JELAJAHI KATEGORI - CATEGORY MARQUEE ========== -->
<?php if (!empty($categories)): ?>
<section class="category-marquee-section">
    <div class="container">
        <div class="section-header">
            <div class="section-pretitle">Temukan Style Anda</div>
            <h2 class="section-title">Jelajahi Kategori</h2>
            <p class="section-description">Dari klasik abadi hingga tren kontemporer, temukan produk sempurna untuk setiap kesempatan</p>
        </div>
    </div>
    
    <div class="category-marquee-wrapper">
        <div class="category-marquee-track">
            <?php 
            // Duplicate categories for seamless loop
            $marquee_categories = array_merge($categories, $categories);
            foreach ($marquee_categories as $category): 
            ?>
                <a href="/pages/all-products.php?category=<?php echo $category['id']; ?>" class="category-marquee-item">
                    <div class="category-icon-wrapper">
                        <?php if (!empty($category['icon'])): ?>
                            <?php if (filter_var($category['icon'], FILTER_VALIDATE_URL)): ?>
                                <!-- Icon uploaded dari admin (URL image) -->
                                <img src="<?php echo htmlspecialchars($category['icon']); ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                     class="category-icon">
                            <?php else: ?>
                                <!-- Icon emoji dari admin -->
                                <span class="category-icon-emoji"><?php echo htmlspecialchars($category['icon']); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Default icon jika belum di-set di admin -->
                            <span class="category-icon-emoji">🛍️</span>
                        <?php endif; ?>
                    </div>
                    <div class="category-marquee-name"><?php echo htmlspecialchars($category['name']); ?></div>
                    <div class="category-marquee-count">Belanja Sekarang →</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== FEATURED PRODUCTS SECTION ========== -->
<?php if (!empty($featured_products)): ?>
<section class="featured-section" style="background: linear-gradient(135deg, #FAFAFA 0%, #FFFFFF 100%);">
    <div class="container">
        <div class="section-header">
            <div class="section-pretitle">Pilihan Spesial</div>
            <h2 class="section-title">Produk Unggulan</h2>
            <p class="section-description">Koleksi terbaik yang dipilih khusus untuk Anda</p>
        </div>

        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
                <a href="/pages/product-detail.php?slug=<?= $product['slug'] ?>" class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($product['image']) ?>"
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <img src="https://images.pexels.com/photos/1926769/pexels-photo-1926769.jpeg?auto=compress&cs=tinysrgb&w=400"
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php endif; ?>

                        <?php if ($product['is_new'] == 1): ?>
                            <span class="product-badge" style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);">✨ NEW</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <?php if (!empty($product['category_name'])): ?>
                            <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                        <?php endif; ?>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price">
                            <?php if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']): ?>
                                <span class="product-price-discount"><?= formatPrice($product['price']) ?></span>
                                <?= formatPrice($product['discount_price']) ?>
                            <?php else: ?>
                                <?= formatPrice($product['price']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($product['stock'])): ?>
                            <?php if ($product['stock'] > 0): ?>
                                <div class="product-stock in-stock">✓ In Stock</div>
                            <?php else: ?>
                                <div class="product-stock out-stock">⚠️ Out of Stock</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <a href="/pages/all-products.php" class="view-all-btn">
            Lihat Semua Produk
        </a>
    </div>
</section>
<?php endif; ?>

<!-- ========== NEW ARRIVALS SECTION ========== -->
<?php if (!empty($new_arrivals)): ?>
<section class="featured-section" style="background: var(--white);">
    <div class="container">
        <div class="section-header">
            <div class="section-pretitle">Koleksi Terbaru</div>
            <h2 class="section-title">New Arrivals</h2>
            <p class="section-description">Produk terbaru yang baru saja tiba</p>
        </div>

        <div class="products-grid">
            <?php foreach ($new_arrivals as $product): ?>
                <a href="/pages/product-detail.php?slug=<?= $product['slug'] ?>" class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($product['image']) ?>"
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <img src="https://images.pexels.com/photos/1926769/pexels-photo-1926769.jpeg?auto=compress&cs=tinysrgb&w=400"
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php endif; ?>
                        <span class="product-badge" style="background: #10B981;">🌟 NEW ARRIVAL</span>
                    </div>
                    <div class="product-info">
                        <?php if (!empty($product['category_name'])): ?>
                            <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                        <?php endif; ?>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price">
                            <?php if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']): ?>
                                <span class="product-price-discount"><?= formatPrice($product['price']) ?></span>
                                <?= formatPrice($product['discount_price']) ?>
                            <?php else: ?>
                                <?= formatPrice($product['price']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($product['stock'])): ?>
                            <?php if ($product['stock'] > 0): ?>
                                <div class="product-stock in-stock">✓ In Stock</div>
                            <?php else: ?>
                                <div class="product-stock out-stock">⚠️ Out of Stock</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <a href="/pages/all-products.php?filter=new" class="view-all-btn">
            Lihat Semua Produk Baru
        </a>
    </div>
</section>
<?php endif; ?>
