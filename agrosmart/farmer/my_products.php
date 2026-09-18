<?php
/**
 * AgroSmart - Farmer's Marketplace Listings
 */
$pageTitle = 'My Listed Products – AgroSmart';
require_once __DIR__ . '/../includes/farmer_auth.php';

$pdo = getDBConnection();
$farmerId = $_SESSION['farmer_id'];
$action = $_GET['action'] ?? '';

// Handle mark as sold
if ($action === 'sold' && isset($_GET['id'])) {
    $prodId = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE products SET status = 'sold' WHERE product_id = ? AND farmer_id = ?");
    $stmt->execute([$prodId, $farmerId]);
    setFlash('success', 'Product marked as Sold.');
    header("Location: " . BASE_URL . "/farmer/my_products.php");
    exit();
}

// Handle delete
if ($action === 'delete' && isset($_GET['id'])) {
    $prodId = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ? AND farmer_id = ?");
    $stmt->execute([$prodId, $farmerId]);
    setFlash('success', 'Product listing deleted.');
    header("Location: " . BASE_URL . "/farmer/my_products.php");
    exit();
}

$stmt = $pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM enquiries e WHERE e.product_id = p.product_id) as enquiry_count FROM products p WHERE p.farmer_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$farmerId]);
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-box-seam me-2"></i>My Marketplace Listings</h2>
    <p class="text-muted mb-0">Track verification status, buyer enquiries, and manage your crop inventory</p>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?= BASE_URL ?>/farmer/add_product.php" class="btn btn-agro-primary">
      <i class="bi bi-plus-circle me-1"></i>List New Produce
    </a>
  </div>
</div>

<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($products)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-basket fs-1 d-block mb-3 text-secondary"></i>
        <h5>You haven't listed any farm produce yet.</h5>
        <p class="small">Connect directly with bulk grains, oilseeds, and vegetable buyers without middleman commissions.</p>
        <a href="<?= BASE_URL ?>/farmer/add_product.php" class="btn btn-agro-primary mt-2">
          <i class="bi bi-cart-plus me-1"></i>List First Product
        </a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Produce</th>
              <th>Category</th>
              <th>Quantity</th>
              <th>Expected Price</th>
              <th>Approval Status</th>
              <th>Enquiries</th>
              <th>Listed Date</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <strong class="text-dark"><?= e($p['product_name']) ?></strong>
                  <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e($p['location']) ?></div>
                </td>
                <td><span class="badge bg-light text-dark border"><?= e($p['category']) ?></span></td>
                <td><strong><?= e($p['quantity']) ?></strong> <?= e($p['unit']) ?></td>
                <td class="text-success fw-bold">₹ <?= number_format($p['expected_price'], 2) ?> / <?= e($p['unit']) ?></td>
                <td>
                  <?php if ($p['status'] === 'approved'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved (Live)</span>
                  <?php elseif ($p['status'] === 'pending'): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Pending Review</span>
                  <?php elseif ($p['status'] === 'sold'): ?>
                    <span class="badge bg-secondary"><i class="bi bi-bag-check me-1"></i>Sold Out</span>
                  <?php else: ?>
                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($p['enquiry_count'] > 0): ?>
                    <a href="<?= BASE_URL ?>/farmer/enquiries.php?product_id=<?= $p['product_id'] ?>" class="badge bg-primary text-decoration-none">
                      <?= $p['enquiry_count'] ?> Buyer Enquiries
                    </a>
                  <?php else: ?>
                    <span class="text-muted small">None</span>
                  <?php endif; ?>
                </td>
                <td class="small text-muted"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                <td class="text-end">
                  <?php if ($p['status'] === 'approved'): ?>
                    <a href="<?= BASE_URL ?>/farmer/my_products.php?action=sold&id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-secondary" title="Mark as Sold">
                      Mark Sold
                    </a>
                  <?php endif; ?>
                  <a href="<?= BASE_URL ?>/farmer/my_products.php?action=delete&id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="product listing">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
