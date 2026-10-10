<?php
/**
 * RealHome - Terms of Service
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

$pageTitle = "Terms of Service";
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white border">
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold mb-3 align-self-start">Legal Agreement</span>
                <h1 class="h2 fw-bold text-dark mb-4">Terms of Service</h1>
                <p class="text-muted small mb-4">Last Updated: October 2026</p>

                <div class="text-secondary" style="line-height: 1.8;">
                    <h3 class="fs-5 fw-bold text-dark mt-4 mb-2">1. Acceptance of Terms</h3>
                    <p>By accessing or using the RealHome website, services, and applications, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our platform.</p>

                    <h3 class="fs-5 fw-bold text-dark mt-4 mb-2">2. Accuracy of Property Information</h3>
                    <p>While RealHome moderates listings and verifies documentation, property buyers and tenants remain responsible for conducting independent physical and legal inspections before executing financial transfers.</p>

                    <h3 class="fs-5 fw-bold text-dark mt-4 mb-2">3. User & Agent Responsibilities</h3>
                    <p>Property owners and agents warrant that any listings posted are genuine, accurate, and authorized by the title owner. Posting fraudulent listings or deceptive prices will result in immediate termination of account access.</p>

                    <h3 class="fs-5 fw-bold text-dark mt-4 mb-2">4. Governing Law</h3>
                    <p>These terms shall be governed by and construed in accordance with the laws of Pakistan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
