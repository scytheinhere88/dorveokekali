<?php
// Get cart count
$cart_count = 0;
$cart_total = 0;

if (isLoggedIn()) {
    $stmt = $pdo->prepare("
        SELECT SUM(ci.qty) as total_items,
               SUM((p.price - (p.price * p.discount_percent / 100) + COALESCE(pv.extra_price, 0)) * ci.qty) as total_amount
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_variants pv ON ci.variant_id = pv.id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $session_id = session_id();
    $stmt = $pdo->prepare("
        SELECT SUM(ci.qty) as total_items,
               SUM((p.price - (p.price * p.discount_percent / 100) + COALESCE(pv.extra_price, 0)) * ci.qty) as total_amount
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN product_variants pv ON ci.variant_id = pv.id
        WHERE ci.session_id = ?
    ");
    $stmt->execute([$session_id]);
}

$cart_data = $stmt->fetch();
$cart_count = intval($cart_data['total_items'] ?? 0);
$cart_total = floatval($cart_data['total_amount'] ?? 0);
?>

<!-- Floating Cart Button (Mobile & Desktop) -->
<div id="floatingCartBtn" class="floating-cart-btn" style="<?php echo $cart_count > 0 ? '' : 'display: none;'; ?>">
    <div class="floating-cart-content" onclick="window.location.href='/pages/cart.php'">
        <div class="cart-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span class="cart-badge" id="cartBadge"><?php echo $cart_count; ?></span>
        </div>
        <div class="cart-info">
            <div class="cart-items-count" id="cartItemsText"><?php echo $cart_count; ?> item<?php echo $cart_count != 1 ? 's' : ''; ?></div>
            <div class="cart-total" id="cartTotalText">Rp <?php echo number_format($cart_total, 0, ',', '.'); ?></div>
        </div>
    </div>
    <button class="close-cart-btn" onclick="closeFloatingCart(event)" aria-label="Close cart">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4L4 12M4 4l8 8"/>
        </svg>
    </button>
</div>

<style>
.floating-cart-btn {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #1A1A1A;
    color: white;
    padding: 12px 20px;
    border-radius: 50px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    max-width: calc(100vw - 40px);
}

.floating-cart-btn:hover {
    transform: translateX(-50%) translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
}

.floating-cart-content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.cart-icon-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #EF4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
}

.cart-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.cart-items-count {
    font-size: 13px;
    font-weight: 600;
    opacity: 0.9;
}

.cart-total {
    font-size: 16px;
    font-weight: 700;
}

.close-cart-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.close-cart-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .floating-cart-btn {
        bottom: 80px; /* Above mobile menu if any */
        padding: 10px 16px;
    }

    .cart-items-count {
        font-size: 12px;
    }

    .cart-total {
        font-size: 14px;
    }
}

/* Hide on cart and checkout pages */
body.cart-page .floating-cart-btn,
body.checkout-page .floating-cart-btn {
    display: none !important;
}

/* Auto-hide on cart and checkout pages by URL */
<?php
$current_page = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($current_page, '/cart.php') !== false || strpos($current_page, '/checkout.php') !== false):
?>
.floating-cart-btn {
    display: none !important;
}
<?php endif; ?>
</style>

<script>
// Close floating cart button
function closeFloatingCart(e) {
    e.stopPropagation();
    const cartBtn = document.getElementById('floatingCartBtn');
    cartBtn.style.animation = 'slideDown 0.3s ease-out forwards';

    setTimeout(() => {
        cartBtn.style.display = 'none';
        // Store in session storage that user closed it
        sessionStorage.setItem('cartButtonClosed', 'true');
    }, 300);
}

// Animation for closing
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(20px);
        }
    }
`;
document.head.appendChild(style);

// Update cart count when items are added
function updateFloatingCart(count, total) {
    const cartBtn = document.getElementById('floatingCartBtn');
    const badge = document.getElementById('cartBadge');
    const itemsText = document.getElementById('cartItemsText');
    const totalText = document.getElementById('cartTotalText');

    if (count > 0) {
        // Reset session storage when cart is updated
        sessionStorage.removeItem('cartButtonClosed');

        badge.textContent = count;
        itemsText.textContent = count + ' item' + (count !== 1 ? 's' : '');
        totalText.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);

        if (cartBtn.style.display === 'none') {
            cartBtn.style.display = 'flex';
            cartBtn.style.animation = 'slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        }
    } else {
        cartBtn.style.display = 'none';
    }
}

// Check if user previously closed the button
window.addEventListener('DOMContentLoaded', () => {
    const wasClosed = sessionStorage.getItem('cartButtonClosed');
    const cartBtn = document.getElementById('floatingCartBtn');
    const badge = document.getElementById('cartBadge');

    // If was closed and we're not on all-products page, keep it hidden
    if (wasClosed && !window.location.pathname.includes('all-products')) {
        cartBtn.style.display = 'none';
    }

    // If on all-products page, show it regardless
    if (window.location.pathname.includes('all-products') && parseInt(badge.textContent) > 0) {
        sessionStorage.removeItem('cartButtonClosed');
        cartBtn.style.display = 'flex';
    }
});
</script>
