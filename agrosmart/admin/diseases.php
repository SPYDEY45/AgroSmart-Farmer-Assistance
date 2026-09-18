<?php
/**
 * AgroSmart - Admin Disease Database Management
 */
$pageTitle = 'Manage Crop Diseases – AgroSmart Admin';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);
$error = '';

// Handle Delete
if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE FROM crop_diseases WHERE disease_id = ?");
    $del->execute([$id]);
    setFlash('success', 'Crop disease diagnosis entry removed.');
    header("Location: " . BASE_URL . "/admin/diseases.php");
    exit();
}

// Handle Add / Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropId = intval($_POST['crop_id'] ?? 0);
    $diseaseName = trim($_POST['disease_name'] ?? '');
    $symptoms = trim($_POST['symptoms'] ?? '');
    $causes = trim($_POST['causes'] ?? '');
    $solutions = trim($_POST['solutions'] ?? '');
    $precautions = trim($_POST['precautions'] ?? '');
    $editId = intval($_POST['edit_id'] ?? 0);

    if ($cropId <= 0 || empty($diseaseName) || empty($symptoms) || empty($solutions)) {
        $error = 'Please fill out all required disease diagnosis fields.';
    } else {
        if ($editId > 0) {
            $stmt = $pdo->prepare("UPDATE crop_diseases SET crop_id = ?, disease_name = ?, symptoms = ?, causes = ?, solutions = ?, precautions = ? WHERE disease_id = ?");
            $stmt->execute([$cropId, $diseaseName, $symptoms, $causes, $solutions, $precautions, $editId]);
            setFlash('success', 'Disease details updated.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO crop_diseases (crop_id, disease_name, symptoms, causes, solutions, precautions) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cropId, $diseaseName, $symptoms, $causes, $solutions, $precautions]);
            setFlash('success', 'New disease profile added to doctor repository.');
        }
        header("Location: " . BASE_URL . "/admin/diseases.php");
        exit();
    }
}

// If Edit
$editDisease = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM crop_diseases WHERE disease_id = ?");
    $stmt->execute([$id]);
    $editDisease = $stmt->fetch();
}

// Fetch all crops
$cropsList = $pdo->query("SELECT crop_id, crop_name FROM crops ORDER BY crop_name ASC")->fetchAll();

$query = "SELECT cd.*, c.crop_name FROM crop_diseases cd JOIN crops c ON cd.crop_id = c.crop_id ORDER BY cd.disease_id DESC";
$diseases = $pdo->query($query)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-danger mb-1"><i class="bi bi-bug me-2"></i>Crop Disease & Pest Management</h2>
    <p class="text-muted mb-0">Maintain plant pathology diagnosis guides, chemical dosages, and cultural controls</p>
  </div>
  <div class="mt-3 mt-md-0">
    <button type="button" class="btn btn-agro-primary" data-bs-toggle="modal" data-bs-target="#diseaseModal">
      <i class="bi bi-plus-circle me-1"></i>Add Disease Diagnosis
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
            <th>Disease / Pest Name</th>
            <th>Target Crop</th>
            <th>Primary Symptoms</th>
            <th>Pathogen / Cause</th>
            <th>Recommended Solution</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($diseases as $d): ?>
            <tr>
              <td><strong class="text-danger fs-6"><?= e($d['disease_name']) ?></strong></td>
              <td><span class="badge bg-success-subtle text-success border border-success"><?= e($d['crop_name']) ?></span></td>
              <td style="max-width: 250px;"><small class="text-muted text-truncate d-block"><?= e($d['symptoms']) ?></small></td>
              <td><small class="text-secondary"><?= e($d['causes']) ?></small></td>
              <td style="max-width: 250px;"><small class="text-dark text-truncate d-block"><?= e($d['solutions']) ?></small></td>
              <td class="text-end">
                <a href="<?= BASE_URL ?>/admin/diseases.php?action=edit&id=<?= $d['disease_id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="<?= BASE_URL ?>/admin/diseases.php?action=delete&id=<?= $d['disease_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="disease record">
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

<!-- Modal for Add/Edit Disease -->
<div class="modal fade <?= $editDisease ? 'show d-block' : '' ?>" id="diseaseModal" tabindex="-1" style="<?= $editDisease ? 'background: rgba(0,0,0,0.5);' : '' ?>">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/admin/diseases.php" method="POST">
        <input type="hidden" name="edit_id" value="<?= $editDisease['disease_id'] ?? 0 ?>">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-bug me-2"></i><?= $editDisease ? 'Edit Disease Record' : 'Add Disease Diagnosis' ?>
          </h5>
          <?php if ($editDisease): ?>
            <a href="<?= BASE_URL ?>/admin/diseases.php" class="btn-close btn-close-white"></a>
          <?php else: ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          <?php endif; ?>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Target Crop *</label>
              <select name="crop_id" class="form-select" required>
                <option value="">-- Choose Crop --</option>
                <?php foreach ($cropsList as $cl): ?>
                  <option value="<?= $cl['crop_id'] ?>" <?= ($editDisease['crop_id'] ?? '') == $cl['crop_id'] ? 'selected' : '' ?>>
                    <?= e($cl['crop_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Disease / Pest Name *</label>
              <input type="text" name="disease_name" class="form-control" required value="<?= e($editDisease['disease_name'] ?? '') ?>" placeholder="e.g. Pink Bollworm or Rust">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Pathogen / Causative Factor *</label>
              <input type="text" name="causes" class="form-control" required value="<?= e($editDisease['causes'] ?? '') ?>" placeholder="e.g. Pectinophora gossypiella or Fungal spores in warm humidity">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Visible Symptoms *</label>
              <textarea name="symptoms" class="form-control" rows="3" required placeholder="Describe appearance on leaves, stem, or fruits..."><?= e($editDisease['symptoms'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Agronomic Chemical & Organic Treatments *</label>
              <textarea name="solutions" class="form-control" rows="3" required placeholder="Exact dosage, chemical and biological spray recommendations..."><?= e($editDisease['solutions'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Cultural Precautions</label>
              <textarea name="precautions" class="form-control" rows="2" placeholder="Crop rotation, resistant seed varieties, pheromone traps..."><?= e($editDisease['precautions'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a href="<?= BASE_URL ?>/admin/diseases.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-danger">Save Disease Entry</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
