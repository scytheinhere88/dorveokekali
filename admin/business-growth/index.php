<?php
require_once __DIR__ . '/../../config.php';
if (!isAdmin()) redirect('/admin/login.php');

// Get date range from filter
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Validate date range (max 6 months)
$start_timestamp = strtotime($start_date);
$end_timestamp = strtotime($end_date);
$six_months_ago = strtotime('-6 months');

if ($start_timestamp < $six_months_ago) {
    $start_date = date('Y-m-d', $six_months_ago);
}

// Today's stats
$today = date('Y-m-d');

// New members today
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$new_members_today = $stmt->fetchColumn();

// New members who deposited today
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT u.id) as count 
    FROM users u
    INNER JOIN topups t ON u.id = t.user_id
    WHERE DATE(u.created_at) = ? AND DATE(t.created_at) = ? AND t.status = 'completed'
");
$stmt->execute([$today, $today]);
$new_members_deposited_today = $stmt->fetchColumn();

// Total deposits today (all members)
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM topups 
    WHERE DATE(created_at) = ? AND status = 'completed'
");
$stmt->execute([$today]);
$total_deposits_today = $stmt->fetchColumn();

// Total successful transactions today
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
    FROM orders 
    WHERE DATE(created_at) = ? AND status IN ('completed', 'shipped', 'delivered')
");
$stmt->execute([$today]);
$today_orders = $stmt->fetch();
$successful_transactions_today = $today_orders['count'] ?? 0;
$total_transaction_amount_today = $today_orders['total'] ?? 0;

// Get daily stats for date range
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as new_members,
        COALESCE(SUM(CASE WHEN id IN (
            SELECT DISTINCT user_id FROM topups WHERE status = 'completed'
        ) THEN 1 ELSE 0 END), 0) as members_deposited
    FROM users 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_members = $stmt->fetchAll();

// Get daily deposits for date range
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as deposit_count,
        COALESCE(SUM(amount), 0) as total_amount
    FROM topups 
    WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_deposits = $stmt->fetchAll();

// Get daily orders for date range
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as order_count,
        COALESCE(SUM(total_amount), 0) as total_amount
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND status IN ('completed', 'shipped', 'delivered')
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_orders = $stmt->fetchAll();

// Combine all data by date
$combined_data = [];
foreach ($daily_members as $row) {
    $combined_data[$row['date']]['new_members'] = $row['new_members'];
    $combined_data[$row['date']]['members_deposited'] = $row['members_deposited'];
}
foreach ($daily_deposits as $row) {
    $combined_data[$row['date']]['deposit_count'] = $row['deposit_count'];
    $combined_data[$row['date']]['deposit_amount'] = $row['total_amount'];
}
foreach ($daily_orders as $row) {
    $combined_data[$row['date']]['order_count'] = $row['order_count'];
    $combined_data[$row['date']]['order_amount'] = $row['total_amount'];
}

krsort($combined_data); // Sort by date descending

$page_title = 'Business Growth Dashboard - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}
.stat-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    border: 2px solid #E5E7EB;
    transition: all 0.3s;
}
.stat-card:hover {
    border-color: #3B82F6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}
.stat-icon {
    font-size: 32px;
    margin-bottom: 12px;
}
.stat-value {
    font-size: 32px;
    font-weight: 800;
    color: #1F2937;
    margin: 8px 0;
}
.stat-label {
    font-size: 14px;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.filter-section {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    border: 2px solid #E5E7EB;
}
.date-filter {
    display: flex;
    gap: 16px;
    align-items: end;
}
</style>

<div class="header">
    <h1>📊 Business Growth Dashboard</h1>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <h3 style="margin: 0 0 16px 0;">📅 Date Range Filter</h3>
    <form method="GET" class="date-filter">
        <div class="form-group" style="margin: 0;">
            <label>Start Date</label>
            <input type="date" name="start_date" value="<?php echo $start_date; ?>" max="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d', strtotime('-6 months')); ?>" required>
        </div>
        <div class="form-group" style="margin: 0;">
            <label>End Date</label>
            <input type="date" name="end_date" value="<?php echo $end_date; ?>" max="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="/admin/business-growth/index.php" class="btn btn-secondary">Reset</a>
    </form>
    <small style="color: #6B7280; display: block; margin-top: 12px;">
        ℹ️ Data is limited to the last 6 months. Older data is automatically cleaned up.
    </small>
</div>

<!-- Today's Stats -->
<h2 style="margin-bottom: 20px;">📈 Today's Overview (<?php echo date('d M Y'); ?>)</h2>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-label">New Members</div>
        <div class="stat-value"><?php echo number_format($new_members_today); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">💳</div>
        <div class="stat-label">New Members Deposited</div>
        <div class="stat-value"><?php echo number_format($new_members_deposited_today); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-label">Total Deposits</div>
        <div class="stat-value">Rp <?php echo number_format($total_deposits_today, 0, ',', '.'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🛒</div>
        <div class="stat-label">Successful Transactions</div>
        <div class="stat-value"><?php echo number_format($successful_transactions_today); ?></div>
    </div>
    
    <div class="stat-card" style="grid-column: span 2;">
        <div class="stat-icon">💵</div>
        <div class="stat-label">Total Transaction Amount</div>
        <div class="stat-value">Rp <?php echo number_format($total_transaction_amount_today, 0, ',', '.'); ?></div>
    </div>
</div>

<!-- Historical Data -->
<h2 style="margin: 40px 0 20px;">📊 Historical Data</h2>
<div class="content-container">
    <?php if (empty($combined_data)): ?>
        <p style="text-align: center; color: #6B7280; padding: 40px;">No data available for selected date range</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>New Members</th>
                    <th>Members Deposited</th>
                    <th>Deposit Count</th>
                    <th>Deposit Amount</th>
                    <th>Orders</th>
                    <th>Order Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($combined_data as $date => $data): ?>
                    <tr>
                        <td><strong><?php echo date('d M Y', strtotime($date)); ?></strong></td>
                        <td><?php echo number_format($data['new_members'] ?? 0); ?></td>
                        <td><?php echo number_format($data['members_deposited'] ?? 0); ?></td>
                        <td><?php echo number_format($data['deposit_count'] ?? 0); ?></td>
                        <td><strong>Rp <?php echo number_format($data['deposit_amount'] ?? 0, 0, ',', '.'); ?></strong></td>
                        <td><?php echo number_format($data['order_count'] ?? 0); ?></td>
                        <td><strong>Rp <?php echo number_format($data['order_amount'] ?? 0, 0, ',', '.'); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
