<?php
/**
 * AgroSmart - Farmer Produce Marketplace
 * Publicly browsable; verified buyers can submit purchase enquiries.
 */
$pageTitle = 'Farmer Marketplace – AgroSmart';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();

// Handle Enquiry Submission from Buyer
$enquirySuccess = '';
$enquiryError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_enquiry'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
        setFlash('warning', 'Please login as a registered Buyer to send purchase enquiries.');
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }

    $buyerId = $_SESSION['buyer_id'] ?? 0;
    $productId = intval($_POST['product_id'] ?? 0);
    $farmerId = intval($_POST['farmer_id'] ?? 0);
    $quantityRequired = floatval($_POST['quantity_required'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $enquiryError = 'Security token expired. Please retry.';
    } elseif ($productId <= 0 || $farmerId <= 0 || $quantityRequired <= 0) {
        $enquiryError = 'Please specify a valid quantity for your enquiry.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO enquiries (product_id, buyer_id, farmer_id, quantity_required, message, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$productId, $buyerId, $farmerId, $quantityRequired, $message]);
        setFlash('success', 'Your enquiry has been dispatched directly to the farmer! Check "My Enquiries" for responses.');
        header("Location: " . BASE_URL . "/buyer/my_enquiries.php");
        exit();
    }
}

// Search and filter parameters
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$location = trim($_GET['location'] ?? '');
$maxPrice = floatval($_GET['max_price'] ?? 0);

$query = "SELECT p.*, f.village, f.district, u.name as farmer_name, u.mobile as farmer_mobile FROM products p JOIN farmers f ON p.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE p.status = 'approved'";
$params = [];

if (!empty($search)) {
    $query .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if (!empty($category)) {
    $query .= " AND p.category = ?";
    $params[] = $category;
}
if (!empty($location)) {
    $query .= " AND (p.location LIKE ? OR f.district LIKE ?)";
    $params[] = "%{$location}%";
    $params[] = "%{$location}%";
}
if ($maxPrice > 0) {
    $query .= " AND p.expected_price <= ?";
    $params[] = $maxPrice;
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-shop me-2"></i>Direct Farmer Produce Marketplace</h2>
    <p class="text-muted mb-0">Direct procurement from verified farmers with transparent pricing and no middleman commissions</p>
  </div>
  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'farmer'): ?>
    <div class="mt-3 mt-md-0">
      <a href="<?= BASE_URL ?>/farmer/add_product.php" class="btn btn-agro-primary">
        <i class="bi bi-cart-plus me-1"></i>List Your Harvest
      </a>
    </div>
  <?php endif; ?>
</div>

<?php if ($enquiryError): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($enquiryError) ?></div>
<?php endif; ?>

<!-- Search & Filters Bar -->
<div class="card card-agro mb-4 p-3 shadow-sm">
  <form method="GET" action="<?= BASE_URL ?>/buyer/marketplace.php" class="row g-2">
    <div class="col-md-4">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Search produce (e.g. Soybean, Wheat)..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="category" class="form-select">
        <option value="">All Categories</option>
        <option value="Grains" <?= $category === 'Grains' ? 'selected' : '' ?>>Grains (धान्य)</option>
        <option value="Pulses" <?= $category === 'Pulses' ? 'selected' : '' ?>>Pulses (डाळी/कडधान्य)</option>
        <option value="Oilseeds" <?= $category === 'Oilseeds' ? 'selected' : '' ?>>Oilseeds (गळीत धान्य)</option>
        <option value="Vegetables" <?= $category === 'Vegetables' ? 'selected' : '' ?>>Vegetables (भाजीपाला)</option>
        <option value="Fruits" <?= $category === 'Fruits' ? 'selected' : '' ?>>Fruits (फळे)</option>
        <option value="Cotton/Fiber" <?= $category === 'Cotton/Fiber' ? 'selected' : '' ?>>Cotton / Fiber</option>
        <option value="Spices" <?= $category === 'Spices' ? 'selected' : '' ?>>Spices (मसाले)</option>
      </select>
    </div>
    <div class="col-md-3">
      <input type="text" name="location" class="form-control" placeholder="Filter District (e.g. Pune, Latur)" value="<?= e($location) ?>">
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-agro-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
    </div>
  </form>
</div>

<!-- Product Cards Grid -->
<?php if (empty($products)): ?>
  <div class="card card-agro p-5 text-center text-muted">
    <i class="bi bi-basket3 fs-1 mb-2 text-secondary"></i>
    <h5>No agricultural lots found matching your filter criteria.</h5>
    <a href="<?= BASE_URL ?>/buyer/marketplace.php" class="btn btn-outline-success mt-2">Reset Filters</a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($products as $p): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card card-agro h-100 shadow-sm d-flex flex-column">
          <!-- Produce Card Header -->
          <div class="card-agro-header d-flex justify-content-between align-items-center">
            <span class="badge bg-light text-dark border"><?= e($p['category']) ?></span>
            <span class="badge bg-success-subtle text-success"><i class="bi bi-patch-check-fill me-1"></i>Approved Lot</span>
          </div>

          <div class="card-body flex-grow-1">
            <h5 class="fw-bold text-dark mb-1"><?= e($p['product_name']) ?></h5>
            <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1 text-danger"></i><?= e($p['location']) ?></p>

            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded mb-3">
              <div>
                <small class="text-muted d-block">Available Lot</small>
                <strong class="fs-6"><?= e($p['quantity']) ?> <?= e($p['unit']) ?></strong>
              </div>
              <div class="text-end">
                <small class="text-muted d-block">Expected Price</small>
                <strong class="text-success fs-5">₹ <?= number_format($p['expected_price'], 2) ?></strong> <small class="text-muted">/ <?= e($p['unit']) ?></small>
              </div>
            </div>

            <div class="small mb-3">
              <span class="text-muted">Farmer:</span> <strong><?= e($p['farmer_name']) ?></strong>
              <div class="text-muted mt-1"><?= e(mb_strimwidth($p['description'] ?: 'High quality direct harvested lot ready for dispatch.', 0, 110, '...')) ?></div>
            </div>
          </div>

          <!-- Card Footer with Enquiry Button -->
          <div class="card-footer bg-white border-top p-3">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'buyer'): ?>
              <button type="button" class="btn btn-agro-primary w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#enquiryModal<?= $p['product_id'] ?>">
                <i class="bi bi-send-fill me-1"></i>Send Purchase Enquiry
              </button>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'farmer'): ?>
              <button type="button" class="btn btn-outline-secondary w-100" disabled>
                <i class="bi bi-person-circle me-1"></i>Farmer View (Trading Disabled)
              </button>
            <?php else: ?>
              <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary w-100 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i>Login as Buyer to Inquire
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Enquiry Modal for each product -->
      <div class="modal fade" id="enquiryModal<?= $p['product_id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form action="<?= BASE_URL ?>/buyer/marketplace.php" method="POST">
              <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
              <input type="hidden" name="submit_enquiry" value="1">
              <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
              <input type="hidden" name="farmer_id" value="<?= $p['farmer_id'] ?>">

              <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-cart-check me-2"></i>Direct Purchase Enquiry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body p-4">
                <div class="p-3 bg-light rounded mb-3">
                  <h6 class="fw-bold text-success mb-1"><?= e($p['product_name']) ?></h6>
                  <div class="small text-muted">Offered by Farmer: <strong><?= e($p['farmer_name']) ?></strong> (<?= e($p['location']) ?>)</div>
                  <div class="small text-muted mt-1">Listed Price: <strong>₹ <?= number_format($p['expected_price'], 2) ?> / <?= e($p['unit']) ?></strong> | Total Available: <?= e($p['quantity']) ?> <?= e($p['unit']) ?></div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Quantity You Require (<?= e($p['unit']) ?>) *</label>
                  <input type="number" step="0.1" name="quantity_required" class="form-control" required min="0.1" max="<?= e($p['quantity']) ?>" value="<?= e($p['quantity']) ?>">
                  <small class="text-muted">Maximum available lot size: <?= e($p['quantity']) ?> <?= e($p['unit']) ?></small>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Offer / Logistics Message</label>
                  <textarea name="message" class="form-control" rows="3" placeholder="Specify your offered rate, pickup truck schedule, quality verification date, or payment terms..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-agro-primary"><i class="bi bi-send me-1"></i>Dispatch Enquiry</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
