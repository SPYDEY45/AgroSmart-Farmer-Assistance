<?php
/**
 * AgroSmart - Admin Role Authentication Guard
 */

require_once __DIR__ . '/auth.php';

if ($_SESSION['role'] !== 'admin') {
    setFlash('danger', 'Unauthorized access. Administrative privileges required.');
    header("Location: " . BASE_URL . "/login.php");
    exit();
}
