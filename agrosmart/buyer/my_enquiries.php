<?php
/**
 * AgroSmart - Buyer Enquiries History & Deals
 */
$pageTitle = 'My Enquiries – AgroSmart';
require_once __DIR__ . '/../includes/buyer_auth.php';

$pdo = getDBConnection();
$buyerId = $_SESSION['buyer_id'];

$stmt = $pdo->prepare("SELECT e.*, p.product_name, p.expected_price, p.unit, p.location, u.name as farmer_name, u.mobile as farmer_mobile, u.email as farmer_email, f.village, f.district FROM enquiries e JOIN products p ON e.product_id = p.product_id JOIN farmers f ON e.farmer_id = f.farmer_id JOIN users u ON f.user_id = u.user_id WHERE e.buyer_id = ? ORDER BY e.created_at DESC");
$stmt->execute([$buyerId]);
$enquiries = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-primary mb-1"><i class="bi bi-chat-left-text me-2"></i>My Sourcing & Purchase Enquiries</h2>
    <p class="text-muted mb-0">Track responses from farmers, negotiated volumes, and direct communication links</p>
  </div>
  <div class="mt-3 mt-md-0">
    <a href="<?= BASE_URL ?>/buyer/marketplace.php" class="btn btn-agro-primary">
      <i class="bi bi-plus-circle me-1"></i>Explore More Farm Lots
    </a>
  </div>
</div>

<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($enquiries)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary"></i>
        <h5>You haven't submitted any purchase enquiries yet.</h5>
        <p class="small">Browse the marketplace and send direct procurement offers to verified farmers.</p>
        <a href="<?= BASE_URL ?>/buyer/marketplace.php" class="btn btn-primary mt-2">Browse Marketplace</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Produce Lot</th>
              <th>Farmer Details</th>
              <th>Requested Volume</th>
              <th>My Message</th>
              <th>Date Sent</th>
              <th>Deal Status</th>
              <th>Direct Contact</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($enquiries as $e): ?>
              <tr>
                <td>
                  <strong class="text-dark"><?= e($e['product_name']) ?></strong>
                  <div class="small text-muted">Listed @ ₹ <?= number_format($e['expected_price'], 2) ?>/<?= e($e['unit']) ?></div>
                </td>
                <td>
                  <strong class="text-success"><?= e($e['farmer_name']) ?></strong>
                  <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e($e['village']) ?>, <?= e($e['district']) ?></div>
                </td>
                <td>
                  <span class="badge bg-primary fs-6"><?= e($e['quantity_required']) ?> <?= e($e['unit']) ?></span>
                </td>
                <td style="max-width: 250px;">
                  <p class="small text-secondary mb-0"><?= e($e['message'] ?: 'Procurement enquiry.') ?></p>
                </td>
                <td class="small text-muted"><?= date('d M Y', strtotime($e['created_at'])) ?></td>
                <td>
                  <?php if ($e['status'] === 'Accepted'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Accepted by Farmer</span>
                  <?php elseif ($e['status'] === 'Rejected'): ?>
                    <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i>Declined</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Pending Farmer Response</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($e['status'] === 'Accepted'): ?>
                    <div class="p-2 bg-success-subtle border border-success rounded text-success small">
                      <div class="fw-bold"><i class="bi bi-telephone-outbound-fill me-1"></i><?= e($e['farmer_mobile']) ?></div>
                      <div><?= e($e['farmer_email']) ?></div>
                    </div>
                  <?php else: ?>
                    <span class="small text-muted">Available after farmer accepts</span>
                  <?php endif; ?>
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
