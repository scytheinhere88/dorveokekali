<?php
require_once __DIR__ . '/../config.php';

$stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE slug = 'faq' AND is_active = 1 LIMIT 1");
$stmt->execute();
$page = $stmt->fetch();

$page_title = 'FAQ - Pertanyaan Seputar Belanja Baju Online di Dorve.id | Panduan Lengkap';
$page_description = 'Temukan jawaban lengkap seputar cara belanja baju online, pengiriman, pembayaran, return, size guide, dan kebijakan toko di Dorve.id. Panduan belanja fashion wanita & pria yang aman, mudah, dan terpercaya.';
$page_keywords = 'faq dorve.id, panduan belanja online, cara belanja baju, tanya jawab fashion, kebijakan return, pengiriman baju, pembayaran online, size guide baju, customer service fashion, pertanyaan belanja online';
include __DIR__ . '/../includes/header.php';
?>

<!-- Schema Markup for FAQ -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Berapa lama waktu pengiriman pesanan?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Pengiriman standar membutuhkan waktu 3-5 hari kerja untuk area Jakarta dan sekitarnya, serta 5-7 hari kerja untuk kota-kota lain di Indonesia. Kami juga menyediakan opsi express shipping (1-2 hari kerja) untuk pengiriman lebih cepat."
      }
    },
    {
      "@type": "Question",
      "name": "Apakah ada gratis ongkir?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Ya! Kami menawarkan gratis ongkir untuk pembelian di atas Rp 500.000 ke seluruh Indonesia. Untuk pembelian di bawah jumlah tersebut, biaya pengiriman flat Rp 25.000."
      }
    },
    {
      "@type": "Question",
      "name": "Metode pembayaran apa saja yang tersedia?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Kami menerima berbagai metode pembayaran termasuk transfer bank (BCA, Mandiri, BNI, BRI), e-wallet (GoPay, OVO, DANA, ShopeePay), kartu kredit/debit (Visa, Mastercard), QRIS, dan COD (Cash on Delivery) untuk area tertentu."
      }
    }
  ]
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
    .faq-hero {
        background: linear-gradient(135deg, var(--charcoal) 0%, #2D2D2D 100%);
        padding: 120px 24px 80px;
        text-align: center;
        color: var(--white);
    }

    .faq-hero h1 {
        font-family: 'Playfair Display', serif;
        font-size: 56px;
        font-weight: 700;
        margin-bottom: 20px;
        line-height: 1.2;
    }

    .faq-hero p {
        font-size: 18px;
        color: rgba(255,255,255,0.9);
        margin-bottom: 48px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    /* ===== SEARCH BAR ===== */
    .faq-search-container {
        max-width: 600px;
        margin: 0 auto;
        position: relative;
    }

    .faq-search {
        width: 100%;
        padding: 20px 60px 20px 24px;
        font-size: 16px;
        border: 2px solid rgba(255,255,255,0.2);
        border-radius: 50px;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        color: var(--white);
        transition: all 0.3s ease;
    }

    .faq-search:focus {
        outline: none;
        border-color: var(--latte);
        background: rgba(255,255,255,0.15);
    }

    .faq-search::placeholder {
        color: rgba(255,255,255,0.6);
    }

    .search-icon {
        position: absolute;
        right: 24px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        color: rgba(255,255,255,0.6);
        pointer-events: none;
    }

    /* ===== QUICK LINKS ===== */
    .quick-links-section {
        padding: 80px 24px;
        background: var(--white);
    }

    .quick-links-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 24px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .quick-link-card {
        text-align: center;
        padding: 32px 20px;
        background: var(--off-white);
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.4s ease;
        border: 2px solid transparent;
        text-decoration: none;
        color: inherit;
    }

    .quick-link-card:hover {
        background: var(--white);
        border-color: var(--latte);
        transform: translateY(-8px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.1);
    }

    .quick-link-icon {
        font-size: 40px;
        margin-bottom: 16px;
    }

    .quick-link-label {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--charcoal);
    }

    /* ===== FAQ CONTAINER ===== */
    .faq-main-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 80px 24px;
        background: var(--white);
    }

    /* ===== CATEGORY TABS ===== */
    .faq-categories {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-bottom: 60px;
        flex-wrap: wrap;
    }

    .category-btn {
        padding: 14px 32px;
        background: var(--white);
        border: 2px solid rgba(0,0,0,0.1);
        cursor: pointer;
        font-size: 13px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 600;
        border-radius: 50px;
        color: var(--charcoal);
    }

    .category-btn:hover {
        border-color: var(--latte);
        background: var(--off-white);
    }

    .category-btn.active {
        background: var(--charcoal);
        color: var(--white);
        border-color: var(--charcoal);
    }

    /* ===== FAQ SECTIONS ===== */
    .faq-section {
        margin-bottom: 80px;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .faq-section-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 32px;
        padding-bottom: 20px;
        border-bottom: 3px solid var(--latte);
    }

    .faq-section-icon {
        font-size: 40px;
    }

    .faq-section-title {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        color: var(--charcoal);
        margin: 0;
    }

    /* ===== FAQ ITEMS ===== */
    .faq-item {
        margin-bottom: 16px;
        border: 2px solid rgba(0,0,0,0.06);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: var(--white);
    }

    .faq-item:hover {
        border-color: var(--latte);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }

    .faq-question {
        width: 100%;
        padding: 24px 28px;
        background: var(--white);
        border: none;
        text-align: left;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        color: var(--charcoal);
        line-height: 1.5;
    }

    .faq-question:hover {
        background: var(--off-white);
    }

    .faq-question.active {
        background: var(--off-white);
        color: var(--charcoal);
    }

    .faq-arrow {
        width: 32px;
        height: 32px;
        background: var(--latte);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 16px;
        flex-shrink: 0;
        margin-left: 20px;
    }

    .faq-question.active .faq-arrow {
        transform: rotate(180deg);
        background: var(--charcoal);
        color: var(--white);
    }

    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease;
        background: var(--white);
    }

    .faq-answer.active {
        max-height: 800px;
    }

    .faq-answer-content {
        padding: 0 28px 28px;
        line-height: 1.9;
        color: var(--grey);
        font-size: 15px;
    }

    .faq-answer-content strong {
        color: var(--charcoal);
        font-weight: 600;
    }

    .faq-answer-content ul {
        margin: 16px 0;
        padding-left: 24px;
    }

    .faq-answer-content li {
        margin-bottom: 8px;
        line-height: 1.7;
    }

    /* ===== HIGHLIGHT BOX ===== */
    .info-box {
        background: linear-gradient(135deg, #FFF9F5 0%, #FFF5ED 100%);
        border-left: 4px solid var(--latte);
        padding: 20px 24px;
        margin: 16px 0;
        border-radius: 8px;
    }

    .info-box strong {
        color: var(--charcoal);
    }

    /* ===== CTA SECTION ===== */
    .faq-cta-section {
        text-align: center;
        margin-top: 100px;
        padding: 80px 40px;
        background: linear-gradient(135deg, var(--charcoal) 0%, #2D2D2D 100%);
        border-radius: 16px;
        color: var(--white);
    }

    .faq-cta-section h3 {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        margin-bottom: 16px;
    }

    .faq-cta-section p {
        color: rgba(255,255,255,0.9);
        margin-bottom: 32px;
        font-size: 16px;
    }

    .cta-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .cta-btn {
        display: inline-block;
        padding: 18px 40px;
        background: var(--white);
        color: var(--charcoal);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        border-radius: 50px;
        transition: all 0.4s ease;
    }

    .cta-btn:hover {
        background: var(--latte);
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.3);
    }

    .cta-btn-outline {
        background: transparent;
        border: 2px solid var(--white);
        color: var(--white);
    }

    .cta-btn-outline:hover {
        background: var(--white);
        color: var(--charcoal);
    }

    /* ===== NO RESULTS ===== */
    .no-results {
        text-align: center;
        padding: 60px 24px;
        display: none;
    }

    .no-results.show {
        display: block;
    }

    .no-results-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .no-results h3 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        margin-bottom: 12px;
        color: var(--charcoal);
    }

    .no-results p {
        color: var(--grey);
        font-size: 16px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .quick-links-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .faq-hero {
            padding: 80px 24px 60px;
        }

        .faq-hero h1 {
            font-size: 40px;
        }

        .quick-links-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .faq-main-container {
            padding: 60px 24px;
        }

        .category-btn {
            padding: 12px 24px;
            font-size: 11px;
        }

        .faq-section-title {
            font-size: 26px;
        }

        .faq-question {
            font-size: 15px;
            padding: 20px 20px;
        }

        .faq-arrow {
            width: 28px;
            height: 28px;
        }

        .faq-answer-content {
            padding: 0 20px 24px;
        }

        .faq-cta-section {
            padding: 60px 24px;
        }

        .faq-cta-section h3 {
            font-size: 28px;
        }

        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }

        .cta-btn {
            width: 100%;
            max-width: 280px;
        }
    }

    @media (max-width: 480px) {
        .faq-hero h1 {
            font-size: 32px;
        }

        .quick-links-grid {
            grid-template-columns: 1fr;
        }

        .faq-section-icon {
            font-size: 32px;
        }
    }
</style>

<!-- Hero Section with Search -->
<section class="faq-hero">
    <h1>Frequently Asked Questions</h1>
    <p>Temukan jawaban untuk pertanyaan Anda tentang belanja fashion di Dorve.id</p>
    
    <div class="faq-search-container">
        <input type="text" 
               class="faq-search" 
               id="faqSearch" 
               placeholder="Cari pertanyaan Anda disini..."
               onkeyup="searchFAQ()">
        <span class="search-icon">🔍</span>
    </div>
</section>

<!-- Quick Links -->
<section class="quick-links-section">
    <div class="quick-links-grid">
        <a href="#" class="quick-link-card" onclick="filterCategory('order'); return false;">
            <div class="quick-link-icon">📦</div>
            <div class="quick-link-label">Cara Order</div>
        </a>
        <a href="#" class="quick-link-card" onclick="filterCategory('shipping'); return false;">
            <div class="quick-link-icon">🚚</div>
            <div class="quick-link-label">Pengiriman</div>
        </a>
        <a href="#" class="quick-link-card" onclick="filterCategory('payment'); return false;">
            <div class="quick-link-icon">💳</div>
            <div class="quick-link-label">Pembayaran</div>
        </a>
        <a href="#" class="quick-link-card" onclick="filterCategory('size'); return false;">
            <div class="quick-link-icon">📏</div>
            <div class="quick-link-label">Size Guide</div>
        </a>
        <a href="#" class="quick-link-card" onclick="filterCategory('returns'); return false;">
            <div class="quick-link-icon">🔄</div>
            <div class="quick-link-label">Return</div>
        </a>
        <a href="#" class="quick-link-card" onclick="filterCategory('account'); return false;">
            <div class="quick-link-icon">👤</div>
            <div class="quick-link-label">Akun</div>
        </a>
    </div>
</section>

<!-- FAQ Container -->
<div class="faq-main-container">
    <!-- Category Filters -->
    <div class="faq-categories">
        <button class="category-btn active" onclick="filterCategory('all')">Semua</button>
        <button class="category-btn" onclick="filterCategory('order')">Cara Order</button>
        <button class="category-btn" onclick="filterCategory('shipping')">Pengiriman</button>
        <button class="category-btn" onclick="filterCategory('payment')">Pembayaran</button>
        <button class="category-btn" onclick="filterCategory('size')">Size Guide</button>
        <button class="category-btn" onclick="filterCategory('returns')">Return & Tukar</button>
        <button class="category-btn" onclick="filterCategory('products')">Produk</button>
        <button class="category-btn" onclick="filterCategory('account')">Akun</button>
    </div>

    <!-- CARA ORDER SECTION -->
    <div class="faq-section" data-category="order">
        <div class="faq-section-header">
            <div class="faq-section-icon">🛍️</div>
            <h2 class="faq-section-title">Cara Order & Belanja</h2>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara order di Dorve.id?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Belanja di <strong>Dorve.id</strong> sangat mudah! Ikuti langkah berikut:</p>
                    <ul>
                        <li><strong>Browse Produk:</strong> Kunjungi halaman kategori atau gunakan search bar untuk menemukan <strong>baju kekinian</strong> yang Anda inginkan</li>
                        <li><strong>Pilih Size & Warna:</strong> Klik produk untuk melihat detail, pilih size dan warna yang sesuai</li>
                        <li><strong>Add to Cart:</strong> Klik "Tambah ke Keranjang" dan lanjutkan shopping atau langsung checkout</li>
                        <li><strong>Checkout:</strong> Review pesanan Anda, isi alamat pengiriman, pilih metode pengiriman</li>
                        <li><strong>Pembayaran:</strong> Pilih metode pembayaran dan selesaikan transaksi</li>
                        <li><strong>Konfirmasi:</strong> Anda akan menerima email konfirmasi pesanan dengan detail lengkap</li>
                    </ul>
                    <div class="info-box">
                        <strong>💡 Tips:</strong> Pastikan Anda sudah login atau buat akun untuk tracking pesanan yang lebih mudah dan mendapatkan member benefits!
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah harus membuat akun untuk berbelanja?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Tidak wajib, Anda bisa checkout sebagai guest. Namun kami sangat merekomendasikan membuat akun karena Anda akan mendapatkan:</p>
                    <ul>
                        <li>Order tracking yang mudah</li>
                        <li>Order history untuk re-order produk favorit</li>
                        <li>Wishlist untuk save produk yang Anda suka</li>
                        <li>Early access ke sale dan promo eksklusif member</li>
                        <li>Reward points setiap pembelian</li>
                        <li>Checkout yang lebih cepat (alamat tersimpan)</li>
                    </ul>
                    <p>Membuat akun gratis dan hanya membutuhkan waktu 1 menit!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah ada minimum order?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Tidak ada minimum order di <strong>Dorve.id</strong>! Anda bisa membeli 1 item atau lebih sesuai kebutuhan. Namun, kami menawarkan <strong>gratis ongkir untuk pembelian di atas Rp 500.000</strong>, jadi belanja lebih banyak = lebih hemat!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara menggunakan voucher/promo code?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Sangat mudah! Saat checkout:</p>
                    <ul>
                        <li>Cari kolom "Masukkan Kode Promo" atau "Voucher Code"</li>
                        <li>Ketik atau paste kode promo Anda</li>
                        <li>Klik "Apply" atau "Gunakan"</li>
                        <li>Diskon akan otomatis teraplikasi ke total belanja Anda</li>
                    </ul>
                    <div class="info-box">
                        <strong>📌 Catatan:</strong> Setiap kode promo memiliki terms & conditions berbeda (minimum purchase, masa berlaku, produk tertentu). Pastikan membaca detail promo sebelum digunakan.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SHIPPING SECTION -->
    <div class="faq-section" data-category="shipping">
        <div class="faq-section-header">
            <div class="faq-section-icon">🚚</div>
            <h2 class="faq-section-title">Pengiriman & Delivery</h2>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Berapa lama waktu pengiriman pesanan?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Estimasi waktu pengiriman bergantung pada lokasi dan jenis layanan:</p>
                    <ul>
                        <li><strong>Standard Shipping (3-5 hari kerja):</strong> Jakarta & sekitarnya (Bogor, Depok, Tangerang, Bekasi)</li>
                        <li><strong>Standard Shipping (5-7 hari kerja):</strong> Kota-kota besar lainnya di Jawa</li>
                        <li><strong>Standard Shipping (7-10 hari kerja):</strong> Luar Jawa (Sumatera, Kalimantan, Sulawesi, dll)</li>
                        <li><strong>Express Shipping (1-2 hari kerja):</strong> Jakarta & Jabodetabek (additional fee)</li>
                        <li><strong>Same Day Delivery:</strong> Tersedia untuk area tertentu di Jakarta (order sebelum jam 12 siang)</li>
                    </ul>
                    <div class="info-box">
                        <strong>⏰ Processing Time:</strong> Pesanan akan diproses dalam 1-2 hari kerja sebelum dikirim. Untuk pre-order items, processing time mungkin lebih lama (akan dijelaskan di product page).
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah ada gratis ongkir?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Ya! Kami menawarkan <strong>FREE SHIPPING</strong> dengan ketentuan berikut:</p>
                    <ul>
                        <li><strong>Gratis Ongkir Rp 500.000+:</strong> Untuk pembelian di atas Rp 500.000 ke SELURUH INDONESIA</li>
                        <li><strong>Flash Sale Free Shipping:</strong> Sesekali ada promo gratis ongkir tanpa minimum purchase (follow IG kami @dorve.id)</li>
                        <li><strong>Member Exclusive:</strong> Member tertentu mendapat benefit gratis ongkir dengan minimum purchase lebih rendah</li>
                    </ul>
                    <p>Untuk pembelian di bawah Rp 500.000, ongkir flat Rp 25.000 untuk seluruh Indonesia.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara tracking pesanan saya?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Tracking pesanan sangat mudah:</p>
                    <ul>
                        <li><strong>Via Email:</strong> Setelah pesanan dikirim, Anda akan menerima email dengan tracking number dan link tracking</li>
                        <li><strong>Via Account Dashboard:</strong> Login ke akun Anda → "My Orders" → Klik order yang ingin di-track</li>
                        <li><strong>Via WhatsApp:</strong> Kirim pesan ke customer service kami dengan nomor order Anda</li>
                    </ul>
                    <p>Real-time tracking memungkinkan Anda melihat posisi paket Anda dari warehouse hingga di depan pintu rumah!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Ke mana saja kalian bisa mengirim?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Kami mengirim ke <strong>SELURUH INDONESIA</strong>! Dari Sabang sampai Merauke, dari Jakarta hingga Papua. Bekerja sama dengan ekspedisi terpercaya seperti JNE, J&T, SiCepat, dan AnterAja untuk memastikan <strong>baju online</strong> Anda sampai dengan aman.</p>
                    <div class="info-box">
                        <strong>🌏 International Shipping:</strong> Saat ini kami fokus untuk melayani customer di Indonesia. Untuk pembelian dari luar negeri, silakan hubungi customer service kami untuk discuss options.
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana jika paket tidak sampai atau rusak?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Kami sangat concern tentang kondisi paket Anda:</p>
                    <ul>
                        <li><strong>Paket Tidak Sampai:</strong> Jika paket tidak sampai dalam estimasi waktu + 3 hari, hubungi customer service kami. Kami akan investigate dan replace atau refund 100%</li>
                        <li><strong>Paket Rusak/Damage:</strong> Foto kondisi paket dan produk, kirim ke CS kami dalam 1x24 jam setelah terima paket. Kami akan replace dengan produk baru atau refund</li>
                        <li><strong>Produk Salah:</strong> Jika Anda menerima produk yang berbeda dari orderan, kami akan mengirim produk yang benar tanpa biaya tambahan</li>
                    </ul>
                    <p>Kepuasan dan keamanan berbelanja Anda adalah prioritas kami!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYMENT SECTION -->
    <div class="faq-section" data-category="payment">
        <div class="faq-section-header">
            <div class="faq-section-icon">💳</div>
            <h2 class="faq-section-title">Metode Pembayaran</h2>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Metode pembayaran apa saja yang diterima?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Kami menerima berbagai metode pembayaran untuk kemudahan Anda:</p>
                    <ul>
                        <li><strong>Transfer Bank:</strong> BCA, Mandiri, BNI, BRI, CIMB Niaga, Permata</li>
                        <li><strong>E-Wallet:</strong> GoPay, OVO, DANA, ShopeePay, LinkAja</li>
                        <li><strong>Kartu Kredit/Debit:</strong> Visa, Mastercard, JCB, American Express</li>
                        <li><strong>QRIS:</strong> Scan QR code untuk pembayaran instant</li>
                        <li><strong>COD (Cash on Delivery):</strong> Bayar tunai saat barang sampai (area tertentu)</li>
                        <li><strong>Cicilan 0%:</strong> Tersedia untuk kartu kredit tertentu (min. purchase berlaku)</li>
                    </ul>
                    <div class="info-box">
                        <strong>🔒 Keamanan:</strong> Semua transaksi diproses melalui payment gateway terenkripsi. Data pembayaran Anda 100% aman!
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah data pembayaran saya aman?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Absolutely! Keamanan data Anda adalah prioritas utama:</p>
                    <ul>
                        <li>Semua transaksi menggunakan <strong>SSL encryption 256-bit</strong></li>
                        <li>Kami bekerja sama dengan payment gateway tersertifikasi PCI-DSS</li>
                        <li>Kami <strong>TIDAK PERNAH</strong> menyimpan data kartu kredit lengkap di server kami</li>
                        <li>Token payment digunakan untuk keamanan maksimal</li>
                        <li>Two-factor authentication untuk transaksi tertentu</li>
                    </ul>
                    <p>Belanja di <strong>Dorve.id</strong> sama amannya dengan belanja di bank!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Berapa lama konfirmasi pembayaran?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Waktu konfirmasi bervariasi tergantung metode:</p>
                    <ul>
                        <li><strong>E-Wallet & Credit Card:</strong> Instant / Real-time</li>
                        <li><strong>QRIS:</strong> Instant / Real-time</li>
                        <li><strong>Transfer Bank:</strong> Otomatis dalam 5-15 menit (sistem kami cek otomatis)</li>
                        <li><strong>Virtual Account:</strong> Real-time setelah Anda transfer</li>
                    </ul>
                    <p>Anda akan menerima email konfirmasi pembayaran segera setelah payment berhasil diverifikasi. Jika lebih dari 1 jam belum ada konfirmasi, silakan hubungi customer service kami.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara kerja COD (Cash on Delivery)?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>COD memungkinkan Anda bayar tunai saat barang sampai:</p>
                    <ul>
                        <li><strong>Availability:</strong> Tersedia untuk Jakarta, Bogor, Depok, Tangerang, Bekasi (Jabodetabek)</li>
                        <li><strong>Fee:</strong> Additional fee Rp 10.000 untuk layanan COD</li>
                        <li><strong>Maximum:</strong> COD tersedia untuk purchase hingga Rp 2.000.000</li>
                        <li><strong>Payment:</strong> Bayar exact amount atau siapkan uang pas kepada kurir</li>
                        <li><strong>Checking:</strong> Anda bisa cek kondisi paket (tidak boleh buka packaging) sebelum bayar</li>
                    </ul>
                    <div class="info-box">
                        <strong>⚠️ Important:</strong> Jika Anda reject paket tanpa alasan valid, account Anda mungkin diblokir untuk future COD orders.
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah tersedia cicilan 0%?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Ya! Kami bekerja sama dengan berbagai bank untuk cicilan 0%:</p>
                    <ul>
                        <li><strong>BCA:</strong> 3, 6, 12 bulan (min. Rp 1.000.000)</li>
                        <li><strong>Mandiri:</strong> 3, 6, 12 bulan (min. Rp 1.000.000)</li>
                        <li><strong>BNI:</strong> 3, 6, 9, 12 bulan (min. Rp 500.000)</li>
                        <li><strong>CIMB Niaga:</strong> 3, 6, 12 bulan (min. Rp 1.000.000)</li>
                    </ul>
                    <p>Pilih opsi "Installment" saat checkout dan pilih bank & tenor yang diinginkan. Terms & conditions sesuai dengan kebijakan masing-masing bank.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SIZE GUIDE SECTION -->
    <div class="faq-section" data-category="size">
        <div class="faq-section-header">
            <div class="faq-section-icon">📏</div>
            <h2 class="faq-section-title">Size Guide & Fitting</h2>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara menentukan size yang tepat?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Untuk mendapatkan size yang perfect fit:</p>
                    <ul>
                        <li><strong>Check Size Chart:</strong> Setiap produk memiliki size chart detail di product page</li>
                        <li><strong>Ukur Badan Anda:</strong> Gunakan measuring tape untuk ukur bust, waist, hip, dan panjang</li>
                        <li><strong>Compare:</strong> Bandingkan measurement Anda dengan size chart kami</li>
                        <li><strong>Read Reviews:</strong> Customer reviews sering include feedback tentang sizing (run small/large/true to size)</li>
                        <li><strong>Ask Us:</strong> Jika ragu, hubungi customer service dengan detail measurement Anda, kami akan recommend size yang tepat</li>
                    </ul>
                    <div class="info-box">
                        <strong>💡 Pro Tip:</strong> Jika Anda between sizes, kami recommend size up untuk comfort, atau size down jika Anda prefer fitted look.
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah size Dorve.id standard/sesuai?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Sebagian besar produk kami <strong>true to size</strong> (sesuai ukuran standar Indonesia). Namun:</p>
                    <ul>
                        <li>Oversized items akan clearly labeled "Oversized Fit"</li>
                        <li>Slim fit atau fitted items akan ada note "Slim Fit" atau "Fitted"</li>
                        <li>Setiap product page include fit description (Regular, Loose, Fitted, etc.)</li>
                        <li>Model specifications (height & weight) included untuk reference</li>
                    </ul>
                    <p>Kami always provide measurement details jadi Anda bisa confident dengan pilihan size Anda!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara mengukur badan dengan benar?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Follow langkah ini untuk measurement yang akurat:</p>
                    <ul>
                        <li><strong>Bust/Dada:</strong> Ukur bagian terfullest dari bust, measuring tape harus parallel dengan lantai</li>
                        <li><strong>Waist/Pinggang:</strong> Ukur bagian tersempit dari waist (biasanya 1-2 inch di atas belly button)</li>
                        <li><strong>Hip/Pinggul:</strong> Ukur bagian terfullest dari hip dan buttocks</li>
                        <li><strong>Shoulder Width:</strong> Ukur dari ujung bahu kiri ke ujung bahu kanan (belakang)</li>
                        <li><strong>Sleeve Length:</strong> Dari shoulder seam sampai wrist dengan lengan slightly bent</li>
                    </ul>
                    <p>Video tutorial cara mengukur tersedia di page Size Guide kami!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana jika saya salah pilih size?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Tidak masalah! Kami menyediakan <strong>FREE SIZE EXCHANGE</strong>:</p>
                    <ul>
                        <li>Exchange ke size berbeda dalam 14 hari setelah terima barang</li>
                        <li>Produk harus unworn, unwashed, dengan tag masih attached</li>
                        <li>Kami cover ongkir exchange (untuk size exchange pertama)</li>
                        <li>Process exchange membutuhkan 7-10 hari kerja</li>
                    </ul>
                    <p>Untuk request exchange, hubungi customer service atau submit via account dashboard Anda.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- RETURNS SECTION -->
    <div class="faq-section" data-category="returns">
        <div class="faq-section-header">
            <div class="faq-section-icon">🔄</div>
            <h2 class="faq-section-title">Return & Exchange</h2>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apa kebijakan return Dorve.id?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Kami menawarkan <strong>14-Day Return Policy</strong>:</p>
                    <ul>
                        <li><strong>Timeframe:</strong> 14 hari dari tanggal Anda terima produk</li>
                        <li><strong>Condition:</strong> Item must be unworn, unwashed, dengan original tags attached</li>
                        <li><strong>Return Shipping:</strong> 
                            <ul>
                                <li>FREE untuk defective items atau kesalahan dari kami</li>
                                <li>Customer cover shipping untuk "change of mind" returns</li>
                            </ul>
                        </li>
                        <li><strong>Refund:</strong> Diproses dalam 5-7 hari kerja setelah kami terima returned item</li>
                        <li><strong>Refund Method:</strong> Ke payment method original atau store credit (pilihan Anda)</li>
                    </ul>
                    <div class="info-box">
                        <strong>❌ Non-Returnable Items:</strong> Underwear, swimwear, earrings, dan sale items marked "Final Sale" tidak dapat di-return untuk hygiene reasons.
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara return produk?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Return process sangat simple:</p>
                    <ul>
                        <li><strong>Step 1:</strong> Login ke akun → My Orders → Select order → Click "Request Return"</li>
                        <li><strong>Step 2:</strong> Pilih items yang ingin di-return dan reason for return</li>
                        <li><strong>Step 3:</strong> Kami akan review request (biasanya dalam 24 jam)</li>
                        <li><strong>Step 4:</strong> Setelah approved, Anda akan receive return label & instructions via email</li>
                        <li><strong>Step 5:</strong> Pack item securely, attach return label, kirim via kurir</li>
                        <li><strong>Step 6:</strong> Track return shipment melalui dashboard</li>
                        <li><strong>Step 7:</strong> Refund diproses setelah kami receive & inspect item</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah bisa tukar produk (exchange)?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Tentu saja! Exchange policy kami:</p>
                    <ul>
                        <li><strong>Size Exchange:</strong> FREE exchange untuk different size (dalam 14 hari)</li>
                        <li><strong>Color Exchange:</strong> Exchange ke warna berbeda (same item) dalam 14 hari</li>
                        <li><strong>Different Item:</strong> Anda bisa return item dan order new one separately</li>
                        <li><strong>Availability:</strong> Exchange subject to stock availability</li>
                    </ul>
                    <p>Process exchange sama seperti return, tapi pilih "Exchange" dan specify size/color yang diinginkan.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana jika produk defect/rusak/salah kirim?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Kami sangat apologize jika ini terjadi. Berikut yang harus dilakukan:</p>
                    <ul>
                        <li><strong>Report Immediately:</strong> Dalam 1x24 jam setelah terima paket</li>
                        <li><strong>Photo Evidence:</strong> Ambil foto clear dari defect/damage/wrong item</li>
                        <li><strong>Contact CS:</strong> Via WhatsApp/email dengan order number & photos</li>
                        <li><strong>Resolution Options:</strong>
                            <ul>
                                <li>FREE replacement dengan produk baru</li>
                                <li>Full refund (including shipping cost)</li>
                                <li>Store credit dengan bonus (pilihan Anda)</li>
                            </ul>
                        </li>
                    </ul>
                    <p>Untuk defect items, kami cover semua return shipping costs!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUCTS SECTION -->
    <div class="faq-section" data-category="products">
        <div class="faq-section-header">
            <div class="faq-section-icon">👗</div>
            <h2 class="faq-section-title">Tentang Produk</h2>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah semua produk 100% authentic/asli?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p><strong>100% AUTHENTIC GUARANTEED!</strong> Semua produk <strong>Dorve.id</strong> adalah:</p>
                    <ul>
                        <li>Original designs dari brand kami sendiri</li>
                        <li>Carefully crafted dengan quality materials</li>
                        <li>Melalui strict quality control sebelum shipping</li>
                        <li>Dilengkapi dengan certificate of authenticity untuk certain items</li>
                    </ul>
                    <p>Kami adalah <strong>brand fashion Indonesia</strong> yang proud dengan products kami. Kepercayaan Anda adalah yang paling penting!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Material apa yang digunakan untuk produk?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Kami menggunakan berbagai premium materials:</p>
                    <ul>
                        <li><strong>Cotton:</strong> 100% cotton atau cotton blend untuk breathability & comfort</li>
                        <li><strong>Linen:</strong> Natural linen untuk lightweight & elegant pieces</li>
                        <li><strong>Silk:</strong> Silk atau silk blend untuk luxury items</li>
                        <li><strong>Polyester:</strong> High-quality polyester untuk durability & easy care</li>
                        <li><strong>Denim:</strong> Premium denim fabric untuk jeans & jackets</li>
                        <li><strong>Sustainable Fabrics:</strong> Organic cotton, Tencel, recycled materials</li>
                    </ul>
                    <p>Material details & composition tercantum di setiap product page!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara merawat produk Dorve.id?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Care instructions berbeda per product, tapi generally:</p>
                    <ul>
                        <li><strong>Washing:</strong> Hand wash atau machine wash gentle cycle dalam cold water</li>
                        <li><strong>Detergent:</strong> Gunakan mild detergent, avoid bleach</li>
                        <li><strong>Drying:</strong> Air dry flat atau hang dry (avoid direct sunlight)</li>
                        <li><strong>Ironing:</strong> Low to medium heat, inside out untuk printed items</li>
                        <li><strong>Storage:</strong> Fold atau hang dengan proper hangers, avoid damp places</li>
                    </ul>
                    <p>Detailed care instructions included dengan setiap purchase dan di hang tag produk!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apakah sold out items akan restock?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Tergantung pada produknya:</p>
                    <ul>
                        <li><strong>Core Collection:</strong> Popular items usually di-restock regularly</li>
                        <li><strong>Limited Edition:</strong> May not restock once sold out</li>
                        <li><strong>Seasonal Items:</strong> Might return next season dengan possible updates</li>
                    </ul>
                    <p>Untuk tau jika item akan restock, klik "Notify Me" button di product page. Anda akan receive email begitu item available kembali!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ACCOUNT SECTION -->
    <div class="faq-section" data-category="account">
        <div class="faq-section-header">
            <div class="faq-section-icon">👤</div>
            <h2 class="faq-section-title">Account & Membership</h2>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara membuat akun?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Membuat akun di <strong>Dorve.id</strong> gratis dan mudah:</p>
                    <ul>
                        <li>Klik "Sign Up" atau "Register" di top right corner</li>
                        <li>Isi nama, email, dan password</li>
                        <li>Atau sign up via Google/Facebook untuk process yang lebih cepat</li>
                        <li>Verify email Anda via link yang kami kirim</li>
                        <li>Done! Anda siap belanja</li>
                    </ul>
                    <p>Dengan akun, Anda get member benefits, order tracking, wishlist, dan exclusive offers!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Lupa password, bagaimana reset?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Tidak masalah! Follow langkah ini:</p>
                    <ul>
                        <li>Klik "Login" → "Forgot Password"</li>
                        <li>Enter email address yang terdaftar</li>
                        <li>Check email untuk password reset link</li>
                        <li>Click link dan create password baru</li>
                        <li>Login dengan password baru Anda</li>
                    </ul>
                    <p>Jika tidak menerima email dalam 10 menit, check spam folder atau contact customer service.</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Apa itu Reward Points dan bagaimana cara kerjanya?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Reward Points adalah loyalty program kami:</p>
                    <ul>
                        <li><strong>Earn Points:</strong> Setiap Rp 10.000 spent = 10 points</li>
                        <li><strong>Redeem:</strong> 100 points = Rp 10.000 discount voucher</li>
                        <li><strong>Bonus Points:</strong> Birthday bonus, review bonus, referral bonus</li>
                        <li><strong>Expiry:</strong> Points valid 12 bulan dari earned date</li>
                        <li><strong>Check Balance:</strong> Login ke account dashboard untuk check points balance</li>
                    </ul>
                    <p>Semakin sering belanja, semakin banyak benefits yang Anda dapatkan!</p>
                </div>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                <span>Bagaimana cara mengubah informasi akun?</span>
                <span class="faq-arrow">▼</span>
            </button>
            <div class="faq-answer">
                <div class="faq-answer-content">
                    <p>Update info akun sangat mudah:</p>
                    <ul>
                        <li>Login → Account Dashboard → "Account Settings"</li>
                        <li>Anda bisa update: Name, Email, Phone, Password</li>
                        <li>Update shipping addresses di "Address Book"</li>
                        <li>Set email preferences di "Communication Preferences"</li>
                    </ul>
                    <p>Jangan lupa klik "Save Changes" setelah update!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- No Results Message -->
    <div class="no-results" id="noResults">
        <div class="no-results-icon">🔍</div>
        <h3>Tidak ada hasil ditemukan</h3>
        <p>Coba gunakan kata kunci lain atau hubungi customer service kami</p>
    </div>

    <!-- CTA Section -->
    <div class="faq-cta-section">
        <h3>Masih Punya Pertanyaan?</h3>
        <p>Tim customer service kami siap membantu Anda 24/7. Jangan ragu untuk menghubungi kami!</p>
        <div class="cta-buttons">
            <a href="https://wa.me/6281377378859" class="cta-btn" target="_blank">WhatsApp Kami</a>
            <a href="/pages/contact.php" class="cta-btn cta-btn-outline">Contact Form</a>
        </div>
    </div>
</div>

<script>
    function toggleFaq(button) {
        const answer = button.nextElementSibling;
        const isActive = button.classList.contains('active');

        // Close all other FAQs
        document.querySelectorAll('.faq-question').forEach(q => q.classList.remove('active'));
        document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('active'));

        // Open clicked FAQ if it wasn't active
        if (!isActive) {
            button.classList.add('active');
            answer.classList.add('active');
            
            // Smooth scroll to question
            setTimeout(() => {
                button.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 300);
        }
    }

    function filterCategory(category) {
        // Update active button
        document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        // Filter sections
        const sections = document.querySelectorAll('.faq-section');
        let visibleCount = 0;

        sections.forEach(section => {
            if (category === 'all' || section.dataset.category === category) {
                section.style.display = 'block';
                visibleCount++;
            } else {
                section.style.display = 'none';
            }
        });

        // Show/hide no results message
        document.getElementById('noResults').classList.toggle('show', visibleCount === 0);

        // Scroll to top of FAQ container
        document.querySelector('.faq-main-container').scrollIntoView({ behavior: 'smooth' });
    }

    function searchFAQ() {
        const searchTerm = document.getElementById('faqSearch').value.toLowerCase();
        const sections = document.querySelectorAll('.faq-section');
        let foundResults = false;

        // If search is empty, show all
        if (searchTerm === '') {
            sections.forEach(section => section.style.display = 'block');
            document.querySelectorAll('.faq-item').forEach(item => item.style.display = 'block');
            document.getElementById('noResults').classList.remove('show');
            return;
        }

        // Search through all FAQ items
        sections.forEach(section => {
            const items = section.querySelectorAll('.faq-item');
            let sectionHasResults = false;

            items.forEach(item => {
                const question = item.querySelector('.faq-question span').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer-content').textContent.toLowerCase();

                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                    sectionHasResults = true;
                    foundResults = true;
                } else {
                    item.style.display = 'none';
                }
            });

            section.style.display = sectionHasResults ? 'block' : 'none';
        });

        // Show/hide no results message
        document.getElementById('noResults').classList.toggle('show', !foundResults);
    }

    // Close all FAQs on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('active'));
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>