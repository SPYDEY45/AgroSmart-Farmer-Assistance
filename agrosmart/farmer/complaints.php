<?php
/**
 * AgroSmart - Farmer Complaints & Grievance Redressal
 */
$pageTitle = 'Farmer Complaints – AgroSmart';
require_once __DIR__ . '/../includes/farmer_auth.php';

$pdo = getDBConnection();
$farmerId = $_SESSION['farmer_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $category = $_POST['category'] ?? 'Market';
    $description = trim($_POST['description'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = 'Security session expired. Please retry.';
    } elseif (empty($subject) || empty($description)) {
        $error = 'Please provide a complaint subject and detailed description.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO complaints (farmer_id, subject, category, description, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->execute([$farmerId, $subject, $category, $description]);
        setFlash('success', 'Your grievance has been lodged successfully with the AgroSmart grievance cell.');
        header("Location: " . BASE_URL . "/farmer/complaints.php");
        exit();
    }
}

// Fetch complaints
$stmt = $pdo->prepare("SELECT * FROM complaints WHERE farmer_id = ? ORDER BY created_at DESC");
$stmt->execute([$farmerId]);
$complaints = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-exclamation-triangle me-2"></i>Farmer Grievance & Complaint Redressal</h2>
    <p class="text-muted mb-0">Report agricultural issues, irrigation disputes, APMC mandi malpractices, or marketplace grievances</p>
  </div>
  <div class="mt-3 mt-md-0">
    <button type="button" class="btn btn-agro-primary" data-bs-toggle="modal" data-bs-target="#newComplaintModal">
      <i class="bi bi-pencil-square me-1"></i>Lodge New Complaint
    </button>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<!-- Complaints List -->
<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($complaints)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-shield-check fs-1 d-block mb-3 text-success"></i>
        <h5>No grievances filed.</h5>
        <p class="small">If you encounter any irregularities with market traders, electricity loadshedding, or fake seeds, file a complaint here.</p>
        <button type="button" class="btn btn-sm btn-agro-primary mt-2" data-bs-toggle="modal" data-bs-target="#newComplaintModal">
          <i class="bi bi-plus-circle me-1"></i>File Grievance
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Ticket #</th>
              <th>Category</th>
              <th>Subject</th>
              <th>Filed On</th>
              <th>Status</th>
              <th>Admin Action / Response</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($complaints as $c): ?>
              <tr>
                <td><code>#CMP-<?= str_pad($c['complaint_id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                <td><span class="badge bg-light text-dark border"><?= e($c['category']) ?></span></td>
                <td>
                  <strong class="text-dark"><?= e($c['subject']) ?></strong>
                  <div class="small text-muted text-truncate" style="max-width: 300px;"><?= e($c['description']) ?></div>
                </td>
                <td class="small text-muted"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td>
                  <?php if ($c['status'] === 'Resolved'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Resolved</span>
                  <?php elseif ($c['status'] === 'In Progress'): ?>
                    <span class="badge bg-info text-dark"><i class="bi bi-arrow-repeat me-1"></i>In Progress</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>
                  <?php endif; ?>
                </td>
                <td style="max-width: 280px;">
                  <?php if (!empty($c['admin_response'])): ?>
                    <div class="p-2 bg-light rounded border small">
                      <strong class="text-success d-block"><i class="bi bi-reply-fill"></i> Official Reply:</strong>
                      <?= e($c['admin_response']) ?>
                    </div>
                  <?php else: ?>
                    <span class="text-muted small">Awaiting review from officer</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- New Complaint Modal -->
<div class="modal fade" id="newComplaintModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/farmer/complaints.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Lodge a Grievance Ticket</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Complaint Category *</label>
            <select name="category" class="form-select" required>
              <option value="Market">Market / Mandi Price Dispute (बाजारभाव तक्रार)</option>
              <option value="Marketplace">Marketplace Buyer Default (खरेदीदार फसवणूक)</option>
              <option value="Irrigation">Irrigation / Canal Water Supply (पाणी पुरवठा)</option>
              <option value="Crop">Seed / Fertilizer Quality Issue (बियाणांची गुणवत्ता)</option>
              <option value="Other">Other Agricultural Administrative Issue (इतर)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Subject *</label>
            <input type="text" name="subject" class="form-control" required placeholder="Brief summary of the grievance">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Detailed Description *</label>
            <textarea name="description" class="form-control" rows="4" required placeholder="Provide dates, names, APMC mandi, or specific details of the complaint..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-agro-primary">Submit Ticket</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
