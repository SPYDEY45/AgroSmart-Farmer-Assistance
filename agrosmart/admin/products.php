<?php
/**
 * AgroSmart - Admin Marketplace Product Approvals & Moderation
 */
$pageTitle = 'Product Approvals – AgroSmart Admin';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Handle Approve
if ($action === 'approve' && $id > 0) {
    $stmt = $pdo->prepare("UPDATE products SET status = 'approved' WHERE product_id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Product has been approved and is now live on the marketplace.');
    header("Location: " . BASE_URL . "/admin/products.php");
    exit();
}

// Handle Reject
if ($action === 'reject' && $id > 0) {
    $stmt = $pdo->prepare("UPDATE products SET status = 'rejected' WHERE product_id = ?");
    $stmt->execute([$id]);
    setFlash('warning', 'Product listing has been rejected.');
    header("Location: " . BASE_URL . "/admin/products.php");
    exit();
}

// Handle Delete
if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Product listing deleted permanently.');
    header("Location: " . BASE_URL . "/admin/products.php");
    exit();
}

$filterStatus = $_GET['status'] ?? '';
$query = "SELECT p.*, f.village, f.district, u.name as farmer_name, u.mobile as farmer_mobile FROM products p JOIN farmers f ON p.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE 1=1";
$params = [];

if (!empty($filterStatus)) {
    $query .= " AND p.status = ?";
    $params[] = $filterStatus;
}
$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-boxes me-2"></i>Marketplace Listings Moderation Desk</h2>
    <p class="text-muted mb-0">Review farmer produce listings, verify rates and locations, and approve lots for live bidding</p>
  </div>
</div>

<!-- Status Filter Tabs -->
<ul class="nav nav-pills mb-4">
  <li class="nav-item">
    <a class="nav-link <?= empty($filterStatus) ? 'active bg-success' : '' ?>" href="<?= BASE_URL ?>/admin/products.php">All Listings</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $filterStatus === 'pending' ? 'active bg-warning text-dark' : '' ?>" href="<?= BASE_URL ?>/admin/products.php?status=pending">Pending Approval</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $filterStatus === 'approved' ? 'active bg-success' : '' ?>" href="<?= BASE_URL ?>/admin/products.php?status=approved">Approved (Live)</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $filterStatus === 'rejected' ? 'active bg-danger' : '' ?>" href="<?= BASE_URL ?>/admin/products.php?status=rejected">Rejected</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $filterStatus === 'sold' ? 'active bg-secondary' : '' ?>" href="<?= BASE_URL ?>/admin/products.php?status=sold">Sold Out</a>
  </li>
</ul>

<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Produce Item</th>
            <th>Farmer Details</th>
            <th>Lot Size</th>
            <th>Asking Price</th>
            <th>Listed Date</th>
            <th>Status</th>
            <th class="text-end">Approval Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($products)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No produce listings found.</td></tr>
          <?php else: ?>
            <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <strong class="text-dark fs-6"><?= e($p['product_name']) ?></strong>
                  <div class="small text-muted"><span class="badge bg-light text-dark border"><?= e($p['category']) ?></span> | <i class="bi bi-geo-alt"></i> <?= e($p['location']) ?></div>
                </td>
                <td>
                  <div class="fw-semibold text-dark"><?= e($p['farmer_name']) ?></div>
                  <div class="small text-success"><i class="bi bi-telephone"></i> <?= e($p['farmer_mobile']) ?></div>
                </td>
                <td><strong><?= e($p['quantity']) ?></strong> <?= e($p['unit']) ?></td>
                <td class="text-success fw-bold">₹ <?= number_format($p['expected_price'], 2) ?> / <?= e($p['unit']) ?></td>
                <td class="small text-muted"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                <td>
                  <?php if ($p['status'] === 'approved'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                  <?php elseif ($p['status'] === 'pending'): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                  <?php elseif ($p['status'] === 'sold'): ?>
                    <span class="badge bg-secondary">Sold Out</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Rejected</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <?php if ($p['status'] !== 'approved'): ?>
                    <a href="<?= BASE_URL ?>/admin/products.php?action=approve&id=<?= $p['product_id'] ?>" class="btn btn-sm btn-success me-1" title="Approve Listing">
                      <i class="bi bi-check-lg"></i> Approve
                    </a>
                  <?php endif; ?>
                  <?php if ($p['status'] !== 'rejected'): ?>
                    <a href="<?= BASE_URL ?>/admin/products.php?action=reject&id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Reject Listing">
                      <i class="bi bi-x-lg"></i> Reject
                    </a>
                  <?php endif; ?>
                  <a href="<?= BASE_URL ?>/admin/products.php?action=delete&id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="product lot">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
