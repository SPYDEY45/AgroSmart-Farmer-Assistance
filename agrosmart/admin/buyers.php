<?php
/**
 * AgroSmart - Admin Buyers Directory Management
 */
$pageTitle = 'Manage Buyers – AgroSmart Admin';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Handle Status Toggle
if ($action === 'toggle' && $id > 0) {
    $uStmt = $pdo->prepare("SELECT u.user_id, u.status FROM users u JOIN buyers b ON u.user_id = b.user_id WHERE b.buyer_id = ?");
    $uStmt->execute([$id]);
    $u = $uStmt->fetch();
    if ($u) {
        $newStatus = $u['status'] === 'active' ? 'blocked' : 'active';
        $upd = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $upd->execute([$newStatus, $u['user_id']]);
        setFlash('success', "Buyer account status set to {$newStatus}.");
    }
    header("Location: " . BASE_URL . "/admin/buyers.php");
    exit();
}

// Handle Delete
if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE u FROM users u JOIN buyers b ON u.user_id = b.user_id WHERE b.buyer_id = ?");
    $del->execute([$id]);
    setFlash('success', 'Buyer account deleted.');
    header("Location: " . BASE_URL . "/admin/buyers.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$query = "SELECT b.*, u.name, u.email, u.mobile, u.status, (SELECT COUNT(*) FROM enquiries e WHERE e.buyer_id = b.buyer_id) as enquiries_count FROM buyers b JOIN users u ON b.user_id = u.user_id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR b.business_name LIKE ? OR b.district LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
$query .= " ORDER BY b.buyer_id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$buyers = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-primary mb-1"><i class="bi bi-shop-window me-2"></i>Registered Produce Buyers Directory</h2>
    <p class="text-muted mb-0">Manage wholesale merchants, food processing procurement desks, and retail traders</p>
  </div>
</div>

<div class="card card-agro mb-4 p-3 shadow-sm">
  <form method="GET" action="<?= BASE_URL ?>/admin/buyers.php" class="row g-2">
    <div class="col-md-10">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Search by contact person, firm name, or district..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
    </div>
  </form>
</div>

<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Business / Firm Name</th>
            <th>Contact Representative</th>
            <th>Commercial Address</th>
            <th>Total Enquiries Sent</th>
            <th>Account Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($buyers)): ?>
            <tr><td colspan="6" class="text-center py-4 text-muted">No buyer accounts found.</td></tr>
          <?php else: ?>
            <?php foreach ($buyers as $b): ?>
              <tr>
                <td>
                  <strong class="text-dark fs-6"><?= e($b['business_name']) ?></strong>
                  <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e($b['district']) ?>, Maharashtra</div>
                </td>
                <td>
                  <div class="fw-semibold"><?= e($b['name']) ?></div>
                  <div class="small text-muted"><?= e($b['email']) ?></div>
                  <div class="small text-primary"><i class="bi bi-telephone"></i> <?= e($b['mobile']) ?></div>
                </td>
                <td style="max-width: 250px;">
                  <span class="small text-secondary"><?= e($b['address']) ?></span>
                </td>
                <td>
                  <span class="badge bg-primary fs-6"><?= $b['enquiries_count'] ?> Enquiries</span>
                </td>
                <td>
                  <?php if ($b['status'] === 'active'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                  <?php else: ?>
                    <span class="badge bg-danger"><i class="bi bi-slash-circle me-1"></i>Blocked</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <a href="<?= BASE_URL ?>/admin/buyers.php?action=toggle&id=<?= $b['buyer_id'] ?>" class="btn btn-sm <?= $b['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?> me-1">
                    <?= $b['status'] === 'active' ? 'Block' : 'Unblock' ?>
                  </a>
                  <a href="<?= BASE_URL ?>/admin/buyers.php?action=delete&id=<?= $b['buyer_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="buyer account">
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
