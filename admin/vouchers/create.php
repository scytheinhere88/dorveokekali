<?php
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    redirect('/admin/login.php');
}

$error = '';
$success = '';

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
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image']['type'];

        if (in_array($fileType, $allowedTypes)) {
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
                INSERT INTO vouchers (code, name, description, image, type, discount_type, discount_value, max_discount,
                                     min_purchase, max_usage_per_user, total_usage_limit, valid_from, valid_until,
                                     terms_conditions, is_active, target_type, target_tier)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $code, $name, $description, $imageName, $type, $discount_type, $discount_value, $max_discount,
                $min_purchase, $max_usage_per_user, $total_usage_limit, $valid_from, $valid_until,
                $terms_conditions, $is_active, $target_type, $target_tier
            ]);

            redirect('/admin/vouchers/index.php?success=created');
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

$page_title = 'Create New Voucher';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
.form-container { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); max-width: 900px; }
.form-group { margin-bottom: 24px; }
label { display: block; margin-bottom: 8px; font-weight: 500; color: #1A1A1A; }
.help-text { font-size: 13px; color: #6c757d; margin-top: 4px; }
input[type="text"], input[type="number"], input[type="datetime-local"], input[type="file"], select, textarea {
    width: 100%; padding: 12px 16px; border: 2px solid #E5E7EB; border-radius: 8px;
    font-size: 15px; font-family: 'Inter', sans-serif; transition: all 0.3s;
}
textarea { min-height: 100px; resize: vertical; }
input:focus, select:focus, textarea:focus { outline: none; border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.checkbox-group { display: flex; align-items: center; gap: 8px; }
.checkbox-group input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; }
.btn { padding: 12px 28px; border-radius: 8px; font-size: 15px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; border: none; }
.btn-primary { background: #3B82F6; color: white; }
.btn-primary:hover { background: #2563EB; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
.btn-secondary { background: #E5E7EB; color: #4B5563; }
.btn-secondary:hover { background: #D1D5DB; }
.button-group { display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 2px solid #F0F0F0; }
.alert { padding: 16px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; }
.alert-error { background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.image-preview { margin-top: 12px; max-width: 200px; border-radius: 8px; border: 2px solid #E5E7EB; }
.image-upload-area {
    border: 2px dashed #D1D5DB; border-radius: 8px; padding: 32px;
    text-align: center; cursor: pointer; transition: all 0.3s; background: #F9FAFB;
}
.image-upload-area:hover { border-color: #3B82F6; background: #EFF6FF; }
.image-upload-area .icon { font-size: 48px; margin-bottom: 12px; }
.page-header { margin-bottom: 32px; }
.page-header h1 { font-size: 32px; color: #1A1A1A; margin-bottom: 8px; font-weight: 700; }
.page-header p { color: #6B7280; font-size: 15px; }
</style>

<div class="page-header">
    <h1>Create New Voucher</h1>
    <p>Add a new voucher or promotional code for customers</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Voucher Icon/Image</label>
                        <div class="image-upload-area" onclick="document.getElementById('image').click()">
                            <div class="icon">🖼️</div>
                            <div style="font-weight: 600; margin-bottom: 4px;">Upload Voucher Icon</div>
                            <div class="help-text">JPG, PNG, GIF, WebP (Max 2MB)</div>
                        </div>
                        <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        <img id="preview" class="image-preview" style="display: none;">
                    </div>

                    <div class="form-group">
                        <label for="code">Kode Voucher *</label>
                        <input type="text" id="code" name="code" required placeholder="DISKON50" style="text-transform: uppercase;">
                        <div class="help-text">Kode unik untuk voucher (akan otomatis huruf besar)</div>
                    </div>

                    <div class="form-group">
                        <label for="name">Nama Voucher *</label>
                        <input type="text" id="name" name="name" required placeholder="Diskon Akhir Tahun">
                        <div class="help-text">Nama yang akan ditampilkan ke user</div>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" placeholder="Dapatkan diskon spesial..."></textarea>
                        <div class="help-text">Deskripsi singkat tentang voucher</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Tipe Voucher *</label>
                            <select id="type" name="type" onchange="updateForm()">
                                <option value="discount">💰 Discount</option>
                                <option value="free_shipping">🚚 Free Shipping</option>
                            </select>
                        </div>

                        <div class="form-group" id="discount_type_group">
                            <label for="discount_type">Tipe Diskon *</label>
                            <select id="discount_type" name="discount_type">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="discount_value" id="value_label">Nilai Diskon *</label>
                            <input type="number" id="discount_value" name="discount_value" min="0" step="0.01" required>
                            <div class="help-text" id="value_help">Contoh: 10 untuk diskon 10%</div>
                        </div>

                        <div class="form-group" id="max_discount_group">
                            <label for="max_discount">Maksimal Diskon (Rp)</label>
                            <input type="number" id="max_discount" name="max_discount" min="0" step="1000" placeholder="50000">
                            <div class="help-text">Untuk tipe persentase, batas maksimal potongan</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="min_purchase">Min. Purchase (Rp)</label>
                            <input type="number" id="min_purchase" name="min_purchase" min="0" step="1000" value="0">
                            <div class="help-text">Minimum total belanja untuk pakai voucher</div>
                        </div>

                        <div class="form-group">
                            <label for="max_usage_per_user">Max Usage per User</label>
                            <input type="number" id="max_usage_per_user" name="max_usage_per_user" min="1" value="1">
                            <div class="help-text">Berapa kali user bisa pakai voucher ini</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="total_usage_limit">Total Usage Limit</label>
                            <input type="number" id="total_usage_limit" name="total_usage_limit" min="1" placeholder="100">
                            <div class="help-text">Total limit untuk semua user (kosongkan untuk unlimited)</div>
                        </div>

                        <div class="form-group">
                            <label for="target_type">Target Type</label>
                            <select id="target_type" name="target_type" onchange="toggleTierInput()">
                                <option value="all">🌟 All Users</option>
                                <option value="tier">🎯 Specific Tier</option>
                                <option value="referral">🔗 Referral Users</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="tier_group" style="display: none;">
                        <label for="target_tier">Target Tier</label>
                        <select id="target_tier" name="target_tier">
                            <option value="">Select Tier</option>
                            <option value="bronze">🥉 Bronze</option>
                            <option value="silver">🥈 Silver</option>
                            <option value="gold">🥇 Gold</option>
                            <option value="platinum">💎 Platinum</option>
                            <option value="vvip">👑 VVIP</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="valid_from">Valid From</label>
                            <input type="datetime-local" id="valid_from" name="valid_from" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>

                        <div class="form-group">
                            <label for="valid_until">Valid Until</label>
                            <input type="datetime-local" id="valid_until" name="valid_until" value="<?= date('Y-m-d\TH:i', strtotime('+1 month')) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="terms_conditions">Terms & Conditions (S&K)</label>
                        <textarea id="terms_conditions" name="terms_conditions" placeholder="1. Berlaku untuk semua produk
2. Tidak dapat digabung dengan promo lain
3. ..."></textarea>
                        <div class="help-text">Syarat dan ketentuan penggunaan voucher</div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-group">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Aktifkan Voucher Sekarang</span>
                        </label>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">💾 Create Voucher</button>
                        <a href="/admin/vouchers/index.php" class="btn btn-secondary">❌ Cancel</a>
                    </div>
                </form>
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

function updateForm() {
    const type = document.getElementById('type').value;
    const discountTypeGroup = document.getElementById('discount_type_group');
    const maxDiscountGroup = document.getElementById('max_discount_group');

    if (type === 'free_shipping') {
        discountTypeGroup.style.display = 'none';
        maxDiscountGroup.querySelector('label').textContent = 'Max Shipping Discount (Rp)';
        maxDiscountGroup.querySelector('.help-text').textContent = 'Batas maksimal potongan ongkir';
    } else {
        discountTypeGroup.style.display = 'block';
        maxDiscountGroup.querySelector('label').textContent = 'Maksimal Diskon (Rp)';
        maxDiscountGroup.querySelector('.help-text').textContent = 'Untuk tipe persentase, batas maksimal potongan';
    }
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>