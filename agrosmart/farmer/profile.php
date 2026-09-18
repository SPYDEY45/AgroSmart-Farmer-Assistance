<?php
/**
 * AgroSmart - Farmer Profile Management
 */
$pageTitle = 'Farmer Profile – AgroSmart';
require_once __DIR__ . '/../includes/farmer_auth.php';

$pdo = getDBConnection();
$farmerId = $_SESSION['farmer_id'];
$userId = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $village = trim($_POST['village'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $landArea = floatval($_POST['land_area'] ?? 1.0);
    $mainCrop = trim($_POST['main_crop'] ?? '');
    $soilType = trim($_POST['soil_type'] ?? 'Black Soil');
    $waterAvailability = trim($_POST['water_availability'] ?? 'Well/Borewell');

    if (empty($name) || empty($mobile) || empty($village)) {
        $error = 'Please fill out all required fields.';
    } else {
        try {
            $pdo->beginTransaction();
            // Update User
            $uStmt = $pdo->prepare("UPDATE users SET name = ?, mobile = ? WHERE user_id = ?");
            $uStmt->execute([$name, $mobile, $userId]);
            $_SESSION['name'] = $name;
            $_SESSION['mobile'] = $mobile;

            // Update Farmer Profile
            $fStmt = $pdo->prepare("UPDATE farmers SET village = ?, district = ?, land_area = ?, main_crop = ?, soil_type = ?, water_availability = ? WHERE farmer_id = ?");
            $fStmt->execute([$village, $district, $landArea, $mainCrop, $soilType, $waterAvailability, $farmerId]);
            $_SESSION['village'] = $village;
            $_SESSION['district'] = $district;

            $pdo->commit();
            $success = 'Profile updated successfully!';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Fetch current details
$stmt = $pdo->prepare("SELECT u.name, u.email, u.mobile, f.* FROM users u JOIN farmers f ON u.user_id = f.user_id WHERE f.farmer_id = ?");
$stmt->execute([$farmerId]);
$farmer = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card card-agro shadow">
      <div class="card-agro-header">
        <h4 class="mb-0 text-success"><i class="bi bi-person-lines-fill me-2"></i>Farmer Profile Settings</h4>
      </div>
      <div class="card-body p-4">
        <?php if ($success): ?>
          <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/farmer/profile.php" method="POST">
          <h6 class="fw-bold text-success border-bottom pb-2 mb-3">Personal & Contact Info</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Full Name</label>
              <input type="text" name="name" class="form-control" required value="<?= e($farmer['name']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email Address (Registered)</label>
              <input type="email" class="form-control" readonly disabled value="<?= e($farmer['email']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Mobile Number</label>
              <input type="tel" name="mobile" class="form-control" required pattern="[0-9]{10}" value="<?= e($farmer['mobile']) ?>">
            </div>
          </div>

          <h6 class="fw-bold text-success border-bottom pb-2 mb-3">Farm & Agronomy Details</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Village / Gram Panchayat</label>
              <input type="text" name="village" class="form-control" required value="<?= e($farmer['village']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">District</label>
              <input type="text" name="district" class="form-control" required value="<?= e($farmer['district']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Total Cultivable Area (Acres)</label>
              <input type="number" step="0.1" name="land_area" class="form-control" required value="<?= e($farmer['land_area']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Main Cash Crop</label>
              <input type="text" name="main_crop" class="form-control" required value="<?= e($farmer['main_crop']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Primary Soil Type</label>
              <select name="soil_type" class="form-select" required>
                <?php foreach (['Black Soil', 'Loamy Soil', 'Red Soil', 'Sandy Loam', 'Clay Loam'] as $st): ?>
                  <option value="<?= $st ?>" <?= $farmer['soil_type'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Water Irrigation Source</label>
              <select name="water_availability" class="form-select" required>
                <?php foreach (['Rainfed', 'Canal', 'Well/Borewell', 'Drip Irrigation'] as $wa): ?>
                  <option value="<?= $wa ?>" <?= $farmer['water_availability'] === $wa ? 'selected' : '' ?>><?= $wa ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-agro-primary px-4"><i class="bi bi-save me-2"></i>Save Profile Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
