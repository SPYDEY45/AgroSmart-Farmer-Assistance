<?php
/**
 * AgroSmart - Farmer's Personal Crop Cultivations
 */
$pageTitle = 'My Crops – AgroSmart';
require_once __DIR__ . '/../includes/farmer_auth.php';

$pdo = getDBConnection();
$farmerId = $_SESSION['farmer_id'];
$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Handle Delete
if ($action === 'delete' && isset($_GET['id'])) {
    $delId = intval($_GET['id']);
    $delStmt = $pdo->prepare("DELETE FROM farmer_crops WHERE id = ? AND farmer_id = ?");
    $delStmt->execute([$delId, $farmerId]);
    setFlash('success', 'Crop record removed successfully.');
    header("Location: " . BASE_URL . "/farmer/my_crops.php");
    exit();
}

// Handle Add / Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropId = intval($_POST['crop_id'] ?? 0);
    $area = floatval($_POST['area'] ?? 1.0);
    $sowingDate = $_POST['sowing_date'] ?? '';
    $harvestDate = $_POST['expected_harvest_date'] ?? '';
    $status = $_POST['status'] ?? 'Sown';
    $editId = intval($_POST['edit_id'] ?? 0);

    if ($cropId <= 0 || empty($sowingDate) || empty($harvestDate) || $area <= 0) {
        $error = 'Please fill out all crop cultivation fields with valid values.';
    } else {
        if ($editId > 0) {
            $stmt = $pdo->prepare("UPDATE farmer_crops SET crop_id = ?, area = ?, sowing_date = ?, expected_harvest_date = ?, status = ? WHERE id = ? AND farmer_id = ?");
            $stmt->execute([$cropId, $area, $sowingDate, $harvestDate, $status, $editId, $farmerId]);
            setFlash('success', 'Cultivation details updated successfully.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO farmer_crops (farmer_id, crop_id, area, sowing_date, expected_harvest_date, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$farmerId, $cropId, $area, $sowingDate, $harvestDate, $status]);
            setFlash('success', 'New crop cultivation added to your active farm list.');
        }
        header("Location: " . BASE_URL . "/farmer/my_crops.php");
        exit();
    }
}

// Fetch all system crops for dropdown
$cropsList = $pdo->query("SELECT crop_id, crop_name, season FROM crops ORDER BY crop_name ASC")->fetchAll();

// Fetch farmer's cultivated crops
$myCropsStmt = $pdo->prepare("SELECT fc.*, c.crop_name, c.season, c.water_requirement FROM farmer_crops fc JOIN crops c ON fc.crop_id = c.crop_id WHERE fc.farmer_id = ? ORDER BY fc.sowing_date DESC");
$myCropsStmt->execute([$farmerId]);
$myCrops = $myCropsStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-flower1 me-2"></i>My Farm Cultivations</h2>
    <p class="text-muted mb-0">Track active field acreage, crop cycles, and expected harvest timelines</p>
  </div>
  <div class="mt-3 mt-md-0">
    <button type="button" class="btn btn-agro-primary" data-bs-toggle="modal" data-bs-target="#addCropModal">
      <i class="bi bi-plus-circle me-1"></i>Add New Crop Sowing
    </button>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<!-- Crops Table -->
<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($myCrops)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-seedling fs-1 d-block mb-3 text-success"></i>
        <h5>No active crops recorded yet!</h5>
        <p class="small">Record your current season crops to receive timely disease alerts, weather tips, and harvest advisory.</p>
        <button type="button" class="btn btn-sm btn-agro-primary mt-2" data-bs-toggle="modal" data-bs-target="#addCropModal">
          <i class="bi bi-plus-lg me-1"></i>Record Sowing Now
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Crop Name</th>
              <th>Season</th>
              <th>Area (Acres)</th>
              <th>Sowing Date</th>
              <th>Expected Harvest</th>
              <th>Growth Stage</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($myCrops as $mc): ?>
              <tr>
                <td>
                  <strong class="text-success"><?= e($mc['crop_name']) ?></strong>
                </td>
                <td><span class="badge bg-light text-dark border"><?= e($mc['season']) ?></span></td>
                <td><?= e($mc['area']) ?> Acres</td>
                <td><?= e($mc['sowing_date']) ?></td>
                <td><?= e($mc['expected_harvest_date']) ?></td>
                <td>
                  <span class="badge <?= $mc['status'] === 'Harvested' ? 'bg-secondary' : 'bg-success' ?>">
                    <?= e($mc['status']) ?>
                  </span>
                </td>
                <td class="text-end">
                  <a href="<?= BASE_URL ?>/farmer/diseases.php?crop_id=<?= $mc['crop_id'] ?>" class="btn btn-sm btn-outline-info" title="Check Diseases">
                    <i class="bi bi-bug"></i>
                  </a>
                  <a href="<?= BASE_URL ?>/farmer/my_crops.php?action=delete&id=<?= $mc['id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="crop cultivation">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Crop Modal -->
<div class="modal fade" id="addCropModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/farmer/my_crops.php" method="POST">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Record Farm Sowing</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Select Crop *</label>
            <select name="crop_id" class="form-select" required>
              <option value="">-- Choose Crop --</option>
              <?php foreach ($cropsList as $cl): ?>
                <option value="<?= $cl['crop_id'] ?>" <?= (isset($_GET['crop_id']) && $_GET['crop_id'] == $cl['crop_id']) ? 'selected' : '' ?>>
                  <?= e($cl['crop_name']) ?> (<?= e($cl['season']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Cultivated Area (Acres) *</label>
            <input type="number" step="0.1" name="area" class="form-control" required min="0.1" value="2.0">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Sowing Date *</label>
              <input type="date" name="sowing_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Expected Harvest *</label>
              <input type="date" name="expected_harvest_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+90 days')) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Current Growth Status</label>
            <select name="status" class="form-select">
              <option value="Sown">Sown (पेरणी झाली)</option>
              <option value="Vegetative">Vegetative (वाढीची अवस्था)</option>
              <option value="Flowering">Flowering / Podding (फुलोरा/धारणा)</option>
              <option value="Harvested">Harvested (कापणी झाली)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-agro-primary">Save Cultivation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
