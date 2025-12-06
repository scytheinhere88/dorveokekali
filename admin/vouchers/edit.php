<?php
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    redirect('/admin/login.php');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    redirect('/admin/vouchers/index.php');
}

// Get existing voucher
$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE id = ?");
$stmt->execute([$id]);
$voucher = $stmt->fetch();

if (!$voucher) {
    redirect('/admin/vouchers/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? 'discount';
    $discount_type = $_POST['discount_type'] ?? 'percentage';
    $discount_value = $_POST['discount_value'] ?? 0;
    $max_discount = $_POST['max_discount'] ?? null;
    $min_purchase = $_POST['min_purchase'] ?? 0;
    $max_usage_per_user = $_POST['max_usage_per_user'] ?? 1;
    $total_usage_limit = $_POST['total_usage_limit'] ?? null;
    $valid_from = $_POST['valid_from'] ?? date('Y-m-d H:i:s');
    $valid_until = $_POST['valid_until'] ?? date('Y-m-d H:i:s', strtotime('+1 year'));
    $terms_conditions = $_POST['terms_conditions'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $target_type = $_POST['target_type'] ?? 'all';
    $target_tier = $_POST['target_tier'] ?? null;

    // Handle image upload
    $imageName = $voucher['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            // Delete old image
            if ($voucher['image'] && file_exists(__DIR__ . '/../../uploads/vouchers/' . $voucher['image'])) {
                unlink(__DIR__ . '/../../uploads/vouchers/' . $voucher['image']);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = 'voucher_' . uniqid() . '.' . $extension;
            $uploadPath = __DIR__ . '/../../uploads/vouchers/' . $imageName;
            
            if (!is_dir(__DIR__ . '/../../uploads/vouchers/')) {
                mkdir(__DIR__ . '/../../uploads/vouchers/', 0755, true);
            }
            
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
        } else {
            $error = 'Invalid image type. Only JPG, PNG, GIF, WebP allowed.';
        }
    }

    if (!$error && $code && $discount_value >= 0) {
        try {
            $stmt = $pdo->prepare("
                UPDATE vouchers SET 
                    code = ?, name = ?, description = ?, image = ?, type = ?, discount_type = ?, 
                    discount_value = ?, max_discount = ?, min_purchase = ?, max_usage_per_user = ?, 
                    total_usage_limit = ?, valid_from = ?, valid_until = ?, terms_conditions = ?, 
                    is_active = ?, target_type = ?, target_tier = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $code, $name, $description, $imageName, $type, $discount_type, $discount_value, $max_discount,
                $min_purchase, $max_usage_per_user, $total_usage_limit, $valid_from, $valid_until,
                $terms_conditions, $is_active, $target_type, $target_tier, $id
            ]);

            redirect('/admin/vouchers/index.php?success=updated');
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = 'Kode voucher sudah digunakan!';
            } else {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif (!$error) {
        $error = 'Kode voucher dan nilai wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Voucher - Admin Dorve</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #F8F9FA; color: #1A1A1A; }
        .admin-layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #1A1A1A; color: white; padding: 30px 0; position: fixed; width: 260px; height: 100vh; overflow-y: auto; }
        .admin-logo { font-size: 24px; font-weight: 700; letter-spacing: 3px; padding: 0 30px 30px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .admin-nav { padding: 20px 0; }
        .nav-item { padding: 12px 30px; color: rgba(255,255,255,0.7); text-decoration: none; display: block; transition: all 0.3s; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.1); color: white; }
        .admin-content { margin-left: 260px; padding: 40px; }
        .header { margin-bottom: 40px; }
        .header h1 { font-size: 32px; font-weight: 600; }
        .form-container { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); max-width: 900px; }
        .form-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #1A1A1A; }
        .help-text { font-size: 13px; color: #6c757d; margin-top: 4px; }
        input[type="text"], input[type="number"], input[type="datetime-local"], input[type="file"], select, textarea {
            width: 100%; padding: 12px 16px; border: 1px solid #E8E8E8; border-radius: 6px;
            font-size: 15px; font-family: 'Inter', sans-serif; transition: all 0.3s;
        }
        textarea { min-height: 100px; resize: vertical; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #1A1A1A; }
        .checkbox-group { display: flex; align-items: center; gap: 8px; }
        .checkbox-group input[type="checkbox"] { width: auto; }
        .btn { padding: 12px 24px; border-radius: 6px; font-size: 15px; font-weight: 500;
            cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; border: none; }
        .btn-primary { background: #1A1A1A; color: white; }
        .btn-primary:hover { background: #000000; }
        .btn-secondary { background: #E8E8E8; color: #1A1A1A; }
        .btn-secondary:hover { background: #D0D0D0; }
        .button-group { display: flex; gap: 12px; margin-top: 32px; }
        .alert { padding: 16px; border-radius: 6px; margin-bottom: 24px; }
        .alert-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .image-preview { margin-top: 12px; max-width: 200px; border-radius: 8px; }
        .image-upload-area {
            border: 2px dashed #E8E8E8; border-radius: 8px; padding: 24px;
            text-align: center; cursor: pointer; transition: all 0.3s;
        }
        .image-upload-area:hover { border-color: #1A1A1A; background: #F8F9FA; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-logo">DORVE</div>
            <nav class="admin-nav">
                <a href="/admin/index.php" class="nav-item">Dashboard</a>
                <a href="/admin/products/index.php" class="nav-item">Produk</a>
                <a href="/admin/categories/index.php" class="nav-item">Kategori</a>
                <a href="/admin/orders/index.php" class="nav-item">Pesanan</a>
                <a href="/admin/users/index.php" class="nav-item">Pengguna</a>
                <a href="/admin/vouchers/index.php" class="nav-item active">Voucher</a>
                <a href="/admin/shipping/index.php" class="nav-item">Pengiriman</a>
                <a href="/admin/pages/index.php" class="nav-item">Halaman CMS</a>
                <a href="/admin/settings/index.php" class="nav-item">Pengaturan</a>
                <a href="/auth/logout.php" class="nav-item">Logout</a>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="header">
                <h1>✏️ Edit Voucher</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Current Icon/Image</label>
                        <?php if ($voucher['image']): ?>
                            <img src="/uploads/vouchers/<?= htmlspecialchars($voucher['image']) ?>" class="image-preview" style="display: block;">
                        <?php else: ?>
                            <p style="color: #6B7280;">No image uploaded</p>
                        <?php endif; ?>
                        <div style="margin-top: 12px;">
                            <label>Upload New Icon (opsional)</label>
                            <input type="file" name="image" accept="image/*" onchange="previewImage(this)">
                            <div class="help-text">Leave empty to keep existing image</div>
                        </div>
                        <img id="preview" class="image-preview" style="display: none; margin-top: 12px;">
                    </div>

                    <div class="form-group">
                        <label for="code">Kode Voucher *</label>
                        <input type="text" id="code" name="code" required value="<?= htmlspecialchars($voucher['code']) ?>" style="text-transform: uppercase;">
                    </div>

                    <div class="form-group">
                        <label for="name">Nama Voucher *</label>
                        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($voucher['name']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description"><?= htmlspecialchars($voucher['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Tipe Voucher *</label>
                            <select id="type" name="type">
                                <option value="discount" <?= $voucher['type'] === 'discount' ? 'selected' : '' ?>>💰 Discount</option>
                                <option value="free_shipping" <?= $voucher['type'] === 'free_shipping' ? 'selected' : '' ?>>🚚 Free Shipping</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="discount_type">Tipe Diskon *</label>
                            <select id="discount_type" name="discount_type">
                                <option value="percentage" <?= $voucher['discount_type'] === 'percentage' ? 'selected' : '' ?>>Persentase (%)</option>
                                <option value="fixed" <?= $voucher['discount_type'] === 'fixed' ? 'selected' : '' ?>>Nominal (Rp)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="discount_value">Nilai Diskon *</label>
                            <input type="number" id="discount_value" name="discount_value" min="0" step="0.01" required value="<?= $voucher['discount_value'] ?>">
                        </div>

                        <div class="form-group">
                            <label for="max_discount">Maksimal Diskon (Rp)</label>
                            <input type="number" id="max_discount" name="max_discount" min="0" step="1000" value="<?= $voucher['max_discount'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="min_purchase">Min. Purchase (Rp)</label>
                            <input type="number" id="min_purchase" name="min_purchase" min="0" step="1000" value="<?= $voucher['min_purchase'] ?>">
                        </div>

                        <div class="form-group">
                            <label for="max_usage_per_user">Max Usage per User</label>
                            <input type="number" id="max_usage_per_user" name="max_usage_per_user" min="1" value="<?= $voucher['max_usage_per_user'] ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="total_usage_limit">Total Usage Limit</label>
                            <input type="number" id="total_usage_limit" name="total_usage_limit" min="1" value="<?= $voucher['total_usage_limit'] ?? '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="target_type">Target Type</label>
                            <select id="target_type" name="target_type" onchange="toggleTierInput()">
                                <option value="all" <?= $voucher['target_type'] === 'all' ? 'selected' : '' ?>>🌟 All Users</option>
                                <option value="tier" <?= $voucher['target_type'] === 'tier' ? 'selected' : '' ?>>🎯 Specific Tier</option>
                                <option value="referral" <?= $voucher['target_type'] === 'referral' ? 'selected' : '' ?>>🔗 Referral Users</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="tier_group" style="display: <?= $voucher['target_type'] === 'tier' ? 'block' : 'none' ?>;">
                        <label for="target_tier">Target Tier</label>
                        <select id="target_tier" name="target_tier">
                            <option value="">Select Tier</option>
                            <option value="bronze" <?= $voucher['target_tier'] === 'bronze' ? 'selected' : '' ?>>🥉 Bronze</option>
                            <option value="silver" <?= $voucher['target_tier'] === 'silver' ? 'selected' : '' ?>>🥈 Silver</option>
                            <option value="gold" <?= $voucher['target_tier'] === 'gold' ? 'selected' : '' ?>>🥇 Gold</option>
                            <option value="platinum" <?= $voucher['target_tier'] === 'platinum' ? 'selected' : '' ?>>💎 Platinum</option>
                            <option value="vvip" <?= $voucher['target_tier'] === 'vvip' ? 'selected' : '' ?>>👑 VVIP</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="valid_from">Valid From</label>
                            <input type="datetime-local" id="valid_from" name="valid_from" value="<?= date('Y-m-d\TH:i', strtotime($voucher['valid_from'])) ?>">
                        </div>

                        <div class="form-group">
                            <label for="valid_until">Valid Until</label>
                            <input type="datetime-local" id="valid_until" name="valid_until" value="<?= date('Y-m-d\TH:i', strtotime($voucher['valid_until'])) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="terms_conditions">Terms & Conditions (S&K)</label>
                        <textarea id="terms_conditions" name="terms_conditions"><?= htmlspecialchars($voucher['terms_conditions'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-group">
                            <input type="checkbox" name="is_active" value="1" <?= $voucher['is_active'] ? 'checked' : '' ?>>
                            <span>Aktifkan Voucher</span>
                        </label>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">💾 Update Voucher</button>
                        <a href="/admin/vouchers/index.php" class="btn btn-secondary">❌ Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('preview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleTierInput() {
        const targetType = document.getElementById('target_type').value;
        document.getElementById('tier_group').style.display = targetType === 'tier' ? 'block' : 'none';
    }
    </script>
</body>
</html>
