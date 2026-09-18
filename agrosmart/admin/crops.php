<?php
/**
 * AgroSmart - Admin Crop Directory CRUD
 */
$pageTitle = 'Manage Crops – AgroSmart Admin';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);
$error = '';

// Handle Delete
if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE FROM crops WHERE crop_id = ?");
    $del->execute([$id]);
    setFlash('success', 'Crop encyclopedia entry removed.');
    header("Location: " . BASE_URL . "/admin/crops.php");
    exit();
}

// Handle Add / Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropName = trim($_POST['crop_name'] ?? '');
    $season = $_POST['season'] ?? 'Kharif';
    $soilType = trim($_POST['soil_type'] ?? 'Black Soil');
    $waterReq = $_POST['water_requirement'] ?? 'Medium';
    $sowing = trim($_POST['sowing_period'] ?? '');
    $harvest = trim($_POST['harvest_period'] ?? '');
    $cultivation = trim($_POST['cultivation_info'] ?? '');
    $precautions = trim($_POST['precautions'] ?? '');
    $editId = intval($_POST['edit_id'] ?? 0);

    if (empty($cropName) || empty($sowing) || empty($harvest)) {
        $error = 'Please fill out all required crop details.';
    } else {
        if ($editId > 0) {
            $stmt = $pdo->prepare("UPDATE crops SET crop_name = ?, season = ?, soil_type = ?, water_requirement = ?, sowing_period = ?, harvest_period = ?, cultivation_info = ?, precautions = ? WHERE crop_id = ?");
            $stmt->execute([$cropName, $season, $soilType, $waterReq, $sowing, $harvest, $cultivation, $precautions, $editId]);
            setFlash('success', 'Crop details updated.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO crops (crop_name, season, soil_type, water_requirement, sowing_period, harvest_period, cultivation_info, precautions) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cropName, $season, $soilType, $waterReq, $sowing, $harvest, $cultivation, $precautions]);
            setFlash('success', 'New crop added to agricultural catalog.');
        }
        header("Location: " . BASE_URL . "/admin/crops.php");
        exit();
    }
}

// If Edit action, fetch single crop
$editCrop = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM crops WHERE crop_id = ?");
    $stmt->execute([$id]);
    $editCrop = $stmt->fetch();
}

$crops = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM crop_diseases cd WHERE cd.crop_id = c.crop_id) as diseases_count FROM crops c ORDER BY c.crop_name ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-flower1 me-2"></i>Crop Catalog & Agronomy Directory</h2>
    <p class="text-muted mb-0">Manage university-approved crop cultivation packages and sowing calendars</p>
  </div>
  <div class="mt-3 mt-md-0">
    <button type="button" class="btn btn-agro-primary" data-bs-toggle="modal" data-bs-target="#cropFormModal">
      <i class="bi bi-plus-circle me-1"></i>Add New Crop
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
            <th>Crop Name</th>
            <th>Season</th>
            <th>Soil Suitability</th>
            <th>Water Need</th>
            <th>Sowing Window</th>
            <th>Harvest Window</th>
            <th>Diseases</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($crops as $c): ?>
            <tr>
              <td><strong class="text-success fs-6"><?= e($c['crop_name']) ?></strong></td>
              <td><span class="badge bg-light text-dark border"><?= e($c['season']) ?></span></td>
              <td><?= e($c['soil_type']) ?></td>
              <td><span class="badge bg-info text-dark"><?= e($c['water_requirement']) ?></span></td>
              <td><?= e($c['sowing_period']) ?></td>
              <td><?= e($c['harvest_period']) ?></td>
              <td>
                <a href="<?= BASE_URL ?>/admin/diseases.php?crop_id=<?= $c['crop_id'] ?>" class="badge bg-danger text-decoration-none">
                  <?= $c['diseases_count'] ?> Diseases
                </a>
              </td>
              <td class="text-end">
                <a href="<?= BASE_URL ?>/admin/crops.php?action=edit&id=<?= $c['crop_id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="<?= BASE_URL ?>/admin/crops.php?action=delete&id=<?= $c['crop_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="crop">
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

<!-- Modal for Add/Edit Crop -->
<div class="modal fade <?= $editCrop ? 'show d-block' : '' ?>" id="cropFormModal" tabindex="-1" style="<?= $editCrop ? 'background: rgba(0,0,0,0.5);' : '' ?>">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/admin/crops.php" method="POST">
        <input type="hidden" name="edit_id" value="<?= $editCrop['crop_id'] ?? 0 ?>">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-flower1 me-2"></i><?= $editCrop ? 'Edit Crop Details' : 'Add New Crop Entry' ?>
          </h5>
          <?php if ($editCrop): ?>
            <a href="<?= BASE_URL ?>/admin/crops.php" class="btn-close btn-close-white"></a>
          <?php else: ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          <?php endif; ?>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Crop Name *</label>
              <input type="text" name="crop_name" class="form-control" required value="<?= e($editCrop['crop_name'] ?? '') ?>" placeholder="e.g. Cotton (कापूस)">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Season *</label>
              <select name="season" class="form-select" required>
                <?php foreach (['Kharif', 'Rabi', 'Zaid', 'Annual'] as $sn): ?>
                  <option value="<?= $sn ?>" <?= ($editCrop['season'] ?? '') === $sn ? 'selected' : '' ?>><?= $sn ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Water Requirement *</label>
              <select name="water_requirement" class="form-select" required>
                <?php foreach (['Low', 'Medium', 'High'] as $wr): ?>
                  <option value="<?= $wr ?>" <?= ($editCrop['water_requirement'] ?? '') === $wr ? 'selected' : '' ?>><?= $wr ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Soil Type *</label>
              <input type="text" name="soil_type" class="form-control" required value="<?= e($editCrop['soil_type'] ?? 'Black Soil, Deep Loam') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Sowing Period *</label>
              <input type="text" name="sowing_period" class="form-control" required value="<?= e($editCrop['sowing_period'] ?? 'June - July') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Harvest Period *</label>
              <input type="text" name="harvest_period" class="form-control" required value="<?= e($editCrop['harvest_period'] ?? 'October - November') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Cultivation Guidance *</label>
              <textarea name="cultivation_info" class="form-control" rows="3" required placeholder="Seed rate, spacing, fertilization, irrigation stages..."><?= e($editCrop['cultivation_info'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Precautions & Field Risk Management</label>
              <textarea name="precautions" class="form-control" rows="2" placeholder="Water stagnation warning, pest scouting schedule..."><?= e($editCrop['precautions'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a href="<?= BASE_URL ?>/admin/crops.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-agro-primary">Save Crop Details</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
