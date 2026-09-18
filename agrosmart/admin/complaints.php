<?php
/**
 * AgroSmart - Admin Farmer Grievance Resolution Cell
 */
$pageTitle = 'Manage Complaints – AgroSmart Admin';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();
$error = '';

// Handle Status & Response Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_complaint'])) {
    $cId = intval($_POST['complaint_id'] ?? 0);
    $status = $_POST['status'] ?? 'In Progress';
    $response = trim($_POST['admin_response'] ?? '');

    if ($cId > 0) {
        $stmt = $pdo->prepare("UPDATE complaints SET status = ?, admin_response = ? WHERE complaint_id = ?");
        $stmt->execute([$status, $response, $cId]);
        setFlash('success', 'Complaint status and official resolution recorded.');
        header("Location: " . BASE_URL . "/admin/complaints.php");
        exit();
    }
}

$filter = $_GET['status'] ?? '';
$query = "SELECT c.*, f.village, f.district, u.name as farmer_name, u.mobile as farmer_mobile FROM complaints c JOIN farmers f ON c.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE 1=1";
$params = [];

if (!empty($filter)) {
    $query .= " AND c.status = ?";
    $params[] = $filter;
}
$query .= " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-danger mb-1"><i class="bi bi-shield-exclamation me-2"></i>Farmer Grievance Redressal Cell</h2>
    <p class="text-muted mb-0">Investigate APMC disputes, irrigation shortages, sub-standard seeds, and platform issues</p>
  </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-pills mb-4">
  <li class="nav-item">
    <a class="nav-link <?= empty($filter) ? 'active bg-danger' : '' ?>" href="<?= BASE_URL ?>/admin/complaints.php">All Grievances</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $filter === 'Pending' ? 'active bg-warning text-dark' : '' ?>" href="<?= BASE_URL ?>/admin/complaints.php?status=Pending">Pending Review</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $filter === 'In Progress' ? 'active bg-info text-dark' : '' ?>" href="<?= BASE_URL ?>/admin/complaints.php?status=In Progress">In Progress</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $filter === 'Resolved' ? 'active bg-success' : '' ?>" href="<?= BASE_URL ?>/admin/complaints.php?status=Resolved">Resolved</a>
  </li>
</ul>

<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Ticket #</th>
            <th>Farmer Details</th>
            <th>Category & Subject</th>
            <th>Description</th>
            <th>Status</th>
            <th>Official Response</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($complaints)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No grievances found.</td></tr>
          <?php else: ?>
            <?php foreach ($complaints as $c): ?>
              <tr>
                <td><code>#CMP-<?= str_pad($c['complaint_id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                <td>
                  <strong><?= e($c['farmer_name']) ?></strong>
                  <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e($c['village']) ?>, <?= e($c['district']) ?></div>
                  <div class="small text-success"><i class="bi bi-telephone"></i> <?= e($c['farmer_mobile']) ?></div>
                </td>
                <td>
                  <span class="badge bg-light text-dark border"><?= e($c['category']) ?></span>
                  <div class="fw-bold text-dark mt-1"><?= e($c['subject']) ?></div>
                  <small class="text-muted"><?= date('d M Y', strtotime($c['created_at'])) ?></small>
                </td>
                <td style="max-width: 250px;">
                  <small class="text-secondary"><?= e($c['description']) ?></small>
                </td>
                <td>
                  <?php if ($c['status'] === 'Resolved'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Resolved</span>
                  <?php elseif ($c['status'] === 'In Progress'): ?>
                    <span class="badge bg-info text-dark"><i class="bi bi-arrow-repeat me-1"></i>In Progress</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>
                  <?php endif; ?>
                </td>
                <td style="max-width: 220px;">
                  <?php if (!empty($c['admin_response'])): ?>
                    <small class="text-success fw-semibold"><?= e($c['admin_response']) ?></small>
                  <?php else: ?>
                    <span class="text-muted small">No reply yet</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#resolveModal<?= $c['complaint_id'] ?>">
                    <i class="bi bi-pencil-square me-1"></i>Process
                  </button>
                </td>
              </tr>

              <!-- Process Modal -->
              <div class="modal fade" id="resolveModal<?= $c['complaint_id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <form action="<?= BASE_URL ?>/admin/complaints.php" method="POST">
                      <input type="hidden" name="update_complaint" value="1">
                      <input type="hidden" name="complaint_id" value="<?= $c['complaint_id'] ?>">
                      <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2"></i>Resolve Grievance #CMP-<?= $c['complaint_id'] ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body p-4">
                        <div class="p-3 bg-light rounded mb-3">
                          <strong>Farmer:</strong> <?= e($c['farmer_name']) ?> (<?= e($c['village']) ?>, <?= e($c['district']) ?>)<br>
                          <strong>Subject:</strong> <?= e($c['subject']) ?><br>
                          <p class="small text-muted mt-2 mb-0"><?= e($c['description']) ?></p>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Status *</label>
                          <select name="status" class="form-select" required>
                            <option value="Pending" <?= $c['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="In Progress" <?= $c['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress (Under Investigation)</option>
                            <option value="Resolved" <?= $c['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved (Closed)</option>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-semibold">Official Admin Action / Response Note *</label>
                          <textarea name="admin_response" class="form-control" rows="3" required placeholder="Explain action taken, APMC notice issued, or resolution details..."><?= e($c['admin_response'] ?? '') ?></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Save Resolution</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
