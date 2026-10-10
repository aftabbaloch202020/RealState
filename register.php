<?php
/**
 * RealHome - User Registration
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error = '';
$name = sanitize_input($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = sanitize_input($_POST['phone'] ?? '');
$role = in_array($_POST['role'] ?? '', ['customer', 'agent']) ? $_POST['role'] : 'customer';
$agencyName = sanitize_input($_POST['agency_name'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed (CSRF). Please refresh and try again.';
    } elseif (empty($name)) {
        $error = 'Please enter your full name.';
    } elseif (!$email) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();

        // Check if email already registered
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'An account with this email address already exists. Please log in.';
        } else {
            // Hash password securely
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $avatar = $role === 'agent' ? 'assets/images/avatars/agent1.jpg' : 'assets/images/avatars/user1.jpg';

            $stmt = $db->prepare("
                INSERT INTO users (name, email, password, phone, role, avatar, agency_name, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$name, $email, $hashedPassword, $phone, $role, $avatar, $role === 'agent' ? $agencyName : null]);
            $newUserId = (int)$db->lastInsertId();

            // Set session login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user'] = [
                'id' => $newUserId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'avatar' => $avatar,
                'phone' => $phone,
                'agency_name' => $agencyName,
                'status' => 'active'
            ];

            // Send welcome notification
            create_notification(
                $newUserId,
                'Welcome to RealHome!',
                'Your account has been created. Start discovering premium properties across Pakistan.',
                'properties.php'
            );

            log_activity($newUserId, 'User Registered', "New user registered with role: {$role}");

            set_flash('success', 'Welcome to RealHome! Your account was registered successfully.');

            if ($role === 'agent') {
                header('Location: ' . BASE_URL . 'agent/index.php');
            } else {
                header('Location: ' . BASE_URL . 'user/index.php');
            }
            exit;
        }
    }
}

$pageTitle = "Create an Account";
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 28px;">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <h1 class="h3 fw-bold text-dark">Join RealHome</h1>
                    <p class="text-muted small">Create your free account to buy, rent, or list properties</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small d-flex align-items-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div><?= e($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>register.php">
                    <?= csrf_field() ?>

                    <!-- Role Selector -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark mb-2">I want to:</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="roleCustomer" value="customer" <?= $role === 'customer' ? 'checked' : '' ?> onchange="toggleAgentFields()">
                                <label class="btn btn-outline-success w-100 rounded-3 py-2 fw-semibold small" for="roleCustomer">
                                    <i class="bi bi-person me-1"></i> Buy or Rent Homes
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="roleAgent" value="agent" <?= $role === 'agent' ? 'checked' : '' ?> onchange="toggleAgentFields()">
                                <label class="btn btn-outline-success w-100 rounded-3 py-2 fw-semibold small" for="roleAgent">
                                    <i class="bi bi-building me-1"></i> List Properties / Agent
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Full Name</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="Muhammad Ali" required value="<?= e($name) ?>">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="ali@example.com" required value="<?= e($email) ?>">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark">Phone Number</label>
                            <input type="text" name="phone" class="form-control rounded-3" placeholder="+92 300 1234567" value="<?= e($phone) ?>">
                        </div>
                    </div>

                    <div class="mb-3" id="agencyField" style="<?= $role === 'agent' ? '' : 'display: none;' ?>">
                        <label class="form-label small fw-bold text-dark">Agency / Company Name</label>
                        <input type="text" name="agency_name" class="form-control rounded-3" placeholder="Prime City Realtors" value="<?= e($agencyName) ?>">
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark">Password</label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Min. 6 chars" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold text-dark">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Repeat password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm mb-3">
                        Register Account
                    </button>
                </form>

                <div class="text-center small text-muted">
                    Already registered? <a href="<?= BASE_URL ?>login.php" class="text-success fw-bold text-decoration-none">Sign In here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAgentFields() {
    const isAgent = document.getElementById('roleAgent').checked;
    document.getElementById('agencyField').style.display = isAgent ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
