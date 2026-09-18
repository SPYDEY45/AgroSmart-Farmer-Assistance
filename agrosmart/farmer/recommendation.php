<?php
/**
 * AgroSmart - Smart Crop Recommendation Engine
 * Educational rule-based recommendation system based on soil, climate, water, and acreage.
 */
$pageTitle = 'Smart Crop Recommendation – AgroSmart';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

// Prefill from logged-in farmer if available
$defaultSoil = 'Black Soil';
$defaultWater = 'Well/Borewell';
$defaultArea = '3.0';
$defaultDistrict = 'Pune';

if (isset($_SESSION['farmer_id'])) {
    $pdo = getDBConnection();
    $fStmt = $pdo->prepare("SELECT soil_type, water_availability, land_area, district FROM farmers WHERE farmer_id = ?");
    $fStmt->execute([$_SESSION['farmer_id']]);
    $fData = $fStmt->fetch();
    if ($fData) {
        $defaultSoil = $fData['soil_type'];
        $defaultWater = $fData['water_availability'];
        $defaultArea = $fData['land_area'];
        $defaultDistrict = $fData['district'];
    }
}

$results = null;
$season = $_POST['season'] ?? 'Kharif';
$soil = $_POST['soil_type'] ?? $defaultSoil;
$water = $_POST['water_availability'] ?? $defaultWater;
$area = floatval($_POST['land_area'] ?? $defaultArea);
$district = $_POST['district'] ?? $defaultDistrict;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $results = recommendCrops($season, $soil, $water, $area);
}
?>

<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="card card-agro shadow mb-4">
      <div class="card-agro-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-success"><i class="bi bi-cpu me-2"></i>Smart Crop Recommendation Engine</h4>
        <span class="badge bg-warning text-dark"><i class="bi bi-mortarboard me-1"></i>BCA Agronomy Algorithm</span>
      </div>

      <div class="card-body p-4">
        <!-- Mandatory Educational Notice (Specified in requirements) -->
        <div class="agro-notice-box">
          <div class="d-flex">
            <i class="bi bi-exclamation-octagon-fill text-warning fs-4 me-3"></i>
            <div>
              <strong><?= __('disclaimer_title') ?>:</strong>
              <p class="mb-0 mt-1"><?= __('recommendation_notice') ?></p>
            </div>
          </div>
        </div>

        <form action="<?= BASE_URL ?>/farmer/recommendation.php" method="POST" class="mb-4">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Cultivation Season *</label>
              <select name="season" class="form-select" required>
                <option value="Kharif" <?= $season === 'Kharif' ? 'selected' : '' ?>>Kharif (Monsoon / जून-ऑक्टोबर)</option>
                <option value="Rabi" <?= $season === 'Rabi' ? 'selected' : '' ?>>Rabi (Winter / ऑक्टोबर-मार्च)</option>
                <option value="Zaid" <?= $season === 'Zaid' ? 'selected' : '' ?>>Zaid / Summer (उन्हाळी)</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Soil Type *</label>
              <select name="soil_type" class="form-select" required>
                <option value="Black Soil" <?= $soil === 'Black Soil' ? 'selected' : '' ?>>Black Soil (काळी माती)</option>
                <option value="Loamy Soil" <?= $soil === 'Loamy Soil' ? 'selected' : '' ?>>Loamy Soil (गाळाची माती)</option>
                <option value="Red Soil" <?= $soil === 'Red Soil' ? 'selected' : '' ?>>Red Soil (तांबडी माती)</option>
                <option value="Sandy Loam" <?= $soil === 'Sandy Loam' ? 'selected' : '' ?>>Sandy Loam (वाळूमिश्रित माती)</option>
                <option value="Clay Loam" <?= $soil === 'Clay Loam' ? 'selected' : '' ?>>Clay Loam (चिकणमाती)</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Water Irrigation Source *</label>
              <select name="water_availability" class="form-select" required>
                <option value="Rainfed" <?= $water === 'Rainfed' ? 'selected' : '' ?>>Rainfed (पावसावर अवलंबून)</option>
                <option value="Canal" <?= $water === 'Canal' ? 'selected' : '' ?>>Canal (कालवा / सिंचन)</option>
                <option value="Well/Borewell" <?= $water === 'Well/Borewell' ? 'selected' : '' ?>>Well / Borewell (विहीर / कूपनलिका)</option>
                <option value="Drip Irrigation" <?= $water === 'Drip Irrigation' ? 'selected' : '' ?>>Drip Irrigation (ठिबक सिंचन)</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label fw-semibold">Planned Land Area (Acres) *</label>
              <input type="number" step="0.1" name="land_area" class="form-control" required min="0.1" value="<?= e($area) ?>">
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold">Location / District</label>
              <input type="text" name="district" class="form-control" value="<?= e($district) ?>" placeholder="e.g. Pune, Latur, Solapur">
            </div>

            <div class="col-12 mt-3">
              <button type="submit" class="btn btn-agro-primary btn-lg w-100 fw-bold">
                <i class="bi bi-stars me-2"></i>Analyze & Generate Crop Recommendations
              </button>
            </div>
          </div>
        </form>

        <!-- Recommendation Output -->
        <?php if ($results !== null): ?>
          <hr class="my-4">
          <h5 class="fw-bold text-success mb-3">
            <i class="bi bi-check2-circle me-2"></i>Recommended Crops for <?= e($season) ?> Season (<?= e($soil) ?>)
          </h5>

          <?php if (empty($results)): ?>
            <div class="alert alert-info">
              No matching crops found for this specific combination. Consider mixed pulse farming or consulting an agriculture expert.
            </div>
          <?php else: ?>
            <div class="row g-4">
              <?php foreach ($results as $r): ?>
                <div class="col-md-6">
                  <div class="card card-agro h-100 p-3 border-start border-success border-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h5 class="fw-bold text-success mb-0"><?= e($r['crop']) ?></h5>
                      <span class="badge bg-success"><?= e($r['suitability']) ?> Suitability</span>
                    </div>
                    <p class="small text-dark mb-2"><strong>Agronomic Reason:</strong> <?= e($r['reason']) ?></p>
                    <div class="p-2 bg-light rounded small mb-2">
                      <span class="text-muted d-block">Estimated Yield for <?= e($area) ?> Acres:</span>
                      <strong class="text-primary fs-6"><?= e($r['expected_yield']) ?></strong>
                    </div>
                    <p class="small text-muted mb-0"><strong>Recommended Practice:</strong> <?= e($r['ideal_practices']) ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
