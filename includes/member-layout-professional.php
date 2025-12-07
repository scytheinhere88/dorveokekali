<!--
    PROFESSIONAL MEMBER LAYOUT CSS
    Include this file in ALL member pages for consistent styling

    Usage:
    <?php include __DIR__ . '/../includes/member-layout-professional.php'; ?>
-->

<style>
    /* ============================================
       PROFESSIONAL MEMBER LAYOUT - INLINE CSS
       Guaranteed to work with no external dependencies!
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
        line-height: 1.6;
    }

    /* ============================================
       MAIN WRAPPER - FLEXBOX LAYOUT
       Desktop: Sidebar LEFT, Content RIGHT
       Mobile: Stacked vertically
       ============================================ */

    .prof-wrapper {
        display: flex;
        max-width: 1400px;
        margin: 100px auto 60px;
        padding: 0 40px;
        gap: 48px;
        align-items: flex-start;
    }

    /* ============================================
       SIDEBAR - LEFT SIDE (Desktop)
       ============================================ */

    .prof-sidebar {
        width: 280px;
        min-width: 280px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 120px;
        overflow: hidden;
        transition: all 0.3s;
    }

    .prof-sidebar-header {
        padding: 28px 24px;
        background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
        color: white;
        text-align: center;
    }

    .prof-sidebar-header h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
    }

    .prof-sidebar-header p {
        font-size: 13px;
        opacity: 0.9;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Navigation Menu */
    .prof-nav {
        list-style: none;
        padding: 12px;
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
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .prof-nav a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 0;
        height: 100%;
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
        transition: width 0.3s;
    }

    .prof-nav a:hover {
        background: #F3F4F6;
        color: #1F2937;
        transform: translateX(4px);
    }

    .prof-nav a:hover::before {
        width: 100%;
    }

    .prof-nav a.active {
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    /* Logout section */
    .prof-nav .logout {
        border-top: 1px solid #E5E7EB;
        margin-top: 12px;
        padding-top: 16px;
    }

    .prof-nav .logout a {
        color: #EF4444;
    }

    .prof-nav .logout a:hover {
        background: #FEE2E2;
        color: #DC2626;
    }

    /* ============================================
       CONTENT - RIGHT SIDE (Desktop)
       ============================================ */

    .prof-content {
        flex: 1;
        min-width: 0;
        background: white;
        border-radius: 20px;
        padding: 48px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
        transition: all 0.3s;
    }

    .prof-content h1 {
        font-family: 'Playfair Display', serif;
        font-size: 40px;
        font-weight: 700;
        margin-bottom: 36px;
        color: #1F2937;
        line-height: 1.2;
    }

    .prof-content h2 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 24px;
        margin-top: 48px;
        color: #1F2937;
    }

    /* ============================================
       RESPONSIVE - MOBILE & TABLET
       ============================================ */

    @media (max-width: 968px) {
        .prof-wrapper {
            flex-direction: column;
            padding: 0 24px;
            margin: 80px auto 40px;
            gap: 24px;
        }

        /* Sidebar becomes full width */
        .prof-sidebar {
            width: 100%;
            position: relative;
            top: 0;
        }

        /* Navigation becomes horizontal scrollable */
        .prof-nav {
            display: flex;
            overflow-x: auto;
            gap: 8px;
            padding: 12px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
        }

        .prof-nav::-webkit-scrollbar {
            display: none; /* Chrome, Safari */
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

        .prof-nav a:hover {
            transform: translateX(0) translateY(-2px);
        }

        .prof-nav .logout {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        /* Content adjustments */
        .prof-content {
            padding: 32px 24px;
            border-radius: 16px;
        }

        .prof-content h1 {
            font-size: 28px;
            margin-bottom: 28px;
        }

        .prof-content h2 {
            font-size: 20px;
            margin-top: 32px;
        }
    }

    @media (max-width: 640px) {
        .prof-wrapper {
            padding: 0 16px;
            margin: 70px auto 30px;
        }

        /* Hide sidebar header on very small screens */
        .prof-sidebar-header {
            padding: 16px;
        }

        .prof-sidebar-header h3 {
            font-size: 16px;
        }

        .prof-content {
            padding: 24px 20px;
        }

        .prof-content h1 {
            font-size: 24px;
            margin-bottom: 24px;
        }

        .prof-content h2 {
            font-size: 18px;
        }
    }

    /* ============================================
       ANIMATIONS
       ============================================ */

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .prof-wrapper {
        animation: fadeIn 0.3s ease-out;
    }

    /* ============================================
       UTILITY CLASSES
       ============================================ */

    .prof-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .prof-badge-success {
        background: #D1FAE5;
        color: #065F46;
    }

    .prof-badge-warning {
        background: #FEF3C7;
        color: #92400E;
    }

    .prof-badge-danger {
        background: #FEE2E2;
        color: #DC2626;
    }

    .prof-badge-info {
        background: #DBEAFE;
        color: #1E40AF;
    }
</style>
