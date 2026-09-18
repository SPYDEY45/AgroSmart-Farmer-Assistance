<?php
/**
 * AgroSmart - Farmer Expert Q&A Consultation Desk
 */
$pageTitle = 'Ask Agriculture Expert – AgroSmart';
require_once __DIR__ . '/../includes/farmer_auth.php';

$pdo = getDBConnection();
$farmerId = $_SESSION['farmer_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropId = intval($_POST['crop_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $question = trim($_POST['question'] ?? '');
    $expertId = !empty($_POST['expert_id']) ? intval($_POST['expert_id']) : null;
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = 'Security session expired. Please retry.';
    } elseif ($cropId <= 0 || empty($subject) || empty($question)) {
        $error = 'Please select a crop and enter your subject and question in detail.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO expert_questions (farmer_id, crop_id, expert_id, subject, question, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$farmerId, $cropId, $expertId, $subject, $question]);
        setFlash('success', 'Your question has been submitted to certified agricultural scientists.');
        header("Location: " . BASE_URL . "/farmer/ask_expert.php");
        exit();
    }
}

// Fetch active experts and crops for form
$experts = $pdo->query("SELECT e.expert_id, e.specialization, u.name FROM experts e JOIN users u ON e.user_id = u.user_id WHERE u.status = 'active'")->fetchAll();
$crops = $pdo->query("SELECT crop_id, crop_name FROM crops ORDER BY crop_name ASC")->fetchAll();

// Fetch farmer's past questions
$qStmt = $pdo->prepare("SELECT eq.*, c.crop_name, u.name as expert_name, e.specialization FROM expert_questions eq JOIN crops c ON eq.crop_id = c.crop_id LEFT JOIN experts e ON eq.expert_id = e.expert_id LEFT JOIN users u ON e.user_id = u.user_id WHERE eq.farmer_id = ? ORDER BY eq.created_at DESC");
$qStmt->execute([$farmerId]);
$myQuestions = $qStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-headset me-2"></i>Agriculture Expert Guidance</h2>
    <p class="text-muted mb-0">Ask certified agronomists and soil scientists regarding pest attacks, fertilizers, and crop health</p>
  </div>
  <div class="mt-3 mt-md-0">
    <button type="button" class="btn btn-agro-primary" data-bs-toggle="modal" data-bs-target="#askQuestionModal">
      <i class="bi bi-question-circle me-1"></i>Ask New Question
    </button>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<!-- Questions List -->
<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($myQuestions)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-mortarboard fs-1 d-block mb-3 text-success"></i>
        <h5>You haven't asked any expert questions yet.</h5>
        <p class="small">Have doubts regarding dosage, yellow leaves, fungal wilt, or market harvesting? Ask our panel of agricultural scientists.</p>
        <button type="button" class="btn btn-sm btn-agro-primary mt-2" data-bs-toggle="modal" data-bs-target="#askQuestionModal">
          <i class="bi bi-question-lg me-1"></i>Submit First Query
        </button>
      </div>
    <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($myQuestions as $q): ?>
          <div class="list-group-item p-4">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <span class="badge bg-success-subtle text-success me-2">Crop: <?= e($q['crop_name']) ?></span>
                <span class="badge <?= $q['status'] === 'Answered' ? 'bg-success' : 'bg-warning text-dark' ?>">
                  <?= e($q['status']) ?>
                </span>
                <h5 class="fw-bold mt-2 text-dark mb-1"><?= e($q['subject']) ?></h5>
              </div>
              <small class="text-muted"><?= date('d M Y, h:i A', strtotime($q['created_at'])) ?></small>
            </div>

            <p class="text-secondary small mb-3"><?= e($q['question']) ?></p>

            <?php if ($q['status'] === 'Answered'): ?>
              <div class="p-3 bg-light border-start border-success border-4 rounded">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <strong class="text-success small">
                    <i class="bi bi-person-check-fill me-1"></i>Answer by Dr./Prof. <?= e($q['expert_name'] ?? 'Agricultural Officer') ?> (<?= e($q['specialization'] ?? 'Agronomist') ?>)
                  </strong>
                  <?php if (!empty($q['answered_at'])): ?>
                    <small class="text-muted"><?= date('d M Y', strtotime($q['answered_at'])) ?></small>
                  <?php endif; ?>
                </div>
                <p class="small text-dark mb-0" style="white-space: pre-line;"><?= e($q['answer']) ?></p>
              </div>
            <?php else: ?>
              <div class="alert alert-warning py-2 mb-0 small">
                <i class="bi bi-hourglass-split me-1"></i>Your inquiry is queued with the agricultural panel. An expert will respond shortly.
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Ask Modal -->
<div class="modal fade" id="askQuestionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/farmer/ask_expert.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="bi bi-question-circle me-2"></i>Ask Agricultural Scientist</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Related Crop *</label>
            <select name="crop_id" class="form-select" required>
              <option value="">-- Select Crop --</option>
              <?php foreach ($crops as $c): ?>
                <option value="<?= $c['crop_id'] ?>"><?= e($c['crop_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Specific Agronomist (Optional)</label>
            <select name="expert_id" class="form-select">
              <option value="">Any Available Certified Expert</option>
              <?php foreach ($experts as $exp): ?>
                <option value="<?= $exp['expert_id'] ?>"><?= e($exp['name']) ?> (<?= e($exp['specialization']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Question Subject *</label>
            <input type="text" name="subject" class="form-control" required placeholder="e.g. Yellowing of Soybean leaves at 45 days">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Detailed Question *</label>
            <textarea name="question" class="form-control" rows="4" required placeholder="Describe symptoms, fertilizers applied, water frequency, and soil type..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-agro-primary">Submit Question</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
