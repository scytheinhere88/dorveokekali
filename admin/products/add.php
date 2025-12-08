<?php
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    redirect('/admin/login.php');
}

$error = '';
$success = '';

// Get categories
$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discount_percent = !empty($_POST['discount_percent']) ? floatval($_POST['discount_percent']) : 0;
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $gender = $_POST['gender'] ?? 'unisex';
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_best_seller = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validate required fields
    if (empty($name)) {
        $error = 'Product name is required!';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than 0!';
    } elseif (empty($category_id)) {
        $error = 'Please select a category!';
    } else {
        try {
            $pdo->beginTransaction();

            // Create slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

            // Check if slug exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }

            // Insert product
            $stmt = $pdo->prepare("
                INSERT INTO products (name, slug, description, price, discount_percent, category_id, gender, is_new, is_best_seller, is_active, stock)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$name, $slug, $description, $price, $discount_percent, $category_id, $gender, $is_new, $is_best_seller, $is_active]);

            $product_id = $pdo->lastInsertId();

            // Handle multiple image uploads (1-5 images)
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $upload_dir = __DIR__ . '/../../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $uploaded_count = 0;
                $first_image = null;

                // Loop through each uploaded file
                foreach ($_FILES['images']['name'] as $key => $filename) {
                    // Skip if no file
                    if (empty($filename)) {
                        continue;
                    }
                    
                    // Check for upload errors
                    if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                        $error_msg = '';
                        switch ($_FILES['images']['error'][$key]) {
                            case UPLOAD_ERR_INI_SIZE:
                            case UPLOAD_ERR_FORM_SIZE:
                                $error_msg = 'File size exceeds limit (max 8MB)';
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $error_msg = 'File was only partially uploaded';
                                break;
                            case UPLOAD_ERR_NO_FILE:
                                $error_msg = 'No file was uploaded';
                                break;
                            default:
                                $error_msg = 'Upload error occurred';
                        }
                        throw new Exception($error_msg . ' for file: ' . $filename);
                    }

                    // Limit to 5 images
                    if ($uploaded_count >= 5) {
                        break;
                    }
                    
                    // Check file size (8MB max)
                    if ($_FILES['images']['size'][$key] > 8388608) {
                        throw new Exception('File size exceeds 8MB limit: ' . $filename);
                    }

                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $new_filename = 'product_' . $product_id . '_' . time() . '_' . $uploaded_count . '.' . $ext;
                        $filepath = $upload_dir . $new_filename;

                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $filepath)) {
                            // Resize image to max 800x800 (optional, improves performance)
                            // This would require GD library

                            $image_path = 'products/' . $new_filename;
                            
                            // First image becomes primary
                            $is_primary = ($uploaded_count === 0) ? 1 : 0;
                            
                            // Insert into product_images table
                            $stmt = $pdo->prepare("
                                INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([$product_id, $image_path, $is_primary, $uploaded_count + 1]);
                            
                            if ($uploaded_count === 0) {
                                $first_image = $image_path;
                            }
                            
                            $uploaded_count++;
                        } else {
                            throw new Exception('Failed to move uploaded file: ' . $filename);
                        }
                    } else {
                        throw new Exception('Invalid file type for: ' . $filename . ' (allowed: jpg, jpeg, png, webp)');
                    }
                }

                // Update main product image field with first uploaded image
                if ($first_image) {
                    $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
                    $stmt->execute([$first_image, $product_id]);
                }
            }

            // Handle variants
            if (isset($_POST['variants']) && is_array($_POST['variants'])) {
                foreach ($_POST['variants'] as $variant) {
                    $color = trim($variant['color'] ?? '');
                    $size = trim($variant['size'] ?? '');
                    $stock = intval($variant['stock'] ?? 0);
                    $sku = trim($variant['sku'] ?? '');

                    if ($color && $size) {
                        // Check if variant already exists
                        $stmt = $pdo->prepare("
                            SELECT id FROM product_variants
                            WHERE product_id = ? AND color = ? AND size = ?
                        ");
                        $stmt->execute([$product_id, $color, $size]);

                        if (!$stmt->fetch()) {
                            $stmt = $pdo->prepare("
                                INSERT INTO product_variants (product_id, color, size, stock, sku, is_active)
                                VALUES (?, ?, ?, ?, ?, 1)
                            ");
                            $stmt->execute([$product_id, $color, $size, $stock, $sku]);
                        }
                    }
                }
            }

            // Update total product stock
            $stmt = $pdo->prepare("
                UPDATE products
                SET stock = (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_id = ? AND is_active = 1)
                WHERE id = ?
            ");
            $stmt->execute([$product_id, $product_id]);

            $pdo->commit();
            $success = 'Product added successfully!';

            // Redirect after 2 seconds
            header("refresh:2;url=/admin/products/index.php");

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="header">
    <h1>Add New Product</h1>
    <a href="/admin/products/index.php" class="btn btn-secondary">← Back to Products</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> Redirecting...</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="product-form" id="productForm">
    <!-- Basic Information -->
    <div class="form-card">
        <h2>Basic Information</h2>

        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" required
                   placeholder="e.g., Premium Cotton Hoodie"
                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"
                      placeholder="Describe your product..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="gender">Gender *</label>
                <select id="gender" name="gender" required>
                    <option value="men" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'men') ? 'selected' : ''; ?>>Men</option>
                    <option value="women" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'women') ? 'selected' : ''; ?>>Women</option>
                    <option value="unisex" <?php echo (!isset($_POST['gender']) || $_POST['gender'] === 'unisex') ? 'selected' : ''; ?>>Unisex</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="form-card">
        <h2>Pricing</h2>

        <div class="form-row">
            <div class="form-group">
                <label for="price">Regular Price (Rp) *</label>
                <input type="number" id="price" name="price" required min="0" step="1000"
                       placeholder="250000"
                       value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="discount_percent">Discount (%)</label>
                <input type="number" id="discount_percent" name="discount_percent" min="0" max="100" step="0.01"
                       placeholder="10 (for 10% off)"
                       value="<?php echo htmlspecialchars($_POST['discount_percent'] ?? ''); ?>">
                <small>Enter discount percentage (0-100). Set to 0 for no discount</small>
            </div>
        </div>
    </div>

    <!-- Product Images -->
    <div class="form-card">
        <h2>Product Images (1-5 images)</h2>

        <div class="form-group">
            <label for="images">Upload Product Images *</label>
            <input type="file" id="images" name="images[]" accept="image/jpeg,image/jpg,image/png,image/webp" multiple required>
            <small>Upload 1-5 images. First image will be the main product image. Recommended size: 800x800px, Max 8MB per image</small>
        </div>
        
        <div id="imagePreviewContainer" class="image-preview-grid" style="margin-top: 20px; display: none;">
            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 12px;">Selected Images:</h4>
            <div id="imagePreview" class="image-preview"></div>
        </div>
    </div>

    <!-- Variants Section -->
    <div class="form-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Product Variants (Color & Size)</h2>
            <button type="button" class="btn btn-primary btn-sm" onclick="addVariant()">
                + Add Variant
            </button>
        </div>

        <div id="variantsContainer">
            <p class="text-muted">Click "Add Variant" to add colors and sizes with stock quantities.</p>
        </div>
    </div>

    <!-- Settings -->
    <div class="form-card">
        <h2>Settings</h2>

        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                <label for="is_active">Active (visible to customers)</label>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="is_new" name="is_new" value="1">
                <label for="is_new">Mark as New Collection</label>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="is_featured" name="is_featured" value="1">
                <label for="is_featured">Featured Product</label>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">
            💾 Save Product
        </button>
        <a href="/admin/products/index.php" class="btn btn-secondary btn-lg">Cancel</a>
    </div>
</form>

<style>
.product-form {
    max-width: 1000px;
}

.form-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.form-card h2 {
    font-size: 20px;
    margin-bottom: 24px;
    font-weight: 600;
    color: #1A1A1A;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 14px;
    color: #1A1A1A;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E8E8E8;
    border-radius: 8px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #1A1A1A;
}

.form-group small {
    display: block;
    margin-top: 6px;
    font-size: 13px;
    color: #666;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-group input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.checkbox-group label {
    margin: 0;
    cursor: pointer;
    font-weight: 500;
}

.variant-item {
    background: #F8F9FA;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 16px;
    border: 2px solid #E8E8E8;
    position: relative;
}

.variant-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.variant-header h4 {
    font-size: 16px;
    font-weight: 600;
    color: #1A1A1A;
}

.btn-remove-variant {
    background: #EF4444;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-remove-variant:hover {
    background: #DC2626;
}

.variant-fields {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 16px;
}

.form-actions {
    display: flex;
    gap: 16px;
    margin-top: 30px;
}

.btn-lg {
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 600;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 14px;
}

.text-muted {
    color: #666;
    font-size: 14px;
    padding: 20px;
    text-align: center;
    background: #F8F9FA;
    border-radius: 8px;
}

.image-preview-grid {
    background: #F9FAFB;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #E5E7EB;
}

.image-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .variant-fields {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-lg {
        width: 100%;
    }
}
</style>

<script>
let variantCount = 0;
const availableSizes = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

function addVariant() {
    variantCount++;

    const container = document.getElementById('variantsContainer');

    // Remove the placeholder message if it exists
    const placeholder = container.querySelector('.text-muted');
    if (placeholder) {
        placeholder.remove();
    }

    const variantHtml = `
        <div class="variant-item" id="variant-${variantCount}">
            <div class="variant-header">
                <h4>Variant #${variantCount}</h4>
                <button type="button" class="btn-remove-variant" onclick="removeVariant(${variantCount})">
                    × Remove
                </button>
            </div>
            <div class="variant-fields">
                <div class="form-group">
                    <label>Color *</label>
                    <input type="text" name="variants[${variantCount}][color]"
                           placeholder="e.g., Black, White, Red" required>
                </div>
                <div class="form-group">
                    <label>Size *</label>
                    <select name="variants[${variantCount}][size]" required>
                        <option value="">Select Size</option>
                        ${availableSizes.map(size => `<option value="${size}">${size}</option>`).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="variants[${variantCount}][stock]"
                           min="0" value="0" required>
                </div>
                <div class="form-group">
                    <label>SKU (optional)</label>
                    <input type="text" name="variants[${variantCount}][sku]"
                           placeholder="e.g., BLK-M-001">
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', variantHtml);
}

function removeVariant(id) {
    const variant = document.getElementById(`variant-${id}`);
    if (variant) {
        variant.remove();
    }

    // If no variants left, show placeholder
    const container = document.getElementById('variantsContainer');
    if (container.children.length === 0) {
        container.innerHTML = '<p class="text-muted">Click "Add Variant" to add colors and sizes with stock quantities.</p>';
    }
}

// Form validation
document.getElementById('productForm').addEventListener('submit', function(e) {
    const variants = document.querySelectorAll('.variant-item');

    if (variants.length === 0) {
        if (!confirm('You haven\'t added any variants. Product will be created without size/color options. Continue?')) {
            e.preventDefault();
            return false;
        }
    }

    // Validate discount percent
    const discountPercent = parseFloat(document.getElementById('discount_percent').value);

    if (discountPercent && (discountPercent < 0 || discountPercent > 100)) {
        alert('Discount percentage must be between 0 and 100!');
        e.preventDefault();
        return false;
    }
});

// Image preview functionality
document.getElementById('images').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewDiv = document.getElementById('imagePreview');
    
    // Clear previous previews
    previewDiv.innerHTML = '';
    
    if (files.length === 0) {
        previewContainer.style.display = 'none';
        return;
    }
    
    // Limit to 5 images
    const filesToShow = files.slice(0, 5);
    
    if (files.length > 5) {
        alert('Maximum 5 images allowed. Only first 5 will be uploaded.');
    }
    
    previewContainer.style.display = 'block';
    
    filesToShow.forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const imgDiv = document.createElement('div');
            imgDiv.style.cssText = 'position: relative; border: 2px solid #E5E7EB; border-radius: 8px; overflow: hidden;';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width: 100%; height: 150px; object-fit: cover;';
            
            const badge = document.createElement('div');
            badge.textContent = index === 0 ? 'Main' : `#${index + 1}`;
            badge.style.cssText = 'position: absolute; top: 8px; left: 8px; background: ' + (index === 0 ? '#10B981' : '#6B7280') + '; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;';
            
            imgDiv.appendChild(img);
            imgDiv.appendChild(badge);
            previewDiv.appendChild(imgDiv);
        };
        
        reader.readAsDataURL(file);
    });
});

// Auto-add one variant on page load for better UX
window.addEventListener('DOMContentLoaded', function() {
    // Optionally auto-add first variant
    // addVariant();
});
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
