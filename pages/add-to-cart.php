<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$product_id = $_POST['product_id'] ?? 0;
$variant_id = $_POST['variant_id'] ?? null;
$qty = intval($_POST['qty'] ?? 1);

if ($qty < 1 || $qty > 10) {
    $qty = 1;
}

try {
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ? AND variant_id <=> ?");
        $stmt->execute([$_SESSION['user_id'], $product_id, $variant_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $new_qty = min($existing['qty'] + $qty, 10);
            $stmt = $pdo->prepare("UPDATE cart_items SET qty = ? WHERE id = ?");
            $stmt->execute([$new_qty, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, variant_id, qty) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id, $variant_id, $qty]);
        }
    } else {
        $session_id = session_id();

        $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE session_id = ? AND product_id = ? AND variant_id <=> ?");
        $stmt->execute([$session_id, $product_id, $variant_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $new_qty = min($existing['qty'] + $qty, 10);
            $stmt = $pdo->prepare("UPDATE cart_items SET qty = ? WHERE id = ?");
            $stmt->execute([$new_qty, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart_items (session_id, product_id, variant_id, qty) VALUES (?, ?, ?, ?)");
            $stmt->execute([$session_id, $product_id, $variant_id, $qty]);
        }
    }

    // Get updated cart count and total
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
        $stmt = $pdo->prepare("
            SELECT SUM(ci.qty) as total_items,
                   SUM((p.price - (p.price * p.discount_percent / 100) + COALESCE(pv.extra_price, 0)) * ci.qty) as total_amount
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.id
            WHERE ci.session_id = ?
        ");
        $stmt->execute([session_id()]);
    }

    $cart_data = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart!',
        'cart_count' => intval($cart_data['total_items'] ?? 0),
        'cart_total' => floatval($cart_data['total_amount'] ?? 0)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error adding to cart: ' . $e->getMessage()]);
}
