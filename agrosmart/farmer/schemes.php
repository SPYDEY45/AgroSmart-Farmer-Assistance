<?php
/**
 * AgroSmart - Government Agricultural Schemes & Subsidies
 */
$pageTitle = 'Government Schemes – AgroSmart';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDBConnection();
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$query = "SELECT * FROM schemes WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (scheme_name LIKE ? OR description LIKE ? OR eligibility LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

$query .= " ORDER BY scheme_id ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$schemes = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-success mb-1"><i class="bi bi-bank me-2"></i>Government Agriculture Schemes</h2>
    <p class="text-muted mb-0">Verified central and state government subsidy portals, eligibility, and direct benefit transfers</p>
  </div>
  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <div class="mt-3 mt-md-0">
      <a href="<?= BASE_URL ?>/admin/schemes.php?action=add" class="btn btn-agro-primary">
        <i class="bi bi-plus-circle me-1"></i>Add New Scheme
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="card card-agro mb-4 p-3">
  <form method="GET" action="<?= BASE_URL ?>/farmer/schemes.php" class="row g-2">
    <div class="col-md-7">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Search by scheme name or benefit (e.g. PM-KISAN, Insurance, Solar)..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="category" class="form-select">
        <option value="">All Categories</option>
        <option value="Central" <?= $category === 'Central' ? 'selected' : '' ?>>Central Govt (केंद्र शासन)</option>
        <option value="State" <?= $category === 'State' ? 'selected' : '' ?>>State Govt (महाराष्ट्र शासन)</option>
        <option value="Subsidy" <?= $category === 'Subsidy' ? 'selected' : '' ?>>Equipment & Solar Subsidy</option>
        <option value="Insurance" <?= $category === 'Insurance' ? 'selected' : '' ?>>Crop Insurance & Risk</option>
      </select>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-agro-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
    </div>
  </form>
</div>

<!-- Schemes Grid -->
<?php if (empty($schemes)): ?>
  <div class="card card-agro p-5 text-center text-muted">
    <i class="bi bi-inbox fs-1 mb-2"></i>
    <h5>No agricultural schemes match your search.</h5>
    <a href="<?= BASE_URL ?>/farmer/schemes.php" class="btn btn-outline-success mt-2">View All Schemes</a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($schemes as $s): ?>
      <div class="col-md-6">
        <div class="card card-agro h-100 shadow-sm d-flex flex-column border-start border-success border-4">
          <div class="card-agro-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-success"><?= e($s['scheme_name']) ?></h5>
            <span class="badge bg-success-subtle text-success border border-success"><?= e($s['category']) ?></span>
          </div>
          <div class="card-body flex-grow-1">
            <p class="small text-secondary mb-3"><?= e($s['description']) ?></p>

            <div class="mb-2">
              <span class="small fw-bold text-dark d-block"><i class="bi bi-gift-fill text-warning me-1"></i> Financial Benefits:</span>
              <p class="small text-success fw-semibold mb-2"><?= e($s['benefits']) ?></p>
            </div>

            <div class="mb-2">
              <span class="small fw-bold text-dark d-block"><i class="bi bi-person-check-fill text-primary me-1"></i> Eligibility Criteria:</span>
              <p class="small text-muted mb-2"><?= e($s['eligibility']) ?></p>
            </div>

            <div class="mb-2">
              <span class="small fw-bold text-dark d-block"><i class="bi bi-file-earmark-text text-secondary me-1"></i> Application Procedure:</span>
              <p class="small text-muted mb-0"><?= e($s['how_to_apply']) ?></p>
            </div>
          </div>
          <div class="card-footer bg-light border-top p-3 d-flex justify-content-between align-items-center">
            <span class="small text-muted"><i class="bi bi-shield-check text-success me-1"></i>Verified Scheme</span>
            <?php if (!empty($s['website'])): ?>
              <a href="<?= e($s['website']) ?>" target="_blank" class="btn btn-sm btn-agro-primary">
                Official Portal <i class="bi bi-box-arrow-up-right ms-1"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
