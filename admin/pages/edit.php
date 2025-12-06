<?php
require_once __DIR__ . '/../../config.php';
if (!isAdmin()) redirect('/admin/login.php');

$page_id = $_GET['id'] ?? 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE cms_pages SET 
        title = ?, 
        slug = ?,
        content = ?,
        meta_title = ?,
        meta_description = ?,
        is_active = ?
        WHERE id = ?");
    
    $stmt->execute([
        $_POST['title'],
        $_POST['slug'],
        $_POST['content'],
        $_POST['meta_title'],
        $_POST['meta_description'],
        isset($_POST['is_active']) ? 1 : 0,
        $page_id
    ]);
    
    $_SESSION['success'] = 'Page updated successfully!';
    redirect('/admin/pages/');
}

// Get page data
$stmt = $pdo->prepare("SELECT * FROM cms_pages WHERE id = ?");
$stmt->execute([$page_id]);
$page = $stmt->fetch();

if (!$page) {
    $_SESSION['error'] = 'Page not found!';
    redirect('/admin/pages/');
}

$page_title = 'Edit CMS Page - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}
.form-group input, .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    font-size: 14px;
}
.form-group textarea {
    min-height: 300px;
    font-family: monospace;
}
.form-group small {
    color: #6B7280;
    font-size: 13px;
}
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.checkbox-group input {
    width: auto;
}
.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
}
.btn-primary {
    background: #3B82F6;
    color: white;
}
.btn-secondary {
    background: #6B7280;
    color: white;
}
.alert {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}
.alert-success {
    background: #D1FAE5;
    color: #065F46;
}
</style>

<div class="header">
    <h1>Edit CMS Page: <?php echo htmlspecialchars($page['title']); ?></h1>
</div>

<div class="content-container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Page Title *</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($page['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Slug *</label>
            <input type="text" name="slug" value="<?php echo htmlspecialchars($page['slug']); ?>" required>
            <small>URL: /pages/<?php echo htmlspecialchars($page['slug']); ?>.php</small>
        </div>
        
        <div class="form-group">
            <label>Content (HTML) *</label>
            <textarea name="content" required><?php echo htmlspecialchars($page['content']); ?></textarea>
            <small>You can use HTML tags</small>
        </div>
        
        <div class="form-group">
            <label>Meta Title (SEO)</label>
            <input type="text" name="meta_title" value="<?php echo htmlspecialchars($page['meta_title'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label>Meta Description (SEO)</label>
            <textarea name="meta_description" style="min-height: 80px;"><?php echo htmlspecialchars($page['meta_description'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group checkbox-group">
            <input type="checkbox" name="is_active" value="1" <?php echo $page['is_active'] ? 'checked' : ''; ?>>
            <label style="margin: 0;">Active (visible on website)</label>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">💾 Update Page</button>
            <a href="/admin/pages/" class="btn btn-secondary">← Back to Pages</a>
            <a href="/pages/<?php echo $page['slug']; ?>.php" class="btn btn-secondary" target="_blank">👁️ View Page</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
