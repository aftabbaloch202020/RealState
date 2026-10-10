<?php
/**
 * RealHome - Reset Password Handler
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$token = sanitize_input($_GET['token'] ?? '');
$error = '';
$success = false;

if (empty($token)) {
    die("Invalid password reset token.");
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
$stmt->execute([$token]);
$resetRecord = $stmt->fetch();

if (!$resetRecord) {
    die("This password reset link is invalid or has expired. Please request a new one.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            // Update password
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $upd = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
            $upd->execute([$hashed, $resetRecord['email']]);

            // Invalidate tokens
            $del = $db->prepare("DELETE FROM password_resets WHERE email = ?");
            $del->execute([$resetRecord['email']]);

            $success = true;
            set_flash('success', 'Your password has been reset successfully! You can now log in.');
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
    }
}

$pageTitle = "Reset Password";
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <h1 class="h4 fw-bold text-dark">Create New Password</h1>
                    <p class="text-muted small">Enter a strong new password for <strong><?= e($resetRecord['email']) ?></strong></p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>reset-password.php?token=<?= urlencode($token) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">New Password</label>
                        <input type="password" name="password" class="form-control rounded-3" placeholder="Min. 6 characters" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Repeat password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm mb-3">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
