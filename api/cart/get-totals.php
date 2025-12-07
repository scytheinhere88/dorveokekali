<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

try {
    $total = 0;
    $count = 0;

    if (isLoggedIn()) {
        $stmt = $pdo->prepare("
            SELECT
                SUM(ci.qty) as total_items,
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
            SELECT
                SUM(ci.qty) as total_items,
                SUM((p.price - (p.price * p.discount_percent / 100) + COALESCE(pv.extra_price, 0)) * ci.qty) as total_amount
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.id
            WHERE ci.session_id = ?
        ");
        $stmt->execute([$session_id]);
    }

    $cart_data = $stmt->fetch();
    $count = intval($cart_data['total_items'] ?? 0);
    $total = floatval($cart_data['total_amount'] ?? 0);

    echo json_encode([
        'success' => true,
        'count' => $count,
        'total' => $total
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching cart totals: ' . $e->getMessage()
    ]);
}
?>
