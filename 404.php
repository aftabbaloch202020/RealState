<?php
/**
 * RealHome - 404 Not Found
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

http_response_code(404);
$pageTitle = "404 Page Not Found";
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-5 text-center">
    <div class="trust-icon-box mx-auto mb-4" style="width: 80px; height: 80px; font-size: 36px; background: #FFFBEB; color: #D97706;">
        <i class="bi bi-geo-alt-fill"></i>
    </div>
    <h1 class="display-4 fw-bold text-dark mb-2">404</h1>
    <h2 class="fs-4 fw-semibold text-secondary mb-3">Property or Page Not Found</h2>
    <p class="text-muted mx-auto mb-4" style="max-width: 480px;">
        The listing or page you are looking for might have been sold, removed, or is temporarily unavailable.
    </p>
    <div class="d-flex justify-content-center gap-3">
        <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-secondary rounded-pill px-4">
            Homepage
        </a>
        <a href="<?= BASE_URL ?>properties.php" class="btn btn-success fw-bold rounded-pill px-4">
            Browse Properties
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
