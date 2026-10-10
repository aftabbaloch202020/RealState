<?php
/**
 * RealHome - 500 Internal Server Error
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

http_response_code(500);
$pageTitle = "500 Server Error";
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-5 text-center">
    <div class="trust-icon-box mx-auto mb-4" style="width: 80px; height: 80px; font-size: 36px; background: #FEF2F2; color: #DC2626;">
        <i class="bi bi-exclamation-octagon-fill"></i>
    </div>
    <h1 class="display-4 fw-bold text-dark mb-2">500</h1>
    <h2 class="fs-4 fw-semibold text-secondary mb-3">Internal Server Error</h2>
    <p class="text-muted mx-auto mb-4" style="max-width: 480px;">
        Our servers encountered an unexpected condition. Please try refreshing or contact our technical support.
    </p>
    <a href="<?= BASE_URL ?>index.php" class="btn btn-success fw-bold rounded-pill px-4">
        Back to RealHome Home
    </a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
