<?php
/**
 * AgroSmart - Multi-Role Authentication Login
 */
$pageTitle = 'Login – AgroSmart Portal';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect to respective dashboard
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'farmer';
    header("Location: " . BASE_URL . "/{$role}/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = 'Invalid security token (CSRF). Please refresh the page.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT user_id, name, email, mobile, password, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // For demo convenience during college viva, if password_verify succeeds OR default fallback password 'password123'
        if ($user && (password_verify($password, $user['password']) || $password === 'password123')) {
            if ($user['status'] === 'blocked') {
                $error = 'Your account has been deactivated or blocked by an administrator.';
            } else {
                // Regenerate session ID on login to protect against session fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['mobile'] = $user['mobile'];

                // Cache specific role IDs
                if ($user['role'] === 'farmer') {
                    $fStmt = $pdo->prepare("SELECT farmer_id, village, district FROM farmers WHERE user_id = ?");
                    $fStmt->execute([$user['user_id']]);
                    $farmer = $fStmt->fetch();
                    if ($farmer) {
                        $_SESSION['farmer_id'] = $farmer['farmer_id'];
                        $_SESSION['village'] = $farmer['village'];
                        $_SESSION['district'] = $farmer['district'];
                    }
                    header("Location: " . BASE_URL . "/farmer/dashboard.php");
                    exit();
                } elseif ($user['role'] === 'buyer') {
                    $bStmt = $pdo->prepare("SELECT buyer_id, business_name FROM buyers WHERE user_id = ?");
                    $bStmt->execute([$user['user_id']]);
                    $buyer = $bStmt->fetch();
                    if ($buyer) {
                        $_SESSION['buyer_id'] = $buyer['buyer_id'];
                        $_SESSION['business_name'] = $buyer['business_name'];
                    }
                    header("Location: " . BASE_URL . "/buyer/dashboard.php");
                    exit();
                } elseif ($user['role'] === 'expert') {
                    $eStmt = $pdo->prepare("SELECT expert_id FROM experts WHERE user_id = ?");
                    $eStmt->execute([$user['user_id']]);
                    $expert = $eStmt->fetch();
                    if ($expert) {
                        $_SESSION['expert_id'] = $expert['expert_id'];
                    }
                    header("Location: " . BASE_URL . "/expert/dashboard.php");
                    exit();
                } elseif ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "/admin/dashboard.php");
                    exit();
                }
            }
        } else {
            $error = 'Invalid email or password. Please verify your credentials.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-4">
  <div class="col-md-6 col-lg-5">
    <div class="card card-agro shadow">
      <div class="card-agro-header text-center py-3">
        <h4 class="mb-1 text-success"><i class="bi bi-shield-lock me-2"></i>Sign In to AgroSmart</h4>
        <small class="text-muted">Farmer, Buyer, Expert & Admin Portal Access</small>
      </div>

      <div class="card-body p-4">
        <?php if ($error): ?>
          <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
            <div><?= e($error) ?></div>
          </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/login.php" method="POST" autocomplete="off">
          <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

          <div class="mb-3">
            <label class="form-label fw-semibold">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" id="login_email" class="form-control" required placeholder="name@example.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password" name="password" id="login_password" class="form-control" required placeholder="Enter password">
            </div>
          </div>

          <button type="submit" class="btn btn-agro-primary w-100 py-2 fs-6">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
          </button>
        </form>

        <hr class="my-4">

        <!-- Viva Demonstration Helper Buttons -->
        <div class="p-3 bg-light rounded border">
          <p class="small fw-bold text-success mb-2"><i class="bi bi-stars me-1"></i>One-Click Demo Credentials (BCA Viva):</p>
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-sm btn-outline-success text-start" onclick="fillLogin('admin@agrosmart.com', 'password123')">
              <i class="bi bi-person-fill-gear me-1"></i> <strong>Admin:</strong> admin@agrosmart.com
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary text-start" onclick="fillLogin('ramesh.farmer@gmail.com', 'password123')">
              <i class="bi bi-person me-1"></i> <strong>Farmer:</strong> ramesh.farmer@gmail.com
            </button>
            <button type="button" class="btn btn-sm btn-outline-info text-start" onclick="fillLogin('buyer@mahaagro.com', 'password123')">
              <i class="bi bi-shop me-1"></i> <strong>Buyer:</strong> buyer@mahaagro.com
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="fillLogin('anand.expert@agrosmart.com', 'password123')">
              <i class="bi bi-mortarboard me-1"></i> <strong>Expert:</strong> anand.expert@agrosmart.com
            </button>
          </div>
        </div>

        <div class="text-center mt-3 small">
          Don't have an account? <a href="<?= BASE_URL ?>/register.php" class="text-success fw-bold">Register as Farmer or Buyer</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function fillLogin(email, pwd) {
  document.getElementById('login_email').value = email;
  document.getElementById('login_password').value = pwd;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
