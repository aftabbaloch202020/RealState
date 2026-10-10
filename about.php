<?php
/**
 * RealHome - About Us
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$pageTitle = "About Us";
include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="py-5 text-white position-relative" style="background: linear-gradient(135deg, #0A1120 0%, #0F172A 100%);">
    <div class="container py-4 text-center">
        <span class="badge bg-success px-3 py-1 rounded-pill fw-bold mb-3">Our Story</span>
        <h1 class="display-5 fw-bold text-white mb-2">Reimagining Real Estate in Pakistan</h1>
        <p class="text-white-50 mx-auto mb-0" style="max-width: 650px;">
            RealHome connects thousands of property buyers, tenants, owners, and developers with verified listings, digital tour bookings, and transparent market insights.
        </p>
    </div>
</div>

<div class="container py-5">
    <!-- Mission & Vision -->
    <div class="row align-items-center g-5 mb-5 pb-4">
        <div class="col-lg-6">
            <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold mb-2">Our Mission</span>
            <h2 class="fs-2 fw-bold text-dark mb-3">Building Trust in Every Transaction</h2>
            <p class="text-secondary" style="line-height: 1.8;">
                Founded with a conviction that finding a home should be transparent, exciting, and reliable, RealHome eliminates fake ads, phantom prices, and unverified brokers.
            </p>
            <p class="text-secondary" style="line-height: 1.8;">
                We equip buyers and investors with high-definition photography, verified title documentation, interactive location tours, and direct communication with certified consultants.
            </p>
            <div class="row g-3 mt-2">
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="fs-3 fw-bold text-success mb-1">100%</div>
                        <div class="small fw-semibold text-dark">Verified Listings</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="fs-3 fw-bold text-primary mb-1">24/7</div>
                        <div class="small fw-semibold text-dark">Consultant Support</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="rounded-4 overflow-hidden shadow-lg border">
                <img src="<?= BASE_URL ?>assets/images/hero-villa.jpg" alt="RealHome Luxury Architecture" class="w-100" style="height: 380px; object-fit: cover;">
            </div>
        </div>
    </div>

    <!-- Core Values -->
    <div class="mb-5 pb-4">
        <div class="text-center mb-5">
            <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold mb-2">What Drives Us</span>
            <h2 class="fs-2 fw-bold text-dark">Our Guiding Pillars</h2>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white border">
                    <div class="trust-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 26px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="fs-5 fw-bold text-dark mb-2">Absolute Transparency</h3>
                    <p class="small text-muted mb-0">Every price, specification, and town planning authorization is verified before publication.</p>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white border">
                    <div class="trust-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 26px;">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h3 class="fs-5 fw-bold text-dark mb-2">Digital Convenience</h3>
                    <p class="small text-muted mb-0">Book tours online, favorite properties, submit inquiries and receive instant replies from your dashboard.</p>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white border">
                    <div class="trust-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 26px;">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="fs-5 fw-bold text-dark mb-2">Client Centricity</h3>
                    <p class="small text-muted mb-0">We measure success by long-term value, client happiness, and successful handovers.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Ready to Start CTA -->
    <div class="p-5 rounded-4 text-center text-white" style="background: linear-gradient(135deg, #064E3B 0%, #047857 100%);">
        <h2 class="fs-2 fw-bold mb-2">Ready to Discover Your Next Home?</h2>
        <p class="text-white-50 mx-auto mb-4" style="max-width: 550px;">
            Explore handpicked residential and commercial opportunities across Karachi, Lahore, Islamabad, and beyond.
        </p>
        <a href="<?= BASE_URL ?>properties.php" class="btn btn-light text-dark fw-bold rounded-pill px-4 py-3 shadow">
            Browse Properties &rarr;
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
