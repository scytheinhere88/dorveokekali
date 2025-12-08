<?php
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    redirect('/admin/login.php');
}

$id = $_GET['id'] ?? 0;
$error = '';
$success = $_GET['success'] ?? '';

// Get product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/admin/products/index.php');
}

// Get categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Get product images
$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$stmt->execute([$id]);
$images = $stmt->fetchAll();

// Get product variants
$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY color, size");
$stmt->execute([$id]);
$variants = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_product') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $discount_percent = $_POST['discount_percent'] ?? 0;
        $category_id = $_POST['category_id'] ?? null;
        $gender = $_POST['gender'] ?? 'women';
        $is_new = isset($_POST['is_new']) ? 1 : 0;
        $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;
        $status = $_POST['status'] ?? 'published';

        if ($name && $price) {
            try {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

                $stmt = $pdo->prepare("
                    UPDATE products
                    SET name = ?, slug = ?, description = ?, price = ?, discount_percent = ?,
                        category_id = ?, gender = ?, is_new = ?, is_best_seller = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $description, $price, $discount_percent, $category_id, $gender, $is_new, $is_best_seller, $id]);

                $success = 'Produk berhasil diupdate!';

                // Refresh product data
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch();
            } catch (PDOException $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'add_image') {
        $image_path = $_POST['image_path'] ?? '';
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;

        if ($image_path) {
            try {
                $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM product_images WHERE product_id = ?");
                $stmt->execute([$id]);
                $result = $stmt->fetch();
                $sort_order = ($result['max_order'] ?? 0) + 1;

                $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $image_path, $is_primary, $sort_order]);

                $success = 'Gambar berhasil ditambahkan!';

                // Refresh images
                $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
                $stmt->execute([$id]);
                $images = $stmt->fetchAll();
            } catch (PDOException $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_image') {
        $image_id = $_POST['image_id'] ?? 0;

        try {
            $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
            $stmt->execute([$image_id, $id]);

            $success = 'Gambar berhasil dihapus!';

            // Refresh images
            $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$id]);
            $images = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    } elseif ($action === 'add_variant') {
        $color = $_POST['color'] ?? '';
        $size = $_POST['size'] ?? '';
        $stock = $_POST['stock'] ?? 0;
        $extra_price = $_POST['extra_price'] ?? 0;

        try {
            $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, color, size, stock, extra_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $color, $size, $stock, $extra_price]);

            $success = 'Varian berhasil ditambahkan!';

            // Refresh variants
            $stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY color, size");
            $stmt->execute([$id]);
            $variants = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    } elseif ($action === 'delete_variant') {
        $variant_id = $_POST['variant_id'] ?? 0;

        try {
            $stmt = $pdo->prepare("DELETE FROM product_variants WHERE id = ? AND product_id = ?");
            $stmt->execute([$variant_id, $id]);

            $success = 'Varian berhasil dihapus!';

            // Refresh variants
            $stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY color, size");
            $stmt->execute([$id]);
            $variants = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit Produk - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
/* Modern Professional Edit Product Page Design */
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    min-height: 100vh;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 60px;
    margin-bottom: 40px;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.page-header h1 {
    color: white;
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 12px;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.page-breadcrumb {
    display: flex;
    align-items: center;
    gap: 12px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
}

.page-breadcrumb a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: color 0.3s;
}

.page-breadcrumb a:hover {
    color: white;
}

.content-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 60px 60px;
}

.alert {
    padding: 20px 28px;
    border-radius: 16px;
    margin-bottom: 32px;
    font-size: 15px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border: 2px solid #10b981;
}

.alert-error {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    border: 2px solid #ef4444;
}

.card {
    background: white;
    border-radius: 24px;
    padding: 48px;
    margin-bottom: 32px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.card:hover {
    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 36px;
    padding-bottom: 24px;
    border-bottom: 3px solid #f3f4f6;
}

.card-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
}

.card-title {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    flex: 1;
}

.form-group {
    margin-bottom: 32px;
}

.form-label {
    display: block;
    margin-bottom: 12px;
    font-weight: 600;
    color: #374151;
    font-size: 15px;
    letter-spacing: 0.3px;
}

.form-label .required {
    color: #ef4444;
    margin-left: 4px;
    font-size: 16px;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    background: #f9fafb;
    transition: all 0.3s;
    color: #1f2937;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-textarea {
    min-height: 140px;
    resize: vertical;
    line-height: 1.6;
}

.form-hint {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    color: #6b7280;
    font-style: italic;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #f9fafb;
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.3s;
}

.checkbox-group:hover {
    background: #f3f4f6;
    border-color: #667eea;
}

.checkbox-group input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.checkbox-group span {
    font-weight: 500;
    color: #374151;
    font-size: 15px;
}

.btn {
    padding: 16px 32px;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-2px);
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    font-size: 13px;
    padding: 10px 18px;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
}

.btn-small {
    padding: 10px 18px;
    font-size: 13px;
}

.button-group {
    display: flex;
    gap: 16px;
    margin-top: 48px;
    padding-top: 32px;
    border-top: 2px solid #f3f4f6;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 32px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
}

.data-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.data-table th {
    padding: 18px 20px;
    text-align: left;
    font-weight: 600;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 13px;
}

.data-table td {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
    font-size: 14px;
}

.data-table tbody tr {
    transition: background 0.2s;
}

.data-table tbody tr:hover {
    background: #f9fafb;
}

.image-preview {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
}

.image-preview:hover {
    transform: scale(1.1);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
    font-size: 15px;
    background: #f9fafb;
    border-radius: 12px;
    margin-top: 24px;
}

@media (max-width: 968px) {
    .page-header {
        padding: 32px 24px;
    }

    .content-wrapper {
        padding: 0 24px 40px;
    }

    .card {
        padding: 32px 24px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .button-group {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="page-header">
    <h1>✏️ Edit Produk</h1>
    <div class="page-breadcrumb">
        <a href="/admin/">Dashboard</a>
        <span>→</span>
        <a href="/admin/products/">Produk</a>
        <span>→</span>
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>
</div>

<div class="content-wrapper">
    <?php if ($error): ?>
        <div class="alert alert-error">
            <span style="font-size: 20px;">❌</span>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success === 'created'): ?>
        <div class="alert alert-success">
            <span style="font-size: 20px;">✅</span>
            Produk berhasil dibuat! Sekarang tambahkan gambar dan varian.
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success">
            <span style="font-size: 20px;">✅</span>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Product Information Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">📦</div>
            <h2 class="card-title">Informasi Produk</h2>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="update_product">

            <div class="form-group">
                <label class="form-label">Nama Produk <span class="required">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" class="form-input" required placeholder="Masukkan nama produk...">
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi Produk</label>
                <textarea name="description" class="form-textarea" placeholder="Jelaskan detail produk, bahan, ukuran, dll..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                <span class="form-hint">Deskripsi ini akan dilihat oleh customer di halaman detail produk</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Harga (Rp) <span class="required">*</span></label>
                    <input type="number" name="price" value="<?php echo $product['price']; ?>" class="form-input" min="0" step="1000" required placeholder="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Diskon (%)</label>
                    <input type="number" name="discount_percent" value="<?php echo $product['discount_percent']; ?>" class="form-input" min="0" max="100" placeholder="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Gender <span class="required">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="women" <?php echo ($product['gender'] ?? 'women') === 'women' ? 'selected' : ''; ?>>👗 Women (Wanita)</option>
                        <option value="men" <?php echo ($product['gender'] ?? '') === 'men' ? 'selected' : ''; ?>>👔 Men (Pria)</option>
                        <option value="unisex" <?php echo ($product['gender'] ?? '') === 'unisex' ? 'selected' : ''; ?>>👫 Unisex</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-group">
                        <input type="checkbox" name="is_new" value="1" <?php echo $product['is_new'] ? 'checked' : ''; ?>>
                        <span>✨ Koleksi Baru</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="checkbox-group">
                        <input type="checkbox" name="is_best_seller" value="1" <?php echo $product['is_best_seller'] ? 'checked' : ''; ?>>
                        <span>⭐ Best Seller</span>
                    </label>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span>
                    Update Produk
                </button>
                <a href="/admin/products/index.php" class="btn btn-secondary">
                    <span>←</span>
                    Kembali
                </a>
            </div>
        </form>
    </div>

    <!-- Product Images Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">🖼️</div>
            <h2 class="card-title">Gambar Produk</h2>
        </div>

        <form method="POST" style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 28px; border-radius: 16px; margin-bottom: 32px;">
            <input type="hidden" name="action" value="add_image">
            <div class="form-row">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">URL Gambar</label>
                    <input type="url" name="image_path" class="form-input" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="checkbox-group">
                        <input type="checkbox" name="is_primary" value="1">
                        <span>📌 Gambar Utama</span>
                    </label>
                    <button type="submit" class="btn btn-primary btn-small" style="width: 100%; margin-top: 12px;">
                        <span>➕</span>
                        Tambah Gambar
                    </button>
                </div>
            </div>
        </form>

        <?php if (count($images) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>URL</th>
                        <th>Utama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($images as $img): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($img['image_path']); ?>" class="image-preview"></td>
                            <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: 'Courier New', monospace; font-size: 12px;"><?php echo htmlspecialchars($img['image_path']); ?></td>
                            <td><?php echo $img['is_primary'] ? '<span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">✓ PRIMARY</span>' : ''; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_image">
                                    <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Hapus gambar ini?')">
                                        <span>🗑️</span>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 48px; margin-bottom: 16px;">🖼️</div>
                <p>Belum ada gambar. Tambahkan gambar produk di atas.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product Variants Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">🎨</div>
            <h2 class="card-title">Varian Produk</h2>
        </div>

        <form method="POST" style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 28px; border-radius: 16px; margin-bottom: 32px;">
            <input type="hidden" name="action" value="add_variant">
            <div class="form-row">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Warna</label>
                    <input type="text" name="color" class="form-input" placeholder="e.g. Hitam, Putih">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Ukuran</label>
                    <select name="size" class="form-select">
                        <option value="">Pilih Ukuran</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="2XL">2XL</option>
                        <option value="3XL">3XL</option>
                    </select>
                </div>
            </div>
            <div class="form-row" style="margin-top: 24px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stock" class="form-input" min="0" value="0" placeholder="0">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Harga Tambahan (Rp)</label>
                    <input type="number" name="extra_price" class="form-input" min="0" step="1000" value="0" placeholder="0">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-small" style="width: 100%; margin-top: 24px;">
                <span>➕</span>
                Tambah Varian
            </button>
        </form>

        <?php if (count($variants) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Warna</th>
                        <th>Ukuran</th>
                        <th>Stok</th>
                        <th>Harga Tambahan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($variants as $variant): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($variant['color'] ?? '-'); ?></td>
                            <td><span style="background: #667eea; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;"><?php echo htmlspecialchars($variant['size'] ?? '-'); ?></span></td>
                            <td><span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;"><?php echo $variant['stock']; ?> pcs</span></td>
                            <td style="font-weight: 600;">Rp <?php echo number_format($variant['extra_price'], 0, ',', '.'); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_variant">
                                    <input type="hidden" name="variant_id" value="<?php echo $variant['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Hapus varian ini?')">
                                        <span>🗑️</span>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 48px; margin-bottom: 16px;">🎨</div>
                <p>Belum ada varian. Tambahkan varian produk di atas.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
