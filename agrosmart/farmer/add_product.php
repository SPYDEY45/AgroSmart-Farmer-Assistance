<?php
/**
 * AgroSmart - Add Produce Listing for Marketplace
 */
$pageTitle = 'Sell Produce – AgroSmart';
require_once __DIR__ . '/../includes/farmer_auth.php';

$pdo = getDBConnection();
$farmerId = $_SESSION['farmer_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['product_name'] ?? '');
    $category = $_POST['category'] ?? 'Grains';
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unit = $_POST['unit'] ?? 'Quintal';
    $expectedPrice = floatval($_POST['expected_price'] ?? 0);
    $location = trim($_POST['location'] ?? ($_SESSION['village'] . ', ' . $_SESSION['district']));
    $description = trim($_POST['description'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = 'Security session expired. Please refresh and retry.';
    } elseif (empty($productName) || $quantity <= 0 || $expectedPrice <= 0 || empty($location)) {
        $error = 'Please fill out all required product fields with valid quantities and prices.';
    } else {
        $photoName = 'default_crop.jpg';
        // Handle file upload if provided
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploaded = uploadProductPhoto($_FILES['photo'], $uploadError);
            if ($uploaded) {
                $photoName = $uploaded;
            } else {
                $error = $uploadError;
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO products (farmer_id, product_name, category, quantity, unit, expected_price, location, description, photo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$farmerId, $productName, $category, $quantity, $unit, $expectedPrice, $location, $description, $photoName]);
            setFlash('success', 'Produce listed successfully! It will appear in the marketplace once approved by an administrator.');
            header("Location: " . BASE_URL . "/farmer/my_products.php");
            exit();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card card-agro shadow">
      <div class="card-agro-header">
        <h4 class="mb-0 text-success"><i class="bi bi-cart-plus me-2"></i>List Agricultural Produce for Sale</h4>
      </div>
      <div class="card-body p-4">
        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/farmer/add_product.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Produce / Crop Name *</label>
              <input type="text" name="product_name" class="form-control" required placeholder="e.g. Organic Soybean (JS-335) or Desi Chana" value="<?= e($_POST['product_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Category *</label>
              <select name="category" class="form-select" required>
                <option value="Grains">Grains (धान्य)</option>
                <option value="Pulses">Pulses (डाळी/कडधान्य)</option>
                <option value="Oilseeds">Oilseeds (गळीत धान्य)</option>
                <option value="Vegetables">Vegetables (भाजीपाला)</option>
                <option value="Fruits">Fruits (फळे)</option>
                <option value="Cotton/Fiber">Cotton / Fiber (कापूस)</option>
                <option value="Spices">Spices (मसाले)</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Available Quantity *</label>
              <input type="number" step="0.1" name="quantity" id="calc_quantity" class="form-control" required min="0.1" placeholder="e.g. 50" value="<?= e($_POST['quantity'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Unit *</label>
              <select name="unit" class="form-select" required>
                <option value="Quintal">Quintal (क्विंटल - 100 kg)</option>
                <option value="Kg">Kilogram (kg)</option>
                <option value="Ton">Ton (टन)</option>
                <option value="Crate">Crate (क्रेट)</option>
                <option value="Bag">Bag (गोणी)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Expected Price (₹ per unit) *</label>
              <input type="number" step="1" name="expected_price" id="calc_price" class="form-control" required min="1" placeholder="e.g. 4800" value="<?= e($_POST['expected_price'] ?? '') ?>">
            </div>

            <!-- Client-side Total Calculator Badge -->
            <div class="col-12">
              <div class="p-2 bg-light border rounded d-flex justify-content-between align-items-center">
                <span class="small text-muted">Estimated Total Valuation:</span>
                <span id="calc_total_display" class="fw-bold text-success fs-6">₹ 0.00</span>
              </div>
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold">Farm Location / APMC Pickup Point *</label>
              <input type="text" name="location" class="form-control" required placeholder="Village, Taluka, District" value="<?= e($_SESSION['village'] ?? '') ?>, <?= e($_SESSION['district'] ?? '') ?>">
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold">Produce Description & Quality Details</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Mention moisture level, harvest date, grading, organic practices, packaging, etc."><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold">Produce Photo (Optional, max 3MB, JPG/PNG/WebP)</label>
              <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
              <small class="text-muted">High-quality photos attract faster buyer enquiries.</small>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="<?= BASE_URL ?>/farmer/my_products.php" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-agro-primary px-4 py-2 fs-6">
              <i class="bi bi-cloud-upload me-2"></i>Publish Listing for Approval
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
