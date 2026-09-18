<?php
/**
 * AgroSmart - Multi-Role Registration Portal (Farmer & Buyer)
 */
$pageTitle = 'Register – AgroSmart';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$activeTab = $_GET['tab'] ?? 'farmer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['reg_role'] ?? 'farmer';
    $activeTab = $role;
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = 'Security token expired. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
            $error = 'Please fill out all required general fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Password and Confirm Password do not match.';
        } else {
            $pdo = getDBConnection();
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = 'This email address is already registered. Please login instead.';
            } else {
                $pdo->beginTransaction();
                try {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $userStmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
                    $userStmt->execute([$name, $email, $mobile, $hashedPassword, $role]);
                    $userId = $pdo->lastInsertId();

                    if ($role === 'farmer') {
                        $village = trim($_POST['village'] ?? '');
                        $district = trim($_POST['district'] ?? 'Pune');
                        $state = trim($_POST['state'] ?? 'Maharashtra');
                        $landArea = floatval($_POST['land_area'] ?? 1.0);
                        $mainCrop = trim($_POST['main_crop'] ?? '');
                        $soilType = trim($_POST['soil_type'] ?? 'Black Soil');
                        $waterAvailability = trim($_POST['water_availability'] ?? 'Well/Borewell');

                        $farmerStmt = $pdo->prepare("INSERT INTO farmers (user_id, village, district, state, land_area, main_crop, soil_type, water_availability) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $farmerStmt->execute([$userId, $village, $district, $state, $landArea, $mainCrop, $soilType, $waterAvailability]);
                    } elseif ($role === 'buyer') {
                        $businessName = trim($_POST['business_name'] ?? '');
                        $address = trim($_POST['address'] ?? '');
                        $district = trim($_POST['district'] ?? 'Pune');
                        $state = trim($_POST['state'] ?? 'Maharashtra');

                        $buyerStmt = $pdo->prepare("INSERT INTO buyers (user_id, business_name, address, district, state) VALUES (?, ?, ?, ?, ?)");
                        $buyerStmt->execute([$userId, $businessName, $address, $district, $state]);
                    }

                    $pdo->commit();
                    setFlash('success', 'Registration successful! You can now login with your credentials.');
                    header("Location: " . BASE_URL . "/login.php");
                    exit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Registration error: " . $e->getMessage());
                    $error = 'An error occurred during registration. Please check your data and retry.';
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-4">
  <div class="col-lg-8">
    <div class="card card-agro shadow">
      <div class="card-agro-header text-center py-3">
        <h4 class="mb-1 text-success"><i class="bi bi-person-plus me-2"></i>Create an AgroSmart Account</h4>
        <small class="text-muted">Register as a Farmer or Agricultural Produce Buyer</small>
      </div>

      <div class="card-body p-4">
        <?php if ($error): ?>
          <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?>
          </div>
        <?php endif; ?>

        <!-- Role Select Tabs -->
        <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3" id="registerTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold <?= $activeTab === 'farmer' ? 'active bg-success' : 'text-dark' ?>" id="farmer-tab" data-bs-toggle="pill" data-bs-target="#farmer-pane" type="button" role="tab">
              <i class="bi bi-flower1 me-2"></i>Register as Farmer (शेतकरी)
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold <?= $activeTab === 'buyer' ? 'active bg-primary' : 'text-dark' ?>" id="buyer-tab" data-bs-toggle="pill" data-bs-target="#buyer-pane" type="button" role="tab">
              <i class="bi bi-shop me-2"></i>Register as Buyer (खरेदीदार)
            </button>
          </li>
        </ul>

        <div class="tab-content" id="registerTabContent">
          <!-- ================= FARMER REGISTRATION ================= -->
          <div class="tab-pane fade <?= $activeTab === 'farmer' ? 'show active' : '' ?>" id="farmer-pane" role="tabpanel">
            <form action="<?= BASE_URL ?>/register.php?tab=farmer" method="POST">
              <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
              <input type="hidden" name="reg_role" value="farmer">

              <h6 class="fw-bold text-success border-bottom pb-2 mb-3">1. Personal & Account Details</h6>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Full Name *</label>
                  <input type="text" name="name" class="form-control" required placeholder="e.g. Ramesh Tukaram Patil" value="<?= e($_POST['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Mobile Number *</label>
                  <input type="tel" name="mobile" class="form-control" required placeholder="10-digit mobile" pattern="[0-9]{10}" value="<?= e($_POST['mobile'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email Address *</label>
                  <input type="email" name="email" class="form-control" required placeholder="farmer@example.com" value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Password *</label>
                  <input type="password" name="password" class="form-control" required placeholder="At least 6 chars">
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Confirm Password *</label>
                  <input type="password" name="confirm_password" class="form-control" required placeholder="Re-type password">
                </div>
              </div>

              <h6 class="fw-bold text-success border-bottom pb-2 mb-3">2. Agricultural & Land Information</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Village *</label>
                  <input type="text" name="village" class="form-control" required placeholder="Village / Town" value="<?= e($_POST['village'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">District *</label>
                  <input type="text" name="district" class="form-control" required placeholder="District (e.g. Pune)" value="<?= e($_POST['district'] ?? 'Pune') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">State</label>
                  <input type="text" name="state" class="form-control" value="Maharashtra" readonly>
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Land Area (Acres) *</label>
                  <input type="number" step="0.1" name="land_area" class="form-control" required min="0.1" value="<?= e($_POST['land_area'] ?? '4.0') ?>">
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Main Crop *</label>
                  <input type="text" name="main_crop" class="form-control" required placeholder="e.g. Soybean, Cotton" value="<?= e($_POST['main_crop'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Soil Type *</label>
                  <select name="soil_type" class="form-select" required>
                    <option value="Black Soil">Black Soil (काळी माती)</option>
                    <option value="Loamy Soil">Loamy Soil (गाळाची माती)</option>
                    <option value="Red Soil">Red Soil (तांबडी माती)</option>
                    <option value="Sandy Loam">Sandy Loam (वाळूमिश्रित)</option>
                    <option value="Clay Loam">Clay Loam (चिकणमाती)</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Water Availability *</label>
                  <select name="water_availability" class="form-select" required>
                    <option value="Rainfed">Rainfed (पावसावर अवलंबून)</option>
                    <option value="Canal">Canal (कालवा पाणी)</option>
                    <option value="Well/Borewell">Well / Borewell (विहीर/बोअरवेल)</option>
                    <option value="Drip Irrigation">Drip Irrigation (ठिबक सिंचन)</option>
                  </select>
                </div>
              </div>

              <button type="submit" class="btn btn-agro-primary w-100 py-2 fs-6">
                <i class="bi bi-check2-circle me-2"></i>Complete Farmer Registration
              </button>
            </form>
          </div>

          <!-- ================= BUYER REGISTRATION ================= -->
          <div class="tab-pane fade <?= $activeTab === 'buyer' ? 'show active' : '' ?>" id="buyer-pane" role="tabpanel">
            <form action="<?= BASE_URL ?>/register.php?tab=buyer" method="POST">
              <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
              <input type="hidden" name="reg_role" value="buyer">

              <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Trader / Business Details</h6>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Contact Person Name *</label>
                  <input type="text" name="name" class="form-control" required placeholder="Full Name" value="<?= e($_POST['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Business / Firm Name *</label>
                  <input type="text" name="business_name" class="form-control" required placeholder="e.g. Maha Agro Wholesalers" value="<?= e($_POST['business_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Mobile Number *</label>
                  <input type="tel" name="mobile" class="form-control" required pattern="[0-9]{10}" placeholder="10-digit mobile" value="<?= e($_POST['mobile'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email Address *</label>
                  <input type="email" name="email" class="form-control" required placeholder="buyer@example.com" value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Password *</label>
                  <input type="password" name="password" class="form-control" required placeholder="At least 6 chars">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Confirm Password *</label>
                  <input type="password" name="confirm_password" class="form-control" required placeholder="Re-type password">
                </div>
              </div>

              <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Location & Commercial Address</h6>
              <div class="row g-3 mb-4">
                <div class="col-12">
                  <label class="form-label fw-semibold">Business / APMC Stall Address *</label>
                  <textarea name="address" class="form-control" rows="2" required placeholder="Shop number, APMC Market Yard, Road"><?= e($_POST['address'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">District *</label>
                  <input type="text" name="district" class="form-control" required placeholder="e.g. Pune, Nashik" value="<?= e($_POST['district'] ?? 'Pune') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">State</label>
                  <input type="text" name="state" class="form-control" value="Maharashtra" readonly>
                </div>
              </div>

              <button type="submit" class="btn btn-primary w-100 py-2 fs-6">
                <i class="bi bi-check2-circle me-2"></i>Complete Buyer Registration
              </button>
            </form>
          </div>
        </div>

        <div class="text-center mt-4 small">
          Already registered? <a href="<?= BASE_URL ?>/login.php" class="text-success fw-bold">Sign In Here</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
