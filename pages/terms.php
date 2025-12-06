<?php
require_once __DIR__ . '/../config.php';

$stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE slug = 'terms' AND is_active = 1 LIMIT 1");
$stmt->execute();
$page = $stmt->fetch();

$page_title = 'Syarat & Ketentuan - Dorve.id | Terms of Service Belanja Fashion Online';
$page_description = 'Syarat dan ketentuan belanja di Dorve.id: kebijakan return 14 hari, garansi produk, cara pembayaran aman, pengiriman, refund, dan hak pelanggan. Baca panduan lengkap sebelum berbelanja baju online dengan aman dan nyaman.';
$page_keywords = 'syarat ketentuan dorve.id, terms of service, kebijakan return, garansi produk, hak pelanggan, cara pembayaran, refund policy, tukar barang, toko baju online terpercaya, terms and conditions fashion';
include __DIR__ . '/../includes/header.php';
?>

<!-- Schema Markup for Legal Page -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Syarat & Ketentuan - Dorve.id",
  "description": "<?php echo $page_description; ?>",
  "publisher": {
    "@type": "Organization",
    "name": "Dorve.id",
    "url": "https://dorve.id"
  },
  "datePublished": "2024-01-01",
  "dateModified": "<?php echo date('Y-m-d'); ?>"
}
</script>

<style>
    :root {
        --charcoal: #1A1A1A;
        --white: #FFFFFF;
        --off-white: #F8F8F8;
        --latte: #D4C5B9;
        --grey: #6B6B6B;
        --cream: #F5F1ED;
    }

    /* ===== HERO SECTION ===== */
    .legal-hero {
        background: linear-gradient(135deg, var(--charcoal) 0%, #2D2D2D 100%);
        padding: 100px 24px 80px;
        text-align: center;
        color: var(--white);
    }

    .legal-hero-content {
        max-width: 800px;
        margin: 0 auto;
    }

    .legal-badge {
        display: inline-block;
        padding: 8px 20px;
        background: rgba(255,255,255,0.1);
        border-radius: 50px;
        font-size: 12px;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 24px;
        font-weight: 600;
    }

    .legal-hero h1 {
        font-family: 'Playfair Display', serif;
        font-size: 52px;
        font-weight: 700;
        margin-bottom: 20px;
        line-height: 1.2;
    }

    .legal-hero p {
        font-size: 18px;
        color: rgba(255,255,255,0.9);
        margin-bottom: 16px;
        line-height: 1.7;
    }

    .legal-updated {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: rgba(255,255,255,0.7);
        margin-top: 24px;
    }

    .legal-updated-icon {
        font-size: 16px;
    }

    /* ===== MAIN CONTAINER ===== */
    .legal-main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 80px 24px;
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 60px;
        align-items: start;
    }

    /* ===== TABLE OF CONTENTS (SIDEBAR) ===== */
    .toc-sidebar {
        position: sticky;
        top: 100px;
        background: var(--off-white);
        padding: 32px;
        border-radius: 12px;
        border: 2px solid rgba(0,0,0,0.06);
    }

    .toc-title {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--charcoal);
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--latte);
    }

    .toc-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .toc-item {
        margin-bottom: 12px;
    }

    .toc-link {
        display: block;
        padding: 10px 16px;
        color: var(--grey);
        text-decoration: none;
        font-size: 14px;
        border-radius: 6px;
        transition: all 0.3s ease;
        line-height: 1.5;
    }

    .toc-link:hover {
        background: var(--white);
        color: var(--charcoal);
        padding-left: 24px;
    }

    .toc-link.active {
        background: var(--charcoal);
        color: var(--white);
        font-weight: 600;
    }

    /* ===== CONTENT AREA ===== */
    .legal-content {
        background: var(--white);
        padding: 60px;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }

    .legal-section {
        margin-bottom: 60px;
        scroll-margin-top: 100px;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 2px solid var(--latte);
    }

    .section-icon {
        font-size: 32px;
        flex-shrink: 0;
    }

    .legal-content h2 {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        color: var(--charcoal);
        margin: 0;
        line-height: 1.3;
    }

    .legal-content h3 {
        font-size: 20px;
        color: var(--charcoal);
        margin: 32px 0 16px;
        font-weight: 700;
    }

    .legal-content p {
        line-height: 1.9;
        color: var(--grey);
        margin-bottom: 20px;
        font-size: 15px;
        text-align: justify;
    }

    .legal-content strong {
        color: var(--charcoal);
        font-weight: 600;
    }

    .legal-content ul {
        margin: 20px 0;
        padding-left: 24px;
        line-height: 1.8;
        color: var(--grey);
    }

    .legal-content li {
        margin-bottom: 12px;
        padding-left: 8px;
    }

    .legal-content li strong {
        color: var(--charcoal);
    }

    /* ===== HIGHLIGHT BOXES ===== */
    .highlight-box {
        background: linear-gradient(135deg, #FFF9F5 0%, #FFF5ED 100%);
        border-left: 4px solid var(--latte);
        padding: 24px 28px;
        margin: 28px 0;
        border-radius: 8px;
    }

    .highlight-box-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .highlight-icon {
        font-size: 24px;
    }

    .highlight-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--charcoal);
        margin: 0;
    }

    .highlight-box p {
        margin: 0;
        text-align: left;
    }

    /* ===== WARNING BOX ===== */
    .warning-box {
        background: #FFF4E6;
        border-left: 4px solid #F59E0B;
        padding: 24px 28px;
        margin: 28px 0;
        border-radius: 8px;
    }

    /* ===== SUCCESS BOX ===== */
    .success-box {
        background: #F0FDF4;
        border-left: 4px solid #10B981;
        padding: 24px 28px;
        margin: 28px 0;
        border-radius: 8px;
    }

    /* ===== INFO BOX ===== */
    .info-box {
        background: #EFF6FF;
        border-left: 4px solid #3B82F6;
        padding: 24px 28px;
        margin: 28px 0;
        border-radius: 8px;
    }

    /* ===== ACCORDION SECTIONS ===== */
    .accordion-item {
        margin-bottom: 16px;
        border: 2px solid rgba(0,0,0,0.06);
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .accordion-item:hover {
        border-color: var(--latte);
    }

    .accordion-header {
        width: 100%;
        padding: 20px 24px;
        background: var(--white);
        border: none;
        text-align: left;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        font-size: 16px;
        font-weight: 600;
        color: var(--charcoal);
    }

    .accordion-header:hover {
        background: var(--off-white);
    }

    .accordion-header.active {
        background: var(--off-white);
    }

    .accordion-arrow {
        width: 28px;
        height: 28px;
        background: var(--latte);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 14px;
        flex-shrink: 0;
    }

    .accordion-header.active .accordion-arrow {
        transform: rotate(180deg);
        background: var(--charcoal);
        color: var(--white);
    }

    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease;
        background: var(--white);
    }

    .accordion-content.active {
        max-height: 2000px;
    }

    .accordion-body {
        padding: 24px;
        border-top: 1px solid rgba(0,0,0,0.06);
    }

    /* ===== CONTACT CTA ===== */
    .contact-cta {
        background: linear-gradient(135deg, var(--charcoal) 0%, #2D2D2D 100%);
        padding: 60px;
        border-radius: 12px;
        text-align: center;
        color: var(--white);
        margin-top: 80px;
    }

    .contact-cta h3 {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        margin-bottom: 16px;
        color: var(--white);
    }

    .contact-cta p {
        color: rgba(255,255,255,0.9);
        margin-bottom: 32px;
        text-align: center;
    }

    .contact-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .contact-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 32px;
        background: var(--white);
        color: var(--charcoal);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .contact-btn:hover {
        background: var(--latte);
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }

    .contact-btn-outline {
        background: transparent;
        border: 2px solid var(--white);
        color: var(--white);
    }

    .contact-btn-outline:hover {
        background: var(--white);
        color: var(--charcoal);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .legal-main-container {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .toc-sidebar {
            position: relative;
            top: 0;
        }

        .legal-content {
            padding: 40px;
        }
    }

    @media (max-width: 768px) {
        .legal-hero {
            padding: 80px 24px 60px;
        }

        .legal-hero h1 {
            font-size: 36px;
        }

        .legal-main-container {
            padding: 60px 24px;
        }

        .legal-content {
            padding: 32px 24px;
        }

        .legal-content h2 {
            font-size: 26px;
        }

        .contact-cta {
            padding: 40px 24px;
        }

        .contact-cta h3 {
            font-size: 26px;
        }

        .contact-buttons {
            flex-direction: column;
            align-items: center;
        }

        .contact-btn {
            width: 100%;
            max-width: 280px;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .legal-hero h1 {
            font-size: 28px;
        }

        .section-icon {
            font-size: 24px;
        }
    }
</style>

<!-- Hero Section -->
<section class="legal-hero">
    <div class="legal-hero-content">
        <div class="legal-badge">📜 Legal Document</div>
        <h1>Syarat & Ketentuan</h1>
        <p>Panduan lengkap untuk berbelanja fashion online dengan aman dan nyaman di Dorve.id</p>
        <div class="legal-updated">
            <span class="legal-updated-icon">📅</span>
            <span>Terakhir diperbarui: <?php echo date('d F Y'); ?></span>
        </div>
    </div>
</section>

<!-- Main Container with TOC -->
<div class="legal-main-container">
    <!-- Table of Contents Sidebar -->
    <aside class="toc-sidebar">
        <div class="toc-title">Daftar Isi</div>
        <ul class="toc-list">
            <li class="toc-item"><a href="#general" class="toc-link">Ketentuan Umum</a></li>
            <li class="toc-item"><a href="#account" class="toc-link">Pendaftaran Akun</a></li>
            <li class="toc-item"><a href="#products" class="toc-link">Produk & Harga</a></li>
            <li class="toc-item"><a href="#ordering" class="toc-link">Pemesanan & Pembayaran</a></li>
            <li class="toc-item"><a href="#shipping" class="toc-link">Pengiriman</a></li>
            <li class="toc-item"><a href="#returns" class="toc-link">Return & Penukaran</a></li>
            <li class="toc-item"><a href="#defects" class="toc-link">Produk Cacat</a></li>
            <li class="toc-item"><a href="#intellectual" class="toc-link">Hak Kekayaan</a></li>
            <li class="toc-item"><a href="#privacy" class="toc-link">Privasi & Data</a></li>
            <li class="toc-item"><a href="#liability" class="toc-link">Pembatasan Tanggung Jawab</a></li>
            <li class="toc-item"><a href="#loyalty" class="toc-link">Program Loyalty</a></li>
            <li class="toc-item"><a href="#law" class="toc-link">Hukum yang Berlaku</a></li>
            <li class="toc-item"><a href="#contact" class="toc-link">Hubungi Kami</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="legal-content">
        <!-- Introduction Box -->
        <div class="highlight-box">
            <div class="highlight-box-header">
                <span class="highlight-icon">👋</span>
                <h4 class="highlight-title">Selamat Datang di Dorve.id</h4>
            </div>
            <p>
                Dengan mengakses dan menggunakan website <strong>Dorve.id</strong>, Anda setuju untuk terikat dengan syarat dan ketentuan yang berlaku. Mohon baca dengan seksama sebelum melakukan transaksi <strong>belanja baju online</strong> di platform kami. Kepuasan dan kenyamanan Anda adalah prioritas kami.
            </p>
        </div>

        <!-- Section 1: General Terms -->
        <section id="general" class="legal-section">
            <div class="section-header">
                <span class="section-icon">📋</span>
                <h2>Ketentuan Umum</h2>
            </div>

            <p>
                Syarat dan ketentuan ini mengatur penggunaan website <strong>Dorve.id</strong> dan pembelian produk <strong>fashion wanita</strong>, <strong>fashion pria</strong>, dan <strong>fashion unisex</strong> melalui platform online kami. Dengan mengakses website ini, Anda menyatakan bahwa:
            </p>

            <ul>
                <li>Anda telah membaca, memahami, dan menyetujui semua ketentuan yang berlaku</li>
                <li>Anda adalah individu yang legally capable untuk enter into binding contracts</li>
                <li>Anda akan comply dengan semua applicable laws dan regulations</li>
                <li>Informasi yang Anda berikan adalah accurate, complete, dan up-to-date</li>
            </ul>

            <h3>Perubahan Syarat & Ketentuan</h3>
            <p>
                <strong>Dorve.id</strong> berhak untuk mengubah, memodifikasi, atau memperbarui syarat dan ketentuan ini kapan saja untuk reflect:
            </p>

            <ul>
                <li>Changes dalam business practices atau operational procedures</li>
                <li>Updates dalam applicable laws dan regulations di Indonesia</li>
                <li>New features, services, atau product offerings</li>
                <li>Improvements dalam customer experience dan service quality</li>
            </ul>

            <div class="warning-box">
                <p><strong>⚠️ Penting:</strong> Perubahan akan effective immediately setelah posted di website. Continued use dari layanan kami setelah changes constitutes your acceptance dari updated terms. Kami encourage Anda untuk periodically review halaman ini.</p>
            </div>

            <h3>Acceptance of Terms</h3>
            <p>
                Dengan melakukan any of the following actions, Anda indicate acceptance dari terms ini:
            </p>

            <ul>
                <li>Creating account di <strong>Dorve.id</strong></li>
                <li>Placing order atau making purchase</li>
                <li>Subscribing ke newsletter atau marketing communications</li>
                <li>Participating dalam programs, contests, atau promotions</li>
                <li>Using any features atau services yang available di website</li>
            </ul>
        </section>

        <!-- Section 2: Account Registration -->
        <section id="account" class="legal-section">
            <div class="section-header">
                <span class="section-icon">👤</span>
                <h2>Pendaftaran Akun</h2>
            </div>

            <h3>Pembuatan Akun</h3>
            <p>
                Untuk full experience di <strong>toko baju online</strong> kami, Anda dapat create member account. Benefits include:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Member Benefits & Privileges</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Order Tracking:</strong> Real-time tracking untuk semua purchases Anda</li>
                            <li><strong>Wishlist:</strong> Save favorite items untuk future purchases</li>
                            <li><strong>Reward Points:</strong> Earn points setiap purchase yang dapat ditukar dengan discounts</li>
                            <li><strong>Exclusive Access:</strong> Early access ke sales, new arrivals, dan limited editions</li>
                            <li><strong>Saved Addresses:</strong> Quick checkout dengan saved shipping addresses</li>
                            <li><strong>Purchase History:</strong> Easy reordering dan view past transactions</li>
                            <li><strong>Birthday Treats:</strong> Special offers untuk birthday month Anda</li>
                        </ul>
                    </div>
                </div>
            </div>

            <h3>Tanggung Jawab Account Holder</h3>
            <p>
                Sebagai account holder, Anda bertanggung jawab untuk:
            </p>

            <ul>
                <li><strong>Accuracy:</strong> Provide accurate, complete, dan current information saat registration</li>
                <li><strong>Security:</strong> Maintain confidentiality dari password dan account credentials</li>
                <li><strong>Liability:</strong> All activities yang occur under your account adalah responsibility Anda</li>
                <li><strong>Updates:</strong> Promptly update account information jika ada changes</li>
                <li><strong>Notification:</strong> Immediately notify kami jika suspect unauthorized account access</li>
                <li><strong>Compliance:</strong> Not use account untuk illegal activities atau violation of terms</li>
            </ul>

            <div class="info-box">
                <p><strong>💡 Security Tip:</strong> Use strong, unique password yang combine uppercase, lowercase, numbers, dan special characters. Enable two-factor authentication jika available untuk extra security.</p>
            </div>

            <h3>Usia Minimum & Legal Capacity</h3>
            <p>
                Untuk create account dan make purchases di <strong>Dorve.id</strong>:
            </p>

            <ul>
                <li>Anda must be at least <strong>17 tahun</strong> atau legal age of majority di jurisdiction Anda</li>
                <li>Jika under 18, Anda must have parental/guardian consent</li>
                <li>Anda must have legal capacity untuk enter into binding contracts</li>
                <li>Account registration constitutes representation bahwa Anda meet age requirements</li>
            </ul>

            <h3>Account Termination</h3>
            <p>
                Kami reserve right untuk suspend atau terminate account jika:
            </p>

            <ul>
                <li>Violation dari syarat dan ketentuan ini</li>
                <li>Fraudulent activities atau suspected fraud</li>
                <li>Abuse dari promotional offers atau reward programs</li>
                <li>Inappropriate behavior towards customer service atau other users</li>
                <li>Multiple chargebacks atau payment disputes</li>
            </ul>
        </section>

        <!-- Section 3: Products & Pricing -->
        <section id="products" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🛍️</span>
                <h2>Produk & Harga</h2>
            </div>

            <h3>Informasi Produk</h3>
            <p>
                Kami strive untuk provide accurate descriptions untuk semua <strong>baju kekinian</strong> di website kami. However:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Product Representation & Accuracy</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Colors:</strong> Actual product colors may vary slightly dari display images due to device screen settings, lighting, dan photography conditions</li>
                            <li><strong>Measurements:</strong> Size measurements provided adalah approximate dan may have minor variations (+/- 1-2 cm) karena nature dari garment production</li>
                            <li><strong>Materials:</strong> Fabric composition dan care instructions stated di product descriptions</li>
                            <li><strong>Images:</strong> Photos may show styling accessories atau items yang not included dalam purchase</li>
                            <li><strong>Descriptions:</strong> While we ensure accuracy, minor errors atau omissions may occur</li>
                        </ul>
                        <div class="warning-box">
                            <p><strong>⚠️ Note:</strong> Jika Anda menerima item yang significantly different dari description, please contact customer service immediately untuk resolution.</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Harga & Ketersediaan</h3>
            <p>
                All prices displayed di website adalah dalam <strong>Indonesian Rupiah (IDR)</strong> dan:
            </p>

            <ul>
                <li><strong>Inclusive:</strong> Prices include PPN (Value Added Tax) where applicable</li>
                <li><strong>Exclusive:</strong> Shipping costs calculated separately at checkout</li>
                <li><strong>Subject to Change:</strong> Prices may change without prior notice</li>
                <li><strong>Valid at Checkout:</strong> Price yang berlaku adalah price pada saat payment completion</li>
            </ul>

            <h3>Hak Dorve.id</h3>
            <p>
                Kami reserve the right untuk:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Our Rights Regarding Products & Orders</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Price Corrections:</strong> Correct pricing errors yang appear di website (even after order placement)</li>
                            <li><strong>Order Cancellation:</strong> Cancel orders jika significant pricing error occurred</li>
                            <li><strong>Stock Limitations:</strong> Refuse atau cancel orders jika product out of stock</li>
                            <li><strong>Quantity Restrictions:</strong> Limit purchase quantities per customer untuk certain items</li>
                            <li><strong>Product Discontinuation:</strong> Discontinue products at any time without notice</li>
                            <li><strong>Refusal of Service:</strong> Refuse service kepada anyone untuk any reason (consistent with applicable law)</li>
                        </ul>
                        <div class="info-box">
                            <p><strong>💡 Good Faith:</strong> Dalam case of cancellation due to errors or stock issues, kami akan promptly refund any payment received.</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Product Availability</h3>
            <p>
                Stock availability di website adalah untuk reference purposes only:
            </p>

            <ul>
                <li>Real-time stock levels may differ dari what's displayed</li>
                <li>High-demand items may sell out during checkout process</li>
                <li>Pre-order items akan shipped sesuai estimated dates provided</li>
                <li>Made-to-order items have longer processing times</li>
            </ul>

            <div class="success-box">
                <p><strong>✅ Restock Notifications:</strong> Sign up untuk restock alerts di product pages untuk receive email when sold-out items become available again!</p>
            </div>
        </section>

        <!-- Section 4: Ordering & Payment -->
        <section id="ordering" class="legal-section">
            <div class="section-header">
                <span class="section-icon">💳</span>
                <h2>Pemesanan & Pembayaran</h2>
            </div>

            <h3>Proses Pemesanan</h3>
            <p>
                Ordering process di <strong>Dorve.id</strong> designed untuk be simple dan user-friendly:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Step-by-Step Ordering Guide</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ol style="margin: 0; padding-left: 24px;">
                            <li><strong>Browse & Select:</strong> Explore collections dan select <strong>baju wanita</strong>, <strong>baju pria</strong>, atau <strong>baju couple</strong> yang Anda inginkan</li>
                            <li><strong>Choose Options:</strong> Select size, color, dan quantity untuk each item</li>
                            <li><strong>Add to Cart:</strong> Click "Add to Cart" button</li>
                            <li><strong>Review Cart:</strong> Verify semua items, quantities, dan details correct</li>
                            <li><strong>Proceed to Checkout:</strong> Enter atau select shipping address</li>
                            <li><strong>Select Shipping:</strong> Choose shipping method dan courier</li>
                            <li><strong>Apply Vouchers:</strong> Enter promo codes jika ada (optional)</li>
                            <li><strong>Choose Payment:</strong> Select preferred payment method</li>
                            <li><strong>Review & Confirm:</strong> Final review sebelum complete order</li>
                            <li><strong>Complete Payment:</strong> Follow payment instructions provided</li>
                        </ol>
                    </div>
                </div>
            </div>

            <h3>Metode Pembayaran</h3>
            <p>
                Kami accept berbagai payment methods untuk convenience Anda:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Available Payment Methods</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <h4 style="margin-top: 0;">Bank Transfer</h4>
                        <ul>
                            <li>BCA, Mandiri, BNI, BRI</li>
                            <li>CIMB Niaga, Permata Bank</li>
                            <li>Virtual Account available untuk semua major banks</li>
                        </ul>

                        <h4>E-Wallet & Digital Payment</h4>
                        <ul>
                            <li>GoPay, OVO, DANA</li>
                            <li>ShopeePay, LinkAja</li>
                            <li>QRIS (Quick Response Indonesian Standard)</li>
                        </ul>

                        <h4>Credit & Debit Cards</h4>
                        <ul>
                            <li>Visa, Mastercard, JCB</li>
                            <li>American Express</li>
                            <li>International cards accepted (with additional verification)</li>
                        </ul>

                        <h4>Cash on Delivery (COD)</h4>
                        <ul>
                            <li>Available untuk Jakarta & surrounding areas (Jabodetabek)</li>
                            <li>Additional COD fee: Rp 10.000</li>
                            <li>Maximum transaction: Rp 2.000.000</li>
                            <li>Exact amount atau prepare cash</li>
                        </ul>

                        <h4>Installment Options</h4>
                        <ul>
                            <li>Cicilan 0% tersedia untuk certain credit cards</li>
                            <li>3, 6, 12 months installment periods</li>
                            <li>Minimum transaction requirements apply</li>
                            <li>Terms & conditions sesuai bank policies</li>
                        </ul>
                    </div>
                </div>
            </div>

            <h3>Payment Security</h3>
            <p>
                Your payment security adalah top priority:
            </p>

            <ul>
                <li><strong>Encryption:</strong> All transactions processed melalui secure, encrypted connections (SSL 256-bit)</li>
                <li><strong>PCI Compliance:</strong> Payment partners certified PCI-DSS compliant</li>
                <li><strong>No Storage:</strong> Kami TIDAK store complete credit card information</li>
                <li><strong>Verification:</strong> Additional security checks untuk suspicious transactions</li>
            </ul>

            <h3>Order Confirmation</h3>
            <p>
                Setelah successful payment, Anda akan receive:
            </p>

            <ul>
                <li><strong>Email Confirmation:</strong> Detailed order summary sent ke registered email</li>
                <li><strong>Order Number:</strong> Unique reference number untuk tracking</li>
                <li><strong>Invoice:</strong> Digital invoice attached atau available untuk download</li>
                <li><strong>Estimated Delivery:</strong> Expected delivery timeframe based on location</li>
            </ul>

            <div class="warning-box">
                <p><strong>⚠️ Payment Timeout:</strong> Unpaid orders akan automatically cancelled setelah 24 hours (atau sesuai payment method deadline). Items will be returned to stock.</p>
            </div>
        </section>

        <!-- Section 5: Shipping -->
        <section id="shipping" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🚚</span>
                <h2>Pengiriman & Ongkos Kirim</h2>
            </div>

            <h3>Processing Time</h3>
            <p>
                Orders akan processed dengan schedule berikut:
            </p>

            <ul>
                <li><strong>Regular Items:</strong> 1-2 business days setelah payment confirmed</li>
                <li><strong>Pre-order Items:</strong> Sesuai estimated timeline di product description (biasanya 7-14 days)</li>
                <li><strong>Custom/Made-to-Order:</strong> 7-21 business days tergantung complexity</li>
                <li><strong>High Volume Periods:</strong> Processing may take longer during sales atau holidays</li>
            </ul>

            <h3>Shipping Partners</h3>
            <p>
                Kami bekerja sama dengan ekspedisi terpercaya:
            </p>

            <ul>
                <li>JNE (Regular, YES, Express)</li>
                <li>J&T Express (Regular, Express)</li>
                <li>SiCepat (Regular, Best, Cargo)</li>
                <li>AnterAja (Regular, Next Day)</li>
                <li>Ninja Xpress (Standard, Express)</li>
            </ul>

            <h3>Delivery Time Estimates</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Estimated Delivery Times by Location</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Jakarta & Surrounding Areas (Jabodetabek):</strong> 2-3 business days</li>
                            <li><strong>Jawa Barat, Jawa Tengah, Jawa Timur:</strong> 3-5 business days</li>
                            <li><strong>Bali, Sumatera (major cities):</strong> 4-7 business days</li>
                            <li><strong>Kalimantan, Sulawesi:</strong> 5-8 business days</li>
                            <li><strong>Papua, Maluku, NTT, NTB:</strong> 7-14 business days</li>
                            <li><strong>Remote Areas:</strong> Up to 21 business days</li>
                        </ul>
                        <div class="info-box">
                            <p><strong>📦 Express Shipping:</strong> Available untuk selected areas dengan delivery 1-2 business days. Additional charges apply.</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Shipping Costs</h3>
            <p>
                Ongkir calculated based on:
            </p>

            <ul>
                <li><strong>Weight:</strong> Total weight dari your order</li>
                <li><strong>Destination:</strong> Shipping address location</li>
                <li><strong>Courier:</strong> Selected shipping method</li>
                <li><strong>Service Type:</strong> Regular vs Express</li>
            </ul>

            <div class="success-box">
                <p><strong>🎉 FREE SHIPPING:</strong> Enjoy gratis ongkir untuk orders di atas <strong>Rp 500.000</strong> ke seluruh Indonesia! Look out untuk free shipping promos yang regular kami adakan.</p>
            </div>

            <h3>Shipping Responsibilities</h3>
            <p>
                Kami NOT responsible untuk:
            </p>

            <ul>
                <li>Delays caused by force majeure (natural disasters, extreme weather, pandemics)</li>
                <li>Customs atau regulatory delays untuk international destinations</li>
                <li>Incorrect address information provided by customer</li>
                <li>Failed delivery attempts karena recipient unavailable</li>
                <li>Packages refused by recipient without valid reason</li>
            </ul>

            <h3>Package Tracking</h3>
            <p>
                Track your order easily:
            </p>

            <ul>
                <li>Tracking number sent via email once shipped</li>
                <li>Real-time updates di "My Orders" section</li>
                <li>Direct link ke courier tracking page</li>
                <li>WhatsApp notifications untuk delivery updates (optional)</li>
            </ul>
        </section>

        <!-- Section 6: Returns & Exchanges -->
        <section id="returns" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🔄</span>
                <h2>Kebijakan Return & Penukaran</h2>
            </div>

            <p>
                Customer satisfaction adalah priority. Kami offer <strong>14-day return policy</strong> untuk give Anda peace of mind when shopping <strong>baju online</strong>.
            </p>

            <h3>Return Eligibility Requirements</h3>
            <p>
                Items eligible untuk return jika meet ALL conditions below:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Detailed Return Requirements</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Timeframe:</strong> Return request submitted within <strong>14 calendar days</strong> dari delivery date</li>
                            <li><strong>Condition:</strong> Item dalam kondisi baru, unworn, unwashed, dan unaltered</li>
                            <li><strong>Tags:</strong> Original tags, labels, dan packaging masih attached dan intact</li>
                            <li><strong>Hygiene:</strong> No signs of wear, stains, odors, atau damage</li>
                            <li><strong>Proof:</strong> Include original invoice atau order confirmation</li>
                            <li><strong>Packaging:</strong> Return dalam original packaging jika possible</li>
                        </ul>
                        <div class="warning-box">
                            <p><strong>⚠️ Important:</strong> Items yang not meet these requirements akan rejected dan returned ke Anda (at your expense).</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Non-Returnable Items</h3>
            <p>
                Following items CANNOT be returned for hygiene dan safety reasons:
            </p>

            <ul>
                <li><strong>Intimates & Underwear:</strong> Bras, panties, shapewear, socks</li>
                <li><strong>Swimwear:</strong> Bikinis, swimsuits, beachwear</li>
                <li><strong>Earrings:</strong> All pierced earrings</li>
                <li><strong>Sale Items:</strong> Items marked "Final Sale" atau purchased dengan discount >50% (kecuali defective)</li>
                <li><strong>Custom Orders:</strong> Personalized atau made-to-order items</li>
                <li><strong>Gift Cards:</strong> Vouchers atau e-gift cards</li>
                <li><strong>Worn Items:</strong> Any item dengan signs of use</li>
            </ul>

            <h3>Return Process</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Step-by-Step Return Process</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ol style="margin: 0; padding-left: 24px;">
                            <li><strong>Contact Us:</strong> Reach out via WhatsApp/Email dalam 14 days dengan order number</li>
                            <li><strong>Submit Request:</strong> Provide photos dari item (dengan tags visible) dan reason for return</li>
                            <li><strong>Await Approval:</strong> Our team will review dalam 1-2 business days</li>
                            <li><strong>Get Return Address:</strong> Jika approved, kami akan provide return shipping address dan instructions</li>
                            <li><strong>Ship Item:</strong> Pack securely dan ship via any courier (tracking recommended)</li>
                            <li><strong>Quality Check:</strong> Kami inspect item upon arrival (2-3 business days)</li>
                            <li><strong>Refund Processing:</strong> Jika approved, refund processed dalam 7-14 business days</li>
                        </ol>
                        <div class="info-box">
                            <p><strong>💡 Return Shipping:</strong> Customer covers return shipping costs untuk "change of mind" returns. Kami cover shipping untuk defective/wrong items.</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Refund Methods</h3>
            <p>
                Refunds will be issued via:
            </p>

            <ul>
                <li><strong>Original Payment Method:</strong> Refund ke payment method yang digunakan untuk purchase</li>
                <li><strong>Store Credit:</strong> Get store credit dengan bonus 10% (optional)</li>
                <li><strong>Bank Transfer:</strong> Direct transfer ke bank account (provide details)</li>
            </ul>

            <h3>Exchange Policy</h3>
            <p>
                Want different size atau color? Exchange is easier than return:
            </p>

            <ul>
                <li><strong>FREE size exchange</strong> untuk first exchange (subject to stock availability)</li>
                <li>Follow same process sebagai return tapi specify desired item</li>
                <li>Kami akan ship replacement once original item received dan verified</li>
                <li>Jika price difference, pay/receive difference amount</li>
            </ul>

            <div class="success-box">
                <p><strong>✅ Quick Exchange:</strong> For faster service, consider ordering new size/color first, then return unwanted item untuk refund.</p>
            </div>
        </section>

        <!-- Section 7: Defective Products -->
        <section id="defects" class="legal-section">
            <div class="section-header">
                <span class="section-icon">⚠️</span>
                <h2>Produk Cacat atau Salah Kirim</h2>
            </div>

            <p>
                Kami apologize jika Anda receive defective item atau wrong product. Your satisfaction adalah priority dan kami akan make it right immediately.
            </p>

            <h3>What Qualifies as Defective?</h3>
            <ul>
                <li><strong>Manufacturing Defects:</strong> Broken zippers, loose buttons, unraveling seams, significant fabric flaws</li>
                <li><strong>Damage:</strong> Tears, holes, stains present upon arrival</li>
                <li><strong>Significant Discrepancy:</strong> Item substantially different dari description/photos</li>
                <li><strong>Wrong Item:</strong> Received completely different product dari what ordered</li>
                <li><strong>Incomplete Order:</strong> Missing items dari order</li>
            </ul>

            <h3>Action Required</h3>
            <p>
                Jika receive defective/wrong item:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Immediate Steps for Defective Items</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ol style="margin: 0; padding-left: 24px;">
                            <li><strong>Report Quickly:</strong> Contact us within <strong>3x24 hours (72 hours)</strong> setelah delivery</li>
                            <li><strong>Document Issue:</strong> Take clear photos/video dari:
                                <ul>
                                    <li>The defect atau wrong item</li>
                                    <li>Product tags/labels</li>
                                    <li>Original packaging</li>
                                    <li>Complete package content</li>
                                </ul>
                            </li>
                            <li><strong>Submit Claim:</strong> Send via WhatsApp/Email dengan order number dan documentation</li>
                            <li><strong>Quick Review:</strong> Our team will assess immediately (usually same day)</li>
                            <li><strong>Resolution:</strong> Choose your preferred solution</li>
                        </ol>
                    </div>
                </div>
            </div>

            <h3>Resolution Options</h3>
            <p>
                For verified defective/wrong items, pilih one of:
            </p>

            <ul>
                <li><strong>Free Replacement:</strong> Kami send brand new item at no charge</li>
                <li><strong>Full Refund:</strong> 100% refund including original shipping cost</li>
                <li><strong>Store Credit + Bonus:</strong> Store credit dengan additional 15% bonus</li>
                <li><strong>Partial Refund:</strong> Keep item dengan partial refund (for minor defects)</li>
            </ul>

            <h3>Return Shipping for Defects</h3>
            <p>
                For defective atau wrong items:
            </p>

            <ul>
                <li><strong>Kami Cover Costs:</strong> Return shipping fees fully covered by Dorve.id</li>
                <li><strong>Prepaid Label:</strong> Kami dapat provide prepaid shipping label (select areas)</li>
                <li><strong>Pickup Service:</strong> Free courier pickup dapat arranged (major cities)</li>
                <li><strong>Reimbursement:</strong> Pay first, kami reimburse dengan proof of shipping</li>
            </ul>

            <div class="success-box">
                <p><strong>✅ Our Commitment:</strong> Defective items akan di-handle dengan highest priority. Expect resolution dalam 3-5 business days maximum.</p>
            </div>
        </section>

        <!-- Section 8: Intellectual Property -->
        <section id="intellectual" class="legal-section">
            <div class="section-header">
                <span class="section-icon">©️</span>
                <h2>Hak Kekayaan Intelektual</h2>
            </div>

            <p>
                All content di <strong>Dorve.id</strong> website adalah intellectual property dari Dorve.id dan protected by Indonesian dan international copyright laws.
            </p>

            <h3>Protected Content Includes:</h3>
            <ul>
                <li><strong>Brand Elements:</strong> Dorve.id logo, trademarks, brand identity</li>
                <li><strong>Visual Content:</strong> Product photos, lookbooks, campaign images, graphics</li>
                <li><strong>Written Content:</strong> Product descriptions, blog posts, copy text</li>
                <li><strong>Design Elements:</strong> Website design, layout, user interface</li>
                <li><strong>Code:</strong> Website source code, scripts, software</li>
                <li><strong>Videos:</strong> Promotional videos, tutorials, behind-the-scenes content</li>
            </ul>

            <h3>Prohibited Uses</h3>
            <p>
                Without written permission dari Dorve.id, Anda TIDAK DIPERKENANKAN untuk:
            </p>

            <ul>
                <li>Copy, reproduce, atau distribute any content dari website</li>
                <li>Use Dorve.id branding, logos, atau trademarks untuk commercial purposes</li>
                <li>Download product images untuk resale di platforms lain</li>
                <li>Modify, adapt, atau create derivative works</li>
                <li>Reverse engineer website atau underlying technology</li>
                <li>Frame atau mirror website content di other sites</li>
                <li>Use automated tools untuk scrape website data</li>
            </ul>

            <h3>Permitted Uses</h3>
            <p>
                Anda MAY use content untuk:
            </p>

            <ul>
                <li><strong>Personal Use:</strong> Print atau save untuk own non-commercial reference</li>
                <li><strong>Social Sharing:</strong> Share links dan posts via social media (dengan proper attribution)</li>
                <li><strong>Reviews:</strong> Use product images dalam genuine reviews (dengan credit ke Dorve.id)</li>
                <li><strong>Editorial:</strong> Fair use dalam news articles atau editorial content (dengan permission)</li>
            </ul>

            <h3>User-Generated Content</h3>
            <p>
                Jika Anda submit content (reviews, photos, comments):
            </p>

            <ul>
                <li>Anda grant Dorve.id royalty-free license untuk use, display, dan promote your content</li>
                <li>Anda warrant bahwa content adalah original dan doesn't infringe third-party rights</li>
                <li>Kami reserve right untuk moderate, edit, atau remove content</li>
            </ul>

            <div class="warning-box">
                <p><strong>⚠️ Copyright Infringement:</strong> Violation dari intellectual property rights dapat result dalam legal action sesuai Indonesian copyright laws.</p>
            </div>
        </section>

        <!-- Section 9: Privacy & Data -->
        <section id="privacy" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🔒</span>
                <h2>Privasi & Perlindungan Data</h2>
            </div>

            <p>
                Your privacy dan security dari personal data adalah extremely important to us. We comply dengan Indonesian data protection regulations.
            </p>

            <h3>Information We Collect</h3>
            <p>
                Kami collect information untuk process orders dan improve service:
            </p>

            <ul>
                <li><strong>Personal Info:</strong> Name, email, phone number</li>
                <li><strong>Shipping Details:</strong> Delivery addresses</li>
                <li><strong>Payment Info:</strong> Transaction data (kami don't store complete credit card numbers)</li>
                <li><strong>Account Data:</strong> Login credentials, purchase history, preferences</li>
                <li><strong>Technical Data:</strong> IP address, browser type, device info, cookies</li>
            </ul>

            <h3>How We Use Your Information</h3>
            <ul>
                <li>Process dan fulfill your orders</li>
                <li>Communicate tentang orders, shipping, customer service</li>
                <li>Send marketing communications (dengan your consent)</li>
                <li>Improve website functionality dan user experience</li>
                <li>Prevent fraud dan enhance security</li>
                <li>Comply dengan legal requirements</li>
            </ul>

            <h3>Data Protection Commitment</h3>
            <p>
                Kami WILL NEVER:
            </p>

            <ul>
                <li><strong>Sell Your Data:</strong> We don't sell personal information ke third parties</li>
                <li><strong>Share Without Consent:</strong> Except dengan service providers necessary untuk operations (shipping, payment)</li>
                <li><strong>Spam You:</strong> Anda can opt-out dari marketing emails anytime</li>
            </ul>

            <div class="info-box">
                <p><strong>🔐 For Complete Details:</strong> Please read our comprehensive <a href="/pages/privacy-policy.php" style="color: var(--charcoal); text-decoration: underline;">Privacy Policy</a> untuk understand how we protect your personal information.</p>
            </div>
        </section>

        <!-- Section 10: Limitation of Liability -->
        <section id="liability" class="legal-section">
            <div class="section-header">
                <span class="section-icon">⚖️</span>
                <h2>Pembatasan Tanggung Jawab</h2>
            </div>

            <p>
                To the fullest extent permitted by applicable law, <strong>Dorve.id</strong> shall NOT be liable untuk:
            </p>

            <h3>Indirect & Consequential Damages</h3>
            <ul>
                <li>Indirect, incidental, special, atau consequential damages</li>
                <li>Loss of profits, revenue, atau business opportunities</li>
                <li>Loss of data atau information</li>
                <li>Personal injury atau property damage (unless caused by our negligence)</li>
            </ul>

            <h3>Website & Service Issues</h3>
            <ul>
                <li><strong>Interruptions:</strong> Service interruptions for maintenance, updates, atau technical issues</li>
                <li><strong>Errors:</strong> Inaccuracies atau errors di website content</li>
                <li><strong>Availability:</strong> Website availability at all times (we strive for 99.9% uptime)</li>
                <li><strong>Compatibility:</strong> Compatibility dengan all devices atau browsers</li>
            </ul>

            <h3>Third-Party Services</h3>
            <p>
                Kami not responsible untuk actions atau failures dari third parties including:
            </p>

            <ul>
                <li><strong>Shipping Delays:</strong> Courier delays beyond our control</li>
                <li><strong>Payment Issues:</strong> Payment gateway downtime atau processing errors</li>
                <li><strong>External Links:</strong> Content di third-party websites</li>
                <li><strong>Force Majeure:</strong> Events beyond reasonable control (natural disasters, pandemics, government actions)</li>
            </ul>

            <h3>Customer Responsibilities</h3>
            <p>
                Kami not liable untuk issues caused by:
            </p>

            <ul>
                <li>Incorrect information provided by customer (wrong address, contact details)</li>
                <li>Failure to read product descriptions atau size guides</li>
                <li>Device incompatibility atau internet connectivity issues</li>
                <li>Unauthorized account access due to customer negligence</li>
            </ul>

            <h3>Maximum Liability</h3>
            <p>
                In any case, our total liability shall not exceed amount yang Anda paid untuk the specific product atau service dalam question.
            </p>

            <div class="warning-box">
                <p><strong>⚠️ Acknowledgment:</strong> By using our services, Anda acknowledge dan accept these limitations of liability.</p>
            </div>
        </section>

        <!-- Section 11: Loyalty Programs -->
        <section id="loyalty" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🎁</span>
                <h2>Program Loyalty & Promosi</h2>
            </div>

            <p>
                <strong>Dorve.id</strong> regularly offers promotions, discounts, reward programs, dan flash sales untuk appreciate loyal customers.
            </p>

            <h3>Reward Points Program</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>How Reward Points Work</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Earning:</strong> Earn 1 point untuk setiap Rp 10.000 spent</li>
                            <li><strong>Redeeming:</strong> 100 points = Rp 10.000 discount voucher</li>
                            <li><strong>Bonus Points:</strong>
                                <ul>
                                    <li>Sign-up bonus: 50 points</li>
                                    <li>Birthday bonus: 100 points</li>
                                    <li>Review bonus: 20 points per review</li>
                                    <li>Referral bonus: 200 points per successful referral</li>
                                </ul>
                            </li>
                            <li><strong>Validity:</strong> Points valid 12 months dari earned date</li>
                            <li><strong>Non-Transferable:</strong> Points cannot be transferred atau exchanged for cash</li>
                        </ul>
                    </div>
                </div>
            </div>

            <h3>Voucher & Promo Codes</h3>
            <p>
                Terms untuk promotional codes:
            </p>

            <ul>
                <li><strong>Expiration:</strong> Each code has specific validity period</li>
                <li><strong>Usage Limits:</strong> One-time use atau limited uses per customer</li>
                <li><strong>Minimum Purchase:</strong> May require minimum transaction amount</li>
                <li><strong>Non-Combinable:</strong> Generally cannot combine multiple codes (unless specified)</li>
                <li><strong>Exclusions:</strong> May not apply to sale items atau certain categories</li>
            </ul>

            <h3>Flash Sales & Special Promotions</h3>
            <p>
                Special terms apply untuk promotional events:
            </p>

            <ul>
                <li>Promotional prices valid only during specified period</li>
                <li>Stock limited dan first-come-first-served basis</li>
                <li>Sale items may have different return policies (store credit only)</li>
                <li>Quantity limits per customer may apply</li>
                <li>We reserve right untuk cancel orders yang violate promotion terms</li>
            </ul>

            <h3>Program Abuse Prevention</h3>
            <p>
                Kami reserve right untuk:
            </p>

            <ul>
                <li>Modify atau discontinue programs at any time</li>
                <li>Void points atau discounts obtained fraudulently</li>
                <li>Limit participation untuk suspected abuse</li>
                <li>Cancel orders yang exploit pricing errors</li>
            </ul>

            <div class="success-box">
                <p><strong>📱 Stay Updated:</strong> Follow kami di Instagram <strong>@dorve.id</strong> untuk first access ke exclusive promos dan flash sales!</p>
            </div>
        </section>

        <!-- Section 12: Governing Law -->
        <section id="law" class="legal-section">
            <div class="section-header">
                <span class="section-icon">⚖️</span>
                <h2>Hukum yang Berlaku & Penyelesaian Sengketa</h2>
            </div>

            <h3>Governing Law</h3>
            <p>
                These Terms & Conditions shall be governed by dan construed in accordance dengan laws of the <strong>Republic of Indonesia</strong>, tanpa regard to conflict of law principles.
            </p>

            <h3>Jurisdiction</h3>
            <p>
                Any disputes arising dari atau relating to these terms shall be subject to exclusive jurisdiction dari courts in <strong>Jakarta, Indonesia</strong>.
            </p>

            <h3>Dispute Resolution Process</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Steps for Dispute Resolution</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ol style="margin: 0; padding-left: 24px;">
                            <li><strong>Direct Communication:</strong> Contact customer service untuk attempt informal resolution</li>
                            <li><strong>Formal Complaint:</strong> Submit written complaint via email dengan details</li>
                            <li><strong>Internal Review:</strong> Management will review dan respond dalam 14 days</li>
                            <li><strong>Mediation:</strong> Jika unresolved, attempt mediation melalui mutually agreed mediator</li>
                            <li><strong>Arbitration:</strong> Binding arbitration di Jakarta sesuai Indonesian arbitration law</li>
                            <li><strong>Legal Action:</strong> As last resort, litigation di appropriate Jakarta court</li>
                        </ol>
                    </div>
                </div>
            </div>

            <h3>Waiver & Severability</h3>
            <ul>
                <li><strong>Waiver:</strong> Failure to enforce any provision doesn't constitute waiver of that provision</li>
                <li><strong>Severability:</strong> Jika any provision found invalid, remaining provisions remain in effect</li>
            </ul>

            <h3>Entire Agreement</h3>
            <p>
                These Terms & Conditions, together dengan our Privacy Policy, constitute entire agreement between Anda dan Dorve.id regarding use of our services.
            </p>
        </section>

        <!-- Section 13: Contact Us -->
        <section id="contact" class="legal-section">
            <div class="section-header">
                <span class="section-icon">📞</span>
                <h2>Hubungi Kami</h2>
            </div>

            <p>
                Jika Anda have questions atau concerns mengenai Terms & Conditions ini, atau need assistance dengan order Anda, please don't hesitate to contact us:
            </p>

            <h3>Customer Service</h3>
            <ul>
                <li>
                    <strong>📧 Email:</strong> support@dorve.id<br>
                    <span style="color: var(--grey); font-size: 14px;">Response time: Within 1-2 business days</span>
                </li>
                <li>
                    <strong>💬 WhatsApp:</strong> +62 813-7737-8859<br>
                    <span style="color: var(--grey); font-size: 14px;">Available: Monday-Friday, 9 AM - 5 PM WIB</span>
                </li>
                <li>
                    <strong>📱 Instagram:</strong> @dorve.id<br>
                    <span style="color: var(--grey); font-size: 14px;">DM us untuk quick questions</span>
                </li>
                <li>
                    <strong>🌐 Website:</strong> https://dorve.id<br>
                    <span style="color: var(--grey); font-size: 14px;">Live chat available di website</span>
                </li>
            </ul>

            <h3>Business Hours</h3>
            <ul>
                <li><strong>Senin - Jumat:</strong> 9:00 AM - 5:00 PM WIB</li>
                <li><strong>Sabtu:</strong> 10:00 AM - 3:00 PM WIB</li>
                <li><strong>Minggu & Public Holidays:</strong> Closed (email responses processed next business day)</li>
            </ul>

            <div class="success-box">
                <p><strong>🚀 Fast Response:</strong> Untuk fastest response, reach us via WhatsApp during business hours. Email inquiries akan responded within 1-2 business days.</p>
            </div>
        </section>

        <!-- Final Thank You -->
        <section class="legal-section" style="border-top: 2px solid var(--latte); padding-top: 60px; margin-top: 80px;">
            <div class="highlight-box">
                <div class="highlight-box-header">
                    <span class="highlight-icon">💝</span>
                    <h4 class="highlight-title">Terima Kasih telah Memilih Dorve.id</h4>
                </div>
                <p>
                    Kami appreciate your trust in <strong>Dorve.id</strong> sebagai <strong>toko baju online terpercaya</strong> Anda. These Terms & Conditions designed untuk protect both you dan us, ensuring fair dan transparent relationship. Kami committed untuk providing excellent products, outstanding service, dan memorable shopping experience. Happy shopping! 🛍️
                </p>
            </div>
        </section>

        <!-- CTA Section -->
        <div class="contact-cta">
            <h3>Siap untuk Berbelanja?</h3>
            <p>Explore koleksi fashion terbaru kami atau contact customer service jika ada pertanyaan</p>
            <div class="contact-buttons">
                <a href="/pages/all-products.php" class="contact-btn">
                    <span>🛍️</span>
                    <span>Browse Collections</span>
                </a>
                <a href="https://wa.me/6281377378859" class="contact-btn contact-btn-outline" target="_blank">
                    <span>💬</span>
                    <span>Chat with Us</span>
                </a>
            </div>
        </div>
    </main>
</div>

<script>
    // Accordion Toggle
    function toggleAccordion(button) {
        const content = button.nextElementSibling;
        const isActive = button.classList.contains('active');

        // Close all accordions in the same section
        const section = button.closest('.legal-section');
        if (section) {
            section.querySelectorAll('.accordion-header').forEach(h => h.classList.remove('active'));
            section.querySelectorAll('.accordion-content').forEach(c => c.classList.remove('active'));
        }

        // Open clicked accordion if it wasn't active
        if (!isActive) {
            button.classList.add('active');
            content.classList.add('active');
        }
    }

    // Table of Contents - Active Link on Scroll
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('.legal-section');
        const tocLinks = document.querySelectorAll('.toc-link');
        
        let currentSection = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 120;
            const sectionHeight = section.clientHeight;
            
            if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
                currentSection = section.getAttribute('id');
            }
        });
        
        tocLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + currentSection) {
                link.classList.add('active');
            }
        });
    });

    // Smooth Scroll for TOC Links
    document.querySelectorAll('.toc-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            
            if (targetSection) {
                window.scrollTo({
                    top: targetSection.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>