<?php
/**
 * AgroSmart - Common Page Header
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Handle Language Switcher query parameter
if (isset($_GET['set_lang'])) {
    $requestedLang = $_GET['set_lang'] === 'mr' ? 'mr' : 'en';
    $_SESSION['lang'] = $requestedLang;
    // Strip set_lang from query parameters to keep clean URL
    $cleanUri = strtok($_SERVER["REQUEST_URI"], '?');
    $queryParams = $_GET;
    unset($queryParams['set_lang']);
    $qs = http_build_query($queryParams);
    $redirectUrl = $cleanUri . ($qs ? '?' . $qs : '');
    header("Location: " . $redirectUrl);
    exit();
}

$pageTitle = $pageTitle ?? __('app_name') . ' - ' . __('tagline');
$currentRole = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="<?= e($_SESSION['lang'] ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Chart.js (For Dashboards and Reports) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <!-- Custom AgroSmart CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/navbar.php'; ?>
<main class="py-4">
    <div class="container">
        <?php displayFlash(); ?>
    </div>
