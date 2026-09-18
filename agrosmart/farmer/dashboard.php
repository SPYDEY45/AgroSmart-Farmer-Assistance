<?php
/**
 * AgroSmart - Farmer Dashboard
 */
$pageTitle = 'Farmer Dashboard – AgroSmart';
require_once __DIR__ . '/../includes/farmer_auth.php';
require_once __DIR__ . '/../includes/header.php';

$farmerId = $_SESSION['farmer_id'] ?? 0;
$pdo = getDBConnection();

// Fetch summary metrics
$myCropsCount = $pdo->prepare("SELECT COUNT(*) FROM farmer_crops WHERE farmer_id = ?");
$myCropsCount->execute([$farmerId]);
$totalCrops = $myCropsCount->fetchColumn() ?: 0;

$myProductsCount = $pdo->prepare("SELECT COUNT(*) FROM products WHERE farmer_id = ?");
$myProductsCount->execute([$farmerId]);
$totalProducts = $myProductsCount->fetchColumn() ?: 0;

$enquiriesCount = $pdo->prepare("SELECT COUNT(*) FROM enquiries WHERE farmer_id = ? AND status = 'Pending'");
$enquiriesCount->execute([$farmerId]);
$pendingEnquiries = $enquiriesCount->fetchColumn() ?: 0;

$complaintsCount = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE farmer_id = ? AND status != 'Resolved'");
$complaintsCount->execute([$farmerId]);
$pendingComplaints = $complaintsCount->fetchColumn() ?: 0;

// Fetch farmer's active crops for the mini-table
$activeCropsStmt = $pdo->prepare("SELECT fc.*, c.crop_name, c.season FROM farmer_crops fc JOIN crops c ON fc.crop_id = c.crop_id WHERE fc.farmer_id = ? ORDER BY fc.sowing_date DESC LIMIT 5");
$activeCropsStmt->execute([$farmerId]);
$activeCrops = $activeCropsStmt->fetchAll();

// Fetch recent buyer enquiries
$recentEnquiriesStmt = $pdo->prepare("SELECT e.*, p.product_name, b.business_name FROM enquiries e JOIN products p ON e.product_id = p.product_id JOIN buyers b ON e.buyer_id = b.buyer_id WHERE e.farmer_id = ? ORDER BY e.created_at DESC LIMIT 4");
$recentEnquiriesStmt->execute([$farmerId]);
$recentEnquiries = $recentEnquiriesStmt->fetchAll();
?>

<!-- Welcome Banner -->
<div class="card bg-success text-white p-4 rounded-4 shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1b5e20 0%, #388e3c 100%);">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
    <div>
      <h2 class="fw-bold mb-1"><span class="me-2">🌾</span><?= __('welcome') ?>, <?= e($_SESSION['name']) ?>!</h2>
      <p class="mb-0 text-white-50">
        <i class="bi bi-geo-alt me-1"></i><?= e($_SESSION['village'] ?? 'Village') ?>, <?= e($_SESSION['district'] ?? 'District') ?> |
        <i class="bi bi-calendar3 me-1"></i><?= date('l, d F Y') ?>
      </p>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= BASE_URL ?>/farmer/recommendation.php" class="btn btn-warning text-dark fw-bold px-3 py-2 shadow-sm">
        <i class="bi bi-magic me-1"></i>Get Crop Recommendation
      </a>
    </div>
  </div>
</div>

<!-- Primary Metric Cards -->
<div class="row g-4 mb-4">
  <div class="col-6 col-lg-3">
    <div class="metric-card metric-green">
      <div>
        <div class="metric-number"><?= $totalCrops ?></div>
        <div class="metric-title"><?= __('nav_my_crops') ?></div>
      </div>
      <i class="bi bi-flower1 metric-icon"></i>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="metric-card metric-gold">
      <div>
        <div class="metric-number"><?= $totalProducts ?></div>
        <div class="metric-title">Listed Products</div>
      </div>
      <i class="bi bi-box-seam metric-icon"></i>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="metric-card metric-blue">
      <div>
        <div class="metric-number"><?= $pendingEnquiries ?></div>
        <div class="metric-title">Buyer Enquiries</div>
      </div>
      <i class="bi bi-chat-dots metric-icon"></i>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="metric-card metric-earth">
      <div>
        <div class="metric-number"><?= $pendingComplaints ?></div>
        <div class="metric-title">Open Complaints</div>
      </div>
      <i class="bi bi-exclamation-triangle metric-icon"></i>
    </div>
  </div>
</div>

<!-- Farmer Quick Actions Grid (Farmer Friendly Large Buttons) -->
<h4 class="fw-bold text-success mb-3"><i class="bi bi-grid-fill me-2"></i><?= __('quick_actions') ?></h4>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <a href="<?= BASE_URL ?>/farmer/my_crops.php?action=add" class="farmer-action-btn shadow-sm">
      <i class="bi bi-plus-circle-dotted"></i>
      <span>Add Cultivation</span>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="<?= BASE_URL ?>/farmer/add_product.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-cart-plus"></i>
      <span>Sell Harvest Produce</span>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="<?= BASE_URL ?>/farmer/recommendation.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-cpu"></i>
      <span>Crop Advisor Engine</span>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="<?= BASE_URL ?>/farmer/weather.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-cloud-sun"></i>
      <span>Weather Advisory</span>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="<?= BASE_URL ?>/admin/market_prices.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-currency-rupee"></i>
      <span>Mandi APMC Rates</span>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="<?= BASE_URL ?>/farmer/diseases.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-bug"></i>
      <span>Crop Disease Doctor</span>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="<?= BASE_URL ?>/farmer/schemes.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-bank"></i>
      <span>Govt Subsidies</span>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="<?= BASE_URL ?>/farmer/ask_expert.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-headset"></i>
      <span>Ask Agronomist</span>
    </a>
  </div>
</div>

<!-- Active Crops & Enquiries Section -->
<div class="row g-4 mb-4">
  <div class="col-lg-7">
    <div class="card card-agro h-100">
      <div class="card-agro-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-flower1 me-2"></i>My Active Field Crops</span>
        <a href="<?= BASE_URL ?>/farmer/my_crops.php" class="btn btn-sm btn-outline-success">Manage All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($activeCrops)): ?>
          <div class="p-4 text-center text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No crops registered yet. Click <a href="<?= BASE_URL ?>/farmer/my_crops.php?action=add">here to add your current crop</a>.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Crop</th>
                  <th>Season</th>
                  <th>Area</th>
                  <th>Sowing Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($activeCrops as $ac): ?>
                  <tr>
                    <td class="fw-bold text-success"><?= e($ac['crop_name']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= e($ac['season']) ?></span></td>
                    <td><?= e($ac['area']) ?> Acres</td>
                    <td><?= e($ac['sowing_date']) ?></td>
                    <td><span class="badge bg-success-subtle text-success"><?= e($ac['status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card card-agro h-100">
      <div class="card-agro-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-chat-left-dots me-2"></i>Recent Buyer Enquiries</span>
        <a href="<?= BASE_URL ?>/farmer/enquiries.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-3">
        <?php if (empty($recentEnquiries)): ?>
          <div class="text-center py-4 text-muted">
            <i class="bi bi-chat-square text-muted fs-2 d-block mb-2"></i>
            No recent buyer enquiries yet.
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($recentEnquiries as $enq): ?>
              <div class="list-group-item px-0 py-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <strong class="text-dark"><?= e($enq['business_name']) ?></strong>
                  <span class="badge <?= $enq['status'] === 'Accepted' ? 'bg-success' : ($enq['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                    <?= e($enq['status']) ?>
                  </span>
                </div>
                <div class="small text-muted mb-1">For: <span class="fw-semibold text-success"><?= e($enq['product_name']) ?></span> (<?= e($enq['quantity_required']) ?> Qtl)</div>
                <div class="small text-secondary text-truncate"><?= e($enq['message']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
