<?php
/**
 * AgroSmart - Crop Information Directory & Guide
 */
$pageTitle = 'Crop Information Guide – AgroSmart';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDBConnection();

$search = trim($_GET['search'] ?? '');
$seasonFilter = trim($_GET['season'] ?? '');
$soilFilter = trim($_GET['soil'] ?? '');

$query = "SELECT c.*, (SELECT COUNT(*) FROM crop_diseases cd WHERE cd.crop_id = c.crop_id) as disease_count FROM crops c WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (c.crop_name LIKE ? OR c.cultivation_info LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if (!empty($seasonFilter)) {
    $query .= " AND c.season = ?";
    $params[] = $seasonFilter;
}
if (!empty($soilFilter)) {
    $query .= " AND c.soil_type LIKE ?";
    $params[] = "%{$soilFilter}%";
}

$query .= " ORDER BY c.crop_name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$crops = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-book me-2"></i>Comprehensive Crop Guide</h2>
    <p class="text-muted mb-0">Agronomic cultivation schedules, soil types, water needs, and harvest calendars</p>
  </div>
  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <div class="mt-3 mt-md-0">
      <a href="<?= BASE_URL ?>/admin/crops.php?action=add" class="btn btn-agro-primary">
        <i class="bi bi-plus-circle me-1"></i>Add New Crop
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Search & Filters -->
<div class="card card-agro mb-4 p-3">
  <form method="GET" action="<?= BASE_URL ?>/farmer/crops.php" class="row g-2">
    <div class="col-md-5">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Search crop name (e.g. Cotton, Soybean, Wheat)..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="season" class="form-select">
        <option value="">All Seasons</option>
        <option value="Kharif" <?= $seasonFilter === 'Kharif' ? 'selected' : '' ?>>Kharif (Monsoon)</option>
        <option value="Rabi" <?= $seasonFilter === 'Rabi' ? 'selected' : '' ?>>Rabi (Winter)</option>
        <option value="Zaid" <?= $seasonFilter === 'Zaid' ? 'selected' : '' ?>>Zaid (Summer)</option>
        <option value="Annual" <?= $seasonFilter === 'Annual' ? 'selected' : '' ?>>Annual / Year-round</option>
      </select>
    </div>
    <div class="col-md-2">
      <select name="soil" class="form-select">
        <option value="">All Soils</option>
        <option value="Black" <?= $soilFilter === 'Black' ? 'selected' : '' ?>>Black Soil</option>
        <option value="Loam" <?= $soilFilter === 'Loam' ? 'selected' : '' ?>>Loamy Soil</option>
        <option value="Red" <?= $soilFilter === 'Red' ? 'selected' : '' ?>>Red Soil</option>
        <option value="Sandy" <?= $soilFilter === 'Sandy' ? 'selected' : '' ?>>Sandy Loam</option>
      </select>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-agro-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
    </div>
  </form>
</div>

<!-- Crops Cards Grid -->
<?php if (empty($crops)): ?>
  <div class="card card-agro p-5 text-center text-muted">
    <i class="bi bi-search fs-1 mb-2"></i>
    <h5>No crops found matching your criteria.</h5>
    <p>Try clearing filters or search term.</p>
    <a href="<?= BASE_URL ?>/farmer/crops.php" class="btn btn-outline-success mt-2">View All Crops</a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($crops as $c): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card card-agro h-100 shadow-sm d-flex flex-column">
          <div class="card-agro-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-success"><?= e($c['crop_name']) ?></h5>
            <span class="badge bg-success-subtle text-success border border-success"><?= e($c['season']) ?></span>
          </div>
          <div class="card-body flex-grow-1">
            <div class="mb-2">
              <span class="text-muted small d-block">Soil Suitability:</span>
              <strong class="text-dark small"><i class="bi bi-layers me-1 text-secondary"></i><?= e($c['soil_type']) ?></strong>
            </div>
            <div class="row g-2 mb-3 small">
              <div class="col-6">
                <span class="text-muted d-block">Water Need:</span>
                <span class="badge <?= $c['water_requirement'] === 'High' ? 'bg-primary' : ($c['water_requirement'] === 'Medium' ? 'bg-info text-dark' : 'bg-secondary') ?>">
                  <?= e($c['water_requirement']) ?>
                </span>
              </div>
              <div class="col-6">
                <span class="text-muted d-block">Sowing Window:</span>
                <strong><?= e($c['sowing_period']) ?></strong>
              </div>
            </div>
            <p class="small text-muted mb-3" style="min-height: 50px;">
              <?= e(mb_strimwidth($c['cultivation_info'], 0, 120, '...')) ?>
            </p>
          </div>
          <div class="card-footer bg-light border-top p-3 d-flex justify-content-between align-items-center">
            <a href="<?= BASE_URL ?>/farmer/diseases.php?crop_id=<?= $c['crop_id'] ?>" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-bug me-1"></i>Diseases (<?= $c['disease_count'] ?>)
            </a>
            <button type="button" class="btn btn-sm btn-agro-primary" data-bs-toggle="modal" data-bs-target="#cropModal<?= $c['crop_id'] ?>">
              Full Details <i class="bi bi-arrow-right ms-1"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Crop Details Modal -->
      <div class="modal fade" id="cropModal<?= $c['crop_id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title fw-bold"><span class="me-2">🌾</span><?= e($c['crop_name']) ?> – Complete Cultivation Guide</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
              <div class="row g-3 mb-3">
                <div class="col-md-3">
                  <div class="p-2 border rounded bg-light">
                    <small class="text-muted d-block">Season</small>
                    <strong class="text-success"><?= e($c['season']) ?></strong>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="p-2 border rounded bg-light">
                    <small class="text-muted d-block">Soil Type</small>
                    <strong><?= e($c['soil_type']) ?></strong>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="p-2 border rounded bg-light">
                    <small class="text-muted d-block">Sowing Window</small>
                    <strong><?= e($c['sowing_period']) ?></strong>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="p-2 border rounded bg-light">
                    <small class="text-muted d-block">Harvest Window</small>
                    <strong><?= e($c['harvest_period']) ?></strong>
                  </div>
                </div>
              </div>

              <h6 class="fw-bold text-success border-bottom pb-1 mb-2">Agronomy & Cultivation Method</h6>
              <p class="small text-secondary" style="white-space: pre-line;"><?= e($c['cultivation_info']) ?></p>

              <h6 class="fw-bold text-danger border-bottom pb-1 mb-2 mt-3">Key Precautions & Risk Management</h6>
              <p class="small text-secondary" style="white-space: pre-line;"><?= e($c['precautions']) ?></p>

              <div class="agro-notice-box small my-3">
                <strong><i class="bi bi-info-circle me-1"></i>Educational Notice:</strong> Agronomy guidelines are prepared from Agricultural University packages of practices. Always consider local micro-climate and field drainage.
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'farmer'): ?>
                <a href="<?= BASE_URL ?>/farmer/my_crops.php?action=add&crop_id=<?= $c['crop_id'] ?>" class="btn btn-success">
                  <i class="bi bi-plus-circle me-1"></i>Add to My Cultivations
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
