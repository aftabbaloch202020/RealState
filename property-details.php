<?php
/**
 * RealHome - Property Details Page
 * Image gallery, specifications, agent profile, inquiries, visit booking, and reviews
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . 'properties.php');
    exit;
}

$db = getDB();

// Increment views count
$db->prepare("UPDATE properties SET views_count = views_count + 1 WHERE id = ?")->execute([$id]);

// Fetch property details with category, location, and agent info
$stmt = $db->prepare("
    SELECT p.*, c.name AS category_name, l.name AS location_name,
           u.id AS agent_id, u.name AS agent_name, u.email AS agent_email, 
           u.phone AS agent_phone, u.avatar AS agent_avatar, u.agency_name, u.bio AS agent_bio
    FROM properties p
    JOIN property_categories c ON p.category_id = c.id
    JOIN locations l ON p.location_id = l.id
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ? AND p.status = 'approved'
");
$stmt->execute([$id]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: ' . BASE_URL . '404.php');
    exit;
}

// Fetch property images
$imgStmt = $db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC, id ASC");
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll();
if (empty($images)) {
    $images[] = ['image_path' => $property['featured_image']];
}

// Fetch features (amenities)
$featStmt = $db->prepare("
    SELECT f.name, f.icon 
    FROM property_feature_map m
    JOIN property_features f ON m.feature_id = f.id
    WHERE m.property_id = ?
");
$featStmt->execute([$id]);
$propertyFeatures = $featStmt->fetchAll();

// Fetch reviews
$revStmt = $db->prepare("
    SELECT r.*, u.name AS user_name, u.avatar AS user_avatar
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.property_id = ? AND r.status = 'approved'
    ORDER BY r.id DESC
");
$revStmt->execute([$id]);
$reviews = $revStmt->fetchAll();

// Fetch related properties
$relStmt = $db->prepare("
    SELECT p.*, c.name AS category_name, l.name AS location_name 
    FROM properties p
    JOIN property_categories c ON p.category_id = c.id
    JOIN locations l ON p.location_id = l.id
    WHERE p.status = 'approved' AND p.id != ? AND (p.category_id = ? OR p.location_id = ?)
    LIMIT 3
");
$relStmt->execute([$id, $property['category_id'], $property['location_id']]);
$relatedProperties = $relStmt->fetchAll();

$isFav = is_logged_in() ? is_property_favorite($id) : false;
$pageTitle = $property['title'];

include __DIR__ . '/includes/header.php';
?>

<!-- Data container for client-side localStorage recently viewed tracking -->
<div data-view-property-id="<?= $id ?>"
     data-view-property-title="<?= e($property['title']) ?>"
     data-view-property-price="<?= e(format_price((float)$property['price'], $property['price_prefix'], $property['price_suffix'])) ?>"
     data-view-property-image="<?= BASE_URL . e($property['featured_image']) ?>"
     data-view-property-location="<?= e($property['address']) ?>"
     data-view-property-purpose="<?= e($property['purpose']) ?>"
     style="display: none;"></div>

<!-- Breadcrumb -->
<div class="bg-light py-3 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>properties.php" class="text-decoration-none">Properties</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>properties.php?location=<?= urlencode($property['location_name']) ?>" class="text-decoration-none"><?= e($property['location_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($property['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <!-- Title & Price Top Row -->
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-<?= $property['purpose'] === 'rent' ? 'info' : 'success' ?> px-3 py-1 rounded-pill fw-bold">
                    <?= $property['purpose'] === 'rent' ? 'For Rent' : 'For Sale' ?>
                </span>
                <span class="badge bg-light text-dark border px-3 py-1 rounded-pill">
                    <?= e($property['category_name']) ?>
                </span>
                <?php if ($property['is_featured']): ?>
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                        <i class="bi bi-star-fill me-1"></i> Featured
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="h2 fw-bold text-dark mb-1"><?= e($property['title']) ?></h1>
            <div class="text-muted small">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e($property['address']) ?>, <?= e($property['city']) ?>
            </div>
        </div>

        <div class="text-lg-end">
            <div class="fs-2 fw-bold text-success">
                <?= format_price((float)$property['price'], $property['price_prefix'], $property['price_suffix']) ?>
            </div>
            <div class="d-flex align-items-center gap-2 justify-content-lg-end mt-2">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill btn-card-favorite px-3 position-static <?= $isFav ? 'active' : '' ?>" data-property-id="<?= $id ?>">
                    <i class="bi <?= $isFav ? 'bi-heart-fill' : 'bi-heart' ?> me-1"></i>
                    <span><?= $isFav ? 'Saved' : 'Save' ?></span>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="navigator.clipboard.writeText(window.location.href); window.showToast('Listing link copied to clipboard!', 'success');">
                    <i class="bi bi-share me-1"></i> Share
                </button>
            </div>
        </div>
    </div>

    <!-- Image Gallery -->
    <div class="row g-3 mb-5">
        <div class="col-lg-8">
            <div class="rounded-4 overflow-hidden shadow-sm border bg-dark" style="height: 480px;">
                <img id="mainGalleryImg" src="<?= BASE_URL . e($images[0]['image_path']) ?>" alt="<?= e($property['title']) ?>" class="w-100 h-100" style="object-fit: cover;">
            </div>
        </div>
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-3 h-100">
                <?php foreach (array_slice($images, 0, 3) as $idx => $img): ?>
                    <div class="rounded-4 overflow-hidden border shadow-sm cursor-pointer gallery-thumb-item <?= $idx === 0 ? 'active' : '' ?>" 
                         data-full-src="<?= BASE_URL . e($img['image_path']) ?>"
                         style="height: 148px; cursor: pointer;">
                        <img src="<?= BASE_URL . e($img['image_path']) ?>" alt="Thumbnail" class="w-100 h-100" style="object-fit: cover;">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Main Content & Sidebar -->
    <div class="row g-4">
        <!-- Left: Specifications, Description, Amenities, Location, Reviews -->
        <div class="col-lg-8">
            <!-- Specifications Strip -->
            <div class="p-4 bg-white rounded-4 border shadow-sm mb-4">
                <h2 class="fs-5 fw-bold text-dark mb-3">Key Property Details</h2>
                <div class="row g-3 text-center">
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-light rounded-3">
                            <i class="bi bi-door-closed fs-3 text-success d-block mb-1"></i>
                            <div class="fw-bold text-dark"><?= (int)$property['bedrooms'] ?> Bedrooms</div>
                            <small class="text-muted">Master Suites</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-light rounded-3">
                            <i class="bi bi-droplet fs-3 text-success d-block mb-1"></i>
                            <div class="fw-bold text-dark"><?= (int)$property['bathrooms'] ?> Bathrooms</div>
                            <small class="text-muted">Fitted Baths</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-light rounded-3">
                            <i class="bi bi-aspect-ratio fs-3 text-success d-block mb-1"></i>
                            <div class="fw-bold text-dark"><?= number_format((float)$property['area']) ?> <?= e($property['area_unit']) ?></div>
                            <small class="text-muted">Covered Area</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-light rounded-3">
                            <i class="bi bi-car-front fs-3 text-success d-block mb-1"></i>
                            <div class="fw-bold text-dark"><?= (int)$property['garages'] ?> Garages</div>
                            <small class="text-muted">Covered Parking</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Description -->
            <div class="p-4 bg-white rounded-4 border shadow-sm mb-4">
                <h2 class="fs-5 fw-bold text-dark mb-3">Property Description</h2>
                <div class="text-secondary" style="line-height: 1.8; font-size: 15px;">
                    <?= nl2br(e($property['description'])) ?>
                </div>
            </div>

            <!-- Amenities & Features -->
            <?php if (!empty($propertyFeatures)): ?>
            <div class="p-4 bg-white rounded-4 border shadow-sm mb-4">
                <h2 class="fs-5 fw-bold text-dark mb-3">Amenities & Features</h2>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                    <?php foreach ($propertyFeatures as $feat): ?>
                        <div class="col">
                            <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3">
                                <i class="bi <?= e($feat['icon']) ?> text-success fs-5"></i>
                                <span class="small fw-semibold text-dark"><?= e($feat['name']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Location Map Section -->
            <div class="p-4 bg-white rounded-4 border shadow-sm mb-4">
                <h2 class="fs-5 fw-bold text-dark mb-3">Location & Neighborhood</h2>
                <p class="text-muted small mb-3">
                    <i class="bi bi-geo-alt text-success me-1"></i> <?= e($property['address']) ?>, <?= e($property['city']) ?>, <?= e($property['state']) ?>
                </p>
                <div class="rounded-3 overflow-hidden border bg-light position-relative" style="height: 250px;">
                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center p-4">
                        <i class="bi bi-map text-success fs-1 mb-2"></i>
                        <div class="fw-bold text-dark"><?= e($property['location_name']) ?> Enclave</div>
                        <p class="text-muted small mb-0">High-security perimeter, underground utilities & immediate access to main boulevards.</p>
                    </div>
                </div>
            </div>

            <!-- Customer Reviews -->
            <div class="p-4 bg-white rounded-4 border shadow-sm mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="fs-5 fw-bold text-dark mb-0">Community Reviews (<?= count($reviews) ?>)</h2>
                </div>

                <!-- Existing reviews -->
                <?php if (empty($reviews)): ?>
                    <p class="text-muted small mb-4">No reviews yet for this listing. Be the first to share your thoughts!</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= BASE_URL . ($rev['user_avatar'] ?: 'assets/images/avatars/user1.jpg') ?>" alt="User" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                        <span class="fw-bold small text-dark"><?= e($rev['user_name']) ?></span>
                                    </div>
                                    <div class="text-warning small">
                                        <?php for ($i = 0; $i < (int)$rev['rating']; $i++): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="small text-secondary mb-1"><?= e($rev['comment']) ?></p>
                                <small class="text-muted" style="font-size: 11px;"><?= time_ago($rev['created_at']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Submit Review Form -->
                <?php if (is_logged_in()): ?>
                    <div class="pt-3 border-top">
                        <h3 class="fs-6 fw-bold text-dark mb-2">Leave a Review</h3>
                        <form id="reviewForm" onsubmit="event.preventDefault(); submitReview();">
                            <input type="hidden" name="property_id" value="<?= $id ?>">
                            <div class="mb-2">
                                <label class="small text-muted fw-bold">Rating</label>
                                <select name="rating" id="reviewRating" class="form-select form-select-sm rounded-3" style="width: 140px;">
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="3">3 - Good</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="1">1 - Poor</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <textarea name="comment" id="reviewComment" class="form-control form-control-sm rounded-3" rows="3" placeholder="Share your experience inspecting or residing in this property..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold">
                                Post Review
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="p-3 bg-light rounded-3 text-center small text-muted">
                        Please <a href="<?= BASE_URL ?>login.php" class="text-success fw-bold">sign in</a> to leave a review.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Sidebar: Agent Contact & Booking Form -->
        <div class="col-lg-4">
            <!-- Agent Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white sticky-top" style="top: 90px;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="<?= BASE_URL . ($property['agent_avatar'] ?: 'assets/images/avatars/agent1.jpg') ?>" alt="<?= e($property['agent_name']) ?>" class="rounded-circle border" style="width: 64px; height: 64px; object-fit: cover;">
                    <div>
                        <div class="badge bg-success-subtle text-success mb-1 small fw-bold">Verified Consultant</div>
                        <h3 class="fs-5 fw-bold text-dark mb-0"><?= e($property['agent_name']) ?></h3>
                        <div class="small text-muted"><?= e($property['agency_name'] ?: 'Prime Real Estate') ?></div>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <a href="tel:<?= e($property['agent_phone']) ?>" class="btn btn-outline-success fw-bold rounded-pill">
                        <i class="bi bi-telephone-fill me-1"></i> Call Consultant
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/\D/', '', $property['agent_phone'] ?: '923001234567') ?>" target="_blank" rel="noopener" class="btn btn-success fw-bold rounded-pill">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp Message
                    </a>
                    <button type="button" class="btn btn-dark fw-bold rounded-pill" data-bs-toggle="modal" data-bs-target="#scheduleVisitModal">
                        <i class="bi bi-calendar-event me-1"></i> Schedule a Visit
                    </button>
                </div>

                <hr class="my-3 text-muted">

                <!-- Quick Inquiry Form -->
                <h3 class="fs-6 fw-bold text-dark mb-2">Send an Inquiry</h3>
                <form id="propertyInquiryForm">
                    <input type="hidden" name="property_id" value="<?= $id ?>">
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control form-control-sm rounded-3" placeholder="Your Name" value="<?= e($currentUser['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <input type="email" name="email" class="form-control form-control-sm rounded-3" placeholder="Your Email" value="<?= e($currentUser['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="phone" class="form-control form-control-sm rounded-3" placeholder="Phone Number" value="<?= e($currentUser['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <textarea name="message" class="form-control form-control-sm rounded-3" rows="3" required placeholder="Hello, I would like more information on this property...">Hello, I am interested in "<?= e($property['title']) ?>". Please let me know its availability and negotiation details.</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold rounded-3 py-2">
                        Submit Inquiry
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Related Properties -->
    <?php if (!empty($relatedProperties)): ?>
        <div class="mt-5 pt-4 border-top">
            <h2 class="fs-4 fw-bold text-dark mb-4">Similar Properties You May Like</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($relatedProperties as $property): ?>
                    <div class="col">
                        <?php include __DIR__ . '/includes/property-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Schedule Visit Modal -->
<div class="modal fade" id="scheduleVisitModal" tabindex="-1" aria-labelledby="scheduleVisitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom">
                <h3 class="modal-title fs-5 fw-bold text-dark" id="scheduleVisitModalLabel">
                    <i class="bi bi-calendar-check text-success me-2"></i> Schedule Property Viewing
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="scheduleVisitForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="property_id" value="<?= $id ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Preferred Date</label>
                        <input type="date" name="visit_date" class="form-control rounded-3" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Preferred Time Slot</label>
                        <select name="visit_time" class="form-select rounded-3" required>
                            <option value="10:00:00">Morning (10:00 AM - 12:00 PM)</option>
                            <option value="14:00:00">Afternoon (02:00 PM - 04:00 PM)</option>
                            <option value="16:30:00">Evening (04:30 PM - 06:30 PM)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Special Requests or Notes</label>
                        <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="e.g. Bringing my architect or family member..."></textarea>
                    </div>
                    
                    <small class="text-muted d-block">
                        <i class="bi bi-shield-check text-success me-1"></i> Your appointment will be confirmed by <?= e($property['agent_name']) ?>.
                    </small>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4">Confirm Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function submitReview() {
    const propId = <?= $id ?>;
    const rating = document.getElementById('reviewRating').value;
    const comment = document.getElementById('reviewComment').value;
    const baseUrl = document.body.dataset.baseUrl || '';

    fetch(`${baseUrl}api/submit-review.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ property_id: propId, rating: rating, comment: comment })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            window.showToast(data.message || 'Error submitting review', 'error');
        }
    })
    .catch(() => window.showToast('Server error', 'error'));
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
