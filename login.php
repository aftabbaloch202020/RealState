<?php
/**
 * RealHome - User Login
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Redirect if already logged in
if (is_logged_in()) {
    $role = user_role();
    if ($role === 'admin') {
        header('Location: ' . BASE_URL . 'admin/index.php');
    } elseif ($role === 'agent') {
        header('Location: ' . BASE_URL . 'agent/index.php');
    } else {
        header('Location: ' . BASE_URL . 'user/index.php');
    }
    exit;
}

$error = '';
$redirect = sanitize_input($_GET['redirect'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed (CSRF). Please refresh and try again.';
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $error = 'Please enter a valid email address and password.';
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'suspended') {
                    $error = 'Your account has been suspended. Please contact customer support.';
                } else {
                    // Login successful - regenerate session ID for security
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['user'] = [
                        'id' => (int)$user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'avatar' => $user['avatar'],
                        'phone' => $user['phone'],
                        'agency_name' => $user['agency_name'],
                        'status' => $user['status']
                    ];

                    log_activity((int)$user['id'], 'User Login', 'User signed into RealHome account.');

                    set_flash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');

                    if (!empty($redirect) && strpos($redirect, 'http') === false) {
                        header('Location: ' . BASE_URL . ltrim($redirect, '/'));
                    } elseif ($user['role'] === 'admin') {
                        header('Location: ' . BASE_URL . 'admin/index.php');
                    } elseif ($user['role'] === 'agent') {
                        header('Location: ' . BASE_URL . 'agent/index.php');
                    } else {
                        header('Location: ' . BASE_URL . 'user/index.php');
                    }
                    exit;
                }
            } else {
                $error = 'Invalid email address or password.';
            }
        }
    }
}

$pageTitle = "Sign In";
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 28px;">
                        <i class="bi bi-houses"></i>
                    </div>
                    <h1 class="h3 fw-bold text-dark">Welcome to RealHome</h1>
                    <p class="text-muted small">Sign in to manage your properties, saved homes & inquiries</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small d-flex align-items-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div><?= e($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>login.php<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="loginEmail" class="form-control border-start-0 ps-0" placeholder="name@example.com" required value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <label class="form-label small fw-bold text-dark">Password</label>
                            <a href="<?= BASE_URL ?>forgot-password.php" class="small text-success fw-semibold text-decoration-none">Forgot password?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="loginPass" class="form-control border-start-0 ps-0" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm mb-3">
                        Sign In
                    </button>
                </form>

                <!-- Demo Credentials Quick-Fill -->
                <div class="p-3 bg-light rounded-4 border mb-4">
                    <div class="small fw-bold text-dark text-center mb-2">⚡ Quick 1-Click Demo Fill:</div>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill fw-semibold px-3" onclick="fillCreds('admin@realhome.com', 'admin123')">
                            Admin
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill fw-semibold px-3" onclick="fillCreds('agent1@realhome.com', 'password123')">
                            Agent
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold px-3" onclick="fillCreds('customer@realhome.com', 'password123')">
                            Customer
                        </button>
                    </div>
                </div>

                <div class="text-center small text-muted">
                    Don't have an account yet? <a href="<?= BASE_URL ?>register.php" class="text-success fw-bold text-decoration-none">Create an Account</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillCreds(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPass').value = pass;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
