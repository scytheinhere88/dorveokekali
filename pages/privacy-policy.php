<?php
require_once __DIR__ . '/../config.php';

$stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE slug = 'privacy-policy' AND is_active = 1 LIMIT 1");
$stmt->execute();
$page = $stmt->fetch();

$page_title = 'Kebijakan Privasi Dorve.id - Perlindungan Data & Keamanan Transaksi Pelanggan';
$page_description = 'Dorve.id berkomitmen melindungi privasi dan keamanan data pribadi Anda. Pelajari bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda saat berbelanja fashion online dengan transparansi penuh.';
$page_keywords = 'kebijakan privasi dorve.id, perlindungan data pribadi, keamanan transaksi online, privasi pelanggan, belanja aman, enkripsi data, gdpr compliance, data protection policy';
include __DIR__ . '/../includes/header.php';
?>

<!-- Schema Markup for Legal Page -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Kebijakan Privasi - Dorve.id",
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
        <div class="legal-badge">🔒 Legal Document</div>
        <h1>Kebijakan Privasi</h1>
        <p>Komitmen kami untuk melindungi privasi dan keamanan data pribadi Anda</p>
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
            <li class="toc-item"><a href="#introduction" class="toc-link">Pendahuluan</a></li>
            <li class="toc-item"><a href="#data-collection" class="toc-link">Informasi yang Dikumpulkan</a></li>
            <li class="toc-item"><a href="#data-usage" class="toc-link">Penggunaan Informasi</a></li>
            <li class="toc-item"><a href="#data-sharing" class="toc-link">Pembagian Data</a></li>
            <li class="toc-item"><a href="#data-security" class="toc-link">Keamanan Data</a></li>
            <li class="toc-item"><a href="#cookies" class="toc-link">Kebijakan Cookie</a></li>
            <li class="toc-item"><a href="#your-rights" class="toc-link">Hak Anda</a></li>
            <li class="toc-item"><a href="#third-party" class="toc-link">Layanan Pihak Ketiga</a></li>
            <li class="toc-item"><a href="#children" class="toc-link">Privasi Anak</a></li>
            <li class="toc-item"><a href="#changes" class="toc-link">Perubahan Kebijakan</a></li>
            <li class="toc-item"><a href="#contact" class="toc-link">Hubungi Kami</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="legal-content">
        <!-- Section 1: Introduction -->
        <section id="introduction" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🛡️</span>
                <h2>Pendahuluan</h2>
            </div>
            
            <p>
                Selamat datang di <strong>Dorve.id</strong>. Kami memahami bahwa privasi dan keamanan data pribadi Anda adalah hal yang sangat penting. Kepercayaan yang Anda berikan kepada kami merupakan aset paling berharga dalam menjalankan bisnis <strong>fashion online Indonesia</strong> ini.
            </p>

            <p>
                Dokumen Kebijakan Privasi ini dibuat untuk menjelaskan secara transparan dan komprehensif bagaimana <strong>Dorve.id</strong> mengumpulkan, menggunakan, menyimpan, dan melindungi data pribadi Anda ketika:
            </p>

            <ul>
                <li>Mengunjungi dan menjelajahi website <strong>Dorve.id</strong></li>
                <li>Membuat akun dan menjadi member kami</li>
                <li>Melakukan transaksi pembelian produk <strong>fashion pria</strong>, <strong>fashion wanita</strong>, atau <strong>fashion unisex</strong></li>
                <li>Berlangganan newsletter dan komunikasi marketing kami</li>
                <li>Berinteraksi dengan customer service atau layanan kami lainnya</li>
            </ul>

            <div class="highlight-box">
                <div class="highlight-box-header">
                    <span class="highlight-icon">✅</span>
                    <h4 class="highlight-title">Komitmen Kami</h4>
                </div>
                <p>
                    Dengan mengakses dan menggunakan layanan <strong>Dorve.id</strong>, Anda menyetujui praktik pengelolaan data yang dijelaskan dalam kebijakan ini. Kami berkomitmen penuh untuk mematuhi peraturan perlindungan data pribadi yang berlaku di Indonesia, termasuk UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi.
                </p>
            </div>

            <p>
                Kami mengundang Anda untuk membaca kebijakan ini dengan seksama. Jika Anda memiliki pertanyaan atau kekhawatiran mengenai praktik privasi kami, jangan ragu untuk menghubungi Tim Perlindungan Data kami melalui informasi kontak yang tersedia di bagian akhir dokumen ini.
            </p>
        </section>

        <!-- Section 2: Data Collection -->
        <section id="data-collection" class="legal-section">
            <div class="section-header">
                <span class="section-icon">📋</span>
                <h2>Informasi yang Kami Kumpulkan</h2>
            </div>

            <p>
                Untuk memberikan layanan <strong>belanja baju online</strong> terbaik dan memproses pesanan Anda secara akurat dan efisien, kami mengumpulkan beberapa kategori informasi. Berikut penjelasan detail mengenai jenis data yang kami kumpulkan:
            </p>

            <h3>2.1 Informasi Pribadi yang Anda Berikan</h3>
            <p>
                Data ini Anda berikan secara sukarela saat mendaftar akun, melakukan checkout, atau berinteraksi dengan layanan kami:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Data Identitas & Kontak</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Nama Lengkap:</strong> Untuk personalisasi dan identifikasi akun Anda</li>
                            <li><strong>Email Address:</strong> Untuk komunikasi, konfirmasi order, dan newsletter</li>
                            <li><strong>Nomor Telepon/WhatsApp:</strong> Untuk konfirmasi pesanan dan koordinasi pengiriman</li>
                            <li><strong>Tanggal Lahir (Opsional):</strong> Untuk special birthday offers</li>
                            <li><strong>Jenis Kelamin (Opsional):</strong> Untuk personalisasi rekomendasi produk</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Data Alamat & Pengiriman</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Alamat Lengkap:</strong> Jalan, RT/RW, Kelurahan, Kecamatan</li>
                            <li><strong>Kota/Kabupaten & Provinsi:</strong> Untuk kalkulasi ongkir</li>
                            <li><strong>Kode Pos:</strong> Untuk akurasi pengiriman</li>
                            <li><strong>Patokan/Landmark:</strong> Memudahkan kurir menemukan lokasi</li>
                            <li><strong>Instruksi Khusus:</strong> Catatan untuk pengiriman (opsional)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Data Transaksi & Pembayaran</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Order History:</strong> Produk yang dibeli, jumlah, dan harga</li>
                            <li><strong>Metode Pembayaran:</strong> Bank transfer, e-wallet, credit card (token only)</li>
                            <li><strong>Transaction ID & Invoice:</strong> Untuk reference dan customer service</li>
                            <li><strong>Refund/Return Data:</strong> Jika ada retur atau pengembalian dana</li>
                        </ul>
                        <div class="success-box">
                            <p><strong>🔒 Keamanan Pembayaran:</strong> Kami TIDAK menyimpan informasi kartu kredit lengkap (nomor kartu, CVV) di server kami. Semua payment data di-handle oleh payment gateway tersertifikasi PCI-DSS.</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3>2.2 Informasi yang Dikumpulkan Secara Otomatis</h3>
            <p>
                Saat Anda mengunjungi <strong>toko baju online</strong> kami, sistem kami secara otomatis mengumpulkan data teknis untuk meningkatkan performa website dan pengalaman pengguna:
            </p>

            <ul>
                <li><strong>IP Address:</strong> Lokasi geografis umum dan deteksi fraud</li>
                <li><strong>Device Information:</strong> Tipe perangkat (mobile/desktop), OS, browser</li>
                <li><strong>Browsing Behavior:</strong> Halaman yang dikunjungi, waktu yang dihabiskan, produk yang dilihat</li>
                <li><strong>Referral Source:</strong> Dari mana Anda datang ke website kami (Google, Instagram, dll)</li>
                <li><strong>Cookies & Tracking:</strong> Session cookies, analytics cookies (lihat bagian Cookies)</li>
            </ul>

            <h3>2.3 Informasi dari Sumber Lain</h3>
            <p>
                Kami juga mungkin menerima informasi tentang Anda dari sumber eksternal:
            </p>

            <ul>
                <li><strong>Social Media:</strong> Jika Anda login via Facebook/Google (dengan izin Anda)</li>
                <li><strong>Payment Providers:</strong> Konfirmasi status pembayaran</li>
                <li><strong>Delivery Partners:</strong> Update status pengiriman</li>
                <li><strong>Marketing Partners:</strong> Performance metrics untuk campaign (anonymized data)</li>
            </ul>
        </section>

        <!-- Section 3: Data Usage -->
        <section id="data-usage" class="legal-section">
            <div class="section-header">
                <span class="section-icon">⚙️</span>
                <h2>Bagaimana Kami Menggunakan Informasi Anda</h2>
            </div>

            <p>
                Data yang kami kumpulkan digunakan semata-mata untuk meningkatkan pengalaman <strong>belanja fashion online</strong> Anda dan menjalankan operasional bisnis kami secara efektif. Berikut adalah tujuan penggunaan data Anda:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>1. Pemrosesan & Fulfillment Pesanan</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li>Verifikasi dan konfirmasi pembayaran Anda</li>
                            <li>Mengemas dan mengirimkan produk <strong>baju kekinian</strong> yang Anda pesan</li>
                            <li>Memberikan tracking information untuk paket Anda</li>
                            <li>Menangani pertanyaan terkait status pesanan</li>
                            <li>Memproses refund, return, atau exchange jika diperlukan</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>2. Layanan Pelanggan & Support</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li>Menjawab pertanyaan Anda via email, WhatsApp, atau live chat</li>
                            <li>Menyelesaikan kendala teknis atau masalah pengiriman</li>
                            <li>Memberikan rekomendasi size atau produk yang sesuai</li>
                            <li>Menindaklanjuti komplain atau feedback Anda</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>3. Personalisasi & Rekomendasi</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li>Menampilkan produk <strong>fashion wanita</strong> atau <strong>fashion pria</strong> yang sesuai dengan preferensi Anda</li>
                            <li>Memberikan size suggestions berdasarkan purchase history</li>
                            <li>Customize homepage experience sesuai browsing behavior</li>
                            <li>Suggest "Complete the Look" atau matching items</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>4. Marketing & Komunikasi (Dengan Persetujuan)</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li>Mengirimkan newsletter tentang <strong>model baju terbaru</strong> dan new arrivals</li>
                            <li>Memberitahu Anda tentang exclusive sales, flash deals, dan promo</li>
                            <li>Share styling tips, fashion trends, dan lookbook inspiration</li>
                            <li>Birthday special offers dan anniversary rewards</li>
                        </ul>
                        <div class="warning-box">
                            <p><strong>⚠️ Catatan Penting:</strong> Anda dapat unsubscribe dari marketing emails kapan saja dengan klik link "Unsubscribe" di bagian bawah email kami. Ini TIDAK akan mempengaruhi transactional emails (order confirmation, shipping updates).</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>5. Keamanan & Fraud Prevention</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li>Mendeteksi dan mencegah aktivitas mencurigakan atau fraudulent</li>
                            <li>Verify identitas untuk high-value transactions</li>
                            <li>Melindungi akun Anda dari unauthorized access</li>
                            <li>Investigate chargeback atau payment disputes</li>
                            <li>Comply dengan legal obligations dan law enforcement requests</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>6. Analytics & Improvement</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li>Menganalisis website performance dan user experience</li>
                            <li>Understand customer preferences dan shopping behavior</li>
                            <li>Improve product offerings dan inventory management</li>
                            <li>Test new features dan optimize conversion rates</li>
                            <li>Measure effectiveness dari marketing campaigns</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 4: Data Sharing -->
        <section id="data-sharing" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🤝</span>
                <h2>Pembagian Informasi kepada Pihak Ketiga</h2>
            </div>

            <div class="highlight-box">
                <div class="highlight-box-header">
                    <span class="highlight-icon">🛡️</span>
                    <h4 class="highlight-title">Komitmen No-Sell Policy</h4>
                </div>
                <p>
                    <strong>Dorve.id TIDAK PERNAH dan TIDAK AKAN PERNAH menjual data pribadi Anda kepada pihak manapun untuk tujuan komersial.</strong> Privasi Anda adalah prioritas utama kami.
                </p>
            </div>

            <p>
                Kami hanya membagikan informasi Anda kepada pihak ketiga terpercaya yang membantu kami mengoperasikan <strong>toko baju online terpercaya</strong> ini, dan hanya sebatas yang diperlukan untuk tujuan spesifik:
            </p>

            <h3>Pihak Ketiga yang Kami Percayai:</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Jasa Pengiriman & Logistik</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p><strong>Partner:</strong> JNE, J&T Express, SiCepat, AnterAja, Ninja Xpress</p>
                        <p><strong>Data yang Dibagikan:</strong></p>
                        <ul>
                            <li>Nama penerima</li>
                            <li>Nomor telepon/HP</li>
                            <li>Alamat pengiriman lengkap</li>
                            <li>Detail produk (untuk customs/insurance)</li>
                        </ul>
                        <p><strong>Tujuan:</strong> Memastikan paket <strong>baju online</strong> Anda sampai dengan aman dan tepat waktu.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Payment Gateway & Processors</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p><strong>Partner:</strong> Midtrans, Xendit, PayPal, dan bank partner</p>
                        <p><strong>Data yang Dibagikan:</strong></p>
                        <ul>
                            <li>Nama dan email</li>
                            <li>Transaction amount</li>
                            <li>Order ID</li>
                            <li>Payment method yang dipilih</li>
                        </ul>
                        <p><strong>Tujuan:</strong> Memproses pembayaran Anda secara aman melalui encrypted channels.</p>
                        <div class="success-box">
                            <p><strong>🔒 Security:</strong> Semua payment partners kami PCI-DSS certified dan comply dengan international security standards.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Cloud & IT Infrastructure Providers</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p><strong>Partner:</strong> AWS, Google Cloud, atau hosting providers</p>
                        <p><strong>Data yang Dibagikan:</strong> Encrypted customer data untuk storage purposes</p>
                        <p><strong>Tujuan:</strong> Menjaga website tetap online, secure, dan perform dengan baik.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Marketing & Analytics Tools</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p><strong>Tools:</strong> Google Analytics, Facebook Pixel, Email Service Providers</p>
                        <p><strong>Data yang Dibagikan:</strong> Anonymized behavioral data dan aggregate statistics</p>
                        <p><strong>Tujuan:</strong> Understand customer behavior, optimize marketing campaigns, improve UX</p>
                        <p><strong>Opt-Out:</strong> Anda dapat disable tracking cookies via browser settings.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Legal & Compliance Situations</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Kami mungkin perlu disclose data Anda jika:</p>
                        <ul>
                            <li>Required by law atau legal process (court orders, subpoenas)</li>
                            <li>To protect Dorve.id's rights, property, atau safety</li>
                            <li>To investigate potential fraud atau security breaches</li>
                            <li>To enforce our Terms & Conditions</li>
                            <li>With your explicit consent</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 5: Data Security -->
        <section id="data-security" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🔐</span>
                <h2>Keamanan & Perlindungan Data</h2>
            </div>

            <p>
                Melindungi data pribadi Anda adalah tanggung jawab yang kami ambil dengan sangat serius. <strong>Dorve.id</strong> mengimplementasikan multiple layers of security untuk safeguard informasi Anda:
            </p>

            <h3>Langkah-Langkah Keamanan Teknis:</h3>

            <ul>
                <li><strong>SSL/TLS Encryption (256-bit):</strong> Semua data yang dikirim antara browser Anda dan server kami di-encrypt</li>
                <li><strong>Secure Data Centers:</strong> Data disimpan di data centers dengan physical security dan 24/7 monitoring</li>
                <li><strong>Firewall Protection:</strong> Multi-layer firewalls untuk prevent unauthorized access</li>
                <li><strong>Regular Security Audits:</strong> Vulnerability assessments dan penetration testing secara berkala</li>
                <li><strong>Data Encryption at Rest:</strong> Customer data di-encrypt bahkan saat stored di database</li>
                <li><strong>Access Controls:</strong> Strict role-based access - hanya authorized personnel yang bisa akses data</li>
                <li><strong>Two-Factor Authentication:</strong> Available untuk member accounts untuk extra security</li>
                <li><strong>Automatic Logout:</strong> Session timeout setelah periode inactivity</li>
            </ul>

            <h3>Praktik Keamanan Operasional:</h3>

            <ul>
                <li><strong>Employee Training:</strong> Semua staff trained tentang data protection dan privacy best practices</li>
                <li><strong>Confidentiality Agreements:</strong> Semua employees sign NDAs</li>
                <li><strong>Limited Access:</strong> Principle of "least privilege" - access hanya diberikan as needed</li>
                <li><strong>Incident Response Plan:</strong> Protocol jelas untuk handle potential data breaches</li>
                <li><strong>Regular Backups:</strong> Automated backups dengan disaster recovery procedures</li>
            </ul>

            <div class="warning-box">
                <p><strong>⚠️ Penting untuk Diingat:</strong> Meskipun kami implement industry-leading security measures, tidak ada sistem yang 100% immune terhadap serangan. Kami encourage Anda untuk juga protect account Anda dengan:</p>
                <ul>
                    <li>Menggunakan password yang strong dan unique</li>
                    <li>Tidak share password dengan siapapun</li>
                    <li>Enable two-factor authentication</li>
                    <li>Logout dari public devices</li>
                    <li>Report suspicious activity immediately</li>
                </ul>
            </div>

            <h3>Apa yang Kami Lakukan Jika Terjadi Data Breach?</h3>
            <p>
                Jika terjadi security incident yang affect data pribadi Anda, kami akan:
            </p>
            <ul>
                <li>Immediately investigate dan contain the breach</li>
                <li>Notify affected customers via email dalam 72 jam</li>
                <li>Inform relevant authorities sesuai legal requirements</li>
                <li>Provide clear guidance tentang langkah-langkah yang perlu Anda ambil</li>
                <li>Implement additional safeguards untuk prevent future incidents</li>
            </ul>
        </section>

        <!-- Section 6: Cookies -->
        <section id="cookies" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🍪</span>
                <h2>Kebijakan Cookie</h2>
            </div>

            <p>
                Website <strong>Dorve.id</strong> menggunakan cookies dan similar tracking technologies untuk enhance your browsing experience dan provide personalized services. Berikut penjelasan detail tentang cookies yang kami gunakan:
            </p>

            <h3>Apa Itu Cookies?</h3>
            <p>
                Cookies adalah file teks kecil yang disimpan di device Anda saat Anda visit website kami. Cookies membantu website "mengingat" Anda dan preferences Anda untuk future visits.
            </p>

            <h3>Jenis-Jenis Cookies yang Kami Gunakan:</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Essential/Strictly Necessary Cookies</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p><strong>Purpose:</strong> Cookies ini essential untuk website function properly. Tanpa cookies ini, services yang Anda request tidak dapat diberikan.</p>
                        <p><strong>Examples:</strong></p>
                        <ul>
                            <li>Shopping cart functionality</li>
                            <li>Login session management</li>
                            <li>Security features</li>
                            <li>Load balancing</li>
                        </ul>
                        <p><strong>Can You Disable:</strong> No - these are necessary untuk basic website operation</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Performance/Analytics Cookies</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p><strong>Purpose:</strong> Collect anonymous information about how visitors use our website.</p>
                        <p><strong>Examples:</strong></p>
                        <ul>
                            <li>Google Analytics</li>
                            <li>Page load time monitoring</li>
                            <li>Error tracking</li>
                            <li>User flow analysis</li>
                        </ul>
                        <p><strong>Can You Disable:</strong> Yes - via browser settings atau cookie preferences</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Functional Cookies</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p><strong>Purpose:</strong> Remember your choices untuk provide enhanced, personalized features.</p>
                        <p><strong>Examples:</strong></p>
                        <ul>
                            <li>Language preferences</li>
                            <li>Recently viewed items</li>
                            <li>Wishlist persistence</li>
                            <li>Size preferences</li>
                        </ul>
                        <p><strong>Can You Disable:</strong> Yes - tapi ini mungkin limit certain features</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Advertising/Marketing Cookies</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p><strong>Purpose:</strong> Track browsing activity untuk deliver relevant advertisements.</p>
                        <p><strong>Examples:</strong></p>
                        <ul>
                            <li>Facebook Pixel</li>
                            <li>Google Ads remarketing</li>
                            <li>Instagram Shopping</li>
                            <li>Affiliate tracking</li>
                        </ul>
                        <p><strong>Can You Disable:</strong> Yes - via browser settings, ad blockers, atau opt-out tools</p>
                    </div>
                </div>
            </div>

            <h3>Cara Mengelola Cookies:</h3>
            <p>Anda memiliki control penuh atas cookies. Berikut cara manage cookies di browser Anda:</p>
            <ul>
                <li><strong>Chrome:</strong> Settings → Privacy and Security → Cookies and other site data</li>
                <li><strong>Firefox:</strong> Options → Privacy & Security → Cookies and Site Data</li>
                <li><strong>Safari:</strong> Preferences → Privacy → Manage Website Data</li>
                <li><strong>Edge:</strong> Settings → Cookies and site permissions</li>
            </ul>

            <div class="warning-box">
                <p><strong>⚠️ Note:</strong> Blocking atau deleting cookies mungkin impact functionality tertentu dari website kami, seperti shopping cart atau staying logged in.</p>
            </div>
        </section>

        <!-- Section 7: Your Rights -->
        <section id="your-rights" class="legal-section">
            <div class="section-header">
                <span class="section-icon">⚖️</span>
                <h2>Hak-Hak Anda Sebagai Customer</h2>
            </div>

            <p>
                Sebagai pelanggan <strong>Dorve.id</strong> yang kami hargai, Anda memiliki hak-hak penuh atas data pribadi Anda sesuai dengan regulasi perlindungan data yang berlaku:
            </p>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>✅ Right to Access (Hak Akses)</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Anda berhak untuk:</p>
                        <ul>
                            <li>Melihat data pribadi apa saja yang kami simpan tentang Anda</li>
                            <li>Request copy dari data Anda dalam format yang machine-readable</li>
                            <li>Understand purpose dari data collection dan processing</li>
                        </ul>
                        <p><strong>How:</strong> Login ke "My Account" → "Personal Information" atau contact customer service</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>✏️ Right to Rectification (Hak Koreksi)</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Anda berhak untuk:</p>
                        <ul>
                            <li>Update atau correct data yang inaccurate atau outdated</li>
                            <li>Complete incomplete information</li>
                            <li>Change alamat, nomor telepon, atau email</li>
                        </ul>
                        <p><strong>How:</strong> Update directly via "Account Settings" atau hubungi kami</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>🗑️ Right to Erasure (Hak Penghapusan)</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Anda berhak untuk:</p>
                        <ul>
                            <li>Request deletion dari account dan personal data Anda</li>
                            <li>Have your data "forgotten" dari our systems</li>
                        </ul>
                        <p><strong>Limitations:</strong> Kami mungkin perlu retain certain data untuk:</p>
                        <ul>
                            <li>Comply dengan legal obligations (tax records, transaction history)</li>
                            <li>Resolve disputes atau enforce agreements</li>
                            <li>Fraud prevention purposes</li>
                        </ul>
                        <p><strong>How:</strong> Contact privacy@dorve.id dengan subject "Data Deletion Request"</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>📧 Right to Object (Hak Menolak)</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Anda berhak untuk:</p>
                        <ul>
                            <li>Object to processing data Anda untuk direct marketing purposes</li>
                            <li>Unsubscribe dari promotional emails kapan saja</li>
                            <li>Opt-out dari behavioral advertising</li>
                        </ul>
                        <p><strong>How:</strong> Click "Unsubscribe" link di email kami atau update preferences di account settings</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>📦 Right to Data Portability (Hak Portabilitas)</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Anda berhak untuk:</p>
                        <ul>
                            <li>Receive data Anda dalam commonly used, machine-readable format</li>
                            <li>Transfer data Anda ke service provider lain</li>
                        </ul>
                        <p><strong>How:</strong> Email request ke privacy@dorve.id</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>⏸️ Right to Restriction (Hak Pembatasan)</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Anda berhak untuk:</p>
                        <ul>
                            <li>Request temporary restriction pada processing data Anda</li>
                            <li>Limit how we use your information sementara disputes di-resolve</li>
                        </ul>
                        <p><strong>How:</strong> Contact customer service dengan clear explanation</p>
                    </div>
                </div>
            </div>

            <div class="highlight-box">
                <div class="highlight-box-header">
                    <span class="highlight-icon">⏱️</span>
                    <h4 class="highlight-title">Response Time</h4>
                </div>
                <p>
                    Kami berkomitmen untuk respond semua requests terkait data rights Anda dalam waktu <strong>30 hari kerja</strong> atau less. Jika request Anda complex, kami akan inform Anda about any extension yang diperlukan.
                </p>
            </div>
        </section>

        <!-- Section 8: Third Party Services -->
        <section id="third-party" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🔗</span>
                <h2>Layanan & Link Pihak Ketiga</h2>
            </div>

            <p>
                Website <strong>Dorve.id</strong> mungkin contain links ke website, plugins, atau services pihak ketiga yang bukan operated atau controlled oleh kami:
            </p>

            <h3>Third-Party Websites:</h3>
            <ul>
                <li>Social media platforms (Instagram, Facebook, TikTok)</li>
                <li>Payment provider portals</li>
                <li>Shipping tracking pages</li>
                <li>Product review platforms</li>
                <li>Partner brand websites</li>
            </ul>

            <div class="warning-box">
                <p><strong>⚠️ Important Notice:</strong> Kami tidak bertanggung jawab atas privacy practices atau content dari third-party websites. Setiap external link akan membawa Anda ke website lain yang memiliki privacy policy sendiri. Kami strongly encourage Anda untuk review privacy policy dari setiap website yang Anda kunjungi.</p>
            </div>

            <h3>Social Media Features:</h3>
            <p>
                Website kami includes social media features seperti:
            </p>
            <ul>
                <li>Share buttons (Facebook, Instagram, WhatsApp)</li>
                <li>Like/Follow buttons</li>
                <li>Instagram feed embeds</li>
                <li>User-generated content dari social media</li>
            </ul>

            <p>
                Features ini mungkin collect IP address Anda, which page you're visiting, dan may set cookie untuk enable feature function properly. Interactions Anda dengan these features governed by privacy policy dari company yang provide them.
            </p>
        </section>

        <!-- Section 9: Children's Privacy -->
        <section id="children" class="legal-section">
            <div class="section-header">
                <span class="section-icon">👶</span>
                <h2>Privasi Anak-Anak</h2>
            </div>

            <p>
                <strong>Dorve.id</strong> sangat peduli dengan privasi dan keamanan children online. Layanan kami tidak intended untuk individuals under the age of 13 years old.
            </p>

            <h3>Kebijakan Kami:</h3>
            <ul>
                <li>Kami <strong>tidak secara sadar</strong> collect personal information dari children under 13</li>
                <li>Untuk create account, user must confirm they are 13 tahun atau lebih</li>
                <li>Jika kami discover bahwa kami telah collected data dari child under 13 tanpa parental consent, kami akan take steps untuk delete information tersebut as quickly as possible</li>
            </ul>

            <h3>Untuk Orang Tua/Guardian:</h3>
            <p>
                Jika Anda adalah parent atau guardian dan believe bahwa child Anda has provided personal information kepada kami, please contact kami immediately:
            </p>
            <ul>
                <li><strong>Email:</strong> privacy@dorve.id dengan subject "Child Data Concern"</li>
                <li>Provide child's name dan details yang membantu kami identify information tersebut</li>
                <li>Kami akan promptly investigate dan take appropriate action</li>
            </ul>

            <div class="success-box">
                <p><strong>🛡️ Our Commitment:</strong> Kami committed untuk protecting children's privacy online dan complying dengan all applicable laws regarding children's data protection.</p>
            </div>
        </section>

        <!-- Section 10: Changes to Policy -->
        <section id="changes" class="legal-section">
            <div class="section-header">
                <span class="section-icon">🔄</span>
                <h2>Perubahan pada Kebijakan Privasi</h2>
            </div>

            <p>
                <strong>Dorve.id</strong> adalah brand yang terus berkembang dan evolving. Kami may update Privacy Policy ini dari time to time untuk reflect:
            </p>

            <ul>
                <li>Changes dalam our business practices</li>
                <li>New features atau services yang kami launch</li>
                <li>Updates dalam applicable laws dan regulations</li>
                <li>Feedback dari customers dan stakeholders</li>
                <li>Improvements dalam security measures</li>
            </ul>

            <h3>Bagaimana Kami Notify Anda:</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Minor Changes</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Untuk minor, non-material changes:</p>
                        <ul>
                            <li>Update "Last Updated" date di top dari page</li>
                            <li>Post notice di homepage (opsional)</li>
                        </ul>
                        <p>Continued use dari our services after changes constitutes acceptance.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>Material/Significant Changes</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Untuk material changes yang significantly affect your rights:</p>
                        <ul>
                            <li><strong>Email Notification:</strong> Kami akan send email ke address yang registered di account Anda</li>
                            <li><strong>Prominent Notice:</strong> Banner notification di website</li>
                            <li><strong>Grace Period:</strong> Minimum 30 days notice before changes take effect</li>
                            <li><strong>Option to Object:</strong> Opportunity untuk opt-out atau close account jika Anda disagree</li>
                        </ul>
                    </div>
                </div>
            </div>

            <h3>Review Regularly:</h3>
            <p>
                Kami encourage Anda untuk periodically review Privacy Policy ini untuk stay informed about how kami protecting your personal information. Awareness adalah first step dalam data protection!
            </p>

            <div class="highlight-box">
                <div class="highlight-box-header">
                    <span class="highlight-icon">📌</span>
                    <h4 class="highlight-title">Version History</h4>
                </div>
                <p>
                    Previous versions dari Privacy Policy kami available upon request. Contact privacy@dorve.id jika Anda ingin review historical versions.
                </p>
            </div>
        </section>

        <!-- Section 11: Contact Us -->
        <section id="contact" class="legal-section">
            <div class="section-header">
                <span class="section-icon">📞</span>
                <h2>Hubungi Kami</h2>
            </div>

            <p>
                Jika Anda memiliki questions, concerns, atau requests terkait Privacy Policy ini atau bagaimana kami handle personal data Anda, kami are here untuk help:
            </p>

            <h3>Tim Perlindungan Data Dorve.id:</h3>

            <ul>
                <li>
                    <strong>📧 Email:</strong> privacy@dorve.id<br>
                    <span style="color: var(--grey); font-size: 14px;">Response time: Within 24-48 hours</span>
                </li>
                <li>
                    <strong>💬 WhatsApp:</strong> +62 812-3456-7890<br>
                    <span style="color: var(--grey); font-size: 14px;">Available: 9 AM - 6 PM WIB (Senin-Jumat)</span>
                </li>
                <li>
                    <strong>📮 Alamat Kantor:</strong><br>
                    Dorve.id<br>
                    Jakarta, Indonesia<br>
                    <span style="color: var(--grey); font-size: 14px;">For formal correspondence only</span>
                </li>
            </ul>

            <h3>What Information to Include:</h3>
            <p>Untuk help kami assist Anda better, please include:</p>
            <ul>
                <li>Your full name dan email address</li>
                <li>Account details (jika applicable)</li>
                <li>Clear description dari your request atau concern</li>
                <li>Any relevant order numbers atau reference IDs</li>
            </ul>

            <div class="success-box">
                <p><strong>🤝 Our Promise:</strong> Kami treat every inquiry seriously dan dengan full respect untuk your privacy rights. Our team trained untuk handle data protection matters dengan professionalism dan confidentiality.</p>
            </div>
        </section>

        <!-- Final Statement -->
        <section class="legal-section" style="border-top: 2px solid var(--latte); padding-top: 60px; margin-top: 80px;">
            <p style="font-size: 16px; line-height: 1.9; text-align: center; font-weight: 500;">
                Terima kasih telah mempercayai <strong>Dorve.id</strong> dengan personal information Anda. Kami committed untuk maintaining that trust through transparent practices, robust security measures, dan respect untuk your privacy rights. Your confidence in us drives kami untuk continuously improve dan protect your data dengan highest standards.
            </p>
            <p style="text-align: center; margin-top: 24px; color: var(--grey);">
                Happy Shopping! 🛍️
            </p>
        </section>

        <!-- CTA Section -->
        <div class="contact-cta">
            <h3>Ada Pertanyaan tentang Privacy Policy?</h3>
            <p>Tim kami siap membantu menjawab semua pertanyaan Anda tentang perlindungan data dan privasi</p>
            <div class="contact-buttons">
                <a href="https://wa.me/6281234567890" class="contact-btn" target="_blank">
                    <span>💬</span>
                    <span>WhatsApp Kami</span>
                </a>
                <a href="mailto:privacy@dorve.id" class="contact-btn contact-btn-outline">
                    <span>📧</span>
                    <span>Email Privacy Team</span>
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