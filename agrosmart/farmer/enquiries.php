<?php
/**
 * AgroSmart - Farmer Product Enquiries Management
 */
$pageTitle = 'Buyer Enquiries – AgroSmart';
require_once __DIR__ . '/../includes/farmer_auth.php';

$pdo = getDBConnection();
$farmerId = $_SESSION['farmer_id'];

// Handle Status Change (Accept / Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enquiry_id'])) {
    $enquiryId = intval($_POST['enquiry_id']);
    $newStatus = $_POST['status'] === 'Accepted' ? 'Accepted' : 'Rejected';

    $stmt = $pdo->prepare("UPDATE enquiries SET status = ? WHERE enquiry_id = ? AND farmer_id = ?");
    $stmt->execute([$newStatus, $enquiryId, $farmerId]);
    setFlash('success', "Enquiry marked as {$newStatus}.");
    header("Location: " . BASE_URL . "/farmer/enquiries.php");
    exit();
}

// Fetch enquiries
$stmt = $pdo->prepare("SELECT e.*, p.product_name, p.expected_price, p.unit, b.business_name, u.name as buyer_contact, u.mobile as buyer_mobile, u.email as buyer_email FROM enquiries e JOIN products p ON e.product_id = p.product_id JOIN buyers b ON e.buyer_id = b.buyer_id JOIN users u ON b.user_id = u.user_id WHERE e.farmer_id = ? ORDER BY e.created_at DESC");
$stmt->execute([$farmerId]);
$enquiries = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
  <h2 class="fw-bold text-success mb-1"><i class="bi bi-chat-dots me-2"></i>Buyer Purchase Enquiries</h2>
  <p class="text-muted mb-0">Review trade offers, requested quantities, and direct contact details from registered traders</p>
</div>

<div class="card card-agro shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($enquiries)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-chat-left-text fs-1 d-block mb-3 text-secondary"></i>
        <h5>No buyer enquiries received yet.</h5>
        <p class="small">When wholesale merchants or buyers express interest in your listed crops, their messages and contact numbers will appear here.</p>
        <a href="<?= BASE_URL ?>/farmer/my_products.php" class="btn btn-sm btn-outline-success mt-2">Manage My Produce Listings</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Buyer / Firm</th>
              <th>Produce Interested</th>
              <th>Required Qty</th>
              <th>Buyer Message</th>
              <th>Enquiry Date</th>
              <th>Status</th>
              <th class="text-end">Respond</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($enquiries as $enq): ?>
              <tr>
                <td>
                  <strong class="text-dark"><?= e($enq['business_name']) ?></strong>
                  <div class="small text-muted"><i class="bi bi-person me-1"></i><?= e($enq['buyer_contact']) ?></div>
                  <div class="small text-success"><i class="bi bi-telephone me-1"></i><?= e($enq['buyer_mobile']) ?></div>
                </td>
                <td>
                  <strong class="text-success"><?= e($enq['product_name']) ?></strong>
                  <div class="small text-muted">Listed @ ₹ <?= number_format($enq['expected_price'], 2) ?>/<?= e($enq['unit']) ?></div>
                </td>
                <td>
                  <span class="badge bg-primary fs-6"><?= e($enq['quantity_required']) ?> <?= e($enq['unit']) ?></span>
                </td>
                <td style="max-width: 250px;">
                  <p class="small text-secondary mb-0"><?= e($enq['message'] ?: 'Interested in purchasing this lot. Please confirm availability.') ?></p>
                </td>
                <td class="small text-muted"><?= date('d M Y, h:i A', strtotime($enq['created_at'])) ?></td>
                <td>
                  <?php if ($enq['status'] === 'Accepted'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Accepted</span>
                  <?php elseif ($enq['status'] === 'Rejected'): ?>
                    <span class="badge bg-danger"><i class="bi bi-x-lg me-1"></i>Rejected</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <?php if ($enq['status'] === 'Pending'): ?>
                    <form action="<?= BASE_URL ?>/farmer/enquiries.php" method="POST" class="d-inline">
                      <input type="hidden" name="enquiry_id" value="<?= $enq['enquiry_id'] ?>">
                      <input type="hidden" name="status" value="Accepted">
                      <button type="submit" class="btn btn-sm btn-success me-1" title="Accept Offer">
                        <i class="bi bi-check-circle me-1"></i>Accept
                      </button>
                    </form>
                    <form action="<?= BASE_URL ?>/farmer/enquiries.php" method="POST" class="d-inline">
                      <input type="hidden" name="enquiry_id" value="<?= $enq['enquiry_id'] ?>">
                      <input type="hidden" name="status" value="Rejected">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Decline Offer">
                        <i class="bi bi-x-circle me-1"></i>Decline
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="small text-muted">Completed</span>
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
