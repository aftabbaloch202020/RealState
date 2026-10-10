<?php
/**
 * RealHome - Contact Us
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed.';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $phone = sanitize_input($_POST['phone'] ?? '');
        $subject = sanitize_input($_POST['subject'] ?? 'Website Inquiry');
        $message = sanitize_input($_POST['message'] ?? '');

        if (empty($name) || !$email || empty($message)) {
            $error = 'Please provide your full name, valid email, and message.';
        } else {
            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'unread', NOW())
            ");
            $stmt->execute([$name, $email, $phone, $subject, $message]);

            set_flash('success', 'Thank you! Your message has been received. Our team will contact you shortly.');
            header('Location: ' . BASE_URL . 'contact.php');
            exit;
        }
    }
}

$pageTitle = "Contact Us";
include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="py-5 text-white position-relative" style="background: linear-gradient(135deg, #0A1120 0%, #0F172A 100%);">
    <div class="container py-4 text-center">
        <span class="badge bg-success px-3 py-1 rounded-pill fw-bold mb-3">Get in Touch</span>
        <h1 class="display-5 fw-bold text-white mb-2">We'd Love to Hear From You</h1>
        <p class="text-white-50 mx-auto mb-0" style="max-width: 650px;">
            Have questions about buying, renting, or advertising on RealHome? Our dedicated property specialists are here to assist.
        </p>
    </div>
</div>

<div class="container py-5">
    <?= render_flash() ?>

    <div class="row g-5">
        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white border">
                <h2 class="fs-4 fw-bold text-dark mb-2">Send Us a Message</h2>
                <p class="text-muted small mb-4">Fill out the inquiry form below and we will respond within 24 business hours.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-4"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>contact.php">
                    <?= csrf_field() ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Your Name</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Muhammad Ali" required value="<?= e($currentUser['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="name@example.com" required value="<?= e($currentUser['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Phone / WhatsApp</label>
                            <input type="text" name="phone" class="form-control rounded-3" placeholder="+92 300 1234567" value="<?= e($currentUser['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Subject</label>
                            <input type="text" name="subject" class="form-control rounded-3" placeholder="Property Inquiry, Valuation, Partnership..." required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">Message</label>
                        <textarea name="message" class="form-control rounded-3" rows="5" placeholder="How can our real estate team help you?" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm">
                        <i class="bi bi-send me-1"></i> Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Contact Information & Offices -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border mb-4">
                <h3 class="fs-5 fw-bold text-dark mb-4">Direct Contact Information</h3>
                
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="trust-icon-box" style="width: 44px; height: 44px; font-size: 20px;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">Headquarters Address</div>
                        <div class="small text-muted"><?= e(get_site_setting('site_address', 'Suite 502, Executive Tower, Main Boulevard, Gulberg III, Lahore, Pakistan')) ?></div>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="trust-icon-box" style="width: 44px; height: 44px; font-size: 20px;">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">Phone Inquiries</div>
                        <a href="tel:<?= e(get_site_setting('site_phone', '+92 300 0000000')) ?>" class="small text-success fw-semibold">
                            <?= e(get_site_setting('site_phone', '+92 (300) 000-0000')) ?>
                        </a>
                        <div class="text-muted" style="font-size: 11px;">Mon - Sat, 9:00 AM - 7:00 PM PKT</div>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="trust-icon-box" style="width: 44px; height: 44px; font-size: 20px;">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">Support Email</div>
                        <a href="mailto:<?= e(get_site_setting('site_email', 'support@realhome.com')) ?>" class="small text-success fw-semibold">
                            <?= e(get_site_setting('site_email', 'support@realhome.com')) ?>
                        </a>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="trust-icon-box" style="width: 44px; height: 44px; font-size: 20px;">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">Instant WhatsApp Help</div>
                        <a href="https://wa.me/923000000000" target="_blank" rel="noopener" class="small text-success fw-semibold">
                            Chat with Property Desk
                        </a>
                    </div>
                </div>
            </div>

            <!-- Regional Offices -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-light border">
                <h4 class="fs-6 fw-bold text-dark mb-3">Regional City Desks</h4>
                <div class="small text-muted mb-2"><strong class="text-dark">Karachi:</strong> Phase 6, DHA Commercial Area</div>
                <div class="small text-muted mb-2"><strong class="text-dark">Islamabad:</strong> Beverly Centre, Blue Area, F-6</div>
                <div class="small text-muted"><strong class="text-dark">Lahore:</strong> Sector Y, Phase 3, DHA & Gulberg III</div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
