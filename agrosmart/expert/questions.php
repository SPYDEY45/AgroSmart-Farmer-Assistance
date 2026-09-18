<?php
/**
 * AgroSmart - Expert Answering Desk
 */
$pageTitle = 'Farmer Inquiries – AgroSmart';
require_once __DIR__ . '/../includes/auth.php';

if ($_SESSION['role'] !== 'expert') {
    setFlash('danger', 'Unauthorized access.');
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Get expert record
$expStmt = $pdo->prepare("SELECT expert_id FROM experts WHERE user_id = ?");
$expStmt->execute([$userId]);
$expertId = $expStmt->fetchColumn() ?: 1;

// Handle Answer Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'])) {
    $qId = intval($_POST['question_id']);
    $answer = trim($_POST['answer'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlash('danger', 'Security session expired. Please retry.');
    } elseif (empty($answer)) {
        setFlash('warning', 'Please write a descriptive advisory answer for the farmer.');
    } else {
        $stmt = $pdo->prepare("UPDATE expert_questions SET answer = ?, expert_id = ?, status = 'Answered', answered_at = NOW() WHERE question_id = ?");
        $stmt->execute([$answer, $expertId, $qId]);
        setFlash('success', 'Your expert answer has been recorded and sent to the farmer.');
        header("Location: " . BASE_URL . "/expert/questions.php");
        exit();
    }
}

// Fetch questions
$selectedId = intval($_GET['id'] ?? 0);
$query = "SELECT eq.*, c.crop_name, u.name as farmer_name, u.mobile as farmer_mobile, f.village, f.district, f.soil_type FROM expert_questions eq JOIN crops c ON eq.crop_id = c.crop_id JOIN farmers f ON eq.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE eq.status = 'Pending' ORDER BY eq.created_at ASC";
$questions = $pdo->query($query)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-patch-question me-2"></i>Farmer Agronomy Consultation Desk</h2>
    <p class="text-muted mb-0">Provide scientific advisories on crop protection, dosage calculations, and field diagnostics</p>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?= BASE_URL ?>/expert/responses.php" class="btn btn-outline-success">
      <i class="bi bi-clock-history me-1"></i>View My Answered History
    </a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-12">
    <?php if (empty($questions)): ?>
      <div class="card card-agro p-5 text-center text-muted">
        <i class="bi bi-emoji-smile fs-1 mb-2 text-success"></i>
        <h5>No pending questions in queue!</h5>
        <p class="small">All submitted farmer questions have been addressed by the panel.</p>
        <a href="<?= BASE_URL ?>/expert/responses.php" class="btn btn-sm btn-outline-success mt-2">View Answered History</a>
      </div>
    <?php else: ?>
      <?php foreach ($questions as $q): ?>
        <div class="card card-agro shadow-sm mb-4 border-start border-warning border-4 <?= ($selectedId === intval($q['question_id'])) ? 'ring-2 border-primary' : '' ?>">
          <div class="card-agro-header d-flex justify-content-between align-items-center bg-light">
            <div>
              <span class="badge bg-success-subtle text-success me-2">Crop: <?= e($q['crop_name']) ?></span>
              <span class="small text-muted"><i class="bi bi-clock me-1"></i><?= date('d M Y, h:i A', strtotime($q['created_at'])) ?></span>
            </div>
            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Awaiting Response</span>
          </div>

          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-4">
                <small class="text-muted d-block">Farmer Name</small>
                <strong><?= e($q['farmer_name']) ?></strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted d-block">Location & Soil</small>
                <span><?= e($q['village']) ?>, <?= e($q['district']) ?> (<?= e($q['soil_type']) ?>)</span>
              </div>
              <div class="col-md-4">
                <small class="text-muted d-block">Contact Number</small>
                <span><i class="bi bi-telephone text-success"></i> <?= e($q['farmer_mobile']) ?></span>
              </div>
            </div>

            <h5 class="fw-bold text-dark mb-2"><?= e($q['subject']) ?></h5>
            <div class="p-3 bg-light rounded text-secondary small mb-3" style="white-space: pre-line;">
              <?= e($q['question']) ?>
            </div>

            <!-- Response Form -->
            <form action="<?= BASE_URL ?>/expert/questions.php" method="POST">
              <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
              <input type="hidden" name="question_id" value="<?= $q['question_id'] ?>">

              <div class="mb-3">
                <label class="form-label fw-bold text-success"><i class="bi bi-pencil-fill me-1"></i>Your Scientific Advice & Treatment Recommendation *</label>
                <textarea name="answer" class="form-control" rows="3" required placeholder="Write treatment advice, dosage (ml or gm per liter of water), spray precautions, or organic remedies..."></textarea>
              </div>

              <div class="text-end">
                <button type="submit" class="btn btn-agro-primary">
                  <i class="bi bi-send-check me-1"></i>Publish Advisory Answer
                </button>
              </div>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
