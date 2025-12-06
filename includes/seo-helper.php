<?php
/**
 * SEO HELPER - DORVE.ID
 * Automatic SEO optimization untuk semua pages
 */

function generateSEO($page_type = 'page', $data = []) {
    // Default meta
    $site_name = 'Dorve House';
    $site_url = 'https://dorve.id';
    $default_image = $site_url . '/public/images/logo.png';
    
    // Extract data
    $title = $data['title'] ?? 'Dorve House - Fashion Online Terpercaya';
    $description = $data['description'] ?? 'Belanja fashion online terlengkap di Indonesia. Koleksi baju pria, wanita, dan unisex dengan harga terjangkau dan kualitas terjamin.';
    $image = $data['image'] ?? $default_image;
    $keywords = $data['keywords'] ?? 'baju online, fashion pria, fashion wanita, baju murah, toko baju online, dorve house';
    $canonical = $data['canonical'] ?? $_SERVER['REQUEST_URI'];
    
    // Generate full title
    $full_title = $title;
    // PHP 7.x compatible check
    if (strpos($title, 'Dorve') === false) {
        $full_title .= ' | Dorve House';
    }
    
    // Output meta tags
    echo "\n<!-- SEO Meta Tags -->\n";
    echo "<title>" . htmlspecialchars($full_title) . "</title>\n";
    echo "<meta name='description' content='" . htmlspecialchars($description) . "'>\n";
    echo "<meta name='keywords' content='" . htmlspecialchars($keywords) . "'>\n";
    echo "<link rel='canonical' href='" . $site_url . $canonical . "'>\n";
    
    // Open Graph
    echo "\n<!-- Open Graph Meta Tags -->\n";
    echo "<meta property='og:site_name' content='" . $site_name . "'>\n";
    echo "<meta property='og:title' content='" . htmlspecialchars($full_title) . "'>\n";
    echo "<meta property='og:description' content='" . htmlspecialchars($description) . "'>\n";
    echo "<meta property='og:image' content='" . $image . "'>\n";
    echo "<meta property='og:url' content='" . $site_url . $canonical . "'>\n";
    echo "<meta property='og:type' content='website'>\n";
    
    // Twitter Card
    echo "\n<!-- Twitter Card Meta Tags -->\n";
    echo "<meta name='twitter:card' content='summary_large_image'>\n";
    echo "<meta name='twitter:title' content='" . htmlspecialchars($full_title) . "'>\n";
    echo "<meta name='twitter:description' content='" . htmlspecialchars($description) . "'>\n";
    echo "<meta name='twitter:image' content='" . $image . "'>\n";
    
    // Viewport
    echo "\n<!-- Responsive Meta -->\n";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=5.0'>\n";
    echo "<meta name='format-detection' content='telephone=no'>\n";
}

function generateBreadcrumb($items = []) {
    if (empty($items)) return;
    
    $site_url = 'https://dorve.id';
    
    // JSON-LD Breadcrumb
    $breadcrumb_list = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => []
    ];
    
    foreach ($items as $index => $item) {
        $breadcrumb_list['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['name'],
            'item' => isset($item['url']) ? $site_url . $item['url'] : null
        ];
    }
    
    echo "\n<!-- Breadcrumb JSON-LD -->\n";
    echo "<script type='application/ld+json'>\n";
    echo json_encode($breadcrumb_list, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n</script>\n";
}

function generateProductSchema($product) {
    if (empty($product)) return;
    
    $site_url = 'https://dorve.id';
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name'],
        'description' => strip_tags($product['description'] ?? ''),
        'image' => $site_url . '/uploads/products/' . $product['image'],
        'sku' => $product['sku'] ?? $product['id'],
        'brand' => [
            '@type' => 'Brand',
            'name' => 'Dorve House'
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => $site_url . '/pages/product-detail.php?id=' . $product['id'],
            'priceCurrency' => 'IDR',
            'price' => $product['discount_price'] ?? $product['price'],
            'availability' => ($product['stock'] ?? 0) > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => [
                '@type' => 'Organization',
                'name' => 'Dorve House'
            ]
        ]
    ];
    
    // Add rating if exists
    if (isset($product['average_rating']) && $product['average_rating'] > 0) {
        $schema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => $product['average_rating'],
            'reviewCount' => $product['total_reviews'] ?? 1
        ];
    }
    
    echo "\n<!-- Product Schema JSON-LD -->\n";
    echo "<script type='application/ld+json'>\n";
    echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n</script>\n";
}

function generateOrganizationSchema() {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Dorve House',
        'url' => 'https://dorve.id',
        'logo' => 'https://dorve.id/public/images/logo.png',
        'description' => 'Toko fashion online terpercaya di Indonesia dengan koleksi lengkap baju pria, wanita, dan unisex.',
        'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => 'ID'
        ],
        'sameAs' => [
            // Add social media URLs here
        ]
    ];
    
    echo "\n<!-- Organization Schema JSON-LD -->\n";
    echo "<script type='application/ld+json'>\n";
    echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n</script>\n";
}
