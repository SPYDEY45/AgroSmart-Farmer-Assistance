<?php
/**
 * AgroSmart - Agriculture Expert Profile
 */
$pageTitle = 'Expert Profile – AgroSmart';
require_once __DIR__ . '/../includes/auth.php';

if ($_SESSION['role'] !== 'expert') {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $experience = intval($_POST['experience_years'] ?? 0);

    if (empty($name) || empty($mobile) || empty($specialization)) {
        $error = 'Please fill out all required fields.';
    } else {
        try {
            $pdo->beginTransaction();
            $uStmt = $pdo->prepare("UPDATE users SET name = ?, mobile = ? WHERE user_id = ?");
            $uStmt->execute([$name, $mobile, $userId]);
            $_SESSION['name'] = $name;

            $eStmt = $pdo->prepare("UPDATE experts SET specialization = ?, qualification = ?, experience_years = ? WHERE user_id = ?");
            $eStmt->execute([$specialization, $qualification, $experience, $userId]);

            $pdo->commit();
            $success = 'Expert profile updated successfully.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to update profile: ' . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("SELECT u.name, u.email, u.mobile, e.* FROM users u JOIN experts e ON u.user_id = e.user_id WHERE u.user_id = ?");
$stmt->execute([$userId]);
$expert = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card card-agro shadow">
      <div class="card-agro-header">
        <h4 class="mb-0 text-success"><i class="bi bi-mortarboard me-2"></i>Agronomist & Scientist Profile</h4>
      </div>
      <div class="card-body p-4">
        <?php if ($success): ?>
          <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/expert/profile.php" method="POST">
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Doctor / Agronomist Name *</label>
              <input type="text" name="name" class="form-control" required value="<?= e($expert['name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email (Official Account)</label>
              <input type="email" class="form-control" readonly disabled value="<?= e($expert['email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Mobile Number *</label>
              <input type="tel" name="mobile" class="form-control" required pattern="[0-9]{10}" value="<?= e($expert['mobile'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Specialization *</label>
              <input type="text" name="specialization" class="form-control" required value="<?= e($expert['specialization'] ?? '') ?>">
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Degrees & Qualifications</label>
              <input type="text" name="qualification" class="form-control" value="<?= e($expert['qualification'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Experience (Years)</label>
              <input type="number" name="experience_years" class="form-control" value="<?= e($expert['experience_years'] ?? 5) ?>">
            </div>
          </div>

          <button type="submit" class="btn btn-agro-primary px-4"><i class="bi bi-save me-2"></i>Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
