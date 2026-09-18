<?php
/**
 * AgroSmart - Buyer Dashboard
 */
$pageTitle = 'Buyer Dashboard – AgroSmart';
require_once __DIR__ . '/../includes/buyer_auth.php';
require_once __DIR__ . '/../includes/header.php';

$buyerId = $_SESSION['buyer_id'] ?? 0;
$pdo = getDBConnection();

// Fetch metrics
$activeProductsCount = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'approved'")->fetchColumn() ?: 0;

$enquiriesStmt = $pdo->prepare("SELECT COUNT(*) FROM enquiries WHERE buyer_id = ?");
$enquiriesStmt->execute([$buyerId]);
$totalEnquiries = $enquiriesStmt->fetchColumn() ?: 0;

$acceptedEnquiriesStmt = $pdo->prepare("SELECT COUNT(*) FROM enquiries WHERE buyer_id = ? AND status = 'Accepted'");
$acceptedEnquiriesStmt->execute([$buyerId]);
$acceptedEnquiries = $acceptedEnquiriesStmt->fetchColumn() ?: 0;

// Recent available approved listings
$recentProductsStmt = $pdo->query("SELECT p.*, f.village, f.district, u.name as farmer_name FROM products p JOIN farmers f ON p.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE p.status = 'approved' ORDER BY p.created_at DESC LIMIT 4");
$recentProducts = $recentProductsStmt->fetchAll();

// Recent enquiries by this buyer
$myEnqStmt = $pdo->prepare("SELECT e.*, p.product_name, p.unit, p.expected_price, u.name as farmer_name, u.mobile as farmer_mobile FROM enquiries e JOIN products p ON e.product_id = p.product_id JOIN farmers f ON e.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE e.buyer_id = ? ORDER BY e.created_at DESC LIMIT 4");
$myEnqStmt->execute([$buyerId]);
$myRecentEnquiries = $myEnqStmt->fetchAll();
?>

<!-- Buyer Welcome Banner -->
<div class="card bg-primary text-white p-4 rounded-4 shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-shop me-2"></i>Welcome, <?= e($_SESSION['name']) ?>!</h2>
      <p class="mb-0 text-white-50">
        <i class="bi bi-building me-1"></i><?= e($_SESSION['business_name'] ?? 'Agricultural Trading Firm') ?> | Direct Farm Sourcing Desk
      </p>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= BASE_URL ?>/buyer/marketplace.php" class="btn btn-warning text-dark fw-bold px-4 py-2 shadow-sm">
        <i class="bi bi-cart3 me-1"></i>Browse Direct Produce
      </a>
    </div>
  </div>
</div>

<!-- Metrics Row -->
<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="metric-card metric-green">
      <div>
        <div class="metric-number"><?= $activeProductsCount ?></div>
        <div class="metric-title">Available Lots</div>
      </div>
      <i class="bi bi-box-seam metric-icon"></i>
    </div>
  </div>
  <div class="col-md-4">
    <div class="metric-card metric-blue">
      <div>
        <div class="metric-number"><?= $totalEnquiries ?></div>
        <div class="metric-title">Sent Inquiries</div>
      </div>
      <i class="bi bi-send-check metric-icon"></i>
    </div>
  </div>
  <div class="col-md-4">
    <div class="metric-card metric-gold">
      <div>
        <div class="metric-number"><?= $acceptedEnquiries ?></div>
        <div class="metric-title">Confirmed Deals</div>
      </div>
      <i class="bi bi-patch-check metric-icon"></i>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<h4 class="fw-bold text-dark mb-3"><i class="bi bi-grid-fill me-2 text-primary"></i>Buyer Operations</h4>
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <a href="<?= BASE_URL ?>/buyer/marketplace.php" class="farmer-action-btn shadow-sm text-primary border-primary">
      <i class="bi bi-shop-window text-primary"></i>
      <span>Browse Marketplace</span>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= BASE_URL ?>/buyer/my_enquiries.php" class="farmer-action-btn shadow-sm text-primary border-primary">
      <i class="bi bi-chat-left-text text-primary"></i>
      <span>My Purchase Enquiries</span>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= BASE_URL ?>/admin/market_prices.php" class="farmer-action-btn shadow-sm text-primary border-primary">
      <i class="bi bi-graph-up-arrow text-primary"></i>
      <span>Daily Mandi Benchmark Rates</span>
    </a>
  </div>
</div>

<!-- Content Grid -->
<div class="row g-4 mb-4">
  <div class="col-lg-7">
    <div class="card card-agro h-100">
      <div class="card-agro-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-boxes me-2"></i>Freshly Listed Farm Produce</span>
        <a href="<?= BASE_URL ?>/buyer/marketplace.php" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentProducts)): ?>
          <div class="p-4 text-center text-muted">No active produce listed at the moment.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Produce</th>
                  <th>Quantity</th>
                  <th>Asking Price</th>
                  <th>Farmer Location</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentProducts as $rp): ?>
                  <tr>
                    <td>
                      <strong class="text-dark"><?= e($rp['product_name']) ?></strong>
                      <div class="small text-muted"><?= e($rp['category']) ?></div>
                    </td>
                    <td><?= e($rp['quantity']) ?> <?= e($rp['unit']) ?></td>
                    <td class="text-success fw-bold">₹ <?= number_format($rp['expected_price'], 2) ?></td>
                    <td class="small text-muted"><?= e($rp['village']) ?>, <?= e($rp['district']) ?></td>
                    <td class="text-end">
                      <a href="<?= BASE_URL ?>/buyer/marketplace.php?highlight=<?= $rp['product_id'] ?>" class="btn btn-sm btn-outline-primary">Inquire</a>
                    </td>
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
        <span><i class="bi bi-clock-history me-2"></i>Recent Enquiries Status</span>
        <a href="<?= BASE_URL ?>/buyer/my_enquiries.php" class="btn btn-sm btn-outline-primary">History</a>
      </div>
      <div class="card-body p-3">
        <?php if (empty($myRecentEnquiries)): ?>
          <div class="text-center py-4 text-muted">No enquiries sent yet.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($myRecentEnquiries as $me): ?>
              <div class="list-group-item px-0 py-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <strong class="text-dark"><?= e($me['product_name']) ?></strong>
                  <span class="badge <?= $me['status'] === 'Accepted' ? 'bg-success' : ($me['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                    <?= e($me['status']) ?>
                  </span>
                </div>
                <div class="small text-muted">Farmer: <?= e($me['farmer_name']) ?></div>
                <?php if ($me['status'] === 'Accepted'): ?>
                  <div class="small text-success fw-semibold"><i class="bi bi-telephone-fill me-1"></i>Call Farmer: <?= e($me['farmer_mobile']) ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
