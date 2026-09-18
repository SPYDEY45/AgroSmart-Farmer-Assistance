<?php
/**
 * AgroSmart - Contact & Support Page
 */
$pageTitle = 'Contact Us – AgroSmart';
require_once __DIR__ . '/includes/header.php';

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = "Thank you for reaching out! Your enquiry has been recorded for the field support team.";
}
?>

<div class="row justify-content-center my-4">
  <div class="col-lg-8">
    <div class="card card-agro shadow">
      <div class="card-agro-header">
        <h4 class="mb-0 text-success"><i class="bi bi-headset me-2"></i>Contact & Agricultural Field Support</h4>
      </div>
      <div class="card-body p-4">
        <?php if ($feedback): ?>
          <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= e($feedback) ?></div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <h6 class="fw-bold text-success mb-2"><i class="bi bi-geo-alt-fill me-2"></i>Field Project Center</h6>
            <p class="small text-muted mb-1">Department of Computer Applications (BCA)</p>
            <p class="small text-muted mb-1">AgroSmart Rural Technology Lab</p>
            <p class="small text-muted mb-0">Pune, Maharashtra - 411001</p>
          </div>
          <div class="col-md-6">
            <h6 class="fw-bold text-success mb-2"><i class="bi bi-telephone-inbound-fill me-2"></i>Kisan Helpline & Helpdesk</h6>
            <p class="small text-muted mb-1"><strong>National Kisan Call Center:</strong> 1800-180-1551 (Toll Free)</p>
            <p class="small text-muted mb-1"><strong>AgroSmart Support:</strong> support@agrosmart.edu</p>
            <p class="small text-muted mb-0"><strong>Working Hours:</strong> Mon - Sat, 9:00 AM - 6:00 PM</p>
          </div>
        </div>

        <form action="<?= BASE_URL ?>/contact.php" method="POST">
          <h6 class="fw-bold text-dark border-top pt-3 mb-3">Send a Message</h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Your Name</label>
              <input type="text" name="name" class="form-control" required placeholder="Enter full name">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Contact Mobile or Email</label>
              <input type="text" name="contact" class="form-control" required placeholder="Mobile or Email">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Subject / Field Query</label>
              <input type="text" name="subject" class="form-control" required placeholder="e.g. Market access or technical issue">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Message</label>
              <textarea name="message" class="form-control" rows="4" required placeholder="Describe your query in detail..."></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-agro-primary"><i class="bi bi-send me-2"></i>Send Message</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
