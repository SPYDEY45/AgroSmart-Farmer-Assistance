<?php
/**
 * AgroSmart - Landing Page
 */
$pageTitle = 'AgroSmart – Smart Agriculture Assistant & Farmer Market Portal';
require_once __DIR__ . '/includes/header.php';
$pdo = getDBConnection();

// Fetch quick preview counts for home cards
$cropCount = $pdo->query("SELECT COUNT(*) FROM crops")->fetchColumn() ?: 8;
$productCount = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'approved'")->fetchColumn() ?: 4;
$farmerCount = $pdo->query("SELECT COUNT(*) FROM farmers")->fetchColumn() ?: 2;
$schemeCount = $pdo->query("SELECT COUNT(*) FROM schemes")->fetchColumn() ?: 4;
?>

<!-- Hero Section -->
<div class="agro-hero text-center mb-5 rounded-4 shadow-sm">
  <div class="container">
    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold text-uppercase mb-3">
      🌱 BCA Field Project 2026
    </span>
    <h1><?= __('hero_title') ?></h1>
    <p class="lead"><?= __('hero_subtitle') ?></p>
    
    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
      <a href="<?= BASE_URL ?>/farmer/crops.php" class="btn btn-warning btn-lg px-4 fw-bold shadow">
        <i class="bi bi-book-half me-2"></i><?= __('btn_explore_crops') ?>
      </a>
      <a href="<?= BASE_URL ?>/buyer/marketplace.php" class="btn btn-light btn-lg px-4 fw-bold text-success shadow">
        <i class="bi bi-shop me-2"></i><?= __('btn_marketplace') ?>
      </a>
      <a href="<?= BASE_URL ?>/farmer/recommendation.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
        <i class="bi bi-magic me-2"></i><?= __('btn_recommendation') ?>
      </a>
    </div>
  </div>
</div>

<!-- Core Feature Highlights -->
<div class="container my-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold text-success">Integrated Agricultural Services</h2>
    <p class="text-muted">A multi-stakeholder ecosystem uniting Farmers, Buyers, Agronomy Experts, and Admin</p>
  </div>

  <div class="row g-4">
    <!-- 1. Crop Information -->
    <div class="col-md-6 col-lg-3">
      <div class="card card-agro h-100 p-3 text-center">
        <div class="fs-1 text-success mb-2">🌾</div>
        <h5 class="fw-bold">Crop Information</h5>
        <p class="text-muted small">Comprehensive cultivation schedules, optimal sowing temperatures, soil suitability, and harvest periods.</p>
        <div class="mt-auto">
          <a href="<?= BASE_URL ?>/farmer/crops.php" class="btn btn-sm btn-outline-success w-100">Explore (<?= $cropCount ?>) Crops</a>
        </div>
      </div>
    </div>

    <!-- 2. Smart Crop Recommendation -->
    <div class="col-md-6 col-lg-3">
      <div class="card card-agro h-100 p-3 text-center">
        <div class="fs-1 text-success mb-2">🌱</div>
        <h5 class="fw-bold">Smart Recommendation</h5>
        <p class="text-muted small">Rule-based agronomy algorithm matching your soil type, season, water source, and acreage with ideal crops.</p>
        <div class="mt-auto">
          <a href="<?= BASE_URL ?>/farmer/recommendation.php" class="btn btn-sm btn-agro-primary w-100">Run Advisory</a>
        </div>
      </div>
    </div>

    <!-- 3. Weather Advisory -->
    <div class="col-md-6 col-lg-3">
      <div class="card card-agro h-100 p-3 text-center">
        <div class="fs-1 text-info mb-2">🌦️</div>
        <h5 class="fw-bold">Weather Advisory</h5>
        <p class="text-muted small">Live and advisory weather reports with temperature, humidity, wind, and rain alerts for field operations.</p>
        <div class="mt-auto">
          <a href="<?= BASE_URL ?>/farmer/weather.php" class="btn btn-sm btn-outline-info w-100">Check Weather</a>
        </div>
      </div>
    </div>

    <!-- 4. Crop Doctor / Diseases -->
    <div class="col-md-6 col-lg-3">
      <div class="card card-agro h-100 p-3 text-center">
        <div class="fs-1 text-danger mb-2">🐛</div>
        <h5 class="fw-bold">Crop Diseases & Pests</h5>
        <p class="text-muted small">Early identification of symptoms, causative pathogens, preventive precautions, and chemical/organic management.</p>
        <div class="mt-auto">
          <a href="<?= BASE_URL ?>/farmer/diseases.php" class="btn btn-sm btn-outline-danger w-100">Diagnosis Guide</a>
        </div>
      </div>
    </div>

    <!-- 5. Farmer Marketplace -->
    <div class="col-md-6 col-lg-3">
      <div class="card card-agro h-100 p-3 text-center">
        <div class="fs-1 text-primary mb-2">🛒</div>
        <h5 class="fw-bold">Farmer Marketplace</h5>
        <p class="text-muted small">Direct farmer-to-buyer sales of grains, pulses, oilseeds, and vegetables without commission middlemen.</p>
        <div class="mt-auto">
          <a href="<?= BASE_URL ?>/buyer/marketplace.php" class="btn btn-sm btn-outline-primary w-100">Shop Produce</a>
        </div>
      </div>
    </div>

    <!-- 6. Market Prices -->
    <div class="col-md-6 col-lg-3">
      <div class="card card-agro h-100 p-3 text-center">
        <div class="fs-1 text-warning mb-2">📊</div>
        <h5 class="fw-bold">APMC Market Prices</h5>
        <p class="text-muted small">Daily benchmark Minimum, Maximum, and Modal mandi prices from Maharashtra market yards for informed trading.</p>
        <div class="mt-auto">
          <a href="<?= BASE_URL ?>/admin/market_prices.php" class="btn btn-sm btn-outline-warning text-dark w-100">View Mandi Rates</a>
        </div>
      </div>
    </div>

    <!-- 7. Government Schemes -->
    <div class="col-md-6 col-lg-3">
      <div class="card card-agro h-100 p-3 text-center">
        <div class="fs-1 text-secondary mb-2">🏛️</div>
        <h5 class="fw-bold">Govt Agriculture Schemes</h5>
        <p class="text-muted small">Official subsidy programs including PM-KISAN, PMFBY crop insurance, Soil Health Card, and farm pond grants.</p>
        <div class="mt-auto">
          <a href="<?= BASE_URL ?>/farmer/schemes.php" class="btn btn-sm btn-outline-secondary w-100">Browse Schemes</a>
        </div>
      </div>
    </div>

    <!-- 8. Expert Guidance -->
    <div class="col-md-6 col-lg-3">
      <div class="card card-agro h-100 p-3 text-center">
        <div class="fs-1 text-success mb-2">👨‍🌾</div>
        <h5 class="fw-bold">Expert Agriculture Guidance</h5>
        <p class="text-muted small">Direct Q&A desk connecting registered farmers with certified agricultural scientists and agronomy officers.</p>
        <div class="mt-auto">
          <a href="<?= BASE_URL ?>/farmer/ask_expert.php" class="btn btn-sm btn-agro-primary w-100">Consult Expert</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Project Statistics Bar -->
<div class="bg-light py-5 border-top border-bottom">
  <div class="container">
    <div class="row text-center g-4">
      <div class="col-6 col-md-3">
        <h2 class="display-5 fw-bold text-success"><?= $farmerCount ?>+</h2>
        <p class="text-muted fw-semibold">Registered Farmers</p>
      </div>
      <div class="col-6 col-md-3">
        <h2 class="display-5 fw-bold text-primary"><?= $productCount ?>+</h2>
        <p class="text-muted fw-semibold">Active Market Listings</p>
      </div>
      <div class="col-6 col-md-3">
        <h2 class="display-5 fw-bold text-warning"><?= $cropCount ?>+</h2>
        <p class="text-muted fw-semibold">Crops Documented</p>
      </div>
      <div class="col-6 col-md-3">
        <h2 class="display-5 fw-bold text-secondary"><?= $schemeCount ?>+</h2>
        <p class="text-muted fw-semibold">Govt Schemes Listed</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
