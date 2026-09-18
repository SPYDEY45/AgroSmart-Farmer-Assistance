<?php
/**
 * AgroSmart - Buyer Business Profile
 */
$pageTitle = 'Buyer Profile – AgroSmart';
require_once __DIR__ . '/../includes/buyer_auth.php';

$pdo = getDBConnection();
$buyerId = $_SESSION['buyer_id'];
$userId = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $businessName = trim($_POST['business_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $district = trim($_POST['district'] ?? '');

    if (empty($name) || empty($mobile) || empty($businessName)) {
        $error = 'Please fill out all required business and contact fields.';
    } else {
        try {
            $pdo->beginTransaction();
            $uStmt = $pdo->prepare("UPDATE users SET name = ?, mobile = ? WHERE user_id = ?");
            $uStmt->execute([$name, $mobile, $userId]);
            $_SESSION['name'] = $name;

            $bStmt = $pdo->prepare("UPDATE buyers SET business_name = ?, address = ?, district = ? WHERE buyer_id = ?");
            $bStmt->execute([$businessName, $address, $district, $buyerId]);
            $_SESSION['business_name'] = $businessName;

            $pdo->commit();
            $success = 'Business profile updated successfully!';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to update business profile.';
        }
    }
}

// Fetch current details
$stmt = $pdo->prepare("SELECT u.name, u.email, u.mobile, b.* FROM users u JOIN buyers b ON u.user_id = b.user_id WHERE b.buyer_id = ?");
$stmt->execute([$buyerId]);
$buyer = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card card-agro shadow">
      <div class="card-agro-header">
        <h4 class="mb-0 text-primary"><i class="bi bi-building me-2"></i>Buyer Commercial Profile</h4>
      </div>
      <div class="card-body p-4">
        <?php if ($success): ?>
          <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/buyer/profile.php" method="POST">
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Contact Representative Name *</label>
              <input type="text" name="name" class="form-control" required value="<?= e($buyer['name']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email (Permanent Account)</label>
              <input type="email" class="form-control" readonly disabled value="<?= e($buyer['email']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Mobile Number *</label>
              <input type="tel" name="mobile" class="form-control" required pattern="[0-9]{10}" value="<?= e($buyer['mobile']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Business / Firm Name *</label>
              <input type="text" name="business_name" class="form-control" required value="<?= e($buyer['business_name']) ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Commercial Mandi / Warehouse Address</label>
              <textarea name="address" class="form-control" rows="2"><?= e($buyer['address']) ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">District</label>
              <input type="text" name="district" class="form-control" required value="<?= e($buyer['district']) ?>">
            </div>
          </div>

          <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i>Update Profile</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
