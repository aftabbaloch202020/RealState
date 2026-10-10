<?php
/**
 * RealHome - Forgot Password
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed.';
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $error = 'Please enter a valid email address.';
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $ins = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $ins->execute([$email, $token, $expires]);

                // In local development, provide a direct reset link
                $resetLink = BASE_URL . 'reset-password.php?token=' . $token;
                $message = "A password reset link has been generated! For local development testing, you can <a href='{$resetLink}' class='fw-bold text-success'>Click here to reset your password</a>.";
            } else {
                // Security practice: Don't reveal if email does not exist
                $message = "If an account exists with this email address, password reset instructions have been generated.";
            }
        }
    }
}

$pageTitle = "Forgot Password";
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 26px;">
                        <i class="bi bi-key"></i>
                    </div>
                    <h1 class="h4 fw-bold text-dark">Reset Your Password</h1>
                    <p class="text-muted small">Enter your email and we will help you regain account access.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success py-3 small mb-4"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>forgot-password.php">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Account Email</label>
                        <input type="email" name="email" class="form-control rounded-3" placeholder="name@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm mb-3">
                        Generate Reset Link
                    </button>
                </form>

                <div class="text-center small text-muted">
                    Remembered password? <a href="<?= BASE_URL ?>login.php" class="text-success fw-bold text-decoration-none">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
