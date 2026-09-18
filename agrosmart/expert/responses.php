<?php
/**
 * AgroSmart - Expert Answer History
 */
$pageTitle = 'Answer History – AgroSmart';
require_once __DIR__ . '/../includes/auth.php';

if ($_SESSION['role'] !== 'expert') {
    setFlash('danger', 'Unauthorized access.');
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];
$expStmt = $pdo->prepare("SELECT expert_id FROM experts WHERE user_id = ?");
$expStmt->execute([$userId]);
$expertId = $expStmt->fetchColumn() ?: 1;

$stmt = $pdo->prepare("SELECT eq.*, c.crop_name, u.name as farmer_name, f.village, f.district FROM expert_questions eq JOIN crops c ON eq.crop_id = c.crop_id JOIN farmers f ON eq.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE eq.expert_id = ? AND eq.status = 'Answered' ORDER BY eq.answered_at DESC");
$stmt->execute([$expertId]);
$answered = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-clock-history me-2"></i>My Consultations History</h2>
    <p class="text-muted mb-0">Record of agricultural advisories delivered to registered farmers</p>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?= BASE_URL ?>/expert/questions.php" class="btn btn-agro-primary">
      <i class="bi bi-question-circle me-1"></i>Check Pending Queue
    </a>
  </div>
</div>

<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($answered)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        <h5>No answered questions in history yet.</h5>
      </div>
    <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($answered as $a): ?>
          <div class="list-group-item p-4">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <span class="badge bg-success-subtle text-success me-2">Crop: <?= e($a['crop_name']) ?></span>
                <span class="small text-muted">Farmer: <strong><?= e($a['farmer_name']) ?></strong> (<?= e($a['village']) ?>, <?= e($a['district']) ?>)</span>
              </div>
              <small class="text-muted">Answered on: <?= date('d M Y, h:i A', strtotime($a['answered_at'])) ?></small>
            </div>

            <h5 class="fw-bold text-dark mb-1"><?= e($a['subject']) ?></h5>
            <p class="text-muted small mb-3"><?= e($a['question']) ?></p>

            <div class="p-3 bg-light border-start border-success border-4 rounded">
              <strong class="text-success small d-block mb-1"><i class="bi bi-check2-circle me-1"></i>My Advisory:</strong>
              <div class="small text-dark" style="white-space: pre-line;"><?= e($a['answer']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
