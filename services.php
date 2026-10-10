<?php
/**
 * RealHome - Professional Real Estate Services
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

$pageTitle = "Our Services";
include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="py-5 text-white position-relative" style="background: linear-gradient(135deg, #0A1120 0%, #0F172A 100%);">
    <div class="container py-4 text-center">
        <span class="badge bg-success px-3 py-1 rounded-pill fw-bold mb-3">Comprehensive Advisory</span>
        <h1 class="display-5 fw-bold text-white mb-2">Real Estate Services & Solutions</h1>
        <p class="text-white-50 mx-auto mb-0" style="max-width: 650px;">
            From residential sales and rental asset management to legal verification and property valuation.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white border">
                <div class="trust-icon-box mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                    <i class="bi bi-house-check"></i>
                </div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Property Sales & Acquisition</h3>
                <p class="small text-secondary mb-3">Assisting buyers and sellers with premium villas, modern apartments, and commercial plazas with end-to-end negotiation support.</p>
                <a href="<?= BASE_URL ?>properties.php?purpose=sale" class="small fw-bold text-success mt-auto">Browse For Sale &rarr;</a>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white border">
                <div class="trust-icon-box mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                    <i class="bi bi-key"></i>
                </div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Rental & Tenancy Advisory</h3>
                <p class="small text-secondary mb-3">Verified lease contracts, tenant background screening, rent escrow coordination, and prompt tenancy handover.</p>
                <a href="<?= BASE_URL ?>properties.php?purpose=rent" class="small fw-bold text-success mt-auto">Browse For Rent &rarr;</a>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white border">
                <div class="trust-icon-box mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Investment Portfolio Management</h3>
                <p class="small text-secondary mb-3">Strategic guidance on high-yield residential plots, off-plan developer projects, and rental yield maximization in DHA & Bahria.</p>
                <a href="<?= BASE_URL ?>contact.php" class="small fw-bold text-success mt-auto">Consult Advisor &rarr;</a>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white border">
                <div class="trust-icon-box mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                    <i class="bi bi-file-earmark-check"></i>
                </div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Legal Verification & Title Deeds</h3>
                <p class="small text-secondary mb-3">Due diligence on property registries, town planning NOCs, transfer procedures, and tax clearance certificates.</p>
                <a href="<?= BASE_URL ?>contact.php" class="small fw-bold text-success mt-auto">Request Audit &rarr;</a>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white border">
                <div class="trust-icon-box mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                    <i class="bi bi-calculator"></i>
                </div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Professional Home Valuation</h3>
                <p class="small text-secondary mb-3">Scientific comparative market analysis (CMA) by certified valuers to determine accurate fair market value for purchase or sale.</p>
                <a href="<?= BASE_URL ?>contact.php" class="small fw-bold text-success mt-auto">Book Appraisal &rarr;</a>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white border">
                <div class="trust-icon-box mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                    <i class="bi bi-camera"></i>
                </div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Media & High-Impact Marketing</h3>
                <p class="small text-secondary mb-3">Professional architectural photography, 4K video tours, and targeted digital distribution across high-net-worth buyer networks.</p>
                <a href="<?= BASE_URL ?>register.php?role=agent" class="small fw-bold text-success mt-auto">Partner With Us &rarr;</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
