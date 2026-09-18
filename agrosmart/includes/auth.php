<?php
/**
 * AgroSmart - General Authentication Guard
 * Ensures user is authenticated before accessing private areas.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['user_id'])) {
    setFlash('warning', 'Please login to access this area.');
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

// Check if user is blocked by admin
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT status, role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser || $currentUser['status'] === 'blocked') {
    session_unset();
    session_destroy();
    session_start();
    setFlash('danger', 'Your account has been deactivated or blocked. Please contact the administrator.');
    header("Location: " . BASE_URL . "/login.php");
    exit();
}
