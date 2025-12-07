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
                    SET name = ?, slug = ?, price = ?, discount_percent = ?,
                        category_id = ?, gender = ?, is_new = ?, is_best_seller = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $price, $discount_percent, $category_id, $gender, $is_new, $is_best_seller, $id]);

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
.form-container {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 2px solid #E8E8E8;
}

.form-group {
    margin-bottom: 24px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #1A1A1A;
}

input[type="text"],
input[type="number"],
input[type="url"],
select,
textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #E8E8E8;
    border-radius: 6px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #1A1A1A;
}

textarea {
    min-height: 120px;
    resize: vertical;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.button-group {
    display: flex;
    gap: 12px;
    margin-top: 32px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #E8E8E8;
}

th {
    background: #F8F9FA;
    font-weight: 600;
}
</style>

<div class="header">
    <h1>Edit Produk: <?php echo htmlspecialchars($product['name']); ?></h1>
</div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success === 'created'): ?>
                <div class="alert alert-success">Produk berhasil dibuat! Sekarang tambahkan gambar dan varian.</div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Product Info -->
            <div class="form-container">
                <h2 class="section-title">Informasi Produk</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_product">

                    <div class="form-group">
                        <label for="name">Nama Produk *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Harga (Rp) *</label>
                            <input type="number" id="price" name="price" min="0" step="1000" value="<?php echo $product['price']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="discount_percent">Diskon (%)</label>
                            <input type="number" id="discount_percent" name="discount_percent" min="0" max="100" value="<?php echo $product['discount_percent']; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select id="category_id" name="category_id">
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender <span style="color: #e74c3c;">*</span></label>
                        <select id="gender" name="gender" required>
                            <option value="women" <?php echo ($product['gender'] ?? 'women') === 'women' ? 'selected' : ''; ?>>Women (Wanita)</option>
                            <option value="men" <?php echo ($product['gender'] ?? '') === 'men' ? 'selected' : ''; ?>>Men (Pria)</option>
                            <option value="unisex" <?php echo ($product['gender'] ?? '') === 'unisex' ? 'selected' : ''; ?>>Unisex (Pria & Wanita)</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" name="is_new" value="1" <?php echo $product['is_new'] ? 'checked' : ''; ?>>
                                <span>Koleksi Baru</span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" name="is_best_seller" value="1" <?php echo $product['is_best_seller'] ? 'checked' : ''; ?>>
                                <span>Best Seller</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo $product['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $product['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $product['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Update Produk</button>
                        <a href="/admin/products/index.php" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>

            <!-- Product Images -->
            <div class="form-container">
                <h2 class="section-title">Gambar Produk</h2>

                <form method="POST">
                    <input type="hidden" name="action" value="add_image">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="image_path">URL Gambar</label>
                            <input type="url" id="image_path" name="image_path" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="form-group">
                            <label class="checkbox-group">
                                <input type="checkbox" name="is_primary" value="1">
                                <span>Gambar Utama</span>
                            </label>
                            <button type="submit" class="btn btn-primary btn-small" style="margin-top: 8px;">Tambah Gambar</button>
                        </div>
                    </div>
                </form>

                <?php if (count($images) > 0): ?>
                    <table>
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
                                    <td><img src="<?php echo htmlspecialchars($img['image_path']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                                    <td style="font-size: 12px; max-width: 300px; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($img['image_path']); ?></td>
                                    <td><?php echo $img['is_primary'] ? '✓' : ''; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_image">
                                            <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Hapus gambar ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #6c757d; margin-top: 16px;">Belum ada gambar. Tambahkan gambar produk di atas.</p>
                <?php endif; ?>
            </div>

            <!-- Product Variants -->
            <div class="form-container">
                <h2 class="section-title">Varian Produk</h2>

                <form method="POST">
                    <input type="hidden" name="action" value="add_variant">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="color">Warna</label>
                            <input type="text" id="color" name="color" placeholder="e.g. Hitam, Putih">
                        </div>
                        <div class="form-group">
                            <label for="size">Ukuran</label>
                            <select id="size" name="size">
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
                    <div class="form-row">
                        <div class="form-group">
                            <label for="stock">Stok</label>
                            <input type="number" id="stock" name="stock" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label for="extra_price">Harga Tambahan (Rp)</label>
                            <input type="number" id="extra_price" name="extra_price" min="0" step="1000" value="0">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Tambah Varian</button>
                </form>

                <?php if (count($variants) > 0): ?>
                    <table>
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
                                    <td><?php echo htmlspecialchars($variant['size'] ?? '-'); ?></td>
                                    <td><?php echo $variant['stock']; ?></td>
                                    <td>Rp <?php echo number_format($variant['extra_price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_variant">
                                            <input type="hidden" name="variant_id" value="<?php echo $variant['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Hapus varian ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #6c757d; margin-top: 16px;">Belum ada varian. Tambahkan varian produk di atas.</p>
                <?php endif; ?>
            </div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
