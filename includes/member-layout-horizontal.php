<?php
/**
 * HORIZONTAL SCROLL MEMBER SIDEBAR
 * Use this for consistent mobile navigation across all member pages
 *
 * Usage:
 *   $active_page = 'dashboard'; // or 'orders', 'wallet', etc
 *   include __DIR__ . '/../includes/member-layout-horizontal.php';
 */

if (!isset($user)) {
    $user = getCurrentUser();
}

// Determine active page if not set
if (!isset($active_page)) {
    $active_page = basename($_SERVER['PHP_SELF'], '.php');
}
?>

<style>
    /* PROFESSIONAL MEMBER LAYOUT - HORIZONTAL SCROLL */
    * { box-sizing: border-box; }

    .prof-wrapper {
        display: flex;
        max-width: 1400px;
        margin: 100px auto 60px;
        padding: 0 40px;
        gap: 48px;
        align-items: flex-start;
    }

    /* SIDEBAR LEFT */
    .prof-sidebar {
        width: 280px;
        min-width: 280px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 120px;
        overflow: hidden;
    }

    .prof-sidebar-header {
        padding: 24px;
        background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        color: white;
        text-align: center;
    }

    .prof-sidebar-header h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .prof-sidebar-header p {
        font-size: 13px;
        opacity: 0.9;
    }

    .prof-nav {
        list-style: none;
        padding: 12px;
        margin: 0;
    }

    .prof-nav li {
        margin-bottom: 4px;
    }

    .prof-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        color: #4B5563;
        text-decoration: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .prof-nav a:hover {
        background: #F3F4F6;
        color: #1F2937;
    }

    .prof-nav a.active {
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: white;
        font-weight: 600;
    }

    .prof-nav .logout {
        border-top: 1px solid #E5E7EB;
        margin-top: 12px;
        padding-top: 16px;
    }

    .prof-nav .logout a {
        color: #EF4444;
    }

    /* CONTENT RIGHT */
    .prof-content {
        flex: 1;
        min-width: 0;
        background: white;
        border-radius: 20px;
        padding: 48px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    }

    .prof-content h1 {
        font-family: 'Playfair Display', serif;
        font-size: 40px;
        font-weight: 700;
        margin-bottom: 36px;
        color: #1F2937;
    }

    /* MOBILE - HORIZONTAL SCROLL */
    @media (max-width: 968px) {
        .prof-wrapper {
            flex-direction: column;
            padding: 0 20px;
            margin: 80px auto 40px;
            gap: 24px;
        }

        .prof-sidebar {
            width: 100%;
            position: relative;
            top: 0;
        }

        .prof-nav {
            display: flex;
            overflow-x: auto;
            gap: 8px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .prof-nav::-webkit-scrollbar {
            display: none;
        }

        .prof-nav li {
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .prof-nav a {
            white-space: nowrap;
            padding: 10px 16px;
            font-size: 13px;
        }

        .prof-nav .logout {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        .prof-content {
            padding: 32px 24px;
        }

        .prof-content h1 {
            font-size: 28px;
            margin-bottom: 28px;
        }
    }

    @media (max-width: 640px) {
        .prof-wrapper {
            padding: 0 16px;
            margin: 70px auto 30px;
        }

        .prof-content {
            padding: 24px 20px;
        }

        .prof-content h1 {
            font-size: 24px;
        }
    }
</style>

<!-- START LAYOUT -->
<div class="prof-wrapper">

    <!-- SIDEBAR LEFT -->
    <aside class="prof-sidebar">
        <div class="prof-sidebar-header">
            <h3>Welcome back!</h3>
            <p><?php echo htmlspecialchars($user['name'] ?? $user['email']); ?></p>
        </div>

        <ul class="prof-nav">
            <li><a href="/member/dashboard.php" class="<?= $active_page === 'dashboard' ? 'active' : '' ?>">🏠 Dashboard</a></li>
            <li><a href="/member/orders.php" class="<?= $active_page === 'orders' ? 'active' : '' ?>">📦 My Orders</a></li>
            <li><a href="/member/wallet.php" class="<?= $active_page === 'wallet' ? 'active' : '' ?>">💰 My Wallet</a></li>
            <li><a href="/member/address-book.php" class="<?= $active_page === 'address-book' ? 'active' : '' ?>">📍 Address Book</a></li>
            <li><a href="/member/referral.php" class="<?= $active_page === 'referral' ? 'active' : '' ?>">👥 My Referrals</a></li>
            <li><a href="/member/vouchers/index.php" class="<?= strpos($_SERVER['REQUEST_URI'], '/vouchers/') !== false ? 'active' : '' ?>">🎟️ My Vouchers</a></li>
            <li><a href="/member/reviews.php" class="<?= $active_page === 'reviews' || $active_page === 'write-review' ? 'active' : '' ?>">⭐ My Reviews</a></li>
            <li><a href="/member/profile.php" class="<?= $active_page === 'profile' ? 'active' : '' ?>">👤 Edit Profile</a></li>
            <li><a href="/member/password.php" class="<?= $active_page === 'password' ? 'active' : '' ?>">🔐 Change Password</a></li>
            <li class="logout"><a href="/auth/logout.php">🚪 Logout</a></li>
        </ul>
    </aside>

    <!-- CONTENT RIGHT - Page content goes here -->
    <main class="prof-content">
