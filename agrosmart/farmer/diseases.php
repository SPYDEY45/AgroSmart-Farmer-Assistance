<?php
/**
 * AgroSmart - Crop Diseases & Pest Doctor Module
 */
$pageTitle = 'Crop Diseases & Pests – AgroSmart';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDBConnection();
$search = trim($_GET['search'] ?? '');
$cropId = intval($_GET['crop_id'] ?? 0);

$query = "SELECT cd.*, c.crop_name FROM crop_diseases cd JOIN crops c ON cd.crop_id = c.crop_id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (cd.disease_name LIKE ? OR cd.symptoms LIKE ? OR cd.solutions LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($cropId > 0) {
    $query .= " AND cd.crop_id = ?";
    $params[] = $cropId;
}

$query .= " ORDER BY cd.disease_id ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$diseases = $stmt->fetchAll();

// Fetch all crops for dropdown
$cropsList = $pdo->query("SELECT crop_id, crop_name FROM crops ORDER BY crop_name ASC")->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-bug me-2"></i>Crop Disease & Pest Management</h2>
    <p class="text-muted mb-0">Symptom identification, pathogen causes, chemical controls, and organic remedies</p>
  </div>
  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <div class="mt-3 mt-md-0">
      <a href="<?= BASE_URL ?>/admin/diseases.php?action=add" class="btn btn-agro-primary">
        <i class="bi bi-plus-circle me-1"></i>Add Disease Record
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Mandatory Educational Notice -->
<div class="agro-notice-box">
  <div class="d-flex">
    <i class="bi bi-shield-exclamation text-warning fs-4 me-3"></i>
    <div>
      <strong><?= __('disclaimer_title') ?>:</strong>
      <p class="mb-0 mt-1"><?= __('disease_notice') ?></p>
    </div>
  </div>
</div>

<!-- Search & Filter Bar -->
<div class="card card-agro mb-4 p-3">
  <form method="GET" action="<?= BASE_URL ?>/farmer/diseases.php" class="row g-2">
    <div class="col-md-7">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Search by disease name, yellow spots, pest or symptoms..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="crop_id" class="form-select">
        <option value="0">All Crops</option>
        <?php foreach ($cropsList as $cl): ?>
          <option value="<?= $cl['crop_id'] ?>" <?= $cropId === intval($cl['crop_id']) ? 'selected' : '' ?>>
            <?= e($cl['crop_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-agro-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
    </div>
  </form>
</div>

<!-- Diseases Grid -->
<?php if (empty($diseases)): ?>
  <div class="card card-agro p-5 text-center text-muted">
    <i class="bi bi-shield-check fs-1 mb-2 text-success"></i>
    <h5>No diseases found matching your search.</h5>
    <a href="<?= BASE_URL ?>/farmer/diseases.php" class="btn btn-outline-success mt-2">Reset Filters</a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($diseases as $d): ?>
      <div class="col-md-6">
        <div class="card card-agro h-100 shadow-sm border-start border-danger border-4">
          <div class="card-agro-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-danger"><?= e($d['disease_name']) ?></h5>
            <span class="badge bg-success-subtle text-success border border-success">Crop: <?= e($d['crop_name']) ?></span>
          </div>

          <div class="card-body">
            <div class="mb-3">
              <strong class="text-dark small d-block"><i class="bi bi-eye-fill text-danger me-1"></i>Visible Symptoms:</strong>
              <p class="small text-secondary mb-0"><?= e($d['symptoms']) ?></p>
            </div>

            <div class="mb-3">
              <strong class="text-dark small d-block"><i class="bi bi-virus text-warning me-1"></i>Causative Agent / Pathogen:</strong>
              <p class="small text-muted mb-0"><?= e($d['causes']) ?></p>
            </div>

            <div class="p-3 bg-light rounded border mb-3">
              <strong class="text-success small d-block mb-1"><i class="bi bi-capsule text-success me-1"></i>Treatment & Agronomic Solution:</strong>
              <p class="small text-dark mb-0" style="white-space: pre-line;"><?= e($d['solutions']) ?></p>
            </div>

            <div class="mb-0">
              <strong class="text-secondary small d-block"><i class="bi bi-shield-check me-1"></i>Preventive Cultural Practices:</strong>
              <p class="small text-muted mb-0"><?= e($d['precautions']) ?></p>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
