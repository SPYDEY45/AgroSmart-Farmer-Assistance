<?php
/**
 * AgroSmart - Farmer Role Authentication Guard
 */

require_once __DIR__ . '/auth.php';

if ($_SESSION['role'] !== 'farmer') {
    setFlash('danger', 'Unauthorized access. Farmer account required.');
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

// Fetch Farmer profile record and cache in session if not set
if (!isset($_SESSION['farmer_id'])) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT farmer_id, village, district, land_area, main_crop FROM farmers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $farmer = $stmt->fetch();
    if ($farmer) {
        $_SESSION['farmer_id'] = $farmer['farmer_id'];
        $_SESSION['village'] = $farmer['village'];
        $_SESSION['district'] = $farmer['district'];
    }
}
