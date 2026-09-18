<?php
/**
 * AgroSmart - Admin Government Schemes Management
 */
$pageTitle = 'Manage Schemes – AgroSmart Admin';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);
$error = '';

// Handle Delete
if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE FROM schemes WHERE scheme_id = ?");
    $del->execute([$id]);
    setFlash('success', 'Scheme removed.');
    header("Location: " . BASE_URL . "/admin/schemes.php");
    exit();
}

// Handle Add / Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['scheme_name'] ?? '');
    $category = $_POST['category'] ?? 'Central';
    $description = trim($_POST['description'] ?? '');
    $eligibility = trim($_POST['eligibility'] ?? '');
    $benefits = trim($_POST['benefits'] ?? '');
    $howToApply = trim($_POST['how_to_apply'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $editId = intval($_POST['edit_id'] ?? 0);

    if (empty($name) || empty($description) || empty($benefits)) {
        $error = 'Please fill out all required scheme fields.';
    } else {
        if ($editId > 0) {
            $stmt = $pdo->prepare("UPDATE schemes SET scheme_name = ?, category = ?, description = ?, eligibility = ?, benefits = ?, how_to_apply = ?, website = ? WHERE scheme_id = ?");
            $stmt->execute([$name, $category, $description, $eligibility, $benefits, $howToApply, $website, $editId]);
            setFlash('success', 'Scheme details updated.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO schemes (scheme_name, category, description, eligibility, benefits, how_to_apply, website) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $description, $eligibility, $benefits, $howToApply, $website]);
            setFlash('success', 'New government welfare scheme published.');
        }
        header("Location: " . BASE_URL . "/admin/schemes.php");
        exit();
    }
}

// If Edit
$editScheme = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM schemes WHERE scheme_id = ?");
    $stmt->execute([$id]);
    $editScheme = $stmt->fetch();
}

$schemes = $pdo->query("SELECT * FROM schemes ORDER BY scheme_id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-bank me-2"></i>Government Schemes Management</h2>
    <p class="text-muted mb-0">Publish subsidy programs, direct benefit guidelines, and state agricultural missions</p>
  </div>
  <div class="mt-3 mt-md-0">
    <button type="button" class="btn btn-agro-primary" data-bs-toggle="modal" data-bs-target="#schemeModal">
      <i class="bi bi-plus-circle me-1"></i>Publish New Scheme
    </button>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Scheme Title</th>
            <th>Category</th>
            <th>Benefits Summary</th>
            <th>Eligibility</th>
            <th>Official Portal</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($schemes as $s): ?>
            <tr>
              <td>
                <strong class="text-success fs-6"><?= e($s['scheme_name']) ?></strong>
                <div class="small text-muted text-truncate" style="max-width: 280px;"><?= e($s['description']) ?></div>
              </td>
              <td><span class="badge bg-light text-dark border"><?= e($s['category']) ?></span></td>
              <td style="max-width: 220px;"><small class="text-dark"><?= e($s['benefits']) ?></small></td>
              <td style="max-width: 220px;"><small class="text-secondary"><?= e($s['eligibility']) ?></small></td>
              <td>
                <?php if ($s['website']): ?>
                  <a href="<?= e($s['website']) ?>" target="_blank" class="badge bg-primary text-decoration-none">
                    Link <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a href="<?= BASE_URL ?>/admin/schemes.php?action=edit&id=<?= $s['scheme_id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="<?= BASE_URL ?>/admin/schemes.php?action=delete&id=<?= $s['scheme_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="scheme">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal for Add/Edit Scheme -->
<div class="modal fade <?= $editScheme ? 'show d-block' : '' ?>" id="schemeModal" tabindex="-1" style="<?= $editScheme ? 'background: rgba(0,0,0,0.5);' : '' ?>">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/admin/schemes.php" method="POST">
        <input type="hidden" name="edit_id" value="<?= $editScheme['scheme_id'] ?? 0 ?>">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-bank me-2"></i><?= $editScheme ? 'Edit Scheme' : 'Publish Welfare Scheme' ?>
          </h5>
          <?php if ($editScheme): ?>
            <a href="<?= BASE_URL ?>/admin/schemes.php" class="btn-close btn-close-white"></a>
          <?php else: ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          <?php endif; ?>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Scheme Name *</label>
              <input type="text" name="scheme_name" class="form-control" required value="<?= e($editScheme['scheme_name'] ?? '') ?>" placeholder="e.g. PM-KISAN or Mahadbt Solar Pump">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Category *</label>
              <select name="category" class="form-select" required>
                <?php foreach (['Central', 'State', 'Subsidy', 'Insurance'] as $cat): ?>
                  <option value="<?= $cat ?>" <?= ($editScheme['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Scheme Description *</label>
              <textarea name="description" class="form-control" rows="3" required placeholder="General objective of the scheme..."><?= e($editScheme['description'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Financial / Equipment Benefits *</label>
              <textarea name="benefits" class="form-control" rows="3" required placeholder="e.g. ₹6,000 per year in 3 equal installments or 90% subsidy on solar pump"><?= e($editScheme['benefits'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Eligibility Criteria *</label>
              <textarea name="eligibility" class="form-control" rows="3" required placeholder="e.g. All landholding farmer families having cultivable land holding..."><?= e($editScheme['eligibility'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Application Procedure / How to Apply</label>
              <textarea name="how_to_apply" class="form-control" rows="2" placeholder="Online via MahaDBT portal or CSC VLE center..."><?= e($editScheme['how_to_apply'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Official Website / Portal Link</label>
              <input type="url" name="website" class="form-control" value="<?= e($editScheme['website'] ?? '') ?>" placeholder="https://pmkisan.gov.in">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a href="<?= BASE_URL ?>/admin/schemes.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-agro-primary">Save Scheme</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
