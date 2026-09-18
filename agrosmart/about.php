<?php
/**
 * AgroSmart - About Project & Academic Viva Documentation
 */
$pageTitle = 'About Project & Viva Guide – AgroSmart';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="card card-agro shadow mb-4">
      <div class="card-agro-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-success"><i class="bi bi-mortarboard-fill me-2"></i>BCA Field Project Academic Documentation</h4>
        <span class="badge bg-success">Field Study & Web Portal</span>
      </div>

      <div class="card-body p-4">
        <div class="alert alert-success d-flex align-items-center mb-4">
          <i class="bi bi-check-circle-fill fs-3 me-3"></i>
          <div>
            <h5 class="alert-heading mb-1">Project: AgroSmart – Smart Agriculture Assistant & Farmer Market Portal</h5>
            <p class="mb-0 small">Designed for BCA (Bachelor of Computer Applications) final-year university submission, viva-voce presentation, and rural agricultural field surveys.</p>
          </div>
        </div>

        <h5 class="fw-bold text-success border-bottom pb-2 mb-3">1. Problem Statement & Motivation</h5>
        <p class="text-muted">
          Smallholder and medium farmers face severe information asymmetry: middleman commission cuts into their profits, scientific agronomy advisories are fragmented, disease diagnosis is delayed, and market mandi prices are obscure. <strong>AgroSmart</strong> solves this by bridging the gap between Farmers, Certified Agronomy Experts, Bulk Produce Buyers, and Government Support Systems through a secure, bilingual, responsive web platform.
        </p>

        <h5 class="fw-bold text-success border-bottom pb-2 mb-3 mt-4">2. System Architecture & Tech Stack</h5>
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="border rounded p-3 text-center h-100 bg-light">
              <i class="bi bi-filetype-html fs-2 text-danger"></i>
              <h6 class="fw-bold mt-2">Presentation Layer</h6>
              <p class="small text-muted mb-0">HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons, Chart.js for data visualization.</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded p-3 text-center h-100 bg-light">
              <i class="bi bi-filetype-php fs-2 text-primary"></i>
              <h6 class="fw-bold mt-2">Application Backend</h6>
              <p class="small text-muted mb-0">PHP 8.2+ with PHP PDO, OOP modules, session authentication, and REST-style JSON endpoints.</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded p-3 text-center h-100 bg-light">
              <i class="bi bi-database-fill-check fs-2 text-warning"></i>
              <h6 class="fw-bold mt-2">Database Layer</h6>
              <p class="small text-muted mb-0">MySQL 8.0+ / MariaDB with 13 normalized tables, foreign keys, cascades, and indexed search.</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded p-3 text-center h-100 bg-light">
              <i class="bi bi-shield-lock-fill fs-2 text-success"></i>
              <h6 class="fw-bold mt-2">Security Engine</h6>
              <p class="small text-muted mb-0">bcrypt password hashing, prepared statements, CSRF tokens, and mime-type upload sanitization.</p>
            </div>
          </div>
        </div>

        <h5 class="fw-bold text-success border-bottom pb-2 mb-3 mt-4">3. Database Schema & Entities (ER Model)</h5>
        <div class="table-responsive mb-4">
          <table class="table table-bordered table-striped small">
            <thead class="table-success">
              <tr>
                <th>Table Name</th>
                <th>Primary Key</th>
                <th>Foreign Key Relationships</th>
                <th>Purpose in System</th>
              </tr>
            </thead>
            <tbody>
              <tr><td><code>users</code></td><td>user_id</td><td>None (Central Auth)</td><td>Stores credentials, role (farmer/buyer/expert/admin), active status.</td></tr>
              <tr><td><code>farmers</code></td><td>farmer_id</td><td><code>user_id -> users(user_id)</code></td><td>Stores land acreage, soil type, water source, village, district.</td></tr>
              <tr><td><code>buyers</code></td><td>buyer_id</td><td><code>user_id -> users(user_id)</code></td><td>Stores firm name, commercial APMC address, district.</td></tr>
              <tr><td><code>experts</code></td><td>expert_id</td><td><code>user_id -> users(user_id)</code></td><td>Agronomist qualifications, specialization, years of experience.</td></tr>
              <tr><td><code>crops</code></td><td>crop_id</td><td>None</td><td>Crop encyclopedia: seasons, water needs, sowing/harvest schedules.</td></tr>
              <tr><td><code>farmer_crops</code></td><td>id</td><td><code>farmer_id, crop_id</code></td><td>Farmer's active farm acreage, sowing date, cultivation phase.</td></tr>
              <tr><td><code>crop_diseases</code></td><td>disease_id</td><td><code>crop_id -> crops(crop_id)</code></td><td>Pathogen diagnosis, visual symptoms, preventive management.</td></tr>
              <tr><td><code>products</code></td><td>product_id</td><td><code>farmer_id -> farmers(farmer_id)</code></td><td>Marketplace inventory: expected price, photo, quantity, approval state.</td></tr>
              <tr><td><code>market_prices</code></td><td>price_id</td><td>None</td><td>APMC mandi records: Min, Max, Modal price with source and date.</td></tr>
              <tr><td><code>enquiries</code></td><td>enquiry_id</td><td><code>product_id, buyer_id, farmer_id</code></td><td>Buyer negotiations, requested quantities, and acceptance statuses.</td></tr>
              <tr><td><code>complaints</code></td><td>complaint_id</td><td><code>farmer_id -> farmers(farmer_id)</code></td><td>Grievances regarding irrigation, marketplace, or crops with admin tracking.</td></tr>
              <tr><td><code>schemes</code></td><td>scheme_id</td><td>None</td><td>Government welfare schemes, verified eligibility criteria, official portals.</td></tr>
              <tr><td><code>expert_questions</code></td><td>question_id</td><td><code>farmer_id, expert_id</code></td><td>Farmer technical questions and certified agricultural answers.</td></tr>
            </tbody>
          </table>
        </div>

        <h5 class="fw-bold text-success border-bottom pb-2 mb-3 mt-4">4. Future Scope for Post-Graduate Studies</h5>
        <ul class="text-muted small">
          <li><strong>Deep Learning Crop Disease Scanner:</strong> Convolutional Neural Networks (MobileNet / YOLOv8) for on-field mobile leaf scanning.</li>
          <li><strong>IoT Micro-Climate Sensors:</strong> LoRaWAN soil moisture probes triggering automated solenoid drip valves.</li>
          <li><strong>Multilingual Voice AI:</strong> Speech-to-text in rural Marathi dialect for hands-free farm recording.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
