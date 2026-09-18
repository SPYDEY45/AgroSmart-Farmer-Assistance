<?php
/**
 * AgroSmart - Admin Agriculture Experts Management
 */
$pageTitle = 'Manage Experts – AgroSmart Admin';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);
$error = '';

// Handle Add Expert POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expert'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? 'password123';
    $specialization = trim($_POST['specialization'] ?? 'Agronomy');
    $qualification = trim($_POST['qualification'] ?? 'M.Sc. Agriculture');
    $experience = intval($_POST['experience_years'] ?? 5);

    if (empty($name) || empty($email) || empty($mobile)) {
        $error = 'Please fill out all required fields for the expert.';
    } else {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'An account with this email address already exists.';
        } else {
            try {
                $pdo->beginTransaction();
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $uStmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password, role, status) VALUES (?, ?, ?, ?, 'expert', 'active')");
                $uStmt->execute([$name, $email, $mobile, $hashed]);
                $userId = $pdo->lastInsertId();

                $eStmt = $pdo->prepare("INSERT INTO experts (user_id, specialization, qualification, experience_years) VALUES (?, ?, ?, ?)");
                $eStmt->execute([$userId, $specialization, $qualification, $experience]);

                $pdo->commit();
                setFlash('success', 'Agriculture expert registered successfully.');
                header("Location: " . BASE_URL . "/admin/experts.php");
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Failed to register expert: ' . $e->getMessage();
            }
        }
    }
}

// Handle Status Toggle
if ($action === 'toggle' && $id > 0) {
    $uStmt = $pdo->prepare("SELECT u.user_id, u.status FROM users u JOIN experts e ON u.user_id = e.user_id WHERE e.expert_id = ?");
    $uStmt->execute([$id]);
    $u = $uStmt->fetch();
    if ($u) {
        $newStatus = $u['status'] === 'active' ? 'blocked' : 'active';
        $upd = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $upd->execute([$newStatus, $u['user_id']]);
        setFlash('success', "Expert account status set to {$newStatus}.");
    }
    header("Location: " . BASE_URL . "/admin/experts.php");
    exit();
}

// Handle Delete
if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE u FROM users u JOIN experts e ON u.user_id = e.user_id WHERE e.expert_id = ?");
    $del->execute([$id]);
    setFlash('success', 'Expert profile and credentials deleted.');
    header("Location: " . BASE_URL . "/admin/experts.php");
    exit();
}

// Fetch experts
$query = "SELECT e.*, u.name, u.email, u.mobile, u.status, (SELECT COUNT(*) FROM expert_questions eq WHERE eq.expert_id = e.expert_id AND eq.status = 'Answered') as answers_count FROM experts e JOIN users u ON e.user_id = u.user_id ORDER BY e.expert_id DESC";
$experts = $pdo->query($query)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-mortarboard me-2"></i>Agriculture Advisory Experts Panel</h2>
    <p class="text-muted mb-0">Manage certified agronomists, plant pathologists, and soil science specialists</p>
  </div>
  <div class="mt-3 mt-md-0">
    <button type="button" class="btn btn-agro-primary" data-bs-toggle="modal" data-bs-target="#addExpertModal">
      <i class="bi bi-plus-circle me-1"></i>Onboard New Expert
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
            <th>Expert Name & Contact</th>
            <th>Specialization</th>
            <th>Degrees / Qualification</th>
            <th>Experience</th>
            <th>Answers Delivered</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($experts as $exp): ?>
            <tr>
              <td>
                <strong class="text-dark"><?= e($exp['name']) ?></strong>
                <div class="small text-muted"><i class="bi bi-envelope"></i> <?= e($exp['email']) ?></div>
                <div class="small text-success"><i class="bi bi-telephone"></i> <?= e($exp['mobile']) ?></div>
              </td>
              <td><span class="badge bg-success-subtle text-success border border-success"><?= e($exp['specialization']) ?></span></td>
              <td><?= e($exp['qualification']) ?></td>
              <td><strong><?= e($exp['experience_years']) ?> Years</strong></td>
              <td>
                <span class="badge bg-primary"><?= $exp['answers_count'] ?> Answers</span>
              </td>
              <td>
                <?php if ($exp['status'] === 'active'): ?>
                  <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                <?php else: ?>
                  <span class="badge bg-danger"><i class="bi bi-slash-circle me-1"></i>Blocked</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a href="<?= BASE_URL ?>/admin/experts.php?action=toggle&id=<?= $exp['expert_id'] ?>" class="btn btn-sm <?= $exp['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?> me-1">
                  <?= $exp['status'] === 'active' ? 'Block' : 'Unblock' ?>
                </a>
                <a href="<?= BASE_URL ?>/admin/experts.php?action=delete&id=<?= $exp['expert_id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-entity="expert account">
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

<!-- Add Expert Modal -->
<div class="modal fade" id="addExpertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/admin/experts.php" method="POST">
        <input type="hidden" name="add_expert" value="1">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Onboard Certified Expert</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Doctor / Expert Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="e.g. Dr. Anand Deshmukh">
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Email Address *</label>
              <input type="email" name="email" class="form-control" required placeholder="expert@agrosmart.com">
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Mobile Number *</label>
              <input type="tel" name="mobile" class="form-control" required pattern="[0-9]{10}" placeholder="10-digit mobile">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Temporary Password</label>
            <input type="password" name="password" class="form-control" value="password123" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Agronomic Specialization *</label>
            <select name="specialization" class="form-select" required>
              <option value="Agronomy">Agronomy & Crop Management</option>
              <option value="Plant Pathology">Plant Pathology (Disease Doctor)</option>
              <option value="Entomology">Entomology (Pest & Insect Control)</option>
              <option value="Soil Science">Soil Science & Nutrient Management</option>
              <option value="Horticulture">Horticulture (Fruits & Vegetables)</option>
            </select>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-8">
              <label class="form-label fw-semibold">Degrees / Qualifications *</label>
              <input type="text" name="qualification" class="form-control" required placeholder="e.g. Ph.D. Agronomy / M.Sc. Agriculture">
            </div>
            <div class="col-4">
              <label class="form-label fw-semibold">Experience (Yrs) *</label>
              <input type="number" name="experience_years" class="form-control" required min="1" value="5">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-agro-primary">Create Expert Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
