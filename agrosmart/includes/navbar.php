<?php
/**
 * AgroSmart - Responsive Main Navigation Bar
 */
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? null;
$userName = $_SESSION['name'] ?? 'User';
$currentLang = $_SESSION['lang'] ?? 'en';
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-agro sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
      <span class="fs-3">🌱</span>
      <span>AgroSmart</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#agroNavbar" aria-controls="agroNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="agroNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/index.php"><i class="bi bi-house-door me-1"></i><?= __('nav_home') ?></a>
        </li>

        <?php if (!$isLoggedIn): ?>
          <!-- Guest Navigation -->
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/crops.php"><i class="bi bi-book me-1"></i><?= __('nav_crops') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/recommendation.php"><i class="bi bi-magic me-1"></i><?= __('nav_recommendation') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/market_prices.php"><i class="bi bi-graph-up me-1"></i><?= __('nav_market_prices') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/buyer/marketplace.php"><i class="bi bi-shop me-1"></i><?= __('nav_marketplace') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/weather.php"><i class="bi bi-cloud-sun me-1"></i><?= __('nav_weather') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/schemes.php"><i class="bi bi-bank me-1"></i><?= __('nav_schemes') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about.php"><?= __('nav_about') ?></a></li>

        <?php elseif ($userRole === 'farmer'): ?>
          <!-- Farmer Navigation -->
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/dashboard.php"><i class="bi bi-speedometer2 me-1"></i><?= __('nav_dashboard') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/my_crops.php"><i class="bi bi-flower1 me-1"></i><?= __('nav_my_crops') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/recommendation.php"><i class="bi bi-cpu me-1"></i><?= __('nav_recommendation') ?></a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-basket me-1"></i><?= __('nav_marketplace') ?>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/farmer/marketplace.php"><i class="bi bi-eye me-2"></i>Browse Market</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/farmer/add_product.php"><i class="bi bi-plus-circle me-2"></i><?= __('nav_sell_product') ?></a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/farmer/my_products.php"><i class="bi bi-box-seam me-2"></i><?= __('nav_my_products') ?></a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/farmer/enquiries.php"><i class="bi bi-chat-dots me-2"></i><?= __('nav_enquiries') ?></a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-info-circle me-1"></i>Advisories
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/farmer/weather.php"><i class="bi bi-cloud-sun me-2"></i><?= __('nav_weather') ?></a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/farmer/crops.php"><i class="bi bi-book me-2"></i><?= __('nav_crops') ?></a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/farmer/diseases.php"><i class="bi bi-bug me-2"></i><?= __('nav_diseases') ?></a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/market_prices.php"><i class="bi bi-currency-rupee me-2"></i><?= __('nav_market_prices') ?></a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/farmer/schemes.php"><i class="bi bi-bank me-2"></i><?= __('nav_schemes') ?></a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/ask_expert.php"><i class="bi bi-person-badge me-1"></i><?= __('nav_ask_expert') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/complaints.php"><i class="bi bi-exclamation-triangle me-1"></i><?= __('nav_complaints') ?></a></li>

        <?php elseif ($userRole === 'buyer'): ?>
          <!-- Buyer Navigation -->
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/buyer/dashboard.php"><i class="bi bi-speedometer2 me-1"></i><?= __('nav_dashboard') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/buyer/marketplace.php"><i class="bi bi-shop me-1"></i><?= __('nav_marketplace') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/buyer/my_enquiries.php"><i class="bi bi-chat-left-text me-1"></i><?= __('nav_enquiries') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/market_prices.php"><i class="bi bi-graph-up me-1"></i><?= __('nav_market_prices') ?></a></li>

        <?php elseif ($userRole === 'expert'): ?>
          <!-- Expert Navigation -->
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/expert/dashboard.php"><i class="bi bi-speedometer2 me-1"></i><?= __('nav_dashboard') ?></a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/expert/questions.php"><i class="bi bi-question-circle me-1"></i>Pending Questions</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/expert/responses.php"><i class="bi bi-check2-all me-1"></i>My Responses</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/farmer/crops.php"><i class="bi bi-book me-1"></i><?= __('nav_crops') ?></a></li>

        <?php elseif ($userRole === 'admin'): ?>
          <!-- Admin Navigation -->
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2 me-1"></i><?= __('nav_dashboard') ?></a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-people me-1"></i>Users
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/farmers.php"><i class="bi bi-person me-2"></i>Farmers</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/buyers.php"><i class="bi bi-shop-window me-2"></i>Buyers</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/experts.php"><i class="bi bi-mortarboard me-2"></i>Experts</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-gear me-1"></i>Catalog & Market
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/products.php"><i class="bi bi-boxes me-2"></i>Products Approval</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/crops.php"><i class="bi bi-flower1 me-2"></i>Crop Directory</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/diseases.php"><i class="bi bi-bug me-2"></i>Disease Database</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/market_prices.php"><i class="bi bi-currency-rupee me-2"></i>APMC Market Prices</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/schemes.php"><i class="bi bi-bank me-2"></i>Government Schemes</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/complaints.php"><i class="bi bi-shield-exclamation me-1"></i>Complaints</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/reports.php"><i class="bi bi-pie-chart me-1"></i>Reports</a></li>
        <?php endif; ?>
      </ul>

      <!-- Right Side Utility Navigation -->
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <!-- Language Switcher -->
        <li class="nav-item dropdown me-2">
          <button class="btn btn-sm btn-outline-light dropdown-toggle text-capitalize" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-translate me-1"></i><?= $currentLang === 'mr' ? 'मराठी' : 'English' ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item <?= $currentLang === 'en' ? 'active' : '' ?>" href="?set_lang=en">English</a></li>
            <li><a class="dropdown-item <?= $currentLang === 'mr' ? 'active' : '' ?>" href="?set_lang=mr">मराठी (Marathi)</a></li>
          </ul>
        </li>

        <?php if ($isLoggedIn): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white fw-semibold" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i><?= e($userName) ?>
              <span class="badge bg-light text-dark text-capitalize ms-1"><?= e($userRole) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if ($userRole === 'farmer'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/farmer/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
              <?php elseif ($userRole === 'buyer'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/buyer/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
              <?php elseif ($userRole === 'expert'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/expert/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i><?= __('nav_logout') ?></a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item me-2">
            <a class="btn btn-sm btn-outline-light px-3" href="<?= BASE_URL ?>/login.php"><i class="bi bi-box-arrow-in-right me-1"></i><?= __('nav_login') ?></a>
          </li>
          <li class="nav-item">
            <a class="btn btn-sm btn-warning text-dark fw-semibold px-3" href="<?= BASE_URL ?>/register.php"><i class="bi bi-pencil-square me-1"></i><?= __('nav_register') ?></a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
