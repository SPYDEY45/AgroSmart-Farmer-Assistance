<?php
/**
 * AgroSmart - Comprehensive System Analytics & BCA Project Reporting Deck
 */
$pageTitle = 'Analytics & Reports – AgroSmart Admin';
require_once __DIR__ . '/../includes/admin_auth.php';

$pdo = getDBConnection();

// Summary Metrics
$totalFarmers = $pdo->query("SELECT COUNT(*) FROM farmers")->fetchColumn() ?: 0;
$totalBuyers = $pdo->query("SELECT COUNT(*) FROM buyers")->fetchColumn() ?: 0;
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;
$totalEnquiries = $pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn() ?: 0;
$acceptedEnquiries = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Accepted'")->fetchColumn() ?: 0;
$totalComplaints = $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn() ?: 0;
$resolvedComplaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Resolved'")->fetchColumn() ?: 0;
$totalQuestions = $pdo->query("SELECT COUNT(*) FROM expert_questions")->fetchColumn() ?: 0;
$answeredQuestions = $pdo->query("SELECT COUNT(*) FROM expert_questions WHERE status = 'Answered'")->fetchColumn() ?: 0;

// Farmers by District
$districtsData = $pdo->query("SELECT district, COUNT(*) as count FROM farmers GROUP BY district ORDER BY count DESC LIMIT 8")->fetchAll();
$districtLabels = array_column($districtsData, 'district');
$districtCounts = array_column($districtsData, 'count');

// Products by Category
$catData = $pdo->query("SELECT category, COUNT(*) as count FROM products GROUP BY category ORDER BY count DESC")->fetchAll();
$catLabels = array_column($catData, 'category');
$catCounts = array_column($catData, 'count');

// Complaints by Category
$compData = $pdo->query("SELECT category, COUNT(*) as count FROM complaints GROUP BY category")->fetchAll();
$compLabels = array_column($compData, 'category');
$compCounts = array_column($compData, 'count');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
  <div>
    <h2 class="fw-bold text-dark mb-1"><i class="bi bi-graph-up me-2 text-success"></i>System Intelligence & Project Analytics</h2>
    <p class="text-muted mb-0">Quantitative operational data, district demographic spread, and platform transaction metrics</p>
  </div>
  <div class="mt-3 mt-md-0">
    <button onclick="window.print()" class="btn btn-outline-dark">
      <i class="bi bi-printer me-1"></i>Print Report for Project Viva
    </button>
  </div>
</div>

<!-- Project Overview Card for Viva Examination -->
<div class="card card-agro mb-4 border-start border-success border-4 shadow-sm">
  <div class="card-body">
    <h5 class="fw-bold text-success mb-2"><i class="bi bi-award me-2"></i>BCA Field Project Summary: AgroSmart Architecture</h5>
    <p class="text-secondary small mb-2">
      AgroSmart is a full-stack digital agriculture ecosystem combining rule-based agronomic recommendation algorithms, direct farm-to-buyer disintermediation, realtime APMC wholesale mandi price integration, and an agronomy advisory pipeline with certified scientists.
    </p>
    <div class="row g-2 text-muted small mt-2">
      <div class="col-md-3"><strong>Database:</strong> MySQL 8 (Normalized 13 tables)</div>
      <div class="col-md-3"><strong>Backend:</strong> PHP 8 (PDO Prepared Stmts)</div>
      <div class="col-md-3"><strong>Frontend:</strong> Bootstrap 5, Chart.js</div>
      <div class="col-md-3"><strong>Security:</strong> CSRF, Bcrypt, XSS escapes</div>
    </div>
  </div>
</div>

<!-- High-Level KPI Summary -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="metric-card metric-green">
      <div>
        <div class="metric-number"><?= $totalFarmers ?></div>
        <div class="metric-title">Registered Farmers</div>
      </div>
      <i class="bi bi-people metric-icon"></i>
    </div>
  </div>
  <div class="col-md-3">
    <div class="metric-card metric-blue">
      <div>
        <div class="metric-number"><?= $totalBuyers ?></div>
        <div class="metric-title">Verified Buyers</div>
      </div>
      <i class="bi bi-shop-window metric-icon"></i>
    </div>
  </div>
  <div class="col-md-3">
    <div class="metric-card metric-gold">
      <div>
        <div class="metric-number"><?= $totalProducts ?></div>
        <div class="metric-title">Lots Listed</div>
      </div>
      <i class="bi bi-boxes metric-icon"></i>
    </div>
  </div>
  <div class="col-md-3">
    <div class="metric-card metric-earth">
      <div>
        <div class="metric-number"><?= $totalEnquiries ?></div>
        <div class="metric-title">Trade Inquiries</div>
      </div>
      <i class="bi bi-send-check metric-icon"></i>
    </div>
  </div>
</div>

<!-- Secondary Statistics Grid -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card card-agro p-3 text-center shadow-sm">
      <small class="text-muted text-uppercase fw-bold">Trade Conversion Rate</small>
      <div class="fs-3 fw-bold text-success mt-1">
        <?= $totalEnquiries > 0 ? round(($acceptedEnquiries / $totalEnquiries) * 100, 1) : 0 ?>%
      </div>
      <small class="text-muted"><?= $acceptedEnquiries ?> of <?= $totalEnquiries ?> inquiries accepted</small>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-agro p-3 text-center shadow-sm">
      <small class="text-muted text-uppercase fw-bold">Grievance Redressal Rate</small>
      <div class="fs-3 fw-bold text-primary mt-1">
        <?= $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100, 1) : 100 ?>%
      </div>
      <small class="text-muted"><?= $resolvedComplaints ?> of <?= $totalComplaints ?> complaints resolved</small>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-agro p-3 text-center shadow-sm">
      <small class="text-muted text-uppercase fw-bold">Expert Consultation Resolution</small>
      <div class="fs-3 fw-bold text-warning text-dark mt-1">
        <?= $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 1) : 100 ?>%
      </div>
      <small class="text-muted"><?= $answeredQuestions ?> of <?= $totalQuestions ?> queries answered</small>
    </div>
  </div>
</div>

<!-- Chart.js Visualizations -->
<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="card card-agro shadow-sm h-100">
      <div class="card-agro-header">
        <i class="bi bi-geo-alt-fill me-2 text-danger"></i>Farmer Geographic Distribution (By District)
      </div>
      <div class="card-body" style="height: 280px; position: relative;">
        <canvas id="districtChart"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-agro shadow-sm h-100">
      <div class="card-agro-header">
        <i class="bi bi-pie-chart-fill me-2 text-success"></i>Marketplace Inventory by Produce Category
      </div>
      <div class="card-body" style="height: 280px; position: relative;">
        <canvas id="categoryChart"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="card card-agro shadow-sm h-100">
      <div class="card-agro-header">
        <i class="bi bi-shield-exclamation me-2 text-warning"></i>Farmer Grievances by Issue Category
      </div>
      <div class="card-body" style="height: 280px; position: relative;">
        <canvas id="complaintChart"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-agro shadow-sm h-100">
      <div class="card-agro-header">
        <i class="bi bi-table me-2 text-primary"></i>Key Module Operational Metrics
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <tbody>
            <tr>
              <td><i class="bi bi-flower1 text-success me-2"></i>Approved Crop Varieties in Database</td>
              <td class="text-end fw-bold"><?= $pdo->query("SELECT COUNT(*) FROM crops")->fetchColumn() ?></td>
            </tr>
            <tr>
              <td><i class="bi bi-bug text-danger me-2"></i>Plant Pathology & Disease Records</td>
              <td class="text-end fw-bold"><?= $pdo->query("SELECT COUNT(*) FROM crop_diseases")->fetchColumn() ?></td>
            </tr>
            <tr>
              <td><i class="bi bi-currency-rupee text-warning me-2"></i>Active APMC Mandi Auction Records</td>
              <td class="text-end fw-bold"><?= $pdo->query("SELECT COUNT(*) FROM market_prices")->fetchColumn() ?></td>
            </tr>
            <tr>
              <td><i class="bi bi-bank text-primary me-2"></i>Published Government Welfare Schemes</td>
              <td class="text-end fw-bold"><?= $pdo->query("SELECT COUNT(*) FROM schemes")->fetchColumn() ?></td>
            </tr>
            <tr>
              <td><i class="bi bi-mortarboard text-success me-2"></i>Certified Agricultural Scientists</td>
              <td class="text-end fw-bold"><?= $pdo->query("SELECT COUNT(*) FROM experts")->fetchColumn() ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // District Chart
  const ctxDistrict = document.getElementById('districtChart');
  if (ctxDistrict) {
    new Chart(ctxDistrict, {
      type: 'bar',
      data: {
        labels: <?= json_encode($districtLabels) ?>,
        datasets: [{
          label: 'Farmers Count',
          data: <?= json_encode($districtCounts) ?>,
          backgroundColor: '#2e7d32'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1 } }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  }

  // Category Doughnut Chart
  const ctxCategory = document.getElementById('categoryChart');
  if (ctxCategory) {
    new Chart(ctxCategory, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($catLabels) ?>,
        datasets: [{
          data: <?= json_encode($catCounts) ?>,
          backgroundColor: ['#4caf50', '#8bc34a', '#ff9800', '#03a9f4', '#e91e63', '#9c27b0']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  }

  // Complaint Chart
  const ctxComp = document.getElementById('complaintChart');
  if (ctxComp) {
    new Chart(ctxComp, {
      type: 'pie',
      data: {
        labels: <?= json_encode($compLabels) ?>,
        datasets: [{
          data: <?= json_encode($compCounts) ?>,
          backgroundColor: ['#f44336', '#ff9800', '#2196f3', '#9e9e9e', '#673ab7']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
