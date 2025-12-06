<?php
require_once __DIR__ . '/../config.php';

$stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE slug = 'shipping-policy' AND is_active = 1 LIMIT 1");
$stmt->execute();
$page = $stmt->fetch();

$page_title = 'Kebijakan Pengiriman - Dorve.id | Gratis Ongkir, COD, & Pengiriman Cepat';
$page_description = 'Informasi lengkap pengiriman Dorve.id: Gratis ongkir seluruh Indonesia untuk pembelian di atas Rp 500.000, layanan COD (bayar di tempat), pengiriman 1-2 hari kerja, tracking real-time dengan JNE, J&T, SiCepat. Belanja fashion online dengan aman dan nyaman.';
$page_keywords = 'gratis ongkir, free shipping, cod, bayar ditempat, pengiriman cepat, jne, j&t, sicepat, anteraja, ninja express, tracking paket, estimasi pengiriman, dorve.id shipping';
include __DIR__ . '/../includes/header.php';
?>

<!-- Schema Markup for Shipping Policy -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Kebijakan Pengiriman - Dorve.id",
  "description": "<?php echo $page_description; ?>",
  "publisher": {
    "@type": "Organization",
    "name": "Dorve.id",
    "url": "https://dorve.id"
  },
  "offers": {
    "@type": "Offer",
    "shippingDetails": {
      "@type": "OfferShippingDetails",
      "shippingRate": {
        "@type": "MonetaryAmount",
        "value": "0",
        "currency": "IDR"
      },
      "deliveryTime": {
        "@type": "ShippingDeliveryTime",
        "handlingTime": {
          "@type": "QuantitativeValue",
          "minValue": "1",
          "maxValue": "2",
          "unitCode": "DAY"
        },
        "transitTime": {
          "@type": "QuantitativeValue",
          "minValue": "2",
          "maxValue": "7",
          "unitCode": "DAY"
        }
      },
      "shippingDestination": {
        "@type": "DefinedRegion",
        "addressCountry": "ID"
      }
    }
  }
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
    .shipping-hero {
        background: linear-gradient(135deg, #2D2D2D 0%, var(--charcoal) 100%);
        padding: 100px 24px 80px;
        text-align: center;
        color: var(--white);
        position: relative;
        overflow: hidden;
    }

    .shipping-hero::before {
        content: '🚚';
        position: absolute;
        font-size: 200px;
        opacity: 0.05;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .shipping-hero-content {
        max-width: 900px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .shipping-badge {
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

    .shipping-hero h1 {
        font-family: 'Playfair Display', serif;
        font-size: 52px;
        font-weight: 700;
        margin-bottom: 20px;
        line-height: 1.2;
    }

    .shipping-hero p {
        font-size: 18px;
        color: rgba(255,255,255,0.9);
        margin-bottom: 32px;
        line-height: 1.7;
    }

    .shipping-features {
        display: flex;
        gap: 32px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 40px;
    }

    .feature-badge {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        padding: 16px 28px;
        border-radius: 50px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 600;
        border: 1px solid rgba(255,255,255,0.2);
        transition: all 0.3s ease;
    }

    .feature-badge:hover {
        background: rgba(255,255,255,0.25);
        transform: translateY(-3px);
    }

    .feature-badge span:first-child {
        font-size: 20px;
    }

    /* ===== MAIN CONTAINER ===== */
    .shipping-main-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 80px 24px;
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 60px;
        align-items: start;
    }

    /* ===== TABLE OF CONTENTS ===== */
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
    .shipping-content {
        background: var(--white);
        padding: 60px;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }

    .shipping-section {
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

    .shipping-content h2 {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        color: var(--charcoal);
        margin: 0;
        line-height: 1.3;
    }

    .shipping-content h3 {
        font-size: 20px;
        color: var(--charcoal);
        margin: 32px 0 16px;
        font-weight: 700;
    }

    .shipping-content p {
        line-height: 1.9;
        color: var(--grey);
        margin-bottom: 20px;
        font-size: 15px;
        text-align: justify;
    }

    .shipping-content strong {
        color: var(--charcoal);
        font-weight: 600;
    }

    .shipping-content ul {
        margin: 20px 0;
        padding-left: 24px;
        line-height: 1.8;
        color: var(--grey);
    }

    .shipping-content li {
        margin-bottom: 12px;
        padding-left: 8px;
    }

    /* ===== SHIPPING TABLE ===== */
    .shipping-table-wrapper {
        overflow-x: auto;
        margin: 32px 0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }

    .shipping-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--white);
    }

    .shipping-table thead {
        background: linear-gradient(135deg, var(--charcoal) 0%, #2D2D2D 100%);
        color: var(--white);
    }

    .shipping-table th {
        padding: 20px 24px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .shipping-table td {
        padding: 20px 24px;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        font-size: 15px;
        color: var(--grey);
    }

    .shipping-table tbody tr {
        transition: all 0.3s ease;
    }

    .shipping-table tbody tr:hover {
        background: var(--off-white);
    }

    .shipping-table tbody tr:last-child td {
        border-bottom: none;
    }

    .shipping-method {
        font-weight: 700;
        color: var(--charcoal);
        font-size: 16px;
    }

    .free-badge {
        display: inline-block;
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: var(--white);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
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

    .success-box {
        background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%);
        border-left: 4px solid #10B981;
        padding: 24px 28px;
        margin: 28px 0;
        border-radius: 8px;
    }

    .warning-box {
        background: linear-gradient(135deg, #FFF4E6 0%, #FED7AA 100%);
        border-left: 4px solid #F59E0B;
        padding: 24px 28px;
        margin: 28px 0;
        border-radius: 8px;
    }

    .info-box {
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        border-left: 4px solid #3B82F6;
        padding: 24px 28px;
        margin: 28px 0;
        border-radius: 8px;
    }

    /* ===== COVERAGE CARDS ===== */
    .coverage-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 24px;
        margin: 32px 0;
    }

    .coverage-card {
        background: var(--off-white);
        padding: 24px;
        border-radius: 12px;
        border: 2px solid rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }

    .coverage-card:hover {
        border-color: var(--latte);
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }

    .coverage-card-icon {
        font-size: 40px;
        margin-bottom: 16px;
    }

    .coverage-card h4 {
        font-size: 18px;
        font-weight: 700;
        color: var(--charcoal);
        margin-bottom: 12px;
    }

    .coverage-card ul {
        margin: 0;
        padding-left: 20px;
        font-size: 14px;
    }

    .coverage-card li {
        margin-bottom: 8px;
    }

    /* ===== COURIER LOGOS ===== */
    .courier-section {
        background: var(--off-white);
        padding: 40px;
        border-radius: 12px;
        margin: 32px 0;
    }

    .courier-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 24px;
        margin-top: 24px;
    }

    .courier-card {
        background: var(--white);
        padding: 24px;
        border-radius: 8px;
        text-align: center;
        border: 2px solid rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }

    .courier-card:hover {
        border-color: var(--latte);
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    .courier-card-icon {
        font-size: 48px;
        margin-bottom: 12px;
    }

    .courier-card p {
        margin: 0;
        font-weight: 600;
        color: var(--charcoal);
        font-size: 14px;
        text-align: center;
    }

    /* ===== TIMELINE ===== */
    .timeline {
        position: relative;
        padding-left: 40px;
        margin: 32px 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 10px;
        bottom: 10px;
        width: 3px;
        background: linear-gradient(180deg, var(--latte) 0%, #E5D5C9 100%);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 32px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -32px;
        top: 5px;
        width: 16px;
        height: 16px;
        background: var(--latte);
        border: 3px solid var(--white);
        border-radius: 50%;
        box-shadow: 0 0 0 4px rgba(212,197,185,0.2);
    }

    .timeline-content {
        background: var(--off-white);
        padding: 20px 24px;
        border-radius: 8px;
        border-left: 3px solid var(--latte);
    }

    .timeline-content h4 {
        font-size: 16px;
        font-weight: 700;
        color: var(--charcoal);
        margin: 0 0 8px 0;
    }

    .timeline-content p {
        margin: 0;
        font-size: 14px;
        text-align: left;
    }

    /* ===== ACCORDION ===== */
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

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .shipping-main-container {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .toc-sidebar {
            position: relative;
            top: 0;
        }

        .shipping-content {
            padding: 40px;
        }
    }

    @media (max-width: 768px) {
        .shipping-hero {
            padding: 80px 24px 60px;
        }

        .shipping-hero h1 {
            font-size: 36px;
        }

        .shipping-features {
            flex-direction: column;
            align-items: center;
        }

        .feature-badge {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }

        .shipping-main-container {
            padding: 60px 24px;
        }

        .shipping-content {
            padding: 32px 24px;
        }

        .shipping-content h2 {
            font-size: 26px;
        }

        .coverage-grid {
            grid-template-columns: 1fr;
        }

        .courier-grid {
            grid-template-columns: repeat(2, 1fr);
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
        .shipping-hero h1 {
            font-size: 28px;
        }

        .shipping-table th,
        .shipping-table td {
            padding: 12px 16px;
            font-size: 13px;
        }

        .courier-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Section -->
<section class="shipping-hero">
    <div class="shipping-hero-content">
        <div class="shipping-badge">🚚 Shipping Information</div>
        <h1>Kebijakan Pengiriman</h1>
        <p>Nikmati pengiriman cepat, aman, dan terpercaya ke seluruh Indonesia. Kami berkomitmen untuk mengantarkan fashion favorit Anda tepat waktu dengan layanan tracking real-time.</p>
        
        <div class="shipping-features">
            <div class="feature-badge">
                <span>🎁</span>
                <span>Gratis Ongkir >500K</span>
            </div>
            <div class="feature-badge">
                <span>⚡</span>
                <span>Proses 1-2 Hari</span>
            </div>
            <div class="feature-badge">
                <span>💳</span>
                <span>COD Available</span>
            </div>
            <div class="feature-badge">
                <span>📦</span>
                <span>Tracking Real-Time</span>
            </div>
        </div>
    </div>
</section>

<!-- Main Container with TOC -->
<div class="shipping-main-container">
    <!-- Table of Contents Sidebar -->
    <aside class="toc-sidebar">
        <div class="toc-title">Daftar Isi</div>
        <ul class="toc-list">
            <li class="toc-item"><a href="#methods" class="toc-link">Metode Pengiriman</a></li>
            <li class="toc-item"><a href="#processing" class="toc-link">Waktu Pemrosesan</a></li>
            <li class="toc-item"><a href="#partners" class="toc-link">Mitra Kurir</a></li>
            <li class="toc-item"><a href="#coverage" class="toc-link">Jangkauan Pengiriman</a></li>
            <li class="toc-item"><a href="#tracking" class="toc-link">Tracking Paket</a></li>
            <li class="toc-item"><a href="#cod" class="toc-link">Cash on Delivery</a></li>
            <li class="toc-item"><a href="#receiving" class="toc-link">Penerimaan Paket</a></li>
            <li class="toc-item"><a href="#holidays" class="toc-link">Hari Raya & Peak Season</a></li>
            <li class="toc-item"><a href="#issues" class="toc-link">Kendala Pengiriman</a></li>
            <li class="toc-item"><a href="#contact" class="toc-link">Hubungi Kami</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="shipping-content">
        <!-- Introduction -->
        <div class="success-box">
            <div class="highlight-box-header">
                <span class="highlight-icon">🎉</span>
                <h4 class="highlight-title">Gratis Ongkir Seluruh Indonesia!</h4>
            </div>
            <p>
                Selamat datang di <strong>Dorve.id</strong>! Kami menawarkan <strong>GRATIS ONGKIR</strong> untuk pembelian di atas <strong>Rp 500.000</strong> ke seluruh Indonesia. Belanja fashion kekinian jadi lebih hemat dan menyenangkan!
            </p>
        </div>

        <!-- Section 1: Shipping Methods & Rates -->
        <section id="methods" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">📦</span>
                <h2>Metode & Tarif Pengiriman</h2>
            </div>

            <p>
                <strong>Dorve.id</strong> bermitra dengan ekspedisi terpercaya untuk memastikan outfit favorit Anda sampai dengan aman dan tepat waktu. Kami menawarkan berbagai pilihan layanan pengiriman yang dapat disesuaikan dengan kebutuhan Anda.
            </p>

            <div class="shipping-table-wrapper">
                <table class="shipping-table">
                    <thead>
                        <tr>
                            <th>Metode Pengiriman</th>
                            <th>Estimasi Pengiriman</th>
                            <th>Biaya & Ketentuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="shipping-method">🚛 Regular Shipping</td>
                            <td>
                                <strong>Jakarta & Sekitarnya:</strong> 2-3 hari kerja<br>
                                <strong>Jawa:</strong> 3-5 hari kerja<br>
                                <strong>Luar Jawa:</strong> 5-7 hari kerja
                            </td>
                            <td>
                                <strong>Flat Rate:</strong> Rp 20.000 (Jakarta)<br>
                                <strong>Other Areas:</strong> Dihitung otomatis<br>
                                <span class="free-badge">GRATIS >500K</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="shipping-method">⚡ Express Shipping</td>
                            <td>
                                <strong>Jakarta & Kota Besar:</strong> 1-2 hari kerja<br>
                                <strong>Area Lain:</strong> 2-4 hari kerja
                            </td>
                            <td>
                                <strong>Express Rate:</strong> 2x regular rate<br>
                                Tersedia untuk area selected
                            </td>
                        </tr>
                        <tr>
                            <td class="shipping-method">💰 Cash on Delivery (COD)</td>
                            <td>
                                <strong>Jabodetabek:</strong> 3-5 hari kerja
                            </td>
                            <td>
                                <strong>COD Fee:</strong> Rp 10.000<br>
                                <strong>Max Order:</strong> Rp 2.000.000<br>
                                Siapkan uang pas
                            </td>
                        </tr>
                        <tr>
                            <td class="shipping-method">🎁 Same Day Delivery</td>
                            <td>
                                <strong>Jakarta:</strong> Hari yang sama<br>
                                Order sebelum jam 12:00 WIB
                            </td>
                            <td>
                                <strong>Premium Fee:</strong> Rp 50.000<br>
                                Minimum order: Rp 300.000<br>
                                Area terbatas
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="info-box">
                <p><strong>💡 Tips Hemat Ongkir:</strong> Gabungkan belanja dengan teman atau keluarga untuk mencapai minimum Rp 500.000 dan nikmati gratis ongkir! Follow Instagram <strong>@dorve.id</strong> untuk promo free shipping spesial.</p>
            </div>
        </section>

        <!-- Section 2: Processing Time -->
        <section id="processing" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">⏱️</span>
                <h2>Waktu Pemrosesan Pesanan</h2>
            </div>

            <p>
                Tim operasional <strong>Dorve.id</strong> bekerja efisien untuk memproses pesanan Anda dengan cepat. Berikut adalah timeline standar untuk pemrosesan order:
            </p>

            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>📧 Step 1: Konfirmasi Pembayaran</h4>
                        <p>Segera setelah pembayaran dikonfirmasi, Anda akan menerima email konfirmasi order dengan detail lengkap dan invoice digital.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>📦 Step 2: Packing & Quality Check</h4>
                        <p><strong>1-2 hari kerja</strong> untuk memproses, packing, dan quality control. Setiap item diperiksa dengan teliti untuk memastikan kualitas sempurna.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>🚚 Step 3: Handover ke Kurir</h4>
                        <p>Paket diserahkan ke ekspedisi pilihan Anda. Nomor resi tracking akan dikirimkan via email dan WhatsApp.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>📍 Step 4: Dalam Perjalanan</h4>
                        <p>Track paket Anda secara real-time melalui nomor resi. Update status tersedia di "My Orders" atau website kurir.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>✅ Step 5: Paket Diterima</h4>
                        <p>Paket sampai di tangan Anda! Jangan lupa lakukan video unboxing untuk keperluan klaim jika diperlukan.</p>
                    </div>
                </div>
            </div>

            <h3>Catatan Penting:</h3>
            <ul>
                <li><strong>Pre-Order Items:</strong> Processing time 7-14 hari kerja sesuai description</li>
                <li><strong>Custom/Made-to-Order:</strong> 14-21 hari kerja tergantung complexity</li>
                <li><strong>Weekend Orders:</strong> Orders di akhir pekan/libur diproses hari kerja berikutnya</li>
                <li><strong>High Season:</strong> Processing may take extra 1-2 days during peak periods</li>
            </ul>
        </section>

        <!-- Section 3: Courier Partners -->
        <section id="partners" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">🤝</span>
                <h2>Mitra Ekspedisi Terpercaya</h2>
            </div>

            <p>
                Kami bekerja sama dengan ekspedisi terbaik di Indonesia untuk menjamin pengiriman yang aman, cepat, dan dapat diandalkan. Pilih kurir favorit Anda saat checkout!
            </p>

            <div class="courier-section">
                <h3 style="margin-top: 0; text-align: center;">Partner Kurir Kami</h3>
                <div class="courier-grid">
                    <div class="courier-card">
                        <div class="courier-card-icon">📦</div>
                        <p>JNE</p>
                    </div>
                    <div class="courier-card">
                        <div class="courier-card-icon">🚚</div>
                        <p>J&T Express</p>
                    </div>
                    <div class="courier-card">
                        <div class="courier-card-icon">⚡</div>
                        <p>SiCepat</p>
                    </div>
                    <div class="courier-card">
                        <div class="courier-card-icon">🏃</div>
                        <p>AnterAja</p>
                    </div>
                    <div class="courier-card">
                        <div class="courier-card-icon">🥷</div>
                        <p>Ninja Xpress</p>
                    </div>
                    <div class="courier-card">
                        <div class="courier-card-icon">🛵</div>
                        <p>Grab Express</p>
                    </div>
                </div>
            </div>

            <h3>Keunggulan Partner Kurir Kami:</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>📦 JNE (Jalur Nugraha Ekakurir)</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Coverage:</strong> Jangkauan terluas ke seluruh Indonesia</li>
                            <li><strong>Services:</strong> Regular (REG), YES (Same Day), Express</li>
                            <li><strong>Reliability:</strong> Track record excellent untuk keamanan paket</li>
                            <li><strong>Best For:</strong> Pengiriman ke area remote atau luar Jawa</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>🚚 J&T Express</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Speed:</strong> Pengiriman cepat untuk area urban</li>
                            <li><strong>Services:</strong> Regular, Express, COD available</li>
                            <li><strong>Tracking:</strong> Real-time tracking dengan akurasi tinggi</li>
                            <li><strong>Best For:</strong> Jakarta, Surabaya, kota-kota besar Jawa</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>⚡ SiCepat Express</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Efficiency:</strong> Processing dan delivery sangat cepat</li>
                            <li><strong>Services:</strong> SIUNTUNG, BEST, GOKIL (same day)</li>
                            <li><strong>Features:</strong> Photo proof of delivery</li>
                            <li><strong>Best For:</strong> Pengiriman express dalam kota atau antar kota</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 4: Coverage Areas -->
        <section id="coverage" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">🗺️</span>
                <h2>Jangkauan Pengiriman</h2>
            </div>

            <p>
                <strong>Dorve.id</strong> melayani pengiriman ke <strong>seluruh Indonesia</strong>, dari Sabang sampai Merauke! Dari kota besar hingga daerah remote, kami memastikan fashion favorit Anda dapat sampai ke tangan Anda.
            </p>

            <div class="coverage-grid">
                <div class="coverage-card">
                    <div class="coverage-card-icon">🏙️</div>
                    <h4>Jabodetabek</h4>
                    <ul>
                        <li>Jakarta (seluruh area)</li>
                        <li>Bogor, Depok, Tangerang</li>
                        <li>Bekasi, Tangerang Selatan</li>
                        <li>Banten & sekitarnya</li>
                    </ul>
                    <p style="margin-top: 12px; font-size: 13px;"><strong>⚡ 2-3 hari kerja</strong></p>
                </div>

                <div class="coverage-card">
                    <div class="coverage-card-icon">🏞️</div>
                    <h4>Pulau Jawa</h4>
                    <ul>
                        <li>Bandung & Jawa Barat</li>
                        <li>Semarang & Jawa Tengah</li>
                        <li>Surabaya, Malang, Jawa Timur</li>
                        <li>Yogyakarta</li>
                    </ul>
                    <p style="margin-top: 12px; font-size: 13px;"><strong>📦 3-5 hari kerja</strong></p>
                </div>

                <div class="coverage-card">
                    <div class="coverage-card-icon">🏝️</div>
                    <h4>Sumatera & Bali</h4>
                    <ul>
                        <li>Medan, Palembang, Padang</li>
                        <li>Pekanbaru, Lampung</li>
                        <li>Denpasar, Bali</li>
                        <li>Lombok, NTB</li>
                    </ul>
                    <p style="margin-top: 12px; font-size: 13px;"><strong>🚚 4-7 hari kerja</strong></p>
                </div>

                <div class="coverage-card">
                    <div class="coverage-card-icon">🌴</div>
                    <h4>Kalimantan & Sulawesi</h4>
                    <ul>
                        <li>Balikpapan, Samarinda</li>
                        <li>Pontianak, Banjarmasin</li>
                        <li>Makassar, Manado</li>
                        <li>Palu, Kendari</li>
                    </ul>
                    <p style="margin-top: 12px; font-size: 13px;"><strong>📮 5-8 hari kerja</strong></p>
                </div>

                <div class="coverage-card">
                    <div class="coverage-card-icon">🏔️</div>
                    <h4>Papua & Maluku</h4>
                    <ul>
                        <li>Jayapura, Sorong</li>
                        <li>Manokwari, Merauke</li>
                        <li>Ambon, Ternate</li>
                        <li>Area Papua lainnya</li>
                    </ul>
                    <p style="margin-top: 12px; font-size: 13px;"><strong>✈️ 7-14 hari kerja</strong></p>
                </div>

                <div class="coverage-card">
                    <div class="coverage-card-icon">🏖️</div>
                    <h4>Nusa Tenggara</h4>
                    <ul>
                        <li>Mataram, Lombok</li>
                        <li>Kupang, NTT</li>
                        <li>Labuan Bajo</li>
                        <li>Pulau-pulau sekitar</li>
                    </ul>
                    <p style="margin-top: 12px; font-size: 13px;"><strong>🚢 5-10 hari kerja</strong></p>
                </div>
            </div>

            <div class="warning-box">
                <p><strong>⚠️ Remote Areas:</strong> Untuk daerah terpencil atau pulau-pulau kecil, estimasi pengiriman dapat mencapai 14-21 hari kerja depending on courier availability dan weather conditions.</p>
            </div>
        </section>

        <!-- Section 5: Package Tracking -->
        <section id="tracking" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">📍</span>
                <h2>Tracking & Pelacakan Paket</h2>
            </div>

            <p>
                Transparansi adalah prioritas kami. Setiap pengiriman dapat dilacak secara <strong>real-time</strong> sehingga Anda selalu tahu posisi paket Anda.
            </p>

            <h3>Cara Tracking Paket:</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>💻 Tracking via Website Dorve.id</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ol style="margin: 0; padding-left: 24px;">
                            <li>Login ke akun <strong>Dorve.id</strong> Anda</li>
                            <li>Masuk ke menu <strong>"My Orders"</strong> atau <strong>"Pesanan Saya"</strong></li>
                            <li>Pilih order yang ingin di-track</li>
                            <li>Click <strong>"Track Package"</strong> untuk melihat real-time status</li>
                            <li>Detail lengkap journey paket dari warehouse hingga alamat Anda</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>📧 Tracking via Email Confirmation</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li>Check email konfirmasi pengiriman dari <strong>noreply@dorve.id</strong></li>
                            <li>Temukan <strong>Nomor Resi (AWB)</strong> di email</li>
                            <li>Click link tracking yang provided atau copy nomor resi</li>
                            <li>Paste nomor resi di website ekspedisi (JNE, J&T, etc.)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>📱 Tracking via WhatsApp</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p>Enable WhatsApp notifications untuk receive automatic updates:</p>
                        <ul>
                            <li>✅ Paket sudah di-pickup oleh kurir</li>
                            <li>✅ Paket dalam perjalanan</li>
                            <li>✅ Paket sudah sampai di kota tujuan</li>
                            <li>✅ Paket out for delivery (sedang diantar)</li>
                            <li>✅ Paket delivered / diterima</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <p><strong>💡 Pro Tip:</strong> Save nomor resi Anda dan set reminders untuk check status regularly, terutama jika approaching estimated delivery date!</p>
            </div>
        </section>

        <!-- Section 6: COD -->
        <section id="cod" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">💰</span>
                <h2>Cash on Delivery (COD)</h2>
            </div>

            <p>
                Belanja lebih aman dengan opsi <strong>Bayar di Tempat</strong>! Anda dapat membayar langsung kepada kurir saat paket diterima.
            </p>

            <h3>Ketentuan COD:</h3>
            <ul>
                <li><strong>Area Coverage:</strong> Jakarta, Bogor, Depok, Tangerang, Bekasi (Jabodetabek)</li>
                <li><strong>COD Fee:</strong> Rp 10.000 per order</li>
                <li><strong>Minimum Order:</strong> Rp 100.000</li>
                <li><strong>Maximum Order:</strong> Rp 2.000.000 (untuk keamanan)</li>
                <li><strong>Payment:</strong> Cash only, siapkan uang pas atau kembalian kecil</li>
            </ul>

            <h3>Prosedur COD:</h3>

            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>1️⃣ Pilih COD saat Checkout</h4>
                        <p>Saat payment method selection, choose <strong>"Cash on Delivery (COD)"</strong></p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>2️⃣ Konfirmasi Order</h4>
                        <p>Pesanan akan diproses setelah konfirmasi. No payment needed at this stage.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>3️⃣ Paket Dikirim</h4>
                        <p>Receive tracking number via email/WhatsApp untuk monitor shipment.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>4️⃣ Kurir Arrives</h4>
                        <p>Pastikan ada yang di rumah untuk receive package. Periksa paket sebelum bayar.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h4>5️⃣ Bayar ke Kurir</h4>
                        <p>Bayar total amount (product + shipping + COD fee) kepada kurir dalam cash.</p>
                    </div>
                </div>
            </div>

            <div class="warning-box">
                <p><strong>⚠️ Penting untuk COD:</strong></p>
                <ul style="margin: 12px 0 0 0; padding-left: 24px;">
                    <li>Siapkan uang pas atau kembalian kecil</li>
                    <li>Periksa paket di depan kurir sebelum membayar</li>
                    <li>Jika reject paket, COD fee tetap charged</li>
                    <li>Multiple reject attempts dapat block COD access untuk future orders</li>
                </ul>
            </div>
        </section>

        <!-- Section 7: Receiving Package -->
        <section id="receiving" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">📬</span>
                <h2>Penerimaan & Pengecekan Paket</h2>
            </div>

            <p>
                Untuk memastikan proses penerimaan berjalan smooth dan protect your rights sebagai customer, please follow guidelines ini:
            </p>

            <h3>Saat Kurir Tiba:</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>✅ Langkah-langkah Penerimaan Paket</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ol style="margin: 0; padding-left: 24px;">
                            <li><strong>Check Package Condition:</strong> Periksa apakah kemasan luar dalam kondisi baik (tidak rusak, basah, atau penyok)</li>
                            <li><strong>Verify Address:</strong> Pastikan paket ditujukan untuk alamat Anda (check label)</li>
                            <li><strong>Check Sender:</strong> Konfirmasi pengirim adalah "Dorve.id" atau "Dorve Official"</li>
                            <li><strong>Sign Proof of Delivery:</strong> Tanda tangan bukti penerimaan HANYA jika paket dalam kondisi baik</li>
                            <li><strong>Video Unboxing (PENTING!):</strong> Record video tanpa jeda saat membuka paket untuk keperluan claim</li>
                        </ol>
                    </div>
                </div>
            </div>

            <h3>Video Unboxing - Syarat & Ketentuan:</h3>
            <div class="highlight-box">
                <div class="highlight-box-header">
                    <span class="highlight-icon">🎥</span>
                    <h4 class="highlight-title">WAJIB Video Unboxing!</h4>
                </div>
                <p>Video unboxing adalah <strong>SYARAT MUTLAK</strong> untuk claim warranty atau komplain:</p>
                <ul style="margin: 12px 0 0 0; padding-left: 24px;">
                    <li><strong>Start Recording:</strong> Sebelum membuka paket, mulai record video</li>
                    <li><strong>Show Package:</strong> Rekam seluruh sisi paket, label, dan kondisi luar</li>
                    <li><strong>Continuous Recording:</strong> NO PAUSE - video harus continuous tanpa cut</li>
                    <li><strong>Show Contents:</strong> Rekam seluruh isi paket saat dibuka</li>
                    <li><strong>Check Items:</strong> Tunjukkan setiap item, tags, dan detail produk</li>
                    <li><strong>Document Issues:</strong> Jika ada masalah, show clearly dalam video</li>
                </ul>
            </div>

            <h3>Jika Paket Rusak/Bermasalah:</h3>
            <ul>
                <li><strong>Jangan Terima:</strong> Refuse paket jika kemasan luar sangat rusak atau bocor</li>
                <li><strong>Foto/Video:</strong> Document kondisi paket dengan kurir sebagai saksi</li>
                <li><strong>Report Immediately:</strong> Hubungi customer service dalam 1x24 jam</li>
                <li><strong>Provide Evidence:</strong> Kirimkan foto/video kondisi paket</li>
            </ul>

            <h3>Jika Tidak Ada yang di Rumah:</h3>
            <ul>
                <li>Kurir akan attempt delivery 2-3 kali</li>
                <li>Leave card notification dengan contact info</li>
                <li>Paket mungkin disimpan di kantor cabang ekspedisi terdekat untuk self pick-up</li>
                <li>Jika 7 hari tidak diambil, paket akan retur ke Dorve.id warehouse</li>
            </ul>
        </section>

        <!-- Section 8: Holidays & Peak Season -->
        <section id="holidays" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">🎊</span>
                <h2>Pengiriman saat Hari Raya & Peak Season</h2>
            </div>

            <p>
                Selama periode high-volume shopping atau hari libur nasional, mohon perhatikan informasi berikut untuk manage expectations:
            </p>

            <h3>Peak Periods:</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>🌙 Ramadan & Hari Raya Idul Fitri</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Processing Time:</strong> +1-2 hari extra untuk order verification</li>
                            <li><strong>Delivery Time:</strong> +2-4 hari tambahan karena high volume</li>
                            <li><strong>Cutoff Orders:</strong> Orders placed 7 hari sebelum Lebaran mungkin delivered setelah Lebaran</li>
                            <li><strong>Operational Hours:</strong> Reduced hours selama bulan Ramadan (info akan diupdate)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>🎄 Natal & Tahun Baru</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Holiday Closures:</strong> No processing pada 25 Dec & 1 Jan</li>
                            <li><strong>Delivery Delays:</strong> Expect +3-5 hari delays untuk period 20 Dec - 5 Jan</li>
                            <li><strong>Last Order Date:</strong> Order sebelum 20 Dec untuk receive before Christmas</li>
                            <li><strong>Year-End Sales:</strong> High volume during 12.12, Boxing Day sales</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>🛍️ Shopping Event Periods</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Harbolnas (12.12):</strong> Volume surge - expect +2-3 hari delays</li>
                            <li><strong>Payday Sales (Tanggal Gajian):</strong> High order volume setiap akhir/awal bulan</li>
                            <li><strong>Flash Sale Events:</strong> Processing prioritized untuk flash sale orders</li>
                            <li><strong>Black Friday/Cyber Monday:</strong> International shopping events with local impact</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <p><strong>📢 Stay Informed:</strong> Kami akan announce operational changes dan estimated delays via email, website banner, dan social media selama peak periods. Plan your orders accordingly!</p>
            </div>
        </section>

        <!-- Section 9: Shipping Issues -->
        <section id="issues" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">⚠️</span>
                <h2>Kendala & Masalah Pengiriman</h2>
            </div>

            <p>
                Meski kami bekerja dengan partner terpercaya, kadang issues dapat terjadi. Berikut adalah guidelines untuk common shipping problems:
            </p>

            <h3>Paket Terlambat:</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>🕐 Apa yang Harus Dilakukan?</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <ol style="margin: 0; padding-left: 24px;">
                            <li><strong>Check Tracking:</strong> Verify last known position dari paket</li>
                            <li><strong>Wait Period:</strong> Give 2-3 hari grace period melewati estimated date</li>
                            <li><strong>Contact Courier:</strong> Call courier customer service dengan nomor resi</li>
                            <li><strong>Contact Dorve.id:</strong> If unresolved, hubungi kami untuk escalation</li>
                            <li><strong>We'll Handle:</strong> Tim kami akan coordinate dengan ekspedisi untuk track down paket</li>
                        </ol>
                        <div class="info-box" style="margin-top: 16px;">
                            <p><strong>💡 Common Delay Causes:</strong> Bad weather, high volume periods, incorrect address, remote locations, holidays, customs (untuk international items).</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Paket Hilang:</h3>
            <p>
                Jika tracking menunjukkan paket hilang atau not moving for extended period:
            </p>
            <ul>
                <li><strong>Report Time:</strong> Segera hubungi kami jika paket stuck >10 hari tanpa movement</li>
                <li><strong>Investigation:</strong> Kami akan file official investigation dengan ekspedisi</li>
                <li><strong>Resolution Time:</strong> Investigation biasanya takes 7-14 hari</li>
                <li><strong>Compensation:</strong> Jika confirmed lost, kami akan:
                    <ul>
                        <li>Kirim replacement item (jika stock available)</li>
                        <li>Full refund including shipping cost (jika out of stock)</li>
                        <li>Store credit dengan bonus 10%</li>
                    </ul>
                </li>
            </ul>

            <h3>Paket Rusak saat Transit:</h3>

            <div class="accordion-item">
                <button class="accordion-header" onclick="toggleAccordion(this)">
                    <span>📦 Handling Damaged Packages</span>
                    <span class="accordion-arrow">▼</span>
                </button>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p><strong>Jika paket diterima dalam kondisi rusak:</strong></p>
                        <ol style="margin: 12px 0 0 0; padding-left: 24px;">
                            <li><strong>Document Immediately:</strong> Foto/video paket rusak dengan kurir sebagai witness</li>
                            <li><strong>Check Contents:</strong> Lakukan unboxing video untuk verify item condition</li>
                            <li><strong>Report 1x24 Jam:</strong> Contact customer service ASAP dengan bukti foto/video</li>
                            <li><strong>Don't Discard:</strong> Keep packaging dan item untuk investigation</li>
                            <li><strong>Claim Process:</strong> Kami akan process claim dengan ekspedisi</li>
                        </ol>
                        <div class="warning-box" style="margin-top: 16px;">
                            <p><strong>⚠️ Critical:</strong> Claims HARUS reported dalam 1x24 jam setelah delivery status. Late reports may not be eligible untuk compensation!</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3>Alamat Salah / Tidak Lengkap:</h3>
            <ul>
                <li>Pastikan provide <strong>complete dan accurate address</strong> saat checkout</li>
                <li>Include landmark, nomor rumah, RT/RW, kode pos</li>
                <li>Provide active phone number untuk courier contact</li>
                <li>Jika address incomplete, kurir may call untuk clarification</li>
                <li>Failed delivery karena wrong address adalah responsibility customer</li>
            </ul>

            <div class="success-box">
                <p><strong>✅ Our Commitment:</strong> Meski shipping issues tidak fully dalam control kami, kami commit untuk always support Anda dan work towards best resolution untuk every case!</p>
            </div>
        </section>

        <!-- Section 10: Contact Us -->
        <section id="contact" class="shipping-section">
            <div class="section-header">
                <span class="section-icon">📞</span>
                <h2>Hubungi Customer Service</h2>
            </div>

            <p>
                Butuh bantuan dengan shipping atau ada pertanyaan tentang pengiriman? Tim customer service kami siap membantu!
            </p>

            <h3>Contact Methods:</h3>
            <ul>
                <li>
                    <strong>📧 Email:</strong> support@dorve.id<br>
                    <span style="color: var(--grey); font-size: 14px;">Response time: 1-2 business days | Best for: Komplain, documentation, formal inquiries</span>
                </li>
                <li>
                    <strong>💬 WhatsApp:</strong> +62 813-7737-8859<br>
                    <span style="color: var(--grey); font-size: 14px;">Response time: 1-4 hours | Best for: Quick questions, tracking updates, urgent issues</span>
                </li>
                <li>
                    <strong>📱 Instagram:</strong> @dorve.id<br>
                    <span style="color: var(--grey); font-size: 14px;">Response time: 2-6 hours | Best for: General inquiries, quick questions</span>
                </li>
                <li>
                    <strong>🌐 Live Chat:</strong> Available di website<br>
                    <span style="color: var(--grey); font-size: 14px;">During business hours | Best for: Real-time assistance</span>
                </li>
            </ul>

            <h3>Operating Hours:</h3>
            <ul>
                <li><strong>Monday - Friday:</strong> 09:00 - 17:00 WIB</li>
                <li><strong>Saturday:</strong> 10:00 - 15:00 WIB</li>
                <li><strong>Sunday & Public Holidays:</strong> Closed (messages akan replied next business day)</li>
            </ul>

            <div class="info-box">
                <p><strong>💡 Tips untuk Fast Response:</strong></p>
                <ul style="margin: 12px 0 0 0; padding-left: 24px;">
                    <li>Include order number dalam semua inquiries</li>
                    <li>Provide tracking number jika pertanyaan tentang delivery</li>
                    <li>Attach relevant photos/videos untuk visual issues</li>
                    <li>Be specific tentang issue atau pertanyaan Anda</li>
                </ul>
            </div>
        </section>

        <!-- Final Note -->
        <div class="highlight-box" style="margin-top: 60px;">
            <div class="highlight-box-header">
                <span class="highlight-icon">💝</span>
                <h4 class="highlight-title">Terima Kasih Memilih Dorve.id</h4>
            </div>
            <p>
                Kami appreciate your trust in <strong>Dorve.id</strong> untuk fulfill your fashion needs. Shipping policy ini designed untuk provide transparency dan ensure best possible delivery experience. Kami continuously improve our logistics untuk serve you better. Happy shopping! 🛍️
            </p>
        </div>

        <!-- CTA Section -->
        <div class="contact-cta">
            <h3>Siap Berbelanja dengan Gratis Ongkir?</h3>
            <p>Explore koleksi terbaru dan nikmati FREE SHIPPING untuk orders >Rp 500.000!</p>
            <div class="contact-buttons">
                <a href="/pages/all-products.php" class="contact-btn">
                    <span>🛍️</span>
                    <span>Browse Collections</span>
                </a>
                <a href="https://wa.me/6281377378859?text=Halo%20Dorve.id,%20saya%20mau%20tanya%20tentang%20pengiriman" class="contact-btn" target="_blank">
                    <span>💬</span>
                    <span>Chat about Shipping</span>
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
        const section = button.closest('.shipping-section');
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
        const sections = document.querySelectorAll('.shipping-section');
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