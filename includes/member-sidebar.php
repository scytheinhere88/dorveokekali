<?php
/**
 * PREMIUM MEMBER SIDEBAR - LUXURY DESIGN
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
    :root {
        --sidebar-bg: #FFFFFF;
        --sidebar-border: #E5E7EB;
        --sidebar-hover: #F9FAFB;
        --sidebar-active: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        --text-primary: #1F2937;
        --text-secondary: #6B7280;
        --text-white: #FFFFFF;
        --accent: #667EEA;
        --accent-hover: #764BA2;
        --danger: #EF4444;
        --danger-hover: #DC2626;
    }

    .member-layout {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 40px;
        max-width: 1400px;
        margin: 100px auto 60px;
        padding: 0 40px;
    }

    .member-sidebar {
        position: sticky;
        top: 120px;
        height: fit-content;
        background: var(--sidebar-bg);
        border-radius: 20px;
        padding: 32px 24px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        border: 1px solid var(--sidebar-border);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .member-sidebar:hover {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .sidebar-header {
        padding: 24px 20px;
        background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
        margin-bottom: 28px;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }

    .sidebar-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--accent) 0%, var(--accent-hover) 100%);
    }

    .sidebar-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 22px;
        margin-bottom: 4px;
        color: var(--text-primary);
        font-weight: 700;
    }

    .sidebar-header p {
        font-size: 14px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-nav li {
        margin-bottom: 6px;
    }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 13px 18px;
        color: var(--text-primary);
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }

    .sidebar-nav a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 3px;
        height: 100%;
        background: var(--accent);
        transform: scaleY(0);
        transition: transform 0.3s;
    }

    .sidebar-nav a:hover {
        background: var(--sidebar-hover);
        padding-left: 22px;
        color: var(--accent);
    }

    .sidebar-nav a:hover::before {
        transform: scaleY(1);
    }

    .sidebar-nav a.active {
        background: var(--sidebar-active);
        color: var(--text-white);
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .sidebar-nav a.active::before {
        background: #FFFFFF;
        transform: scaleY(1);
    }

    .sidebar-nav a.active:hover {
        padding-left: 18px;
        transform: translateX(2px);
    }

    .sidebar-icon {
        font-size: 18px;
        width: 24px;
        text-align: center;
        transition: transform 0.3s;
    }

    .sidebar-nav a:hover .sidebar-icon {
        transform: scale(1.2);
    }

    .logout-btn {
        margin-top: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 14px 20px;
        background: var(--sidebar-bg);
        border: 2px solid rgba(239, 68, 68, 0.3);
        color: var(--danger);
        text-decoration: none;
        text-align: center;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .logout-btn:hover {
        background: var(--danger);
        color: var(--text-white);
        border-color: var(--danger);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
    }

    .logout-btn:active {
        transform: translateY(0);
    }

    .mobile-menu-toggle {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        background: var(--sidebar-active);
        border-radius: 50%;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        z-index: 999;
        transition: all 0.3s;
    }

    .mobile-menu-toggle:active {
        transform: scale(0.95);
    }

    @media (max-width: 968px) {
        .member-layout {
            grid-template-columns: 1fr;
            gap: 0;
            padding: 0 20px;
            margin: 60px auto 40px;
        }

        .member-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 85%;
            max-width: 320px;
            height: 100vh;
            overflow-y: auto;
            border-radius: 0 20px 20px 0;
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: none;
        }

        .member-sidebar.open {
            transform: translateX(0);
            box-shadow: 8px 0 32px rgba(0, 0, 0, 0.2);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        .mobile-menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .member-content {
            padding-top: 0;
        }
    }

    @media (max-width: 480px) {
        .member-sidebar {
            width: 90%;
        }

        .member-layout {
            padding: 0 15px;
        }
    }
</style>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="member-sidebar" id="memberSidebar">
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

<button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileSidebar()">
    ☰
</button>

<script>
function toggleMobileSidebar() {
    const sidebar = document.getElementById('memberSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('mobileMenuToggle');

    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');

    if (sidebar.classList.contains('open')) {
        toggle.innerHTML = '✕';
        document.body.style.overflow = 'hidden';
    } else {
        toggle.innerHTML = '☰';
        document.body.style.overflow = '';
    }
}

document.getElementById('sidebarOverlay').addEventListener('click', toggleMobileSidebar);

document.querySelectorAll('.sidebar-nav a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 968) {
            setTimeout(toggleMobileSidebar, 300);
        }
    });
});
</script>
