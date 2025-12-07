<!--
    PROFESSIONAL MEMBER LAYOUT
    Luxury & Clean Design - Fully Responsive
    Use this for ALL member pages
-->

<style>
    /* ============================================
       PROFESSIONAL MEMBER LAYOUT - GUARANTEED TO WORK!
       ============================================ */

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: #F8F9FA;
        color: #1F2937;
    }

    /* ============================================
       DESKTOP: SIDEBAR LEFT, CONTENT RIGHT
       ============================================ */

    .member-wrapper {
        display: flex;
        max-width: 1400px;
        margin: 100px auto 60px;
        padding: 0 40px;
        gap: 48px;
        align-items: flex-start;
    }

    /* SIDEBAR - LEFT SIDE */
    .member-sidebar-pro {
        width: 280px;
        min-width: 280px;
        background: white;
        border-radius: 20px;
        padding: 0;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 120px;
        overflow: hidden;
    }

    .sidebar-header-pro {
        padding: 28px 24px;
        background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        color: white;
        text-align: center;
    }

    .sidebar-header-pro h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .sidebar-header-pro p {
        font-size: 13px;
        opacity: 0.9;
    }

    .sidebar-nav-pro {
        list-style: none;
        padding: 12px;
    }

    .sidebar-nav-pro li {
        margin-bottom: 4px;
    }

    .sidebar-nav-pro a {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        color: #4B5563;
        text-decoration: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .sidebar-nav-pro a:hover {
        background: #F3F4F6;
        color: #1F2937;
        transform: translateX(4px);
    }

    .sidebar-nav-pro a.active {
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: white;
        font-weight: 600;
    }

    .sidebar-nav-pro a svg,
    .sidebar-nav-pro a i {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .sidebar-nav-pro .logout {
        border-top: 1px solid #E5E7EB;
        margin-top: 12px;
        padding-top: 16px;
    }

    .sidebar-nav-pro .logout a {
        color: #EF4444;
    }

    .sidebar-nav-pro .logout a:hover {
        background: #FEE2E2;
        color: #DC2626;
    }

    /* CONTENT - RIGHT SIDE */
    .member-content-pro {
        flex: 1;
        min-width: 0;
        background: white;
        border-radius: 20px;
        padding: 48px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    }

    .member-content-pro h1 {
        font-family: 'Playfair Display', serif;
        font-size: 40px;
        font-weight: 700;
        margin-bottom: 36px;
        color: #1F2937;
        line-height: 1.2;
    }

    /* ============================================
       MOBILE: STACKED LAYOUT
       ============================================ */

    @media (max-width: 968px) {
        .member-wrapper {
            flex-direction: column;
            padding: 0 24px;
            margin: 80px auto 40px;
            gap: 24px;
        }

        .member-sidebar-pro {
            width: 100%;
            position: relative;
            top: 0;
        }

        .sidebar-nav-pro {
            display: flex;
            overflow-x: auto;
            padding: 12px;
            gap: 8px;
            -webkit-overflow-scrolling: touch;
        }

        .sidebar-nav-pro li {
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .sidebar-nav-pro a {
            white-space: nowrap;
            padding: 10px 16px;
            font-size: 13px;
        }

        .sidebar-nav-pro .logout {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        .sidebar-header-pro {
            padding: 20px;
        }

        .sidebar-header-pro h3 {
            font-size: 16px;
        }

        .member-content-pro {
            padding: 32px 24px;
        }

        .member-content-pro h1 {
            font-size: 28px;
            margin-bottom: 28px;
        }
    }

    @media (max-width: 640px) {
        .member-wrapper {
            padding: 0 16px;
            margin: 70px auto 30px;
        }

        .sidebar-header-pro {
            display: none; /* Hide header on small mobile */
        }

        .member-content-pro {
            padding: 24px 20px;
            border-radius: 16px;
        }

        .member-content-pro h1 {
            font-size: 24px;
            margin-bottom: 24px;
        }
    }

    /* ============================================
       SMOOTH ANIMATIONS
       ============================================ */

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .member-wrapper {
        animation: fadeIn 0.3s ease-out;
    }
</style>

<!-- Mobile Menu Toggle (for very small screens) -->
<style>
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        border-radius: 50%;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        z-index: 100;
        transition: all 0.3s;
    }

    .mobile-menu-toggle:active {
        transform: scale(0.95);
    }

    @media (max-width: 640px) {
        .mobile-menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }
</style>
