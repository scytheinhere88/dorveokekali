<?php
/**
 * ADMIN - Print Order Invoice
 * Professional A4 invoice layout
 */
require_once __DIR__ . '/../../config.php';

if (!isAdmin()) {
    redirect('/admin/login.php');
}

$orderId = (int)($_GET['id'] ?? 0);

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email, u.phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    die('Order not found');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.sku, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// Get shipping address
$stmt = $pdo->prepare("SELECT * FROM order_addresses WHERE order_id = ? LIMIT 1");
$stmt->execute([$orderId]);
$address = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - Order #<?= $order['order_number'] ?></title>
    <style>
        @media print {
            @page { margin: 15mm; }
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.5;
            padding: 20px;
        }
        .invoice-container {
            max-width: 800px; margin: 0 auto;
            background: white; padding: 40px;
            border: 2px solid #E5E7EB;
        }
        .invoice-header {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 40px; margin-bottom: 30px;
            padding-bottom: 20px; border-bottom: 3px solid #1A1A1A;
        }
        .company-info h1 {
            font-size: 32px; font-weight: bold;
            margin-bottom: 8px; color: #1A1A1A;
        }
        .company-info p {
            color: #6B7280; margin-bottom: 4px;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-title {
            font-size: 24px; font-weight: bold;
            margin-bottom: 8px; color: #667EEA;
        }
        .invoice-meta { margin-bottom: 4px; }
        .invoice-meta strong { color: #1A1A1A; }
        
        .section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 14px; font-weight: bold;
            background: #F3F4F6; padding: 8px 12px;
            margin-bottom: 12px; border-left: 4px solid #667EEA;
        }
        
        .customer-info, .shipping-info {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-block h3 {
            font-size: 12px; font-weight: bold;
            margin-bottom: 8px; color: #374151;
        }
        .info-block p {
            color: #6B7280; margin-bottom: 4px;
        }
        
        table {
            width: 100%; border-collapse: collapse;
            margin: 20px 0;
        }
        thead {
            background: #F3F4F6;
        }
        th {
            padding: 12px; text-align: left;
            font-weight: bold; border-bottom: 2px solid #E5E7EB;
        }
        td {
            padding: 12px; border-bottom: 1px solid #E5E7EB;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .item-image {
            width: 50px; height: 50px;
            object-fit: cover; border-radius: 4px;
        }
        .item-sku {
            font-size: 10px; color: #6B7280;
            background: #F3F4F6; padding: 2px 6px;
            border-radius: 3px; display: inline-block;
        }
        
        .totals {
            margin-top: 30px; float: right; width: 350px;
        }
        .total-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #E5E7EB;
        }
        .total-row.grand {
            font-size: 18px; font-weight: bold;
            background: #F3F4F6; padding: 12px;
            margin-top: 8px; border: 2px solid #667EEA;
        }
        
        .footer {
            margin-top: 50px; padding-top: 20px;
            border-top: 2px solid #E5E7EB;
            text-align: center; color: #6B7280;
            font-size: 11px; clear: both;
        }
        
        .print-btn {
            padding: 12px 24px; background: #667EEA;
            color: white; border: none; border-radius: 8px;
            font-weight: bold; cursor: pointer;
            margin-bottom: 20px;
        }
        .print-btn:hover { background: #5568D3; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" class="print-btn">🖨️ Print Invoice</button>
        <button onclick="window.close()" class="print-btn" style="background: #6B7280;">❌ Close</button>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1>DORVE</h1>
                <p>Jl. Contoh No. 123</p>
                <p>Jakarta, Indonesia 12345</p>
                <p>Phone: +62 812-3456-7890</p>
                <p>Email: info@dorve.id</p>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-meta">
                    <strong>Order #:</strong> <?= htmlspecialchars($order['order_number']) ?>
                </div>
                <div class="invoice-meta">
                    <strong>Date:</strong> <?= date('d M Y', strtotime($order['created_at'])) ?>
                </div>
                <div class="invoice-meta">
                    <strong>Status:</strong> <?= strtoupper($order['payment_status']) ?>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="section">
            <div class="section-title">CUSTOMER INFORMATION</div>
            <div class="customer-info">
                <div class="info-block">
                    <h3>Bill To:</h3>
                    <p><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
                    <p><?= htmlspecialchars($order['email']) ?></p>
                    <p><?= htmlspecialchars($order['phone']) ?></p>
                </div>
                <?php if ($address): ?>
                <div class="info-block">
                    <h3>Ship To:</h3>
                    <p><strong><?= htmlspecialchars($address['recipient_name'] ?? $order['customer_name']) ?></strong></p>
                    <p><?= htmlspecialchars($address['phone'] ?? '') ?></p>
                    <p><?= nl2br(htmlspecialchars($address['address'] ?? '')) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Items -->
        <div class="section">
            <div class="section-title">ORDER ITEMS</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">Image</th>
                        <th>Product Details</th>
                        <th class="text-center" style="width: 80px;">Qty</th>
                        <th class="text-right" style="width: 120px;">Price</th>
                        <th class="text-right" style="width: 120px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <img src="/uploads/products/<?= htmlspecialchars($item['image'] ?? 'placeholder.jpg') ?>" 
                                 class="item-image" alt="Product">
                        </td>
                        <td>
                            <div style="font-weight: bold; margin-bottom: 4px;">
                                <?= htmlspecialchars($item['name']) ?>
                            </div>
                            <span class="item-sku">SKU: <?= htmlspecialchars($item['sku'] ?? 'N/A') ?></span>
                        </td>
                        <td class="text-center"><?= $item['quantity'] ?></td>
                        <td class="text-right">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                        <td class="text-right">
                            <strong>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></span>
            </div>
            <div class="total-row">
                <span>Shipping Cost:</span>
                <span>Rp <?= number_format($order['shipping_cost'], 0, ',', '.') ?></span>
            </div>
            <?php if ($order['voucher_discount'] > 0): ?>
            <div class="total-row" style="color: #10B981;">
                <span>Discount (<?= htmlspecialchars($order['voucher_codes']) ?>):</span>
                <span>- Rp <?= number_format($order['voucher_discount'], 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <?php if ($order['voucher_free_shipping']): ?>
            <div class="total-row" style="color: #10B981;">
                <span>Free Shipping:</span>
                <span>- Rp <?= number_format($order['shipping_cost'], 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <div class="total-row grand">
                <span>GRAND TOTAL:</span>
                <span>Rp <?= number_format($order['total_payable_amount'], 0, ',', '.') ?></span>
            </div>
        </div>

        <div style="clear: both;"></div>

        <!-- Payment Info -->
        <div class="section">
            <div class="section-title">PAYMENT INFORMATION</div>
            <p>Payment Method: <strong><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></strong></p>
            <p>Payment Status: <strong><?= strtoupper($order['payment_status']) ?></strong></p>
            <?php if ($order['paid_at']): ?>
            <p>Paid At: <strong><?= date('d M Y H:i', strtotime($order['paid_at'])) ?></strong></p>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your order!</strong></p>
            <p>This is a computer-generated invoice. No signature required.</p>
            <p>For questions, contact us at info@dorve.id or +62 812-3456-7890</p>
        </div>
    </div>

    <script>
    // Auto print on load (optional)
    // window.onload = () => window.print();
    </script>
</body>
</html>
