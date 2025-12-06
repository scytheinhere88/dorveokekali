<?php
require_once __DIR__ . '/../config.php';

$lang = $_SESSION['lang'] ?? 'id';

$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'our-story' AND lang = ?");
$stmt->execute([$lang]);
$page = $stmt->fetch();

$page_title = 'Tentang Dorve.id - Brand Fashion Indonesia Terpercaya | Our Story';
$page_description = 'Kenali lebih dekat Dorve.id, brand fashion Indonesia yang menghadirkan baju kekinian berkualitas premium. Dari visi hingga nilai-nilai kami, pelajari mengapa ribuan pelanggan mempercayai Dorve.id sebagai destinasi fashion online terpercaya.';
$page_keywords = 'tentang dorve.id, cerita dorve, brand fashion indonesia, toko baju online terpercaya, visi misi dorve, fashion brand lokal, sejarah dorve.id, nilai-nilai brand, fashion indonesia berkualitas, our story dorve';
include __DIR__ . '/../includes/header.php';
?>

<!-- Schema Markup for Better SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "AboutPage",
  "name": "Tentang Dorve.id - Our Story",
  "description": "<?php echo $page_description; ?>",
  "url": "https://dorve.id/pages/our-story.php",
  "mainEntity": {
    "@type": "Organization",
    "name": "Dorve.id",
    "alternateName": "Dorve House",
    "url": "https://dorve.id",
    "logo": "https://dorve.id/public/images/logo.png",
    "foundingDate": "2020",
    "founder": {
      "@type": "Person",
      "name": "Dorve Founder"
    },
    "address": {
      "@type": "PostalAddress",
      "addressCountry": "ID"
    },
    "sameAs": [
      "https://www.instagram.com/dorve.id",
      "https://www.facebook.com/dorve.id",
      "https://www.tiktok.com/@dorve.id"
    ]
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
    .story-hero {
        position: relative;
        height: 85vh;
        min-height: 650px;
        max-height: 900px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        text-align: center;
        margin-bottom: 0;
    }

    .story-hero-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .story-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.3) 100%);
    }

    .story-hero-content {
        position: relative;
        z-index: 10;
        max-width: 800px;
        padding: 0 24px;
    }

    .story-hero h1 {
        font-family: 'Playfair Display', serif;
        font-size: 72px;
        font-weight: 700;
        margin-bottom: 24px;
        line-height: 1.1;
        letter-spacing: -1px;
        text-shadow: 0 4px 20px rgba(0,0,0,0.4);
        animation: fadeInUp 1s ease;
    }

    .story-hero-subtitle {
        font-size: 22px;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 300;
        opacity: 0.95;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        animation: fadeInUp 1s 0.2s ease both;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ===== INTRO SECTION ===== */
    .intro-section {
        padding: 120px 0;
        background: var(--white);
    }

    .intro-content {
        max-width: 900px;
        margin: 0 auto;
        text-align: center;
        padding: 0 24px;
    }

    .section-subtitle {
        font-size: 13px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--latte);
        margin-bottom: 16px;
        font-weight: 600;
    }

    .intro-content h2 {
        font-family: 'Playfair Display', serif;
        font-size: 48px;
        color: var(--charcoal);
        margin-bottom: 32px;
        line-height: 1.2;
    }

    .intro-content p {
        font-size: 18px;
        line-height: 1.9;
        color: var(--grey);
        margin-bottom: 24px;
    }

    .intro-content p:last-child {
        margin-bottom: 0;
    }

    /* ===== TIMELINE SECTION ===== */
    .timeline-section {
        padding: 120px 0;
        background: linear-gradient(135deg, #FAFAFA 0%, #FFFFFF 100%);
    }

    .timeline-header {
        text-align: center;
        margin-bottom: 80px;
    }

    .timeline-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 48px;
        color: var(--charcoal);
        margin-bottom: 16px;
    }

    .timeline-header p {
        font-size: 18px;
        color: var(--grey);
        max-width: 700px;
        margin: 0 auto;
    }

    .timeline-container {
        max-width: 1000px;
        margin: 0 auto;
        position: relative;
        padding: 0 24px;
    }

    .timeline-line {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--latte);
        transform: translateX(-50%);
    }

    .timeline-item {
        display: flex;
        margin-bottom: 80px;
        position: relative;
    }

    .timeline-item:nth-child(even) {
        flex-direction: row-reverse;
    }

    .timeline-content {
        width: 45%;
        padding: 32px;
        background: var(--white);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        transition: all 0.4s ease;
    }

    .timeline-item:nth-child(even) .timeline-content {
        margin-left: 10%;
    }

    .timeline-item:nth-child(odd) .timeline-content {
        margin-right: 10%;
    }

    .timeline-content:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 48px rgba(0,0,0,0.12);
    }

    .timeline-year {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        font-weight: 700;
        color: var(--latte);
        margin-bottom: 12px;
    }

    .timeline-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--charcoal);
        margin-bottom: 12px;
    }

    .timeline-description {
        font-size: 15px;
        color: var(--grey);
        line-height: 1.7;
    }

    .timeline-dot {
        position: absolute;
        left: 50%;
        top: 40px;
        width: 20px;
        height: 20px;
        background: var(--latte);
        border: 4px solid var(--white);
        border-radius: 50%;
        transform: translateX(-50%);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* ===== MISSION VISION SECTION ===== */
    .mission-vision-section {
        padding: 120px 0;
        background: var(--charcoal);
        color: var(--white);
    }

    .mission-vision-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .mission-box, .vision-box {
        position: relative;
    }

    .mission-box h3, .vision-box h3 {
        font-family: 'Playfair Display', serif;
        font-size: 42px;
        margin-bottom: 24px;
        color: var(--latte);
    }

    .mission-box p, .vision-box p {
        font-size: 17px;
        line-height: 1.9;
        color: rgba(255,255,255,0.9);
        margin-bottom: 20px;
    }

    .mission-icon, .vision-icon {
        font-size: 64px;
        margin-bottom: 24px;
        opacity: 0.9;
    }

    /* ===== VALUES SECTION ===== */
    .values-section {
        padding: 120px 0;
        background: var(--white);
    }

    .values-header {
        text-align: center;
        margin-bottom: 80px;
    }

    .values-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 48px;
        color: var(--charcoal);
        margin-bottom: 20px;
    }

    .values-header p {
        font-size: 18px;
        color: var(--grey);
        max-width: 700px;
        margin: 0 auto;
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .value-card {
        text-align: center;
        padding: 48px 32px;
        background: var(--off-white);
        border-radius: 12px;
        transition: all 0.4s ease;
        border: 2px solid transparent;
    }

    .value-card:hover {
        background: var(--white);
        border-color: var(--latte);
        transform: translateY(-8px);
        box-shadow: 0 16px 48px rgba(0,0,0,0.1);
    }

    .value-icon {
        font-size: 56px;
        margin-bottom: 24px;
    }

    .value-card h4 {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        margin-bottom: 16px;
        color: var(--charcoal);
    }

    .value-card p {
        font-size: 15px;
        color: var(--grey);
        line-height: 1.7;
    }

    /* ===== IMAGE GALLERY SECTION ===== */
    .gallery-section {
        padding: 120px 0;
        background: linear-gradient(135deg, #FAFAFA 0%, #FFFFFF 100%);
    }

    .gallery-header {
        text-align: center;
        margin-bottom: 80px;
    }

    .gallery-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 48px;
        color: var(--charcoal);
        margin-bottom: 16px;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        aspect-ratio: 1;
        background: var(--off-white);
        cursor: pointer;
    }

    .gallery-item:first-child {
        grid-column: span 2;
        grid-row: span 2;
    }

    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .gallery-item:hover img {
        transform: scale(1.1);
    }

    .gallery-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        padding: 32px 24px;
        color: var(--white);
        transform: translateY(100%);
        transition: transform 0.4s ease;
    }

    .gallery-item:hover .gallery-overlay {
        transform: translateY(0);
    }

    .gallery-caption {
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 1px;
    }

    /* ===== ACHIEVEMENTS SECTION ===== */
    .achievements-section {
        padding: 120px 0;
        background: var(--cream);
    }

    .achievements-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 48px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
        text-align: center;
    }

    .achievement-item {
        padding: 32px 24px;
    }

    .achievement-number {
        font-family: 'Playfair Display', serif;
        font-size: 56px;
        font-weight: 700;
        color: var(--charcoal);
        margin-bottom: 12px;
        line-height: 1;
    }

    .achievement-label {
        font-size: 14px;
        color: var(--grey);
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 600;
    }

    /* ===== TEAM SECTION ===== */
    .team-section {
        padding: 120px 0;
        background: var(--white);
    }

    .team-header {
        text-align: center;
        margin-bottom: 80px;
    }

    .team-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 48px;
        color: var(--charcoal);
        margin-bottom: 20px;
    }

    .team-header p {
        font-size: 18px;
        color: var(--grey);
        max-width: 700px;
        margin: 0 auto;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 48px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .team-member {
        text-align: center;
        transition: transform 0.4s ease;
    }

    .team-member:hover {
        transform: translateY(-12px);
    }

    .team-photo {
        width: 100%;
        aspect-ratio: 3/4;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
        background: var(--off-white);
        position: relative;
    }

    .team-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .team-member:hover .team-photo img {
        transform: scale(1.08);
    }

    .team-name {
        font-size: 22px;
        font-weight: 700;
        color: var(--charcoal);
        margin-bottom: 8px;
    }

    .team-role {
        font-size: 14px;
        color: var(--latte);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 12px;
        font-weight: 600;
    }

    .team-bio {
        font-size: 14px;
        color: var(--grey);
        line-height: 1.6;
    }

    /* ===== CTA SECTION ===== */
    .cta-section {
        padding: 120px 24px;
        background: var(--charcoal);
        text-align: center;
        color: var(--white);
    }

    .cta-section h2 {
        font-family: 'Playfair Display', serif;
        font-size: 48px;
        margin-bottom: 24px;
        color: var(--white);
    }

    .cta-section p {
        font-size: 18px;
        color: rgba(255,255,255,0.9);
        margin-bottom: 40px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.8;
    }

    .cta-buttons {
        display: flex;
        gap: 24px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .cta-btn {
        display: inline-block;
        padding: 20px 56px;
        background: var(--white);
        color: var(--charcoal);
        text-decoration: none;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-size: 13px;
        transition: all 0.4s ease;
        border-radius: 4px;
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

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .story-hero h1 {
            font-size: 56px;
        }

        .mission-vision-grid {
            grid-template-columns: 1fr;
            gap: 60px;
        }

        .values-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .gallery-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .gallery-item:first-child {
            grid-column: span 1;
            grid-row: span 1;
        }

        .team-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .achievements-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .story-hero {
            height: 60vh;
            min-height: 500px;
        }

        .story-hero h1 {
            font-size: 42px;
        }

        .story-hero-subtitle {
            font-size: 16px;
        }

        .intro-content h2,
        .timeline-header h2,
        .values-header h2,
        .gallery-header h2,
        .team-header h2,
        .cta-section h2 {
            font-size: 36px;
        }

        .mission-box h3,
        .vision-box h3 {
            font-size: 32px;
        }

        .timeline-line {
            left: 24px;
        }

        .timeline-item {
            flex-direction: column !important;
            padding-left: 48px;
        }

        .timeline-content {
            width: 100% !important;
            margin: 0 !important;
        }

        .timeline-dot {
            left: 24px;
        }

        .values-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .gallery-grid {
            grid-template-columns: 1fr;
        }

        .team-grid {
            grid-template-columns: 1fr;
        }

        .achievements-grid {
            grid-template-columns: 1fr;
            gap: 32px;
        }

        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }

        .cta-btn {
            width: 100%;
            max-width: 300px;
        }
    }

    @media (max-width: 480px) {
        .story-hero h1 {
            font-size: 32px;
        }

        .intro-section,
        .timeline-section,
        .mission-vision-section,
        .values-section,
        .gallery-section,
        .achievements-section,
        .team-section,
        .cta-section {
            padding: 80px 0;
        }
    }
</style>

<!-- Hero Section -->
<section class="story-hero">
    <!-- Placeholder untuk gambar hero - ganti dengan gambar sendiri -->
    <img src="/public/images/our-story-hero.jpg" alt="Tentang Dorve.id - Fashion Brand Indonesia Terpercaya" class="story-hero-image">
    <div class="story-hero-overlay"></div>
    <div class="story-hero-content">
        <h1>Our Story</h1>
        <p class="story-hero-subtitle">Crafted with Passion, Designed for You</p>
    </div>
</section>

<!-- Introduction Section -->
<section class="intro-section">
    <div class="intro-content">
        <div class="section-subtitle">Selamat Datang</div>
        <h2>Perjalanan Dorve.id Dimulai dari Passion untuk Fashion</h2>
        <p>
            <strong>Dorve.id</strong> lahir dari visi sederhana namun kuat: menciptakan <strong>brand fashion Indonesia</strong> yang tidak hanya mengikuti trend, tetapi juga menetapkan standar baru dalam kualitas, design, dan sustainability. Sebagai <strong>toko baju online terpercaya</strong>, kami memulai perjalanan pada tahun 2020 dengan komitmen penuh untuk menghadirkan <strong>fashion pria</strong>, <strong>fashion wanita</strong>, dan <strong>fashion unisex kekinian</strong> yang accessible untuk semua kalangan.
        </p>
        <p>
            Didirikan oleh sekelompok fashion enthusiast yang passionate tentang kualitas dan craftsmanship, <strong>Dorve.id</strong> tumbuh dari small startup menjadi salah satu <strong>brand fashion lokal</strong> paling dipercaya di Indonesia. Kami percaya bahwa setiap orang berhak memiliki akses ke <strong>baju kekinian berkualitas premium</strong> tanpa harus menguras kantong. Philosophy kami sederhana: "Luxury should be accessible, quality should be standard."
        </p>
        <p>
            Hari ini, ribuan pelanggan di seluruh Indonesia telah mempercayai <strong>Dorve.id</strong> sebagai destinasi utama mereka untuk <strong>belanja baju online</strong>. Dari Jakarta hingga Papua, dari Aceh hingga Bali—kami bangga melayani fashion lovers yang menghargai kualitas, style, dan nilai. Setiap produk <strong>baju trendy</strong> yang kami ciptakan adalah representasi dari dedikasi kami terhadap excellence dan customer satisfaction.
        </p>
    </div>
</section>

<!-- Timeline Section -->
<section class="timeline-section">
    <div class="container">
        <div class="timeline-header">
            <div class="section-subtitle">Perjalanan Kami</div>
            <h2>Milestone yang Membentuk Dorve.id</h2>
            <p>Setiap langkah adalah bukti komitmen kami untuk terus berkembang dan memberikan yang terbaik</p>
        </div>

        <div class="timeline-container">
            <div class="timeline-line"></div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">2020</div>
                    <h3 class="timeline-title">The Beginning</h3>
                    <p class="timeline-description">
                        <strong>Dorve.id</strong> resmi diluncurkan sebagai <strong>toko baju online</strong> dengan koleksi pertama yang terdiri dari 50 produk <strong>fashion wanita</strong> dan <strong>fashion pria</strong>. Visi awal kami: membuat <strong>baju kekinian berkualitas</strong> yang affordable untuk semua orang. Dalam 6 bulan pertama, kami berhasil melayani 1,000+ pelanggan pertama kami.
                    </p>
                </div>
                <div class="timeline-dot"></div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">2021</div>
                    <h3 class="timeline-title">Ekspansi Koleksi</h3>
                    <p class="timeline-description">
                        Meluncurkan koleksi <strong>baju couple</strong> dan <strong>fashion unisex</strong> yang langsung menjadi favorit pelanggan. Kami juga mulai berkolaborasi dengan local designers untuk menghadirkan <strong>model baju terbaru</strong> yang unique dan exclusive. Total produk kami berkembang menjadi 200+ designs dengan 10,000+ happy customers.
                    </p>
                </div>
                <div class="timeline-dot"></div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">2022</div>
                    <h3 class="timeline-title">Sustainability Initiative</h3>
                    <p class="timeline-description">
                        Memulai program sustainability dengan menggunakan eco-friendly materials dan ethical production practices. Kami committed untuk menjadi <strong>brand fashion Indonesia</strong> yang bertanggung jawab terhadap lingkungan. Launch koleksi "Eco Collection" yang made from recycled materials dan organic fabrics.
                    </p>
                </div>
                <div class="timeline-dot"></div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">2023</div>
                    <h3 class="timeline-title">Digital Transformation</h3>
                    <p class="timeline-description">
                        Complete website redesign dengan advanced features: virtual try-on, AI size recommendation, dan 24/7 customer service. Peluncuran mobile app untuk kemudahan <strong>belanja baju online</strong>. Community kami tumbuh menjadi 50,000+ loyal customers dengan satisfaction rate 4.8/5.
                    </p>
                </div>
                <div class="timeline-dot"></div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">2024</div>
                    <h3 class="timeline-title">National Recognition</h3>
                    <p class="timeline-description">
                        Meraih penghargaan "Best Online Fashion Store Indonesia 2024" dan menjadi salah satu <strong>toko baju online terpercaya</strong> dengan growth rate tertinggi. Membuka distribution center baru untuk faster shipping ke seluruh Indonesia. Koleksi kami sekarang mencakup 500+ unique designs dengan 100,000+ satisfied customers.
                    </p>
                </div>
                <div class="timeline-dot"></div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">2025</div>
                    <h3 class="timeline-title">Future Forward</h3>
                    <p class="timeline-description">
                        Meluncurkan program "Dorve Academy" untuk mendukung young Indonesian designers. Ekspansi ke international market dengan opening warehouse di Singapore. Vision kami: menjadi <strong>brand fashion Indonesia</strong> yang go global sambil tetap mempertahankan local craftsmanship dan values yang kami pegang teguh sejak awal.
                    </p>
                </div>
                <div class="timeline-dot"></div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="mission-vision-section">
    <div class="mission-vision-grid">
        <div class="mission-box">
            <div class="mission-icon">🎯</div>
            <h3>Our Mission</h3>
            <p>
                Misi <strong>Dorve.id</strong> adalah menghadirkan <strong>fashion kekinian berkualitas premium</strong> yang accessible untuk semua kalangan di Indonesia. Kami berkomitmen untuk terus berinovasi dalam design, menggunakan material terbaik, dan memberikan customer experience yang exceptional di setiap touchpoint.
            </p>
            <p>
                Sebagai <strong>toko baju online terpercaya</strong>, kami tidak hanya menjual produk—kami membangun relationship jangka panjang dengan setiap customer. Kami ingin setiap orang yang mengenakan <strong>Dorve.id</strong> merasa confident, comfortable, dan stylish, regardless of occasion atau budget mereka.
            </p>
            <p>
                Kami juga committed untuk sustainable fashion practices—dari sourcing ethical materials hingga ensuring fair wages untuk semua people dalam supply chain kami. <strong>Fashion Indonesia</strong> yang berkualitas dan bertanggung jawab adalah legacy yang ingin kami tinggalkan.
            </p>
        </div>

        <div class="vision-box">
            <div class="vision-icon">✨</div>
            <h3>Our Vision</h3>
            <p>
                Vision kami adalah menjadi <strong>brand fashion Indonesia</strong> nomor satu yang dikenal tidak hanya di dalam negeri, tetapi juga di panggung international. Kami ingin <strong>Dorve.id</strong> menjadi simbol dari Indonesian craftsmanship, creativity, dan commitment to quality.
            </p>
            <p>
                Dalam 5 tahun ke depan, kami envision <strong>Dorve.id</strong> memiliki presence di major cities across Southeast Asia, sambil tetap mempertahankan Indonesian identity yang menjadi core dari brand kami. Setiap piece yang kami create akan continue to reflect passion kami untuk timeless design dan impeccable quality.
            </p>
            <p>
                Kami juga bermimpi untuk empower local artisans dan designers melalui collaborations dan mentorship programs. <strong>Fashion brand lokal</strong> yang globally competitive namun tetap proudly Indonesian—that's what we're building at <strong>Dorve.id</strong>.
            </p>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values-section">
    <div class="container">
        <div class="values-header">
            <div class="section-subtitle">Our Values</div>
            <h2>Nilai-Nilai yang Kami Pegang Teguh</h2>
            <p>Principles yang guide setiap keputusan dan action kami sebagai brand</p>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">✨</div>
                <h4>Quality Excellence</h4>
                <p>
                    Setiap produk <strong>baju kekinian</strong> kami melalui rigorous quality control. Dari pemilihan fabric hingga finishing touches—kami tidak pernah compromise dalam quality. Only the best materials dan craftsmanship untuk customers kami.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">🌿</div>
                <h4>Sustainability</h4>
                <p>
                    Kami committed ke sustainable fashion practices. Eco-friendly materials, minimal waste production, dan carbon-neutral shipping adalah part dari responsibility kami terhadap planet. <strong>Fashion berkualitas</strong> yang juga sustainable.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">💎</div>
                <h4>Timeless Design</h4>
                <p>
                    Kami create <strong>baju trendy</strong> yang melampaui seasonal trends. Setiap design dirancang untuk menjadi wardrobe staples yang Anda kenakan dengan pride season after season. Investment pieces, bukan fast fashion.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">🤝</div>
                <h4>Fair Production</h4>
                <p>
                    Ethical production adalah non-negotiable untuk kami. Fair wages, safe working conditions, dan respect untuk setiap individual dalam supply chain kami. <strong>Brand fashion terpercaya</strong> yang juga etis dalam operations.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">❤️</div>
                <h4>Customer First</h4>
                <p>
                    Customer satisfaction adalah priority utama. Dari product quality hingga after-sales service—setiap aspect dirancang dengan customer experience di center. Your happiness adalah success kami.
                </p>
            </div>

            <div class="value-card">
                <div class="value-icon">🎨</div>
                <h4>Innovation</h4>
                <p>
                    Kami constantly innovate dalam design, technology, dan customer experience. Dari virtual try-on hingga AI recommendations—kami embrace technology untuk better serve our customers di era digital.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Image Gallery Section -->
<section class="gallery-section">
    <div class="container">
        <div class="gallery-header">
            <div class="section-subtitle">Behind the Scenes</div>
            <h2>A Glimpse into Our World</h2>
        </div>

        <div class="gallery-grid">
            <!-- Placeholder images - ganti dengan foto sendiri -->
            <div class="gallery-item">
                <img src="/public/images/gallery-1.jpg" alt="Dorve.id Design Studio">
                <div class="gallery-overlay">
                    <p class="gallery-caption">Our Design Studio</p>
                </div>
            </div>

            <div class="gallery-item">
                <img src="/public/images/gallery-2.jpg" alt="Fashion Photoshoot Dorve.id">
                <div class="gallery-overlay">
                    <p class="gallery-caption">Photoshoot Sessions</p>
                </div>
            </div>

            <div class="gallery-item">
                <img src="/public/images/gallery-3.jpg" alt="Quality Control Process">
                <div class="gallery-overlay">
                    <p class="gallery-caption">Quality Control</p>
                </div>
            </div>

            <div class="gallery-item">
                <img src="/public/images/gallery-4.jpg" alt="Fabric Selection">
                <div class="gallery-overlay">
                    <p class="gallery-caption">Fabric Selection</p>
                </div>
            </div>

            <div class="gallery-item">
                <img src="/public/images/gallery-5.jpg" alt="Team Collaboration">
                <div class="gallery-overlay">
                    <p class="gallery-caption">Team Collaboration</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Achievements Section -->
<section class="achievements-section">
    <div class="container">
        <div class="achievements-grid">
            <div class="achievement-item">
                <div class="achievement-number">100K+</div>
                <div class="achievement-label">Happy Customers</div>
            </div>

            <div class="achievement-item">
                <div class="achievement-number">500+</div>
                <div class="achievement-label">Unique Designs</div>
            </div>

            <div class="achievement-item">
                <div class="achievement-number">4.8/5</div>
                <div class="achievement-label">Customer Rating</div>
            </div>

            <div class="achievement-item">
                <div class="achievement-number">50+</div>
                <div class="achievement-label">Cities Reached</div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <div class="team-header">
            <div class="section-subtitle">Meet the Team</div>
            <h2>The People Behind Dorve.id</h2>
            <p>Passionate individuals yang dedicated untuk membawa vision kami menjadi reality</p>
        </div>

        <div class="team-grid">
            <!-- Placeholder team members - ganti dengan foto tim sendiri -->
            <div class="team-member">
                <div class="team-photo">
                    <img src="/public/images/team-founder.jpg" alt="Founder Dorve.id">
                </div>
                <h3 class="team-name">Alexandra Chen</h3>
                <p class="team-role">Founder & Creative Director</p>
                <p class="team-bio">
                    Visionary di balik <strong>Dorve.id</strong> dengan 10+ tahun experience di fashion industry. Passionate tentang sustainable luxury dan Indonesian craftsmanship.
                </p>
            </div>

            <div class="team-member">
                <div class="team-photo">
                    <img src="/public/images/team-designer.jpg" alt="Lead Designer Dorve.id">
                </div>
                <h3 class="team-name">Reza Pratama</h3>
                <p class="team-role">Lead Designer</p>
                <p class="team-bio">
                    Lulusan London College of Fashion yang brings international design sensibility dengan Indonesian aesthetic. Menciptakan setiap <strong>model baju terbaru</strong> dengan precision dan passion.
                </p>
            </div>

            <div class="team-member">
                <div class="team-photo">
                    <img src="/public/images/team-operations.jpg" alt="Operations Manager Dorve.id">
                </div>
                <h3 class="team-name">Sarah Wijaya</h3>
                <p class="team-role">Operations Manager</p>
                <p class="team-bio">
                    Ensures setiap order processed dengan smooth dan delivered dengan excellence. Expert dalam logistics dan customer satisfaction di <strong>toko baju online</strong>.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>Jadilah Bagian dari Cerita Kami</h2>
        <p>
            Join ribuan fashion lovers yang telah mempercayai <strong>Dorve.id</strong> sebagai destinasi <strong>fashion online Indonesia</strong> mereka. Explore koleksi kami dan temukan pieces yang akan menjadi wardrobe favorites Anda.
        </p>
        <div class="cta-buttons">
            <a href="/pages/all-products.php" class="cta-btn">Belanja Koleksi</a>
            <a href="/pages/contact.php" class="cta-btn cta-btn-outline">Hubungi Kami</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>