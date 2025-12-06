<?php
require_once __DIR__ . '/../../config.php';
if (!isAdmin()) redirect('/admin/login.php');

// Get banners
try {
    $stmt = $pdo->query("SELECT * FROM banners ORDER BY display_order ASC, created_at DESC");
    $banners = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table might not exist yet
    $banners = [];
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    if ($_POST['action'] === 'delete') {
        try {
            // Get image path before deleting
            $stmt = $pdo->prepare("SELECT image_url FROM banners WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $banner = $stmt->fetch();
            
            if ($banner && $banner['image_url']) {
                $image_path = __DIR__ . '/../../' . $banner['image_url'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            $_SESSION['success'] = 'Banner deleted successfully!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to delete banner: ' . $e->getMessage();
        }
        redirect('/admin/promotion/index.php');
    } elseif ($_POST['action'] === 'toggle_status') {
        try {
            $stmt = $pdo->prepare("UPDATE banners SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['success'] = 'Banner status updated!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to update status';
        }
        redirect('/admin/promotion/index.php');
    }
}

$page_title = 'Kelola Promosi & Banner - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="header">
    <h1>Kelola Promosi & Banner</h1>
    <?php if (!empty($banners) || empty($banners)): ?>
        <a href="/admin/promotion/add.php" class="btn btn-primary">+ Add Banner</a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="content-container">
    <p style="margin-bottom: 20px; color: #6B7280;">Manage homepage banners and promotional content</p>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Image</th>
                <th>CTA</th>
                <th>Order</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($banners)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 60px;">
                        <div style="color: #9CA3AF; font-size: 48px; margin-bottom: 16px;">📢</div>
                        <p style="color: #6B7280; margin-bottom: 16px;">No banners yet. Create your first banner!</p>
                        <a href="/admin/promotion/add.php" class="btn btn-primary">+ Add First Banner</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($banners as $banner): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($banner['title']); ?></strong><?php if ($banner['subtitle']): ?><br><small style="color: #6B7280;"><?php echo htmlspecialchars($banner['subtitle']); ?></small><?php endif; ?></td>
                        <td>
                            <?php if (($banner['banner_type'] ?? 'slider') === 'popup'): ?>
                                <span style="background: #F59E0B; color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">🔔 POPUP</span>
                            <?php else: ?>
                                <span style="background: #3B82F6; color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">🖼️ SLIDER</span>
                            <?php endif; ?>
                        </td>
                        <td><?php if ($banner['image_url']): ?><img src="<?php echo htmlspecialchars($banner['image_url']); ?>" style="width: 120px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #E5E7EB;"><?php endif; ?></td>
                        <td><span style="background: #F3F4F6; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;"><?php echo htmlspecialchars($banner['cta_text'] ?? 'Shop Now'); ?></span></td>
                        <td><strong><?php echo $banner['display_order']; ?></strong></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                                <button type="submit" style="border: none; background: none; cursor: pointer; padding: 0;">
                                    <span style="padding: 6px 12px; background: <?php echo $banner['is_active'] ? '#ECFDF5' : '#F3F4F6'; ?>; color: <?php echo $banner['is_active'] ? '#059669' : '#6B7280'; ?>; border-radius: 6px; font-size: 12px; font-weight: 600;"><?php echo $banner['is_active'] ? '✓ Active' : 'Inactive'; ?></span>
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="/admin/promotion/edit.php?id=<?php echo $banner['id']; ?>" class="btn btn-secondary">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this banner?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
