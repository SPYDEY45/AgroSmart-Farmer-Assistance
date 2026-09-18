<?php
/**
 * AgroSmart - Buyer Role Authentication Guard
 */

require_once __DIR__ . '/auth.php';

if ($_SESSION['role'] !== 'buyer') {
    setFlash('danger', 'Unauthorized access. Buyer account required.');
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

if (!isset($_SESSION['buyer_id'])) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT buyer_id, business_name, district FROM buyers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $buyer = $stmt->fetch();
    if ($buyer) {
        $_SESSION['buyer_id'] = $buyer['buyer_id'];
        $_SESSION['business_name'] = $buyer['business_name'];
    }
}
