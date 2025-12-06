<?php
/**
 * SHARED MEMBER SIDEBAR
 * Include this in all member pages for consistency
 * 
 * Usage: include __DIR__ . '/../includes/member-sidebar.php';
 */

if (!isset($user)) {
    $user = getCurrentUser();
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    .member-sidebar {
        position: sticky;
        top: 120px;
        height: fit-content;
    }

    .sidebar-header {
        padding: 30px;
        background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
        margin-bottom: 24px;
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .sidebar-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        margin-bottom: 8px;
        color: var(--charcoal);
    }

    .sidebar-header p {
        font-size: 14px;
        color: var(--grey);
    }

    .sidebar-nav {
        list-style: none;
    }

    .sidebar-nav li {
        margin-bottom: 8px;
    }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        color: var(--charcoal);
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
    }

    .sidebar-nav a:hover {
        background: var(--cream);
        padding-left: 24px;
        transform: translateX(4px);
    }

    .sidebar-nav a.active {
        background: linear-gradient(135deg, var(--charcoal) 0%, #2D2D2D 100%);
        color: var(--white);
        font-weight: 600;
    }

    .sidebar-nav a.active:hover {
        padding-left: 20px;
        transform: none;
    }

    .sidebar-icon {
        font-size: 18px;
        width: 24px;
        text-align: center;
    }

    .logout-btn {
        margin-top: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px 20px;
        background: var(--white);
        border: 2px solid rgba(200, 30, 58, 0.3);
        color: #C41E3A;
        text-decoration: none;
        text-align: center;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .logout-btn:hover {
        background: #C41E3A;
        color: var(--white);
        border-color: #C41E3A;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(196, 30, 58, 0.3);
    }

    @media (max-width: 968px) {
        .member-sidebar {
            position: static;
        }
    }
</style>

<aside class="member-sidebar">
    <div class="sidebar-header">
        <h3>Welcome back!</h3>
        <p><?php echo htmlspecialchars($user['name']); ?></p>
    </div>

    <ul class="sidebar-nav">
        <li>
            <a href="/member/dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <span class="sidebar-icon">🏠</span>
                Dashboard
            </a>
        </li>
        <li>
            <a href="/member/wallet.php" class="<?= $current_page === 'wallet.php' ? 'active' : '' ?>">
                <span class="sidebar-icon">💰</span>
                My Wallet
            </a>
        </li>
        <li>
            <a href="/member/orders.php" class="<?= $current_page === 'orders.php' ? 'active' : '' ?>">
                <span class="sidebar-icon">📦</span>
                My Orders
            </a>
        </li>
        <li>
            <a href="/member/referral.php" class="<?= $current_page === 'referral.php' ? 'active' : '' ?>">
                <span class="sidebar-icon">🎁</span>
                My Referrals
            </a>
        </li>
        <li>
            <a href="/member/address-book.php" class="<?= $current_page === 'address-book.php' ? 'active' : '' ?>">
                <span class="sidebar-icon">📍</span>
                Address Book
            </a>
        </li>
        <li>
            <a href="/member/vouchers/" class="<?= strpos($_SERVER['REQUEST_URI'], '/vouchers/') !== false ? 'active' : '' ?>">
                <span class="sidebar-icon">🎫</span>
                My Vouchers
            </a>
        </li>
        <li>
            <a href="/member/reviews.php" class="<?= $current_page === 'reviews.php' ? 'active' : '' ?>">
                <span class="sidebar-icon">⭐</span>
                My Reviews
            </a>
        </li>
        <li>
            <a href="/member/profile.php" class="<?= $current_page === 'profile.php' ? 'active' : '' ?>">
                <span class="sidebar-icon">👤</span>
                Edit Profile
            </a>
        </li>
        <li>
            <a href="/member/password.php" class="<?= $current_page === 'password.php' ? 'active' : '' ?>">
                <span class="sidebar-icon">🔒</span>
                Change Password
            </a>
        </li>
    </ul>

    <a href="/auth/logout.php" class="logout-btn">
        <span style="font-size: 16px;">🚪</span>
        Logout
    </a>
</aside>
