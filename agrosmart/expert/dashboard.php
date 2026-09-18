<?php
/**
 * AgroSmart - Agriculture Expert Dashboard
 */
$pageTitle = 'Expert Dashboard – AgroSmart';
require_once __DIR__ . '/../includes/auth.php';

if ($_SESSION['role'] !== 'expert') {
    setFlash('danger', 'Unauthorized access. Expert account required.');
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Get expert record
$expStmt = $pdo->prepare("SELECT expert_id, specialization, qualification, experience_years FROM experts WHERE user_id = ?");
$expStmt->execute([$userId]);
$expert = $expStmt->fetch();
$expertId = $expert['expert_id'] ?? 0;

// Metrics
$pendingQCount = $pdo->query("SELECT COUNT(*) FROM expert_questions WHERE status = 'Pending'")->fetchColumn() ?: 0;
$answeredByMeCount = $pdo->prepare("SELECT COUNT(*) FROM expert_questions WHERE expert_id = ? AND status = 'Answered'");
$answeredByMeCount->execute([$expertId]);
$totalAnswered = $answeredByMeCount->fetchColumn() ?: 0;

// Recent pending questions
$pendingQuestionsStmt = $pdo->query("SELECT eq.*, c.crop_name, u.name as farmer_name, f.village, f.district FROM expert_questions eq JOIN crops c ON eq.crop_id = c.crop_id JOIN farmers f ON eq.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE eq.status = 'Pending' ORDER BY eq.created_at ASC LIMIT 5");
$pendingQuestions = $pendingQuestionsStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Expert Welcome Banner -->
<div class="card bg-success text-white p-4 rounded-4 shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-mortarboard me-2"></i>Welcome, Dr./Prof. <?= e($_SESSION['name']) ?>!</h2>
      <p class="mb-0 text-white-75">
        <i class="bi bi-award me-1"></i>Specialization: <?= e($expert['specialization'] ?? 'Agronomy') ?> | <?= e($expert['qualification'] ?? 'M.Sc. Agriculture') ?> (<?= e($expert['experience_years'] ?? 5) ?>+ yrs exp)
      </p>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="<?= BASE_URL ?>/expert/questions.php" class="btn btn-warning text-dark fw-bold px-3 py-2 shadow-sm">
        <i class="bi bi-chat-left-dots me-1"></i>Answer Pending Questions
      </a>
    </div>
  </div>
</div>

<!-- Metrics Row -->
<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="metric-card metric-gold">
      <div>
        <div class="metric-number"><?= $pendingQCount ?></div>
        <div class="metric-title">Awaiting Expert Answer</div>
      </div>
      <i class="bi bi-question-circle metric-icon"></i>
    </div>
  </div>
  <div class="col-md-6">
    <div class="metric-card metric-green">
      <div>
        <div class="metric-number"><?= $totalAnswered ?></div>
        <div class="metric-title">Consultations Resolved By Me</div>
      </div>
      <i class="bi bi-patch-check metric-icon"></i>
    </div>
  </div>
</div>

<!-- Pending Inquiries Table -->
<div class="card card-agro shadow-sm">
  <div class="card-agro-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-inboxes me-2"></i>Pending Farmer Agronomy Queries</span>
    <a href="<?= BASE_URL ?>/expert/questions.php" class="btn btn-sm btn-outline-success">View All</a>
  </div>
  <div class="card-body p-0">
    <?php if (empty($pendingQuestions)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-check2-all fs-1 d-block mb-3 text-success"></i>
        <h5>All farmer agronomy inquiries are answered!</h5>
        <p class="small">Great job. Check back later for incoming crop protection queries.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Crop</th>
              <th>Farmer Name & Location</th>
              <th>Query Subject</th>
              <th>Submitted Date</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pendingQuestions as $pq): ?>
              <tr>
                <td><span class="badge bg-success-subtle text-success border border-success"><?= e($pq['crop_name']) ?></span></td>
                <td>
                  <strong class="text-dark"><?= e($pq['farmer_name']) ?></strong>
                  <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e($pq['village']) ?>, <?= e($pq['district']) ?></div>
                </td>
                <td>
                  <strong class="text-dark"><?= e($pq['subject']) ?></strong>
                  <div class="small text-muted text-truncate" style="max-width: 320px;"><?= e($pq['question']) ?></div>
                </td>
                <td class="small text-muted"><?= date('d M Y, h:i A', strtotime($pq['created_at'])) ?></td>
                <td class="text-end">
                  <a href="<?= BASE_URL ?>/expert/questions.php?id=<?= $pq['question_id'] ?>" class="btn btn-sm btn-agro-primary">
                    <i class="bi bi-reply-fill me-1"></i>Give Advice
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
