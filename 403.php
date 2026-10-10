<?php
/**
 * RealHome - 403 Forbidden Error
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

http_response_code(403);
$pageTitle = "403 Forbidden Access";
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-5 text-center">
    <div class="trust-icon-box mx-auto mb-4" style="width: 80px; height: 80px; font-size: 36px; background: #FEF2F2; color: #DC2626;">
        <i class="bi bi-shield-lock-fill"></i>
    </div>
    <h1 class="display-4 fw-bold text-dark mb-2">403 Forbidden</h1>
    <h2 class="fs-4 fw-semibold text-secondary mb-3">Access Denied</h2>
    <p class="text-muted mx-auto mb-4" style="max-width: 480px;">
        You do not have administrative permission to view this restricted area. Please sign in with an authorized account or return to safety.
    </p>
    <a href="<?= BASE_URL ?>index.php" class="btn btn-success fw-bold rounded-pill px-4 py-2">
        Return to Homepage
    </a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
