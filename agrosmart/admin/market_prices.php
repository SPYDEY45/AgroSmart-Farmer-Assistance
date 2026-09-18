<?php
/**
 * AgroSmart - APMC Market Mandi Prices Module
 * Open to farmers, buyers, and guests for price transparency; admin has CRUD access.
 */
$pageTitle = 'APMC Mandi Market Prices – AgroSmart';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);
$error = '';

// Admin: Handle Delete
if ($isAdmin && $action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE FROM market_prices WHERE price_id = ?");
    $del->execute([$id]);
    setFlash('success', 'Market price entry removed.');
    header("Location: " . BASE_URL . "/admin/market_prices.php");
    exit();
}

// Admin: Handle Add / Edit POST
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropName = trim($_POST['crop_name'] ?? '');
    $marketName = trim($_POST['market_name'] ?? '');
    $district = trim($_POST['district'] ?? 'Pune');
    $state = trim($_POST['state'] ?? 'Maharashtra');
    $minPrice = floatval($_POST['min_price'] ?? 0);
    $maxPrice = floatval($_POST['max_price'] ?? 0);
    $modalPrice = floatval($_POST['modal_price'] ?? 0);
    $priceDate = $_POST['price_date'] ?? date('Y-m-d');
    $editId = intval($_POST['edit_id'] ?? 0);

    if (empty($cropName) || empty($marketName) || $modalPrice <= 0) {
        $error = 'Please fill out crop, market yard, and valid price figures.';
    } else {
        if ($editId > 0) {
            $stmt = $pdo->prepare("UPDATE market_prices SET crop_name = ?, market_name = ?, district = ?, state = ?, min_price = ?, max_price = ?, modal_price = ?, price_date = ? WHERE price_id = ?");
            $stmt->execute([$cropName, $marketName, $district, $state, $minPrice, $maxPrice, $modalPrice, $priceDate, $editId]);
            setFlash('success', 'Market price record updated.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO market_prices (crop_name, market_name, district, state, min_price, max_price, modal_price, price_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cropName, $marketName, $district, $state, $minPrice, $maxPrice, $modalPrice, $priceDate]);
            setFlash('success', 'New mandi price record published.');
        }
        header("Location: " . BASE_URL . "/admin/market_prices.php");
        exit();
    }
}

// Search and filters
$search = trim($_GET['search'] ?? '');
$marketFilter = trim($_GET['market'] ?? '');
$dateFilter = trim($_GET['date'] ?? '');

$query = "SELECT * FROM market_prices WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND crop_name LIKE ?";
    $params[] = "%{$search}%";
}
if (!empty($marketFilter)) {
    $query .= " AND market_name = ?";
    $params[] = $marketFilter;
}
if (!empty($dateFilter)) {
    $query .= " AND price_date = ?";
    $params[] = $dateFilter;
}

$query .= " ORDER BY price_date DESC, crop_name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$prices = $stmt->fetchAll();

// Unique markets for filter
$markets = $pdo->query("SELECT DISTINCT market_name FROM market_prices ORDER BY market_name ASC")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-graph-up-arrow me-2"></i>Daily APMC Mandi Market Prices</h2>
    <p class="text-muted mb-0">Benchmark wholesale minimum, maximum, and modal auction rates across Maharashtra market yards</p>
  </div>
  <?php if ($isAdmin): ?>
    <div class="mt-3 mt-md-0">
      <button type="button" class="btn btn-agro-primary" data-bs-toggle="modal" data-bs-target="#mandiPriceModal">
        <i class="bi bi-plus-circle me-1"></i>Publish Mandi Rate
      </button>
    </div>
  <?php endif; ?>
</div>

<!-- Mandatory Educational Notice -->
<div class="agro-notice-box">
  <div class="d-flex">
    <i class="bi bi-info-circle-fill text-warning fs-4 me-3"></i>
    <div>
      <strong><?= __('disclaimer_title') ?>:</strong>
      <p class="mb-0 mt-1"><?= __('market_notice') ?></p>
    </div>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<!-- Search & Filters -->
<div class="card card-agro mb-4 p-3 shadow-sm">
  <form method="GET" action="<?= BASE_URL ?>/admin/market_prices.php" class="row g-2">
    <div class="col-md-5">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Search commodity (e.g. Soybean, Cotton, Wheat)..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="market" class="form-select">
        <option value="">All Market Yards (APMC)</option>
        <?php foreach ($markets as $m): ?>
          <option value="<?= e($m) ?>" <?= $marketFilter === $m ? 'selected' : '' ?>><?= e($m) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <input type="date" name="date" class="form-control" value="<?= e($dateFilter) ?>">
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-agro-primary"><i class="bi bi-funnel me-1"></i>Filter Rates</button>
    </div>
  </form>
</div>

<!-- Mandi Rates Table -->
<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Commodity</th>
            <th>APMC Market Yard</th>
            <th>District & State</th>
            <th>Min Price (₹/Qtl)</th>
            <th>Max Price (₹/Qtl)</th>
            <th>Modal / Avg Price (₹/Qtl)</th>
            <th>Auction Date</th>
            <?php if ($isAdmin): ?>
              <th class="text-end">Admin</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($prices)): ?>
            <tr><td colspan="<?= $isAdmin ? '8' : '7' ?>" class="text-center py-4 text-muted">No market price records found.</td></tr>
          <?php else: ?>
            <?php foreach ($prices as $pr): ?>
              <tr>
                <td><strong class="text-dark fs-6"><?= e($pr['crop_name']) ?></strong></td>
                <td><span class="badge bg-light text-dark border"><?= e($pr['market_name']) ?></span></td>
                <td><?= e($pr['district']) ?>, <?= e($pr['state']) ?></td>
                <td class="text-secondary">₹ <?= number_format($pr['min_price'], 2) ?></td>
                <td class="text-secondary">₹ <?= number_format($pr['max_price'], 2) ?></td>
                <td>
                  <strong class="text-success fs-6">₹ <?= number_format($pr['modal_price'], 2) ?></strong>
                </td>
                <td class="small text-muted"><?= date('d M Y', strtotime($pr['price_date'])) ?></td>
                <?php if ($isAdmin): ?>
                  <td class="text-end">
                    <a href="<?= BASE_URL ?>/admin/market_prices.php?action=delete&id=<?= $pr['price_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="mandi rate">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Admin Add Modal -->
<?php if ($isAdmin): ?>
  <div class="modal fade" id="mandiPriceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="<?= BASE_URL ?>/admin/market_prices.php" method="POST">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title fw-bold"><i class="bi bi-currency-rupee me-2"></i>Publish APMC Mandi Auction Rate</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label fw-semibold">Commodity / Crop *</label>
              <input type="text" name="crop_name" class="form-control" required placeholder="e.g. Soybean, Cotton, Wheat, Onion">
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label fw-semibold">Market Yard (APMC) *</label>
                <input type="text" name="market_name" class="form-control" required placeholder="e.g. Pune APMC, Latur APMC">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">District *</label>
                <input type="text" name="district" class="form-control" required value="Pune">
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-4">
                <label class="form-label fw-semibold">Min Price (₹)</label>
                <input type="number" step="1" name="min_price" class="form-control" required value="4000">
              </div>
              <div class="col-4">
                <label class="form-label fw-semibold">Max Price (₹)</label>
                <input type="number" step="1" name="max_price" class="form-control" required value="4900">
              </div>
              <div class="col-4">
                <label class="form-label fw-semibold">Modal (₹) *</label>
                <input type="number" step="1" name="modal_price" class="form-control" required value="4650">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Auction Date *</label>
              <input type="date" name="price_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-agro-primary">Publish Rate</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
