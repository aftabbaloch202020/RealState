<?php
/**
 * RealHome - Privacy Policy
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

$pageTitle = "Privacy Policy";
include __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white border">
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold mb-3 align-self-start">Legal Information</span>
                <h1 class="h2 fw-bold text-dark mb-4">Privacy Policy</h1>
                <p class="text-muted small mb-4">Last Updated: October 2026</p>

                <div class="text-secondary" style="line-height: 1.8;">
                    <h3 class="fs-5 fw-bold text-dark mt-4 mb-2">1. Information We Collect</h3>
                    <p>When you register an account, schedule property viewings, or send inquiries to consultants on RealHome, we collect personal information including your full name, email address, phone number, and account login credentials.</p>

                    <h3 class="fs-5 fw-bold text-dark mt-4 mb-2">2. How We Use Your Information</h3>
                    <p>We use your information solely to facilitate verified real estate communication between buyers, tenants, and authorized listing brokers, to manage your saved properties and viewing bookings, and to notify you regarding important updates to your inquiries.</p>

                    <h3 class="fs-5 fw-bold text-dark mt-4 mb-2">3. Security and Data Protection</h3>
                    <p>We implement enterprise security standards including encrypted password storage (bcrypt), prepared SQL statements to prevent injection, and secure session management. We do not sell or rent your personal contact information to unauthorized third-party marketing companies.</p>

                    <h3 class="fs-5 fw-bold text-dark mt-4 mb-2">4. Contacting Our Data Officer</h3>
                    <p>For questions or requests concerning your data, please contact us at <a href="mailto:support@realhome.com" class="text-success fw-bold">support@realhome.com</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
