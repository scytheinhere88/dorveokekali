<?php
require_once __DIR__ . '/../config.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$user = getCurrentUser();
$userId = $_SESSION['user_id'];

// Get user's saved addresses
$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$userId]);
$savedAddresses = $stmt->fetchAll();

// Get cart items with discount
$stmt = $pdo->prepare("SELECT ci.*, p.name, p.price, p.discount_percent, pv.size, pv.color,
                       COALESCE(pi.image_path, p.image) as image_path
                       FROM cart_items ci
                       JOIN products p ON ci.product_id = p.id
                       LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                       LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                       WHERE ci.user_id = ?");
$stmt->execute([$userId]);
$cart_items = $stmt->fetchAll();

// Redirect if cart is empty
if (empty($cart_items)) {
    $_SESSION['error_message'] = 'Your cart is empty!';
    redirect('/pages/cart.php');
}

// Add stock validation and calculate subtotal with discount
$subtotal = 0;
$has_stock_issues = false;
$stock_error_message = '';

foreach ($cart_items as &$item) {
    // Get available stock
    if ($item['variant_id']) {
        $stmt = $pdo->prepare("SELECT stock FROM product_variants WHERE id = ? AND is_active = 1");
        $stmt->execute([$item['variant_id']]);
        $variant = $stmt->fetch();
        $item['available_stock'] = $variant['stock'] ?? 0;
    } else {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(stock), 0) as total_stock
                               FROM product_variants
                               WHERE product_id = ? AND is_active = 1");
        $stmt->execute([$item['product_id']]);
        $result = $stmt->fetch();
        $item['available_stock'] = $result['total_stock'] ?? 0;
    }

    // Check stock
    if ($item['available_stock'] <= 0) {
        $has_stock_issues = true;
        $stock_error_message = 'Beberapa produk di keranjang Anda sudah habis stocknya.';
    } elseif ($item['qty'] > $item['available_stock']) {
        $has_stock_issues = true;
        $stock_error_message = 'Kuantitas beberapa produk melebihi stock yang tersedia.';
    }

    $item_price = calculateDiscount($item['price'], $item['discount_percent']);
    $subtotal += $item_price * $item['qty'];
}

// Redirect back to cart if stock issues
if ($has_stock_issues) {
    $_SESSION['error_message'] = $stock_error_message . ' Silakan periksa keranjang Anda.';
    redirect('/pages/cart.php');
}

try {
    $stmt = $pdo->query("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY display_order");
    $payment_methods = $stmt->fetchAll();
} catch (Exception $e) {
    $payment_methods = [];
}

$payment_enabled = [];
foreach ($payment_methods as $method) {
    $payment_enabled[$method['type']] = true;
}

try {
    $stmt = $pdo->query("SELECT * FROM payment_gateway_settings WHERE is_active = 1");
    $gateway_settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($gateway_settings as $gateway) {
        $payment_enabled[$gateway['gateway_name']] = true;
    }
} catch (Exception $e) {
    // Gateway settings not available
}

$page_title = 'Checkout - Selesaikan Pembayaran | Dorve House';
$page_description = 'Checkout pesanan baju wanita Anda dengan aman. Pilih metode pembayaran: transfer bank, e-wallet, COD. Gratis ongkir min Rp500.000.';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* ================================================================
   ULTIMATE PROFESSIONAL CHECKOUT DESIGN 2024
   Modern, Clean, Fully Responsive, Production Ready
   ================================================================ */

* { box-sizing: border-box; margin: 0; padding: 0; }

/* Main Container */
.checkout-wrapper {
    max-width: 1400px;
    margin: 100px auto 80px;
    padding: 0 40px;
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 60px;
    align-items: flex-start;
}

/* LEFT SIDE - CHECKOUT FORM */
.checkout-form-area {
    width: 100%;
}

.checkout-main-title {
    font-family: 'Playfair Display', serif;
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 16px;
    color: #1F2937;
    line-height: 1.2;
}

.checkout-subtitle {
    font-size: 17px;
    color: #6B7280;
    margin-bottom: 48px;
    line-height: 1.6;
}

/* Form Sections */
.form-section-box {
    background: white;
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 32px;
    border: 1px solid #E5E7EB;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-section-box:hover {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    transform: translateY(-4px);
}

.section-title {
    font-size: 24px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 32px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.section-title::before {
    content: '';
    width: 5px;
    height: 32px;
    background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
    border-radius: 3px;
}

/* Form Elements */
.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 10px;
}

.form-group label .required {
    color: #EF4444;
    margin-left: 4px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    background: #F9FAFB;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667EEA;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
}

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* Shipping/Payment Option Cards */
.option-card {
    display: flex;
    align-items: center;
    padding: 20px 24px;
    border: 2px solid #E5E7EB;
    border-radius: 14px;
    margin-bottom: 16px;
    cursor: pointer;
    transition: all 0.3s;
    background: #F9FAFB;
    position: relative;
}

.option-card:hover {
    border-color: #667EEA;
    background: white;
    transform: translateX(4px);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
}

.option-card.selected {
    border-color: #667EEA;
    background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.25);
}

.option-card input[type="radio"] {
    width: 22px;
    height: 22px;
    margin-right: 16px;
    cursor: pointer;
    flex-shrink: 0;
}

.option-card-content {
    flex: 1;
}

.option-card-name {
    font-size: 16px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 4px;
}

.option-card-desc {
    font-size: 14px;
    color: #6B7280;
    line-height: 1.5;
}

.option-card-price {
    font-size: 18px;
    font-weight: 700;
    color: #667EEA;
    margin-left: 12px;
    flex-shrink: 0;
}

.option-card.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Loading State */
.loading-state {
    text-align: center;
    padding: 60px 20px;
    color: #6B7280;
}

.spinner {
    width: 48px;
    height: 48px;
    border: 5px solid #E5E7EB;
    border-top-color: #667EEA;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* RIGHT SIDE - ORDER SUMMARY */
.order-summary-sidebar {
    background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
    border-radius: 24px;
    padding: 40px;
    color: white;
    position: sticky;
    top: 120px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.summary-title {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 32px;
    color: white;
}

.cart-items-list {
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.cart-item-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 16px;
    font-size: 15px;
    color: rgba(255, 255, 255, 0.9);
}

.cart-item-name {
    flex: 1;
}

.cart-item-price {
    font-weight: 600;
    color: white;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 16px;
    font-size: 15px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.summary-line-label {
    color: rgba(255, 255, 255, 0.8);
}

.summary-line-value {
    font-weight: 600;
    color: white;
}

/* Voucher Section in Summary */
.voucher-section-summary {
    margin: 28px 0;
    padding: 24px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 14px;
    border: 2px dashed rgba(255, 255, 255, 0.2);
}

.btn-open-voucher {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.btn-open-voucher:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
}

#applied-vouchers-container {
    margin-top: 16px;
}

.applied-voucher {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: rgba(16, 185, 129, 0.2);
    border-radius: 10px;
    margin-bottom: 8px;
    font-size: 14px;
}

.applied-voucher .code {
    font-weight: 700;
    font-family: 'Courier New', monospace;
    color: #10B981;
    font-size: 15px;
}

.remove-voucher {
    color: #EF4444;
    cursor: pointer;
    font-size: 20px;
    font-weight: 700;
    transition: all 0.2s;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.1);
}

.remove-voucher:hover {
    background: rgba(239, 68, 68, 0.3);
    transform: scale(1.1);
}

/* Total Price */
.summary-total {
    display: flex;
    justify-content: space-between;
    padding-top: 28px;
    margin-top: 28px;
    border-top: 2px solid rgba(255, 255, 255, 0.2);
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: white;
}

/* ULTIMATE CHECKOUT BUTTON */
.btn-place-order {
    width: 100%;
    padding: 22px;
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 16px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 32px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
}

.btn-place-order::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-place-order:hover::before {
    width: 400px;
    height: 400px;
}

.btn-place-order:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(16, 185, 129, 0.5);
}

.btn-place-order:active {
    transform: translateY(-2px);
}

.btn-place-order:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* VOUCHER MODAL - SUPER PREMIUM */
.voucher-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(12px);
    animation: fadeIn 0.3s;
    align-items: center;
    justify-content: center;
}

.voucher-modal.show {
    display: flex;
}

.voucher-modal-content {
    background: white;
    border-radius: 28px;
    max-width: 920px;
    width: 92%;
    max-height: 88vh;
    overflow-y: auto;
    box-shadow: 0 24px 72px rgba(0, 0, 0, 0.4);
    animation: slideUp 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.voucher-modal-header {
    padding: 36px;
    background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
    color: white;
    border-radius: 28px 28px 0 0;
    position: sticky;
    top: 0;
    z-index: 10;
}

.voucher-modal-header h2 {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 10px;
}

.voucher-modal-header p {
    font-size: 16px;
    opacity: 0.95;
}

.close-modal {
    position: absolute;
    right: 28px;
    top: 28px;
    font-size: 36px;
    cursor: pointer;
    color: white;
    transition: all 0.3s;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
}

.close-modal:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg) scale(1.1);
}

.voucher-modal-body {
    padding: 40px;
}

.voucher-type-section {
    margin-bottom: 48px;
}

.voucher-type-title {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 24px;
    color: #1F2937;
    display: flex;
    align-items: center;
    gap: 12px;
}

.vouchers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.voucher-card-mini {
    background: white;
    border: 2px solid #E5E7EB;
    border-radius: 16px;
    padding: 24px;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.voucher-card-mini::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #667EEA, #764BA2);
    opacity: 0;
    transition: opacity 0.3s;
}

.voucher-card-mini:hover {
    border-color: #667EEA;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.2);
    transform: translateY(-4px);
}

.voucher-card-mini:hover::before {
    opacity: 1;
}

.voucher-card-mini.selected {
    border-color: #10B981;
    background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}

.voucher-card-mini.selected::before {
    background: linear-gradient(90deg, #10B981, #059669);
    opacity: 1;
}

.voucher-card-mini.selected::after {
    content: '✓';
    position: absolute;
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    background: #10B981;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
}

.voucher-code-mini {
    font-family: 'Courier New', monospace;
    font-size: 18px;
    font-weight: 700;
    color: #667EEA;
    margin-bottom: 8px;
}

.voucher-card-mini.selected .voucher-code-mini {
    color: #10B981;
}

.voucher-name-mini {
    font-size: 15px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
}

.voucher-value-mini {
    font-size: 20px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 12px;
}

.voucher-condition-mini {
    font-size: 13px;
    color: #6B7280;
    margin-bottom: 6px;
}

.voucher-modal-footer {
    padding: 28px 40px;
    background: #F9FAFB;
    border-top: 1px solid #E5E7EB;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    bottom: 0;
}

.selected-count {
    font-size: 16px;
    color: #6B7280;
    font-weight: 600;
}

.btn-apply-vouchers {
    padding: 14px 36px;
    background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-apply-vouchers:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
}

/* Bank Transfer Modal */
.bank-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(12px);
    align-items: center;
    justify-content: center;
}

.bank-modal.show {
    display: flex;
}

.bank-modal-content {
    background: white;
    border-radius: 28px;
    max-width: 600px;
    width: 92%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 72px rgba(0, 0, 0, 0.5);
    animation: slideUp 0.5s;
}

.bank-modal-header {
    padding: 36px;
    background: linear-gradient(135deg, #1A1A1A 0%, #2D2D2D 100%);
    color: white;
    border-radius: 28px 28px 0 0;
    text-align: center;
}

.bank-modal-header h2 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 12px;
}

.bank-modal-body {
    padding: 40px;
}

.transfer-amount-display {
    text-align: center;
    padding: 32px;
    background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
    border-radius: 16px;
    margin-bottom: 32px;
    border: 2px solid #C7D2FE;
}

.transfer-amount-label {
    font-size: 14px;
    color: #6B7280;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.transfer-amount-value {
    font-family: 'Playfair Display', serif;
    font-size: 42px;
    font-weight: 700;
    color: #667EEA;
}

.bank-list {
    margin: 32px 0;
}

.bank-list h3 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #1F2937;
}

.bank-item {
    background: #F9FAFB;
    padding: 20px 24px;
    border-radius: 12px;
    margin-bottom: 12px;
    border: 1px solid #E5E7EB;
}

.bank-name {
    font-size: 18px;
    font-weight: 700;
    color: #1F2937;
    margin-bottom: 4px;
}

.bank-details {
    font-size: 14px;
    color: #6B7280;
}

.bank-account-number {
    font-family: 'Courier New', monospace;
    font-size: 16px;
    font-weight: 700;
    color: #667EEA;
    margin-top: 8px;
}

.btn-understood {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 17px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 24px;
}

.btn-understood:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
}

/* MOBILE RESPONSIVE */
@media (max-width: 1024px) {
    .checkout-wrapper {
        grid-template-columns: 1fr;
        gap: 40px;
        padding: 0 24px;
        margin: 80px auto 60px;
    }

    .order-summary-sidebar {
        position: relative;
        top: 0;
        order: 2;
    }

    .checkout-main-title {
        font-size: 36px;
    }
}

@media (max-width: 768px) {
    .checkout-wrapper {
        padding: 0 20px;
        margin: 70px auto 40px;
    }

    .checkout-main-title {
        font-size: 32px;
        margin-bottom: 12px;
    }

    .checkout-subtitle {
        font-size: 15px;
        margin-bottom: 32px;
    }

    .form-section-box {
        padding: 28px 24px;
        margin-bottom: 24px;
        border-radius: 16px;
    }

    .section-title {
        font-size: 20px;
        margin-bottom: 24px;
    }

    .section-title::before {
        width: 4px;
        height: 24px;
    }

    .form-row-2 {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 14px 16px;
        font-size: 16px; /* Prevent iOS zoom */
        border-radius: 10px;
    }

    .option-card {
        padding: 16px 20px;
        border-radius: 12px;
    }

    .option-card-name {
        font-size: 15px;
    }

    .option-card-desc {
        font-size: 13px;
    }

    .option-card-price {
        font-size: 16px;
    }

    .order-summary-sidebar {
        padding: 32px 24px;
        border-radius: 20px;
    }

    .summary-title {
        font-size: 28px;
        margin-bottom: 24px;
    }

    .summary-total {
        font-size: 28px;
    }

    .btn-place-order {
        padding: 18px;
        font-size: 16px;
        border-radius: 14px;
    }

    .voucher-modal-content {
        width: 96%;
        max-height: 92vh;
        border-radius: 20px;
    }

    .voucher-modal-header {
        padding: 28px 24px;
        border-radius: 20px 20px 0 0;
    }

    .voucher-modal-header h2 {
        font-size: 28px;
    }

    .voucher-modal-header p {
        font-size: 14px;
    }

    .close-modal {
        width: 40px;
        height: 40px;
        font-size: 28px;
        right: 20px;
        top: 20px;
    }

    .voucher-modal-body {
        padding: 24px 20px;
    }

    .voucher-type-title {
        font-size: 20px;
        margin-bottom: 20px;
    }

    .vouchers-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .voucher-card-mini {
        padding: 20px;
    }

    .voucher-modal-footer {
        padding: 20px 24px;
        flex-direction: column;
        gap: 16px;
    }

    .btn-apply-vouchers {
        width: 100%;
    }

    .bank-modal-content {
        width: 96%;
        border-radius: 20px;
    }

    .bank-modal-header {
        padding: 28px 24px;
    }

    .bank-modal-header h2 {
        font-size: 28px;
    }

    .bank-modal-body {
        padding: 28px 24px;
    }

    .transfer-amount-display {
        padding: 24px;
    }

    .transfer-amount-value {
        font-size: 36px;
    }
}

@media (max-width: 480px) {
    .checkout-wrapper {
        padding: 0 16px;
        margin: 60px auto 30px;
    }

    .checkout-main-title {
        font-size: 28px;
    }

    .form-section-box {
        padding: 24px 20px;
    }

    .section-title {
        font-size: 18px;
    }

    .order-summary-sidebar {
        padding: 28px 20px;
    }

    .summary-title {
        font-size: 24px;
    }

    .cart-item-row,
    .summary-line {
        font-size: 14px;
    }

    .summary-total {
        font-size: 24px;
    }
}
</style>

<!-- MAIN CHECKOUT WRAPPER -->
<div class="checkout-wrapper">

    <!-- LEFT SIDE: CHECKOUT FORM -->
    <div class="checkout-form-area">
        
        <h1 class="checkout-main-title">Complete Your Order</h1>
        <p class="checkout-subtitle">Fill in your shipping details and select your preferred payment method to complete your purchase.</p>

        <form id="checkout-form" method="POST">

            <!-- SECTION 1: SHIPPING ADDRESS -->
            <div class="form-section-box">
                <h3 class="section-title">📍 Shipping Address</h3>

                <?php if (!empty($savedAddresses)): ?>
                <div class="form-group">
                    <label>Saved Addresses</label>
                    <select id="saved-address-select" class="form-select">
                        <option value="">Select a saved address</option>
                        <?php foreach ($savedAddresses as $addr): ?>
                            <option value="<?= $addr['id'] ?>" 
                                    data-name="<?= htmlspecialchars($addr['recipient_name']) ?>"
                                    data-phone="<?= htmlspecialchars($addr['phone']) ?>"
                                    data-address="<?= htmlspecialchars($addr['address']) ?>"
                                    data-lat="<?= $addr['latitude'] ?>"
                                    data-lng="<?= $addr['longitude'] ?>"
                                    <?= $addr['is_default'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($addr['label']) ?> - <?= htmlspecialchars($addr['recipient_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Recipient Name <span class="required">*</span></label>
                        <input type="text" id="recipient-name" name="recipient_name" required 
                               value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" required 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Full Address <span class="required">*</span></label>
                    <textarea id="address" name="address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">

                <a href="/member/address-book.php" class="btn-new-address" style="display: inline-block; padding: 12px 20px; background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); color: white; text-decoration: none; border-radius: 12px; font-weight: 600; text-align: center; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    ➕ Add New Address
                </a>
            </div>

            <!-- SECTION 2: SHIPPING METHOD -->
            <div class="form-section-box">
                <h3 class="section-title">🚚 Shipping Method</h3>
                
                <div id="shipping-rates-container" class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading shipping options...</p>
                </div>
            </div>

            <!-- SECTION 3: PAYMENT METHOD -->
            <div class="form-section-box">
                <h3 class="section-title">💳 Payment Method</h3>

                <?php if (!empty($payment_methods)): ?>
                    <?php foreach ($payment_methods as $method): ?>
                        <?php
                        $disabled = false;
                        $desc = htmlspecialchars($method['description'] ?? '');

                        if ($method['type'] === 'wallet') {
                            $disabled = $user['wallet_balance'] <= 0;
                            $desc = 'Balance: Rp ' . number_format($user['wallet_balance'], 0, ',', '.');
                        }
                        ?>
                        <div class="option-card <?= $disabled ? 'disabled' : '' ?>"
                             onclick="<?= $disabled ? '' : "selectPaymentMethod('{$method['type']}', this)" ?>">
                            <input type="radio" name="payment_method"
                                   value="<?= htmlspecialchars($method['type']) ?>"
                                   id="payment-<?= htmlspecialchars($method['type']) ?>"
                                   <?= $disabled ? 'disabled' : '' ?>>
                            <div class="option-card-content">
                                <div class="option-card-name">
                                    <?= htmlspecialchars($method['name']) ?>
                                </div>
                                <div class="option-card-desc"><?= $desc ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 24px; text-align: center; color: #666; background: #f9fafb; border-radius: 12px;">
                        <p>⚠️ No payment methods available at the moment.</p>
                        <p style="margin-top: 8px; font-size: 14px;">Please contact admin to enable payment methods.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="voucher_discount" id="voucher-discount-input" value="0">
            <input type="hidden" name="voucher_free_shipping" id="voucher-free-shipping-input" value="0">
            <input type="hidden" name="voucher_codes" id="voucher-codes-input" value="">
            <input type="hidden" name="shipping_cost" id="shipping-cost-input" value="0">
            <input type="hidden" name="courier_code" id="courier-code-input" value="">
            <input type="hidden" name="courier_service" id="courier-service-input" value="">

        </form>

    </div>

    <!-- RIGHT SIDE: ORDER SUMMARY -->
    <div class="order-summary-sidebar">
        <h3 class="summary-title">💰 Order Summary</h3>

        <!-- Cart Items -->
        <div class="cart-items-list">
            <?php foreach ($cart_items as $item):
                $item_price = calculateDiscount($item['price'], $item['discount_percent']);
                $item_total = $item_price * $item['qty'];
            ?>
                <div class="cart-item-row">
                    <div class="cart-item-name">
                        <?= htmlspecialchars($item['name']) ?>
                        <?php if ($item['size'] || $item['color']): ?>
                            <small style="display: block; font-size: 13px; opacity: 0.8;">
                                <?= $item['size'] ? 'Size: ' . $item['size'] : '' ?>
                                <?= $item['color'] ? ' Color: ' . $item['color'] : '' ?>
                            </small>
                        <?php endif; ?>
                        <small style="display: block; font-size: 13px; opacity: 0.8;">
                            Qty: <?= $item['qty'] ?> × Rp <?= number_format($item_price, 0, ',', '.') ?>
                        </small>
                    </div>
                    <div class="cart-item-price">Rp <?= number_format($item_total, 0, ',', '.') ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Summary Lines -->
        <div class="summary-line">
            <span class="summary-line-label">Subtotal</span>
            <span class="summary-line-value" id="summary-subtotal">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
        </div>

        <div class="summary-line" id="summary-shipping-row" style="display: none;">
            <span class="summary-line-label">Shipping</span>
            <span class="summary-line-value" id="summary-shipping">Rp 0</span>
        </div>

        <!-- Voucher Section -->
        <div class="voucher-section-summary">
            <button type="button" class="btn-open-voucher" onclick="openVoucherModal()">
                🎟️ Apply Vouchers
            </button>
            <div id="applied-vouchers-container"></div>
        </div>

        <div class="summary-line" id="summary-discount-row" style="display: none;">
            <span class="summary-line-label">Discount</span>
            <span class="summary-line-value" id="summary-discount" style="color: #10B981;">- Rp 0</span>
        </div>

        <div class="summary-line" id="summary-freeship-row" style="display: none;">
            <span class="summary-line-label">Free Shipping</span>
            <span class="summary-line-value" id="summary-freeship" style="color: #10B981;">- Rp 0</span>
        </div>

        <!-- Total -->
        <div class="summary-total">
            <span>Total</span>
            <span id="summary-total">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
        </div>

        <!-- Place Order Button -->
        <button type="button" class="btn-place-order" id="btn-checkout" onclick="processCheckout()" disabled>
            🛒 Place Order
        </button>
    </div>

</div>

<!-- VOUCHER MODAL -->
<div class="voucher-modal" id="voucher-modal">
    <div class="voucher-modal-content">
        <div class="voucher-modal-header">
            <span class="close-modal" onclick="closeVoucherModal()">×</span>
            <h2>🎟️ Select Your Vouchers</h2>
            <p>Choose up to 2 vouchers: 1 Free Shipping + 1 Discount</p>
        </div>
        
        <div class="voucher-modal-body">
            <!-- Free Shipping Vouchers -->
            <div class="voucher-type-section">
                <h3 class="voucher-type-title">🚚 Free Shipping Vouchers</h3>
                <div class="vouchers-grid" id="free-shipping-vouchers">
                    <p style="text-align: center; color: #6B7280; padding: 40px;">Loading vouchers...</p>
                </div>
            </div>

            <!-- Discount Vouchers -->
            <div class="voucher-type-section">
                <h3 class="voucher-type-title">💰 Discount Vouchers</h3>
                <div class="vouchers-grid" id="discount-vouchers">
                    <p style="text-align: center; color: #6B7280; padding: 40px;">Loading vouchers...</p>
                </div>
            </div>
        </div>

        <div class="voucher-modal-footer">
            <div class="selected-count">Selected: <strong id="selected-count">0</strong> / 2</div>
            <button class="btn-apply-vouchers" onclick="applySelectedVouchers()">Apply Vouchers</button>
        </div>
    </div>
</div>

<!-- Midtrans Snap Script -->
<?php if ($payment_settings && $payment_settings['midtrans_enabled']): ?>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
<?php endif; ?>

<script>
// ================================================================
// ULTIMATE CHECKOUT JAVASCRIPT - FULLY FUNCTIONAL
// ================================================================

const subtotal = <?= $subtotal ?>;
let availableVouchers = { free_shipping: [], discount: [] };
let selectedVouchers = { free_shipping: null, discount: null };
let currentShippingCost = 0;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Auto-load default address if exists
    const savedAddressSelect = document.getElementById('saved-address-select');
    if (savedAddressSelect && savedAddressSelect.value) {
        savedAddressSelect.dispatchEvent(new Event('change'));
    }
});

// ===== ADDRESS SELECTION =====
document.getElementById('saved-address-select')?.addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (!option.value) return;

    document.getElementById('recipient-name').value = option.dataset.name || '';
    document.getElementById('phone').value = option.dataset.phone || '';
    document.getElementById('address').value = option.dataset.address || '';
    document.getElementById('latitude').value = option.dataset.lat || '';
    document.getElementById('longitude').value = option.dataset.lng || '';

    if (option.dataset.lat && option.dataset.lng) {
        fetchShippingRates(option.dataset.lat, option.dataset.lng);
    }
});

// ===== SHIPPING CALCULATION =====
function fetchShippingRates(lat, lng) {
    const container = document.getElementById('shipping-rates-container');
    container.innerHTML = '<div class="loading-state"><div class="spinner"></div><p>Loading shipping options...</p></div>';

    const cartItems = <?= json_encode($cart_items) ?>;

    fetch('/api/shipping/calculate-rates.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ latitude: lat, longitude: lng, items: cartItems })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.rates && data.rates.length > 0) {
            renderShippingRates(data.rates);
        } else {
            container.innerHTML = '<p style="color: #EF4444; text-align: center; padding: 40px;">⚠️ No shipping options available for this address</p>';
        }
    })
    .catch(e => {
        container.innerHTML = '<p style="color: #EF4444; text-align: center; padding: 40px;">❌ Error loading shipping options</p>';
        console.error('Shipping error:', e);
    });
}

function renderShippingRates(rates) {
    const container = document.getElementById('shipping-rates-container');
    container.innerHTML = rates.map((rate, idx) => `
        <div class="option-card" onclick="selectShipping(${rate.price}, '${rate.courier_code}', '${rate.courier_service_name}', this)">
            <input type="radio" name="shipping_method" value="${idx}" id="shipping-${idx}">
            <div class="option-card-content">
                <div class="option-card-name">${rate.courier_name} - ${rate.courier_service_name}</div>
                <div class="option-card-desc">${rate.description || ''} • ${rate.duration || 'N/A'}</div>
            </div>
            <div class="option-card-price">Rp ${formatNumber(rate.price)}</div>
        </div>
    `).join('');
}

function selectShipping(price, code, service, element) {
    // Remove selected from all
    document.querySelectorAll('#shipping-rates-container .option-card').forEach(el => el.classList.remove('selected'));
    
    // Add selected to clicked
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;

    // Update values
    currentShippingCost = price;
    document.getElementById('shipping-cost-input').value = price;
    document.getElementById('courier-code-input').value = code;
    document.getElementById('courier-service-input').value = service;

    // Update display
    document.getElementById('summary-shipping').textContent = 'Rp ' + formatNumber(price);
    document.getElementById('summary-shipping-row').style.display = 'flex';

    // Recalculate
    recalculateTotal();

    // Enable checkout button
    document.getElementById('btn-checkout').disabled = false;
}

// ===== PAYMENT METHOD SELECTION =====
function selectPaymentMethod(method, element) {
    if (element.classList.contains('disabled')) return;

    document.querySelectorAll('.form-section-box:last-of-type .option-card').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
}

// ===== VOUCHER FUNCTIONS =====
function openVoucherModal() {
    document.getElementById('voucher-modal').classList.add('show');
    loadAvailableVouchers();
}

function closeVoucherModal() {
    document.getElementById('voucher-modal').classList.remove('show');
}

function loadAvailableVouchers() {
    fetch(`/api/vouchers/get-available.php?cart_total=${subtotal}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                availableVouchers = data.vouchers;
                renderVouchers();
            }
        })
        .catch(e => console.error('Error loading vouchers:', e));
}

function renderVouchers() {
    // Free Shipping
    const fsContainer = document.getElementById('free-shipping-vouchers');
    if (availableVouchers.free_shipping.length === 0) {
        fsContainer.innerHTML = '<p style="color: #6B7280; text-align: center; padding: 40px;">No free shipping vouchers available</p>';
    } else {
        fsContainer.innerHTML = availableVouchers.free_shipping.map(v => `
            <div class="voucher-card-mini ${selectedVouchers.free_shipping?.id === v.id ? 'selected' : ''}"
                 onclick='selectVoucher("free_shipping", ${JSON.stringify(v).replace(/'/g, "&#39;")})'>
                <div class="voucher-code-mini">${v.code}</div>
                <div class="voucher-name-mini">${v.name}</div>
                <div class="voucher-value-mini">FREE SHIPPING</div>
                ${v.discount_value ? `<div class="voucher-condition-mini">Max: Rp ${formatNumber(v.discount_value)}</div>` : ''}
                ${v.min_purchase ? `<div class="voucher-condition-mini">📦 Min: Rp ${formatNumber(v.min_purchase)}</div>` : ''}
                <div class="voucher-condition-mini">🔢 ${v.max_usage - v.usage_count} uses left</div>
            </div>
        `).join('');
    }

    // Discount
    const dcContainer = document.getElementById('discount-vouchers');
    if (availableVouchers.discount.length === 0) {
        dcContainer.innerHTML = '<p style="color: #6B7280; text-align: center; padding: 40px;">No discount vouchers available</p>';
    } else {
        dcContainer.innerHTML = availableVouchers.discount.map(v => {
            let valueText = v.discount_type === 'percentage' 
                ? `${v.discount_value}% OFF` 
                : `Rp ${formatNumber(v.discount_value)} OFF`;
            
            return `
                <div class="voucher-card-mini ${selectedVouchers.discount?.id === v.id ? 'selected' : ''}"
                     onclick='selectVoucher("discount", ${JSON.stringify(v).replace(/'/g, "&#39;")})'>
                    <div class="voucher-code-mini">${v.code}</div>
                    <div class="voucher-name-mini">${v.name}</div>
                    <div class="voucher-value-mini">${valueText}</div>
                    ${v.min_purchase ? `<div class="voucher-condition-mini">📦 Min: Rp ${formatNumber(v.min_purchase)}</div>` : ''}
                    <div class="voucher-condition-mini">🔢 ${v.max_usage - v.usage_count} uses left</div>
                </div>
            `;
        }).join('');
    }
}

function selectVoucher(type, voucher) {
    if (selectedVouchers[type]?.id === voucher.id) {
        selectedVouchers[type] = null;
    } else {
        selectedVouchers[type] = voucher;
    }
    renderVouchers();
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = (selectedVouchers.free_shipping ? 1 : 0) + (selectedVouchers.discount ? 1 : 0);
    document.getElementById('selected-count').textContent = count;
}

function applySelectedVouchers() {
    const container = document.getElementById('applied-vouchers-container');
    container.innerHTML = '';

    if (selectedVouchers.free_shipping) {
        container.innerHTML += `
            <div class="applied-voucher">
                <div>
                    <span class="code">${selectedVouchers.free_shipping.code}</span>
                    <div style="font-size: 11px; opacity: 0.8;">Free Shipping</div>
                </div>
                <span class="remove-voucher" onclick='removeVoucher("free_shipping")'>✕</span>
            </div>
        `;
    }

    if (selectedVouchers.discount) {
        container.innerHTML += `
            <div class="applied-voucher">
                <div>
                    <span class="code">${selectedVouchers.discount.code}</span>
                    <div style="font-size: 11px; opacity: 0.8;">Discount Voucher</div>
                </div>
                <span class="remove-voucher" onclick='removeVoucher("discount")'>✕</span>
            </div>
        `;
    }

    recalculateTotal();
    closeVoucherModal();
}

function removeVoucher(type) {
    selectedVouchers[type] = null;
    applySelectedVouchers();
}

// ===== TOTAL CALCULATION =====
function recalculateTotal() {
    let total = subtotal + currentShippingCost;
    let discountAmount = 0;
    let freeShippingAmount = 0;

    // Calculate discount
    if (selectedVouchers.discount) {
        const v = selectedVouchers.discount;
        if (v.discount_type === 'percentage') {
            discountAmount = subtotal * (v.discount_value / 100);
            if (v.max_discount) {
                discountAmount = Math.min(discountAmount, v.max_discount);
            }
        } else {
            discountAmount = v.discount_value;
        }
        total -= discountAmount;
    }

    // Calculate free shipping
    if (selectedVouchers.free_shipping) {
        const v = selectedVouchers.free_shipping;
        freeShippingAmount = currentShippingCost;
        if (v.discount_value) {
            freeShippingAmount = Math.min(freeShippingAmount, v.discount_value);
        }
        total -= freeShippingAmount;
    }

    // Update display
    if (discountAmount > 0) {
        document.getElementById('summary-discount').textContent = '- Rp ' + formatNumber(discountAmount);
        document.getElementById('summary-discount-row').style.display = 'flex';
    } else {
        document.getElementById('summary-discount-row').style.display = 'none';
    }

    if (freeShippingAmount > 0) {
        document.getElementById('summary-freeship').textContent = '- Rp ' + formatNumber(freeShippingAmount);
        document.getElementById('summary-freeship-row').style.display = 'flex';
    } else {
        document.getElementById('summary-freeship-row').style.display = 'none';
    }

    document.getElementById('summary-total').textContent = 'Rp ' + formatNumber(Math.max(0, total));

    // Update hidden inputs
    document.getElementById('voucher-discount-input').value = discountAmount;
    document.getElementById('voucher-free-shipping-input').value = freeShippingAmount;
    
    const voucherCodes = [
        selectedVouchers.free_shipping?.code,
        selectedVouchers.discount?.code
    ].filter(Boolean).join(',');
    document.getElementById('voucher-codes-input').value = voucherCodes;
}

// ===== CHECKOUT PROCESSING =====
function processCheckout() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    const shippingMethod = document.querySelector('input[name="shipping_method"]:checked');

    if (!paymentMethod) {
        alert('Please select a payment method');
        return;
    }

    if (!shippingMethod) {
        alert('Please select a shipping method');
        return;
    }

    // Check wallet balance
    if (paymentMethod.value === 'wallet') {
        const walletBalance = <?= $user['wallet_balance'] ?>;
        const total = parseFloat(document.getElementById('summary-total').textContent.replace(/[^\d]/g, ''));
        if (walletBalance < total) {
            alert('Insufficient wallet balance');
            return;
        }
    }

    // Disable button
    const btn = document.getElementById('btn-checkout');
    btn.disabled = true;
    btn.textContent = 'Processing...';

    // Get form data
    const formData = new FormData(document.getElementById('checkout-form'));

    // Send to API
    fetch('/api/orders/create.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (paymentMethod.value === 'wallet') {
                alert('Order placed successfully!');
                window.location.href = '/member/orders.php';
            } else if (paymentMethod.value === 'midtrans' && data.snap_token) {
                snap.pay(data.snap_token, {
                    onSuccess: () => { window.location.href = '/member/orders.php?success=1'; },
                    onPending: () => { window.location.href = '/member/orders.php?pending=1'; },
                    onError: () => { alert('Payment failed'); btn.disabled = false; btn.textContent = '🛒 PLACE ORDER'; },
                    onClose: () => { btn.disabled = false; btn.textContent = '🛒 PLACE ORDER'; }
                });
            } else if (paymentMethod.value === 'bank_transfer') {
                showBankTransferModal(data);
            }
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
            btn.disabled = false;
            btn.textContent = '🛒 PLACE ORDER';
        }
    })
    .catch(e => {
        alert('Error processing order');
        console.error(e);
        btn.disabled = false;
        btn.textContent = '🛒 PLACE ORDER';
    });
}

// ===== BANK TRANSFER MODAL =====
function showBankTransferModal(data) {
    fetch('/api/payment/get-banks.php')
        .then(r => r.json())
        .then(bankData => {
            const modal = document.createElement('div');
            modal.className = 'bank-modal show';
            modal.innerHTML = `
                <div class="bank-modal-content">
                    <div class="bank-modal-header">
                        <h2>💳 Bank Transfer</h2>
                        <p>Complete your payment</p>
                    </div>
                    <div class="bank-modal-body">
                        <div class="transfer-amount-display">
                            <div class="transfer-amount-label">Total Amount</div>
                            <div class="transfer-amount-value">Rp ${formatNumber(data.total_amount)}</div>
                        </div>
                        <div class="bank-list">
                            <h3>Transfer to:</h3>
                            ${bankData.banks ? bankData.banks.map(b => `
                                <div class="bank-item">
                                    <div class="bank-name">${b.bank_name}</div>
                                    <div class="bank-details">${b.account_name}</div>
                                    <div class="bank-account-number">${b.account_number}</div>
                                </div>
                            `).join('') : '<p>No banks available</p>'}
                        </div>
                        <button class="btn-understood" onclick="document.querySelector('.bank-modal').remove(); window.location.href='/member/orders.php'">
                            Got It!
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        });
}

// ===== UTILITY FUNCTIONS =====
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Close modal on outside click
window.onclick = function(event) {
    const voucherModal = document.getElementById('voucher-modal');
    if (event.target === voucherModal) {
        closeVoucherModal();
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
