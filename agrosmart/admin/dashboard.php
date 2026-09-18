<?php
/**
 * AgroSmart - Administrator Control Panel & Analytics Dashboard
 */
$pageTitle = 'Admin Dashboard – AgroSmart';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();

// Summary Metrics
$farmerCount = $pdo->query("SELECT COUNT(*) FROM farmers")->fetchColumn() ?: 0;
$buyerCount = $pdo->query("SELECT COUNT(*) FROM buyers")->fetchColumn() ?: 0;
$expertCount = $pdo->query("SELECT COUNT(*) FROM experts")->fetchColumn() ?: 0;
$cropCount = $pdo->query("SELECT COUNT(*) FROM crops")->fetchColumn() ?: 0;

$pendingProductsCount = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'pending'")->fetchColumn() ?: 0;
$approvedProductsCount = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'approved'")->fetchColumn() ?: 0;
$soldProductsCount = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'sold'")->fetchColumn() ?: 0;

$pendingComplaintsCount = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Pending'")->fetchColumn() ?: 0;
$resolvedComplaintsCount = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Resolved'")->fetchColumn() ?: 0;

// Recent pending products for review
$pendingProducts = $pdo->query("SELECT p.*, u.name as farmer_name, f.district FROM products p JOIN farmers f ON p.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE p.status = 'pending' ORDER BY p.created_at DESC LIMIT 5")->fetchAll();

// Recent complaints
$recentComplaints = $pdo->query("SELECT c.*, u.name as farmer_name, f.district FROM complaints c JOIN farmers f ON c.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE c.status = 'Pending' ORDER BY c.created_at DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Admin Header -->
<div class="card bg-dark text-white p-4 rounded-4 shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%);">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-speedometer2 me-2 text-warning"></i>AgroSmart Master Administration Console</h2>
      <p class="mb-0 text-white-50">
        System Overview, Moderation Desks, User Role Governance & Agronomic Database Control
      </p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
      <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-warning text-dark fw-bold">
        <i class="bi bi-pie-chart-fill me-1"></i>Analytics Reports
      </a>
    </div>
  </div>
</div>

<!-- Primary Metric Counters -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="metric-card metric-green">
      <div>
        <div class="metric-number"><?= $farmerCount ?></div>
        <div class="metric-title">Farmers</div>
      </div>
      <i class="bi bi-person metric-icon"></i>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="metric-card metric-blue">
      <div>
        <div class="metric-number"><?= $buyerCount ?></div>
        <div class="metric-title">Buyers</div>
      </div>
      <i class="bi bi-shop metric-icon"></i>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="metric-card metric-gold">
      <div>
        <div class="metric-number"><?= $pendingProductsCount ?></div>
        <div class="metric-title">Lots To Approve</div>
      </div>
      <i class="bi bi-hourglass-split metric-icon"></i>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="metric-card metric-earth">
      <div>
        <div class="metric-number"><?= $pendingComplaintsCount ?></div>
        <div class="metric-title">Open Complaints</div>
      </div>
      <i class="bi bi-exclamation-triangle metric-icon"></i>
    </div>
  </div>
</div>

<!-- Analytics Charts (Chart.js) -->
<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="card card-agro shadow-sm h-100">
      <div class="card-agro-header">
        <i class="bi bi-people-fill me-2"></i>Platform Users by Role
      </div>
      <div class="card-body d-flex justify-content-center align-items-center" style="position: relative; height: 260px;">
        <canvas id="userRolesChart"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-agro shadow-sm h-100">
      <div class="card-agro-header">
        <i class="bi bi-box-seam me-2"></i>Marketplace Inventory Status
      </div>
      <div class="card-body d-flex justify-content-center align-items-center" style="position: relative; height: 260px;">
        <canvas id="productStatusChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Quick Admin Management Navigation -->
<h5 class="fw-bold text-dark mb-3"><i class="bi bi-tools me-2 text-success"></i>Management Desks</h5>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-2">
    <a href="<?= BASE_URL ?>/admin/products.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-boxes text-warning"></i>
      <span class="small">Product Approvals</span>
    </a>
  </div>
  <div class="col-6 col-md-2">
    <a href="<?= BASE_URL ?>/admin/farmers.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-people text-success"></i>
      <span class="small">Farmers Directory</span>
    </a>
  </div>
  <div class="col-6 col-md-2">
    <a href="<?= BASE_URL ?>/admin/buyers.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-shop-window text-primary"></i>
      <span class="small">Buyers Directory</span>
    </a>
  </div>
  <div class="col-6 col-md-2">
    <a href="<?= BASE_URL ?>/admin/crops.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-flower1 text-success"></i>
      <span class="small">Crop Catalog</span>
    </a>
  </div>
  <div class="col-6 col-md-2">
    <a href="<?= BASE_URL ?>/admin/market_prices.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-currency-rupee text-warning"></i>
      <span class="small">APMC Mandi Rates</span>
    </a>
  </div>
  <div class="col-6 col-md-2">
    <a href="<?= BASE_URL ?>/admin/complaints.php" class="farmer-action-btn shadow-sm">
      <i class="bi bi-shield-exclamation text-danger"></i>
      <span class="small">Complaints Cell</span>
    </a>
  </div>
</div>

<!-- Pending Approvals & Complaints Tables -->
<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="card card-agro h-100 shadow-sm">
      <div class="card-agro-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-hourglass-top me-2 text-warning"></i>Products Awaiting Approval (<?= count($pendingProducts) ?>)</span>
        <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-sm btn-outline-success">Review All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($pendingProducts)): ?>
          <div class="p-4 text-center text-muted small">No lots currently waiting for approval.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead>
                <tr>
                  <th>Produce</th>
                  <th>Farmer</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pendingProducts as $pp): ?>
                  <tr>
                    <td><strong><?= e($pp['product_name']) ?></strong></td>
                    <td><?= e($pp['farmer_name']) ?> (<?= e($pp['district']) ?>)</td>
                    <td><?= e($pp['quantity']) ?> <?= e($pp['unit']) ?></td>
                    <td>₹ <?= number_format($pp['expected_price'], 2) ?></td>
                    <td class="text-end">
                      <a href="<?= BASE_URL ?>/admin/products.php?action=approve&id=<?= $pp['product_id'] ?>" class="btn btn-xs btn-success py-0 px-2">Approve</a>
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

  <div class="col-lg-6">
    <div class="card card-agro h-100 shadow-sm">
      <div class="card-agro-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-exclamation-circle me-2 text-danger"></i>Open Farmer Grievances (<?= count($recentComplaints) ?>)</span>
        <a href="<?= BASE_URL ?>/admin/complaints.php" class="btn btn-sm btn-outline-danger">Resolve</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentComplaints)): ?>
          <div class="p-4 text-center text-muted small">No open grievances pending.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Subject</th>
                  <th>Farmer</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentComplaints as $rc): ?>
                  <tr>
                    <td><span class="badge bg-light text-dark border"><?= e($rc['category']) ?></span></td>
                    <td><strong><?= e($rc['subject']) ?></strong></td>
                    <td><?= e($rc['farmer_name']) ?></td>
                    <td class="text-muted"><?= date('d M', strtotime($rc['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1. User Roles Doughnut Chart
  const ctxUsers = document.getElementById('userRolesChart');
  if (ctxUsers) {
    new Chart(ctxUsers, {
      type: 'doughnut',
      data: {
        labels: ['Farmers', 'Buyers', 'Experts'],
        datasets: [{
          data: [<?= $farmerCount ?>, <?= $buyerCount ?>, <?= $expertCount ?>],
          backgroundColor: ['#2e7d32', '#0288d1', '#795548']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  }

  // 2. Product Status Bar Chart
  const ctxProducts = document.getElementById('productStatusChart');
  if (ctxProducts) {
    new Chart(ctxProducts, {
      type: 'bar',
      data: {
        labels: ['Approved (Live)', 'Pending', 'Sold Out'],
        datasets: [{
          label: 'Produce Lots',
          data: [<?= $approvedProductsCount ?>, <?= $pendingProductsCount ?>, <?= $soldProductsCount ?>],
          backgroundColor: ['#4caf50', '#ffb300', '#9e9e9e']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1 } }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
