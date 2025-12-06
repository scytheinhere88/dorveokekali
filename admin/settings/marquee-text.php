<?php
require_once __DIR__ . '/../../config.php';

// Check if admin is logged in
if (!isAdmin()) {
    header('Location: /admin/login.php');
    exit;
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($title)) {
        $error = 'Main text is required';
    } else {
        try {
            // Check if marquee banner exists
            $stmt = $pdo->query("SELECT id FROM banners WHERE banner_type = 'marquee' LIMIT 1");
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE banners SET title = ?, subtitle = ?, is_active = ?, updated_at = NOW() WHERE banner_type = 'marquee'");
                $stmt->execute([$title, $subtitle, $is_active]);
                $success = 'Marquee text updated successfully!';
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO banners (banner_type, title, subtitle, is_active, display_order) VALUES ('marquee', ?, ?, ?, 1)");
                $stmt->execute([$title, $subtitle, $is_active]);
                $success = 'Marquee text created successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get current marquee text
$marquee = null;
try {
    $stmt = $pdo->query("SELECT * FROM banners WHERE banner_type = 'marquee' LIMIT 1");
    $marquee = $stmt->fetch();
} catch (PDOException $e) {
    $error = 'Error loading marquee text: ' . $e->getMessage();
}

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <h1>🎭 Marquee Text (Running Text)</h1>
        <p class="page-description">Manage running text that appears below navigation bar on homepage</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            ✅ <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            ❌ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Edit Marquee Text</h2>
            <p class="text-muted">Text yang akan berjalan (scroll) di bawah navbar</p>
        </div>

        <form method="POST" class="form-horizontal">
            <div class="form-group">
                <label for="title" class="form-label">
                    Main Text *
                    <span class="label-help">Text utama yang akan ditampilkan</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-control" 
                       placeholder="e.g., 🎉 Selamat Datang di Dorve.id!"
                       value="<?php echo htmlspecialchars($marquee['title'] ?? ''); ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="subtitle" class="form-label">
                    Additional Text (Optional)
                    <span class="label-help">Text tambahan akan digabung dengan " | " separator</span>
                </label>
                <input type="text" 
                       id="subtitle" 
                       name="subtitle" 
                       class="form-control" 
                       placeholder="e.g., Gratis Ongkir untuk pembelian di atas Rp 500.000"
                       value="<?php echo htmlspecialchars($marquee['subtitle'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="checkbox-container">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           <?php echo (!$marquee || $marquee['is_active']) ? 'checked' : ''; ?>>
                    <span class="checkbox-label">Active (Show on homepage)</span>
                </label>
            </div>

            <div class="preview-section">
                <h3 class="preview-title">Preview</h3>
                <div class="marquee-preview" id="marqueePreview">
                    <div class="marquee-content" id="marqueeContent">
                        <?php 
                        $preview_text = $marquee ? htmlspecialchars($marquee['title']) : 'Your text will appear here';
                        if ($marquee && !empty($marquee['subtitle'])) {
                            $preview_text .= ' | ' . htmlspecialchars($marquee['subtitle']);
                        }
                        echo $preview_text;
                        ?>
                    </div>
                </div>
                <p class="text-muted" style="margin-top: 12px; font-size: 13px;">
                    ℹ️ Preview shows how text will scroll on homepage. Type in fields above to see live preview.
                </p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    💾 Save Marquee Text
                </button>
                <a href="/admin/index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <div class="info-box">
        <h3>ℹ️ How It Works</h3>
        <ul>
            <li><strong>Main Text</strong>: Primary message (required)</li>
            <li><strong>Additional Text</strong>: Optional secondary message</li>
            <li><strong>Display</strong>: "Main Text | Additional Text" (separated by |)</li>
            <li><strong>Animation</strong>: Auto-scrolling from right to left</li>
            <li><strong>Location</strong>: Below navigation bar on homepage</li>
        </ul>
    </div>
</div>

<style>
.admin-content {
    max-width: 900px;
    margin: 0 auto;
    padding: 30px;
}

.page-header {
    margin-bottom: 32px;
}

.page-header h1 {
    font-size: 32px;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.page-description {
    color: #666;
    font-size: 15px;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 24px;
}

.card-header {
    padding: 24px 30px;
    border-bottom: 2px solid #f0f0f0;
}

.card-header h2 {
    font-size: 20px;
    color: #1a1a1a;
    margin-bottom: 6px;
}

.text-muted {
    color: #666;
    font-size: 14px;
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-weight: 500;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.form-horizontal {
    padding: 30px;
}

.form-group {
    margin-bottom: 28px;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 8px;
    font-size: 15px;
}

.label-help {
    display: block;
    font-weight: 400;
    color: #666;
    font-size: 13px;
    margin-top: 4px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 15px;
    font-family: inherit;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.checkbox-container {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-container input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.checkbox-label {
    font-size: 15px;
    color: #1a1a1a;
}

.preview-section {
    margin: 32px 0;
    padding: 24px;
    background: #f8f9fa;
    border-radius: 8px;
}

.preview-title {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 16px;
}

.marquee-preview {
    background: linear-gradient(90deg, #1A1A1A 0%, #333333 100%);
    color: white;
    padding: 14px 0;
    overflow: hidden;
    border-radius: 6px;
    position: relative;
}

.marquee-content {
    display: inline-block;
    white-space: nowrap;
    animation: marqueeScroll 20s linear infinite;
    padding-left: 100%;
    font-size: 15px;
}

@keyframes marqueeScroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}

.marquee-preview:hover .marquee-content {
    animation-play-state: paused;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 2px solid #f0f0f0;
}

.btn {
    padding: 12px 32px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: #e5e7eb;
    color: #4b5563;
}

.btn-secondary:hover {
    background: #d1d5db;
}

.info-box {
    background: #eff6ff;
    border: 2px solid #bfdbfe;
    border-radius: 12px;
    padding: 24px;
}

.info-box h3 {
    font-size: 16px;
    color: #1e40af;
    margin-bottom: 16px;
}

.info-box ul {
    list-style: none;
    padding: 0;
}

.info-box li {
    padding: 8px 0;
    color: #1e3a8a;
    font-size: 14px;
    line-height: 1.6;
}

.info-box li strong {
    color: #1e40af;
}
</style>

<script>
// Live preview update
document.getElementById('title').addEventListener('input', updatePreview);
document.getElementById('subtitle').addEventListener('input', updatePreview);

function updatePreview() {
    const title = document.getElementById('title').value || 'Your text will appear here';
    const subtitle = document.getElementById('subtitle').value;
    
    let previewText = title;
    if (subtitle.trim()) {
        previewText += ' | ' + subtitle;
    }
    
    document.getElementById('marqueeContent').textContent = previewText + '   •   ' + previewText + '   •   ' + previewText;
}

// Initialize preview
updatePreview();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
