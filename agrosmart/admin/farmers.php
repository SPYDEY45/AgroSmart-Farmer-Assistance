<?php
/**
 * AgroSmart - Admin Farmers Directory Management
 */
$pageTitle = 'Manage Farmers – AgroSmart Admin';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Handle Status Toggle (Block / Activate)
if ($action === 'toggle' && $id > 0) {
    $uStmt = $pdo->prepare("SELECT u.user_id, u.status FROM users u JOIN farmers f ON u.user_id = f.user_id WHERE f.farmer_id = ?");
    $uStmt->execute([$id]);
    $u = $uStmt->fetch();
    if ($u) {
        $newStatus = $u['status'] === 'active' ? 'blocked' : 'active';
        $upd = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $upd->execute([$newStatus, $u['user_id']]);
        setFlash('success', "Farmer account has been set to {$newStatus}.");
    }
    header("Location: " . BASE_URL . "/admin/farmers.php");
    exit();
}

// Handle Delete
if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE u FROM users u JOIN farmers f ON u.user_id = f.user_id WHERE f.farmer_id = ?");
    $del->execute([$id]);
    setFlash('success', 'Farmer account and all associated data deleted.');
    header("Location: " . BASE_URL . "/admin/farmers.php");
    exit();
}

// Search
$search = trim($_GET['search'] ?? '');
$query = "SELECT f.*, u.name, u.email, u.mobile, u.status, (SELECT COUNT(*) FROM farmer_crops fc WHERE fc.farmer_id = f.farmer_id) as crops_count, (SELECT COUNT(*) FROM products p WHERE p.farmer_id = f.farmer_id) as products_count FROM farmers f JOIN users u ON f.user_id = u.user_id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.mobile LIKE ? OR f.district LIKE ? OR f.village LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
$query .= " ORDER BY f.farmer_id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$farmers = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-people me-2"></i>Registered Farmers Directory</h2>
    <p class="text-muted mb-0">Monitor land holdings, agronomic profiles, and account verification states</p>
  </div>
</div>

<!-- Search Bar -->
<div class="card card-agro mb-4 p-3 shadow-sm">
  <form method="GET" action="<?= BASE_URL ?>/admin/farmers.php" class="row g-2">
    <div class="col-md-10">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Search farmer by name, mobile number, village, or district..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-agro-primary"><i class="bi bi-search me-1"></i>Search</button>
    </div>
  </form>
</div>

<!-- Farmers Table -->
<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Farmer Details</th>
            <th>Location</th>
            <th>Land & Soil</th>
            <th>Water Source</th>
            <th>Crops / Listings</th>
            <th>Account Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($farmers)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No farmer accounts found matching query.</td></tr>
          <?php else: ?>
            <?php foreach ($farmers as $f): ?>
              <tr>
                <td>
                  <strong class="text-dark"><?= e($f['name']) ?></strong>
                  <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= e($f['email']) ?></div>
                  <div class="small text-success"><i class="bi bi-telephone me-1"></i><?= e($f['mobile']) ?></div>
                </td>
                <td>
                  <?= e($f['village']) ?>,<br>
                  <span class="fw-semibold"><?= e($f['district']) ?></span>
                </td>
                <td>
                  <strong><?= e($f['land_area']) ?> Acres</strong><br>
                  <small class="text-muted"><?= e($f['soil_type']) ?></small>
                </td>
                <td><span class="badge bg-light text-dark border"><?= e($f['water_availability']) ?></span></td>
                <td>
                  <span class="badge bg-success-subtle text-success me-1"><?= $f['crops_count'] ?> Crops</span>
                  <span class="badge bg-primary-subtle text-primary"><?= $f['products_count'] ?> Products</span>
                </td>
                <td>
                  <?php if ($f['status'] === 'active'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                  <?php else: ?>
                    <span class="badge bg-danger"><i class="bi bi-slash-circle me-1"></i>Blocked</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <a href="<?= BASE_URL ?>/admin/farmers.php?action=toggle&id=<?= $f['farmer_id'] ?>" class="btn btn-sm <?= $f['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?> me-1" title="Toggle Account Access">
                    <?= $f['status'] === 'active' ? 'Block' : 'Unblock' ?>
                  </a>
                  <a href="<?= BASE_URL ?>/admin/farmers.php?action=delete&id=<?= $f['farmer_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="farmer account">
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
