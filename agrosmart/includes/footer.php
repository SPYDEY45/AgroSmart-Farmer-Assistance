<?php
/**
 * AgroSmart - Common Footer
 */
?>
</main>

<footer class="agro-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <h5 class="fw-bold mb-3"><span class="fs-4">🌱</span> AgroSmart</h5>
        <p class="small text-white-50">
          Smart Agriculture Assistant & Farmer Market Portal. A comprehensive BCA Field Project aimed at empowering farmers with real-time agronomy advisories, crop doctor guidance, market transparency, and direct digital commerce.
        </p>
        <p class="small text-warning mb-0">
          <i class="bi bi-mortarboard-fill me-1"></i> Designed for BCA Final Year Field Project Submission & Viva.
        </p>
      </div>

      <div class="col-lg-2 col-md-6">
        <h6 class="text-uppercase fw-bold text-white mb-3">Quick Links</h6>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/farmer/crops.php">Crop Guide</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/farmer/recommendation.php">Smart Recommendations</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/admin/market_prices.php">APMC Market Prices</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/buyer/marketplace.php">Farmer Marketplace</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="text-uppercase fw-bold text-white mb-3">Farmer Services</h6>
        <ul class="list-unstyled small">
          <li class="mb-2"><a href="<?= BASE_URL ?>/farmer/weather.php">Weather Advisory</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/farmer/diseases.php">Crop Diseases & Pest Control</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/farmer/schemes.php">Government Schemes</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/farmer/complaints.php">Grievance / Complaint Redressal</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/about.php">Project Scope & Viva Prep</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="text-uppercase fw-bold text-white mb-3">Demo Logins (Viva)</h6>
        <div class="p-2 bg-dark bg-opacity-25 rounded border border-success small text-white-50">
          <div class="mb-1"><strong>Admin:</strong> <code>admin@agrosmart.com</code></div>
          <div class="mb-1"><strong>Farmer:</strong> <code>ramesh.farmer@gmail.com</code></div>
          <div class="mb-1"><strong>Buyer:</strong> <code>buyer@mahaagro.com</code></div>
          <div class="mb-1"><strong>Expert:</strong> <code>anand.expert@agrosmart.com</code></div>
          <div class="text-warning mt-1">Default Password: <code>password123</code></div>
        </div>
      </div>
    </div>

    <hr class="border-success my-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-white-50">
      <div>&copy; <?= date('Y') ?> AgroSmart – BCA Field Project. Open source academic software.</div>
      <div class="mt-2 mt-md-0">
        <span class="badge bg-success-subtle text-success me-2">PHP 8+ PDO</span>
        <span class="badge bg-success-subtle text-success me-2">MySQL 8+</span>
        <span class="badge bg-success-subtle text-success">Bootstrap 5</span>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
