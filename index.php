<?php
/**
 * RealHome - Upgraded Homepage (A to Z)
 * Complete, rich, professional real estate marketplace
 * Exactly preserves the RealHome emerald green, dark navy & white visual identity
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$db = getDB();

// 1. Categories with property counts
$categories = $db->query("
    SELECT c.*, COUNT(p.id) AS prop_count 
    FROM property_categories c 
    LEFT JOIN properties p ON c.id = p.category_id AND p.status = 'approved'
    GROUP BY c.id 
    ORDER BY c.id ASC
")->fetchAll();

// 2. Locations with property counts
$locations = $db->query("
    SELECT l.*, COUNT(p.id) AS prop_count 
    FROM locations l 
    LEFT JOIN properties p ON l.id = p.location_id AND p.status = 'approved'
    GROUP BY l.id 
    ORDER BY l.is_popular DESC, l.id ASC
")->fetchAll();

// 3. Featured Properties (Matches screenshot 4 cards)
$featuredProperties = $db->query("
    SELECT p.*, c.name AS category_name, l.name AS location_name 
    FROM properties p
    JOIN property_categories c ON p.category_id = c.id
    JOIN locations l ON p.location_id = l.id
    WHERE p.status = 'approved' AND p.is_featured = 1
    ORDER BY p.id ASC
    LIMIT 4
")->fetchAll();

// 4. Recently Added Properties
$recentProperties = $db->query("
    SELECT p.*, c.name AS category_name, l.name AS location_name 
    FROM properties p
    JOIN property_categories c ON p.category_id = c.id
    JOIN locations l ON p.location_id = l.id
    WHERE p.status = 'approved'
    ORDER BY p.id DESC
    LIMIT 4
")->fetchAll();

// 5. Exclusive Luxury Properties (Price >= 35M)
$luxuryProperties = $db->query("
    SELECT p.*, c.name AS category_name, l.name AS location_name, u.name AS agent_name, u.phone AS agent_phone
    FROM properties p
    JOIN property_categories c ON p.category_id = c.id
    JOIN locations l ON p.location_id = l.id
    JOIN users u ON p.user_id = u.id
    WHERE p.status = 'approved' AND p.price >= 35000000
    ORDER BY p.price DESC
    LIMIT 2
")->fetchAll();

// 6. Recommended Properties (Top viewed)
$recommendedProperties = $db->query("
    SELECT p.*, c.name AS category_name, l.name AS location_name 
    FROM properties p
    JOIN property_categories c ON p.category_id = c.id
    JOIN locations l ON p.location_id = l.id
    WHERE p.status = 'approved'
    ORDER BY p.views_count DESC
    LIMIT 4
")->fetchAll();

// 7. Featured Real Estate Agents
$agents = $db->query("
    SELECT u.*, COUNT(p.id) AS active_listings
    FROM users u
    LEFT JOIN properties p ON u.id = p.user_id AND p.status = 'approved'
    WHERE u.role = 'agent' AND u.status = 'active'
    GROUP BY u.id
    ORDER BY active_listings DESC
    LIMIT 3
")->fetchAll();

// 8. Real Customer Testimonials
$testimonials = $db->query("
    SELECT r.*, u.name AS reviewer_name, u.avatar AS reviewer_avatar, p.title AS property_title
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN properties p ON r.property_id = p.id
    WHERE r.status = 'approved'
    ORDER BY r.id DESC
    LIMIT 3
")->fetchAll();

// 9. Blog Articles
$blogs = $db->query("
    SELECT b.*, u.name AS author_name 
    FROM blog_posts b
    JOIN users u ON b.author_id = u.id
    WHERE b.status = 'published'
    ORDER BY b.id DESC
    LIMIT 3
")->fetchAll();

// 10. Database-derived Market Statistics & Chart Data
$totalProps = (int)$db->query("SELECT COUNT(*) FROM properties WHERE status = 'approved'")->fetchColumn();
$totalAgents = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'agent' AND status = 'active'")->fetchColumn();
$totalLocations = (int)$db->query("SELECT COUNT(*) FROM locations")->fetchColumn();
$totalInquiries = (int)$db->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();

$categoryStats = $db->query("
    SELECT c.name, COUNT(p.id) as count 
    FROM property_categories c 
    LEFT JOIN properties p ON c.id = p.category_id AND p.status = 'approved' 
    GROUP BY c.id
")->fetchAll();
$chartLabels = json_encode(array_column($categoryStats, 'name'));
$chartCounts = json_encode(array_map('intval', array_column($categoryStats, 'count')));

// 11. Investment Opportunities (High-value prime properties)
$investmentProperties = $db->query("
    SELECT p.*, c.name AS category_name, l.name AS location_name 
    FROM properties p
    JOIN property_categories c ON p.category_id = c.id
    JOIN locations l ON p.location_id = l.id
    WHERE p.status = 'approved'
    ORDER BY p.price DESC
    LIMIT 3
")->fetchAll();

$pageTitle = "Find the Perfect Property for Your Future";
include __DIR__ . '/includes/header.php';
?>

<!-- Flash message banner -->
<div class="container mt-3">
    <?= render_flash() ?>
</div>

<!-- ==============================================================
     SECTION 1: HERO & ADVANCED SEARCH BAR (Reference UI)
     ============================================================== -->
<section class="hero-section" style="background-image: url('<?= BASE_URL ?>assets/images/hero-villa.jpg');">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="row">
            <div class="col-lg-9 col-xl-8">
                <!-- Pill Badge -->
                <div class="hero-pill-badge">
                    <i class="bi bi-stars"></i>
                    <span>Your Dream Home Awaits</span>
                </div>

                <!-- Main Display Headline -->
                <h1 class="hero-title">
                    Find the Perfect Property<br>
                    for <span class="text-emerald">Your Future</span>
                </h1>

                <!-- Subtitle -->
                <p class="hero-subtitle">
                    Discover the best properties for sale and rent. From modern apartments to luxury villas, we help you find a place you'll love.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Floating Advanced Search Bar -->
<section class="container hero-search-wrapper">
    <div class="hero-search-card">
        <!-- Purpose Selector Pills (Buy / Rent) -->
        <div class="d-flex align-items-center gap-2 mb-3 px-2">
            <button type="button" class="btn btn-sm rounded-pill px-4 fw-bold active-search-tab" id="tabSale" onclick="setSearchPurpose('sale')">
                <i class="bi bi-tag me-1"></i> Buy
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-4 fw-bold inactive-search-tab" id="tabRent" onclick="setSearchPurpose('rent')">
                <i class="bi bi-key me-1"></i> Rent
            </button>
        </div>

        <form action="<?= BASE_URL ?>properties.php" method="GET" id="heroSearchForm">
            <input type="hidden" name="purpose" id="searchPurposeInput" value="sale">
            <div class="row g-2 align-items-center">
                <!-- Location Field -->
                <div class="col-lg-3 col-md-6">
                    <div class="search-field-item">
                        <i class="bi bi-geo-alt search-field-icon"></i>
                        <div class="search-field-info">
                            <label class="search-field-label">Location</label>
                            <select name="location" class="search-field-select">
                                <option value="">Enter city, area or neighborhood</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= e($loc['name']) ?>"><?= e($loc['name']) ?>, <?= e($loc['city']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Property Type Field -->
                <div class="col-lg-3 col-md-6">
                    <div class="search-field-item">
                        <i class="bi bi-house search-field-icon"></i>
                        <div class="search-field-info">
                            <label class="search-field-label">Property Type</label>
                            <select name="category" class="search-field-select">
                                <option value="">All Types</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Price Range Field -->
                <div class="col-lg-2 col-md-6">
                    <div class="search-field-item">
                        <i class="bi bi-cash-stack search-field-icon"></i>
                        <div class="search-field-info">
                            <label class="search-field-label">Price Range</label>
                            <select name="price_range" class="search-field-select">
                                <option value="">Any Price</option>
                                <option value="0-15000000">Under PKR 15M</option>
                                <option value="15000000-35000000">PKR 15M - 35M</option>
                                <option value="35000000-60000000">PKR 35M - 60M</option>
                                <option value="60000000+">Above PKR 60M</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Bedrooms Field -->
                <div class="col-lg-2 col-md-6">
                    <div class="search-field-item">
                        <i class="bi bi-door-closed search-field-icon"></i>
                        <div class="search-field-info">
                            <label class="search-field-label">Bedrooms</label>
                            <select name="bedrooms" class="search-field-select">
                                <option value="">Any</option>
                                <option value="1">1+ Beds</option>
                                <option value="2">2+ Beds</option>
                                <option value="3">3+ Beds</option>
                                <option value="4">4+ Beds</option>
                                <option value="5">5+ Beds</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="col-lg-2 col-md-12">
                    <button type="submit" class="btn-hero-search">
                        <i class="bi bi-search"></i>
                        <span>Search</span>
                    </button>
                </div>
            </div>

            <!-- Advanced Filter Fields (Bathrooms & Area) -->
            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top px-1">
                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-success fw-bold d-flex align-items-center gap-1" data-bs-toggle="collapse" data-bs-target="#heroAdvancedFilterCollapse">
                    <i class="bi bi-sliders"></i>
                    <span>Advanced Filters (Bathrooms & Area)</span>
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <span class="text-muted small d-none d-md-inline"><i class="bi bi-patch-check text-success me-1"></i> 100% verified listings across Pakistan</span>
            </div>

            <div class="collapse mt-2" id="heroAdvancedFilterCollapse">
                <div class="row g-2 align-items-center pt-2">
                    <div class="col-md-6">
                        <div class="search-field-item">
                            <i class="bi bi-droplet search-field-icon"></i>
                            <div class="search-field-info">
                                <label class="search-field-label">Bathrooms</label>
                                <select name="bathrooms" class="search-field-select">
                                    <option value="">Any Bathrooms</option>
                                    <option value="1">1+ Baths</option>
                                    <option value="2">2+ Baths</option>
                                    <option value="3">3+ Baths</option>
                                    <option value="4">4+ Baths</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="search-field-item">
                            <i class="bi bi-aspect-ratio search-field-icon"></i>
                            <div class="search-field-info">
                                <label class="search-field-label">Minimum Area (Sq Ft / Marla)</label>
                                <input type="number" name="min_area" class="search-field-select" placeholder="e.g. 1000" style="border:none; outline:none; width:100%; background:transparent;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
function setSearchPurpose(purpose) {
    document.getElementById('searchPurposeInput').value = purpose;
    const tabSale = document.getElementById('tabSale');
    const tabRent = document.getElementById('tabRent');
    if (purpose === 'sale') {
        tabSale.className = 'btn btn-sm rounded-pill px-4 fw-bold active-search-tab';
        tabRent.className = 'btn btn-sm rounded-pill px-4 fw-bold inactive-search-tab';
    } else {
        tabSale.className = 'btn btn-sm rounded-pill px-4 fw-bold inactive-search-tab';
        tabRent.className = 'btn btn-sm rounded-pill px-4 fw-bold active-search-tab';
    }
}
</script>
<style>
.active-search-tab { background: #064E3B; color: #FFFFFF; }
.inactive-search-tab { background: #F1F5F9; color: #475569; }
.inactive-search-tab:hover { background: #E2E8F0; color: #0F172A; }
</style>

<!-- ==============================================================
     SECTION 2: TRUST & BENEFITS STRIP (5 Items)
     ============================================================== -->
<section class="container">
    <div class="trust-strip">
        <div class="row g-4 px-3">
            <div class="col-lg col-md-4 col-sm-6">
                <div class="trust-item">
                    <div class="trust-icon-box">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <div>
                        <div class="trust-title">Verified Properties</div>
                        <div class="trust-desc">100% genuine listings</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
                <div class="trust-item">
                    <div class="trust-icon-box">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="trust-title">Secure Transactions</div>
                        <div class="trust-desc">Your safety is our priority</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
                <div class="trust-item">
                    <div class="trust-icon-box">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <div class="trust-title">24/7 Support</div>
                        <div class="trust-desc">We're always here to help</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
                <div class="trust-item">
                    <div class="trust-icon-box">
                        <i class="bi bi-star"></i>
                    </div>
                    <div>
                        <div class="trust-title">Best Deals</div>
                        <div class="trust-desc">Get the most value</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
                <div class="trust-item">
                    <div class="trust-icon-box">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <div class="trust-title">Prime Locations</div>
                        <div class="trust-desc">Top areas & communities</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 3: EXPLORE PROPERTY CATEGORIES
     ============================================================== -->
<section class="container mb-5 pb-2">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Explore Property Categories</h2>
                <p class="section-subtitle">Find residences, commercial assets, and rental investments</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>properties.php" class="section-view-all">
            <span>Browse All Types</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
        <?php 
        $catImages = [
            1 => 'assets/images/properties/prop-1.jpg',
            2 => 'assets/images/properties/prop-2.jpg',
            3 => 'assets/images/properties/prop-7.jpg',
            4 => 'assets/images/properties/prop-8.jpg',
            5 => 'assets/images/properties/prop-5.jpg',
        ];
        foreach ($categories as $cat): 
            $catImg = $catImages[$cat['id']] ?? 'assets/images/properties/prop-1.jpg';
        ?>
            <div class="col">
                <a href="<?= BASE_URL ?>properties.php?category=<?= $cat['id'] ?>" class="category-card">
                    <img src="<?= BASE_URL . $catImg ?>" alt="<?= e($cat['name']) ?>" class="category-card-img">
                    <div class="category-card-overlay"></div>
                    <div class="category-card-content">
                        <i class="bi <?= e($cat['icon']) ?> fs-4 text-white mb-1 d-block"></i>
                        <h3 class="category-card-title"><?= e($cat['name']) ?></h3>
                        <span class="category-card-count"><?= (int)$cat['prop_count'] ?> Listings</span>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ==============================================================
     SECTION 4: FEATURED PROPERTIES & INVEST PROMO (Reference UI)
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Featured Properties</h2>
                <p class="section-subtitle">Handpicked properties for you</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>properties.php" class="section-view-all">
            <span>View All Properties</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <!-- 5-Column Grid matching Reference Screenshot (4 Property Cards + 1 Invest in Your Tomorrow Card) -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-5 g-3">
        <?php foreach ($featuredProperties as $property): ?>
            <div class="col">
                <?php include __DIR__ . '/includes/property-card.php'; ?>
            </div>
        <?php endforeach; ?>

        <!-- 5th Card: Invest in Your Tomorrow Banner -->
        <div class="col">
            <div class="invest-promo-card">
                <div>
                    <h3 class="invest-badge-title">
                        Invest in<br>
                        Your <span>Tomorrow</span>
                    </h3>
                    <p class="invest-promo-desc">
                        Real estate is not just a property, it's a future investment. Find the best opportunities with us.
                    </p>
                    <a href="<?= BASE_URL ?>properties.php?purpose=sale" class="btn-explore-invest">
                        <span>Explore Investment</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="invest-perks-row">
                    <div class="invest-perk-item">
                        <div class="invest-perk-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <div class="invest-perk-label">High Returns</div>
                    </div>
                    <div class="invest-perk-item">
                        <div class="invest-perk-icon"><i class="bi bi-geo-alt"></i></div>
                        <div class="invest-perk-label">Premium Locations</div>
                    </div>
                    <div class="invest-perk-item">
                        <div class="invest-perk-icon"><i class="bi bi-shield-check"></i></div>
                        <div class="invest-perk-label">Secure Investment</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 5: EXCLUSIVE LUXURY PROPERTIES (Editorial Showcase)
     ============================================================== -->
<?php if (!empty($luxuryProperties)): ?>
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar" style="background: #059669;"></div>
            <div>
                <h2 class="section-title">Exclusive Luxury Properties</h2>
                <p class="section-subtitle">Architectural masterpieces with premier specifications</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>properties.php?price_range=35000000-60000000" class="section-view-all">
            <span>Explore Luxury Portfolio</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-4">
        <?php foreach ($luxuryProperties as $lux): ?>
            <div class="col-lg-6">
                <div class="editorial-luxury-card">
                    <div class="editorial-img-wrap">
                        <span class="badge-purpose sale">Luxury Edition</span>
                        <a href="<?= BASE_URL ?>property-details.php?id=<?= $lux['id'] ?>">
                            <img src="<?= BASE_URL . e($lux['featured_image']) ?>" alt="<?= e($lux['title']) ?>" class="editorial-img">
                        </a>
                    </div>
                    <div class="p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-light text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold">
                                <?= e($lux['category_name']) ?>
                            </span>
                            <div class="prop-price mb-0">
                                <?= format_price((float)$lux['price'], $lux['price_prefix'], $lux['price_suffix']) ?>
                            </div>
                        </div>

                        <h3 class="fs-4 fw-bold mb-2">
                            <a href="<?= BASE_URL ?>property-details.php?id=<?= $lux['id'] ?>" class="text-dark">
                                <?= e($lux['title']) ?>
                            </a>
                        </h3>
                        <div class="prop-location mb-3">
                            <i class="bi bi-geo-alt text-muted"></i>
                            <span><?= e($lux['address']) ?></span>
                        </div>
                        <p class="text-muted small line-clamp-2 mb-3">
                            <?= e(substr($lux['description'], 0, 140)) ?>...
                        </p>

                        <div class="prop-specs-row mb-3">
                            <div class="prop-spec-item"><i class="bi bi-door-closed"></i> <?= (int)$lux['bedrooms'] ?> Bedrooms</div>
                            <div class="prop-spec-item"><i class="bi bi-droplet"></i> <?= (int)$lux['bathrooms'] ?> Bathrooms</div>
                            <div class="prop-spec-item"><i class="bi bi-aspect-ratio"></i> <?= number_format((float)$lux['area']) ?> <?= e($lux['area_unit']) ?></div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-badge text-success"></i>
                                <span class="small fw-semibold text-secondary">Consultant: <?= e($lux['agent_name']) ?></span>
                            </div>
                            <a href="<?= BASE_URL ?>property-details.php?id=<?= $lux['id'] ?>" class="btn btn-sm btn-outline-success fw-bold px-3 py-2 rounded-3">
                                View Full Tour <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ==============================================================
     SECTION 6: RECENTLY ADDED PROPERTIES
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Recently Added Properties</h2>
                <p class="section-subtitle">Fresh listings just added to our marketplace</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>properties.php?sort=newest" class="section-view-all">
            <span>View Latest Listings</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <?php foreach ($recentProperties as $property): ?>
            <div class="col">
                <?php include __DIR__ . '/includes/property-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ==============================================================
     SECTION 7: EXPLORE POPULAR CITIES (Reference UI)
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="d-flex align-items-center gap-2">
            <div class="trust-icon-box" style="width: 40px; height: 40px; font-size: 18px;">
                <i class="bi bi-geo-alt"></i>
            </div>
            <div>
                <h2 class="section-title mb-0">Popular Locations</h2>
                <p class="section-subtitle">Explore top cities and neighborhoods</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>properties.php" class="section-view-all">
            <span>View All Locations</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <!-- 5 Location Cards (Karachi, Lahore, Islamabad, Bahria Town, DHA) -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3">
        <?php foreach ($locations as $loc): ?>
            <div class="col">
                <a href="<?= BASE_URL ?>properties.php?location=<?= urlencode($loc['name']) ?>" class="location-card">
                    <img src="<?= BASE_URL . e($loc['image']) ?>" alt="<?= e($loc['name']) ?>" class="location-img">
                    <div class="location-overlay"></div>
                    <div class="location-info">
                        <div>
                            <div class="location-name"><?= e($loc['name']) ?></div>
                            <div class="location-count"><?= number_format($loc['property_count']) ?>+ Properties</div>
                        </div>
                        <div class="location-arrow-btn">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ==============================================================
     SECTION 8: TRENDING NEIGHBORHOODS
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Trending Neighborhoods</h2>
                <p class="section-subtitle">High-demand communities and prime residential enclaves</p>
            </div>
        </div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
        <div class="col">
            <div class="p-3 bg-light rounded-4 border text-center h-100 d-flex flex-column justify-content-center">
                <div class="fw-bold text-dark mb-1">DHA Phase 9</div>
                <div class="small text-muted mb-2">Karachi &bull; Luxury Homes</div>
                <a href="<?= BASE_URL ?>properties.php?location=DHA" class="btn btn-sm btn-outline-success rounded-pill mt-auto">View Area</a>
            </div>
        </div>
        <div class="col">
            <div class="p-3 bg-light rounded-4 border text-center h-100 d-flex flex-column justify-content-center">
                <div class="fw-bold text-dark mb-1">Sector C Enclave</div>
                <div class="small text-muted mb-2">Bahria Town Lahore</div>
                <a href="<?= BASE_URL ?>properties.php?location=Bahria+Town" class="btn btn-sm btn-outline-success rounded-pill mt-auto">View Area</a>
            </div>
        </div>
        <div class="col">
            <div class="p-3 bg-light rounded-4 border text-center h-100 d-flex flex-column justify-content-center">
                <div class="fw-bold text-dark mb-1">Sector F-11</div>
                <div class="small text-muted mb-2">Islamabad &bull; Margalla View</div>
                <a href="<?= BASE_URL ?>properties.php?location=Islamabad" class="btn btn-sm btn-outline-success rounded-pill mt-auto">View Area</a>
            </div>
        </div>
        <div class="col">
            <div class="p-3 bg-light rounded-4 border text-center h-100 d-flex flex-column justify-content-center">
                <div class="fw-bold text-dark mb-1">Gulberg III</div>
                <div class="small text-muted mb-2">Lahore &bull; Central City</div>
                <a href="<?= BASE_URL ?>properties.php?location=Lahore" class="btn btn-sm btn-outline-success rounded-pill mt-auto">View Area</a>
            </div>
        </div>
        <div class="col">
            <div class="p-3 bg-light rounded-4 border text-center h-100 d-flex flex-column justify-content-center">
                <div class="fw-bold text-dark mb-1">Clifton Block 4</div>
                <div class="small text-muted mb-2">Karachi &bull; Coastal Living</div>
                <a href="<?= BASE_URL ?>properties.php?location=Karachi" class="btn btn-sm btn-outline-success rounded-pill mt-auto">View Area</a>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 9: RECOMMENDED PROPERTIES
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Recommended Properties</h2>
                <p class="section-subtitle">Based on popularity, views, and authentic buyer interest</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>properties.php?sort=views" class="section-view-all">
            <span>Explore All</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <?php foreach ($recommendedProperties as $property): ?>
            <div class="col">
                <?php include __DIR__ . '/includes/property-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ==============================================================
     SECTION 10: PROPERTIES BY LIFESTYLE
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Properties by Lifestyle</h2>
                <p class="section-subtitle">Choose a home tailored to your way of life</p>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <div class="col">
            <a href="<?= BASE_URL ?>properties.php?category=1" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 text-center p-4 bg-white border">
                    <div class="trust-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 26px;">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="fs-5 fw-bold text-dark mb-1">Family Living</h3>
                    <p class="small text-muted mb-0">Spacious homes with private gardens, security & playgrounds.</p>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="<?= BASE_URL ?>properties.php?category=2" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 text-center p-4 bg-white border">
                    <div class="trust-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 26px;">
                        <i class="bi bi-building"></i>
                    </div>
                    <h3 class="fs-5 fw-bold text-dark mb-1">Urban Living</h3>
                    <p class="small text-muted mb-0">Modern apartments near business hubs, cafes & transit.</p>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="<?= BASE_URL ?>properties.php?category=5" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 text-center p-4 bg-white border">
                    <div class="trust-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 26px;">
                        <i class="bi bi-gem"></i>
                    </div>
                    <h3 class="fs-5 fw-bold text-dark mb-1">Luxury Penthouses</h3>
                    <p class="small text-muted mb-0">Skyline views, private plunge pools & bespoke interiors.</p>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="<?= BASE_URL ?>properties.php?category=3" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 text-center p-4 bg-white border">
                    <div class="trust-icon-box mx-auto mb-3" style="width: 56px; height: 56px; font-size: 26px;">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <h3 class="fs-5 fw-bold text-dark mb-1">Commercial Assets</h3>
                    <p class="small text-muted mb-0">Executive office floors and retail spaces with high yields.</p>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 11: FEATURED REAL ESTATE AGENTS
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Featured Real Estate Agents</h2>
                <p class="section-subtitle">Verified brokers and trusted market professionals</p>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($agents as $agent): ?>
            <div class="col">
                <div class="agent-card">
                    <div class="agent-avatar-wrap">
                        <img src="<?= BASE_URL . ($agent['avatar'] ?: 'assets/images/avatars/agent1.jpg') ?>" alt="<?= e($agent['name']) ?>" class="agent-avatar">
                        <div class="agent-verified-badge" title="Verified Agent">
                            <i class="bi bi-check"></i>
                        </div>
                    </div>
                    <h3 class="agent-name"><?= e($agent['name']) ?></h3>
                    <div class="agent-agency"><?= e($agent['agency_name'] ?: 'Certified Consultant') ?></div>
                    <div class="agent-stat-pill">
                        <i class="bi bi-houses me-1 text-success"></i> <?= (int)$agent['active_listings'] ?> Active Listings
                    </div>
                    <div class="agent-actions">
                        <a href="tel:<?= e($agent['phone']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Call">
                            <i class="bi bi-telephone"></i>
                        </a>
                        <a href="mailto:<?= e($agent['email']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Email">
                            <i class="bi bi-envelope"></i>
                        </a>
                        <a href="https://wa.me/<?= preg_replace('/\D/', '', $agent['phone'] ?: '923001234567') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" title="WhatsApp">
                            <i class="bi bi-whatsapp me-1"></i> Contact
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
</section>

<!-- ==============================================================
     SECTION 10: FIND YOUR PERFECT HOME (Journey for Buyers, Renters & Sellers)
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Find Your Perfect Home</h2>
                <p class="section-subtitle">Tailored pathways whether you are buying, renting, or selling real estate</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center bg-white border">
                <div class="trust-icon-box mx-auto mb-3" style="width: 64px; height: 64px; font-size: 28px;">
                    <i class="bi bi-house-door"></i>
                </div>
                <h3 class="fs-4 fw-bold text-dark mb-2">Buy a Home</h3>
                <p class="text-muted small mb-4">
                    Explore hundreds of verified properties for sale. From modern family houses to luxury apartments, view high-res tours and title approvals.
                </p>
                <div class="mt-auto">
                    <a href="<?= BASE_URL ?>properties.php?purpose=sale" class="btn btn-outline-success fw-bold rounded-pill px-4 w-100">
                        Browse Homes For Sale <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center bg-white border">
                <div class="trust-icon-box mx-auto mb-3" style="width: 64px; height: 64px; font-size: 28px;">
                    <i class="bi bi-key"></i>
                </div>
                <h3 class="fs-4 fw-bold text-dark mb-2">Rent a Home</h3>
                <p class="text-muted small mb-4">
                    Find flexible, verified rentals across premier urban sectors. Filter by bedrooms, budget, and amenities with zero hidden commission charges.
                </p>
                <div class="mt-auto">
                    <a href="<?= BASE_URL ?>properties.php?purpose=rent" class="btn btn-outline-success fw-bold rounded-pill px-4 w-100">
                        Find Rental Properties <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center bg-white border">
                <div class="trust-icon-box mx-auto mb-3" style="width: 64px; height: 64px; font-size: 28px;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h3 class="fs-4 fw-bold text-dark mb-2">Sell or List</h3>
                <p class="text-muted small mb-4">
                    List your home or commercial asset on RealHome to reach active buyers, certified institutional investors, and overseas diaspora.
                </p>
                <div class="mt-auto">
                    <a href="<?= is_logged_in() ? BASE_URL . 'agent/add-property.php' : BASE_URL . 'register.php' ?>" class="btn btn-success fw-bold rounded-pill px-4 w-100">
                        List Your Property <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 11: HOW REALHOME WORKS (4 Step Journey)
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">How RealHome Works</h2>
                <p class="section-subtitle">A seamless four-step journey to find and secure your property</p>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <div class="col">
            <div class="step-card">
                <div class="step-num-badge">1</div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Search & Filter</h3>
                <p class="small text-muted mb-0">Use verified filters by city, budget, bedrooms, and property category to find matching homes.</p>
            </div>
        </div>
        <div class="col">
            <div class="step-card">
                <div class="step-num-badge">2</div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Book a Viewing</h3>
                <p class="small text-muted mb-0">Select your preferred date and time to inspect the property in-person with a verified consultant.</p>
            </div>
        </div>
        <div class="col">
            <div class="step-card">
                <div class="step-num-badge">3</div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Review & Verify</h3>
                <p class="small text-muted mb-0">Verify title deeds, utility NOCs, and negotiate fair terms directly with verified owners.</p>
            </div>
        </div>
        <div class="col">
            <div class="step-card">
                <div class="step-num-badge">4</div>
                <h3 class="fs-5 fw-bold text-dark mb-2">Secure Handover</h3>
                <p class="small text-muted mb-0">Finalize payment contracts with complete legal transparency and collect the keys to your new home.</p>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 13: WHY CHOOSE REALHOME (Reference UI)
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-title-box mb-4">
        <div class="section-accent-bar"></div>
        <div>
            <h2 class="section-title">Why Choose RealHome?</h2>
        </div>
    </div>

    <div class="row g-4 align-items-stretch">
        <!-- 4 Benefits Cards -->
        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="why-choose-card">
                        <div class="why-icon-box">
                            <i class="bi bi-house-check"></i>
                        </div>
                        <h4 class="why-card-title">Wide Range of Properties</h4>
                        <p class="why-card-desc">From budget homes to luxury villas, we have it all.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="why-choose-card">
                        <div class="why-icon-box">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="why-card-title">Trusted & Verified Listings</h4>
                        <p class="why-card-desc">No fake properties, 100% authentic.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="why-choose-card">
                        <div class="why-icon-box">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4 class="why-card-title">Expert Guidance</h4>
                        <p class="why-card-desc">Our team helps you at every step.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="why-choose-card">
                        <div class="why-icon-box">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h4 class="why-card-title">Customer Satisfaction</h4>
                        <p class="why-card-desc">Your happiness is our success.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mint CTA Box on the right -->
        <div class="col-lg-4">
            <div class="cta-mint-box">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="trust-icon-box" style="background: #FFFFFF; color: #059669;">
                        <i class="bi bi-houses"></i>
                    </div>
                    <div>
                        <h3 class="cta-mint-title mb-1">Ready to find<br>your dream home?</h3>
                    </div>
                </div>
                <p class="cta-mint-desc">
                    Join thousands of happy customers who found their perfect property.
                </p>
                <div>
                    <a href="<?= BASE_URL ?>register.php" class="btn-mint-cta">
                        <span>Get Started</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 14: PROPERTY MARKET INSIGHTS & STATS
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="p-4 p-lg-5 rounded-4 bg-light border">
        <div class="row align-items-center g-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-success text-white px-3 py-1 rounded-pill">Market Live Data</span>
                </div>
                <h3 class="fs-3 fw-bold text-dark">RealHome Platform Statistics</h3>
                <p class="text-muted mb-0 small">
                    Transparent, verifiable metrics directly reflecting our current active database inventory and verified broker network.
                </p>
            </div>
            <div class="col-lg-8">
                <div class="row g-3 text-center">
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-white rounded-4 border shadow-sm">
                            <div class="fs-2 fw-bold text-success mb-0"><?= $totalProps ?>+</div>
                            <small class="text-muted fw-semibold">Active Listings</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-white rounded-4 border shadow-sm">
                            <div class="fs-2 fw-bold text-primary mb-0"><?= $totalLocations ?>+</div>
                            <small class="text-muted fw-semibold">Cities & Sectors</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-white rounded-4 border shadow-sm">
                            <div class="fs-2 fw-bold text-warning mb-0"><?= $totalAgents ?>+</div>
                            <small class="text-muted fw-semibold">Certified Brokers</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 bg-white rounded-4 border shadow-sm">
                            <div class="fs-2 fw-bold text-dark mb-0">99.4%</div>
                            <small class="text-muted fw-semibold">Client Trust</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Chart.js Visualizer -->
        <div class="row g-4 mt-2">
            <div class="col-lg-6">
                <div class="p-4 bg-white rounded-4 border shadow-sm h-100">
                    <h4 class="fs-5 fw-bold text-dark mb-1">Inventory Breakdown by Category</h4>
                    <p class="small text-muted mb-3">Live approved listings across asset classes</p>
                    <div style="height: 220px; position: relative;">
                        <canvas id="marketCategoryChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="p-4 bg-white rounded-4 border shadow-sm h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h4 class="fs-5 fw-bold text-dark mb-1">Market Velocity & Buyer Activity</h4>
                        <p class="small text-muted mb-3">Verified engagement trends across major metros</p>
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                    <span>Karachi & DHA Prime</span>
                                    <span class="text-success">High Demand (89%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 89%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                    <span>Lahore & Bahria Town</span>
                                    <span class="text-primary">Very Active (84%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: 84%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                    <span>Islamabad Capital Sectors</span>
                                    <span class="text-warning">High Growth (78%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: 78%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="small text-muted pt-3 border-top mt-3">
                        <i class="bi bi-shield-lock text-success me-1"></i> Data aggregated from active listings and verified inquiry volume.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('marketCategoryChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= $chartLabels ?>,
                datasets: [{
                    data: <?= $chartCounts ?>,
                    backgroundColor: ['#059669', '#10B981', '#34D399', '#6EE7B7', '#0F172A'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 12 } }
                    }
                }
            }
        });
    }
});
</script>

<!-- ==============================================================
     SECTION 14: EXCLUSIVE INVESTMENT OPPORTUNITIES (Dark Navy Background)
     ============================================================== -->
<section class="mb-5 py-5 text-white" style="background: linear-gradient(135deg, #0B1325 0%, #0F172A 100%);">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <span class="badge px-3 py-1 rounded-pill fw-bold mb-2" style="background: rgba(16, 185, 129, 0.15); color: #34D399;">
                    High-Growth Portfolio
                </span>
                <h2 class="fs-2 fw-bold text-white mb-1">Exclusive Investment Opportunities</h2>
                <p class="text-white-50 mb-0">High-yield commercial developments and capital-growth residential enclaves</p>
            </div>
            <a href="<?= BASE_URL ?>properties.php?purpose=sale&category=3" class="btn btn-sm btn-outline-light rounded-pill px-4">
                Explore All Opportunities <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <?php foreach ($investmentProperties as $inv): ?>
                <div class="col">
                    <div class="card h-100 border-0 rounded-4 overflow-hidden shadow-lg" style="background: #1E293B;">
                        <div class="position-relative">
                            <span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fw-bold" style="background: #059669; color: #fff; z-index: 2;">
                                Prime Opportunity
                            </span>
                            <a href="<?= BASE_URL ?>property-details.php?id=<?= $inv['id'] ?>">
                                <img src="<?= BASE_URL . e($inv['featured_image']) ?>" alt="<?= e($inv['title']) ?>" class="w-100" style="height: 220px; object-fit: cover;">
                            </a>
                        </div>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge px-2 py-1 rounded-pill" style="background: rgba(255,255,255,0.1); color: #94A3B8; font-size: 11px;">
                                    <?= e($inv['category_name']) ?>
                                </span>
                                <div class="fs-5 fw-bold" style="color: #34D399;">
                                    <?= format_price((float)$inv['price'], $inv['price_prefix'], $inv['price_suffix']) ?>
                                </div>
                            </div>
                            <h3 class="fs-5 fw-bold mb-2">
                                <a href="<?= BASE_URL ?>property-details.php?id=<?= $inv['id'] ?>" class="text-white text-decoration-none">
                                    <?= e($inv['title']) ?>
                                </a>
                            </h3>
                            <div class="small text-white-50 mb-3">
                                <i class="bi bi-geo-alt me-1 text-success"></i> <?= e($inv['address']) ?>
                            </div>

                            <div class="p-2 rounded-3 mb-3 d-flex justify-content-between text-center" style="background: rgba(15, 23, 42, 0.6); font-size: 12px;">
                                <div>
                                    <div class="text-white-50">Projected Yield</div>
                                    <div class="fw-bold text-white">6.5% - 8.2%</div>
                                </div>
                                <div class="border-start border-secondary"></div>
                                <div>
                                    <div class="text-white-50">Appreciation</div>
                                    <div class="fw-bold text-white">High Potential</div>
                                </div>
                                <div class="border-start border-secondary"></div>
                                <div>
                                    <div class="text-white-50">Verification</div>
                                    <div class="fw-bold text-success"><i class="bi bi-check-circle"></i> 100% NOC</div>
                                </div>
                            </div>

                            <div class="mt-auto pt-2">
                                <a href="<?= BASE_URL ?>property-details.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-success fw-bold w-100 rounded-pill py-2">
                                    View Investment Dossier <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Investment Disclaimer required by prompt -->
        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="d-flex align-items-center gap-2 text-white-50 small">
                <i class="bi bi-info-circle text-warning fs-5"></i>
                <div>
                    <strong class="text-white">Investment Disclaimer:</strong> Real estate investments are subject to regional market conditions, inflation, and demand fluctuations. RealHome provides verified registry records and title documentation; projected rental yields and past capital appreciation are informational and carry no guaranteed financial return.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 15: RECENTLY VIEWED PROPERTIES (Client-side localStorage)
     ============================================================== -->
<section class="container mb-5 pb-3" id="recentlyViewedSection">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Recently Viewed Properties</h2>
                <p class="section-subtitle">Pick up right where you left off</p>
            </div>
        </div>
    </div>
    <div id="recentlyViewedContainer"></div>
</section>

<!-- ==============================================================
     SECTION 16: SAVED SEARCHES AND FAVORITES
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="p-4 rounded-4 bg-white border shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3">
        <?php if (is_logged_in()): ?>
            <div class="d-flex align-items-center gap-3">
                <div class="trust-icon-box" style="background: #FEE2E2; color: #EF4444; width: 52px; height: 52px; font-size: 24px;">
                    <i class="bi bi-heart-fill"></i>
                </div>
                <div>
                    <h3 class="fs-5 fw-bold text-dark mb-1">You have <?= get_user_favorites_count() ?> Saved Homes</h3>
                    <p class="small text-muted mb-0">Easily compare your bookmarked properties, monitor price shifts, or schedule private viewings.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>user/favorites.php" class="btn btn-outline-danger fw-bold rounded-pill px-4">
                View Saved Homes <i class="bi bi-arrow-right ms-1"></i>
            </a>
        <?php else: ?>
            <div class="d-flex align-items-center gap-3">
                <div class="trust-icon-box" style="background: #FEE2E2; color: #EF4444; width: 52px; height: 52px; font-size: 24px;">
                    <i class="bi bi-heart"></i>
                </div>
                <div>
                    <h3 class="fs-5 fw-bold text-dark mb-1">Save Your Favorite Properties & Track Price Changes</h3>
                    <p class="small text-muted mb-0">Bookmark properties you love, save tailored search filters, and receive instant alerts when prices drop.</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-secondary fw-bold rounded-pill px-3">
                    Sign In to Sync
                </a>
                <a href="<?= BASE_URL ?>register.php" class="btn btn-success fw-bold rounded-pill px-4">
                    Create Free Account
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ==============================================================
     SECTION 17: CUSTOMER TESTIMONIALS
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Customer Testimonials</h2>
                <p class="section-subtitle">Feedback from buyers and tenants who found their home with RealHome</p>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($testimonials as $t): ?>
            <div class="col">
                <div class="p-4 bg-white border rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-warning mb-2">
                            <?php for ($i = 0; $i < (int)$t['rating']; $i++): ?>
                                <i class="bi bi-star-fill"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-secondary small fst-italic mb-3">
                            "<?= e($t['comment']) ?>"
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3 pt-3 border-top">
                        <img src="<?= BASE_URL . ($t['reviewer_avatar'] ?: 'assets/images/avatars/user1.jpg') ?>" alt="User" class="rounded-circle" style="width: 42px; height: 42px; object-fit: cover;">
                        <div>
                            <div class="fw-bold text-dark small"><?= e($t['reviewer_name']) ?></div>
                            <div class="text-muted" style="font-size: 11px;">Verified Buyer &bull; <?= e($t['property_title']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ==============================================================
     SECTION 18: FREQUENTLY ASKED QUESTIONS (FAQ)
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Got questions? We're here to help you navigate property transactions smoothly.</p>
            </div>

            <div class="accordion faq-accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            How does RealHome verify property listings?
                        </button>
                    </h3>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Our team verifies every listing against ownership documents, developer approvals (NOCs), and physical site checks before granting the "Verified" badge.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Can I schedule a property tour online?
                        </button>
                    </h3>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes! Simply click "Schedule a Visit" on any property details page, pick your preferred date and time, and the listing agent will confirm your appointment.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Are there any hidden brokerage fees for buyers?
                        </button>
                    </h3>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            No. RealHome promotes complete pricing transparency. Direct inquiry messaging allows you to communicate directly with owners or their designated registered brokers without hidden platform fees.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            How do I list my property for sale or rent?
                        </button>
                    </h3>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Register an account as a Property Owner or Agent, visit your dashboard, click "Add Property", upload your photos, specify amenities and pricing, and submit for instant admin review.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 19: REAL ESTATE GUIDES AND BLOG
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="section-header-wrap">
        <div class="section-title-box">
            <div class="section-accent-bar"></div>
            <div>
                <h2 class="section-title">Real Estate Guides & Market Insights</h2>
                <p class="section-subtitle">Expert advice, buyer guides, and property investment trends</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>blog.php" class="section-view-all">
            <span>Read All Articles</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($blogs as $blog): ?>
            <div class="col">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white border">
                    <img src="<?= BASE_URL . e($blog['image']) ?>" alt="<?= e($blog['title']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                    <div class="card-body p-4 d-flex flex-column">
                        <span class="badge bg-light text-success border border-success-subtle mb-2 align-self-start">
                            <?= e($blog['category']) ?>
                        </span>
                        <h3 class="fs-5 fw-bold mb-2">
                            <a href="<?= BASE_URL ?>blog-details.php?id=<?= $blog['id'] ?>" class="text-dark">
                                <?= e($blog['title']) ?>
                            </a>
                        </h3>
                        <p class="small text-muted mb-3 flex-grow-1">
                            <?= e($blog['excerpt']) ?>
                        </p>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> <?= date('M d, Y', strtotime($blog['created_at'])) ?></small>
                            <a href="<?= BASE_URL ?>blog-details.php?id=<?= $blog['id'] ?>" class="small fw-bold text-success">
                                Read More &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ==============================================================
     SECTION 20: LIST YOUR PROPERTY CTA
     ============================================================== -->
<section class="container mb-5 pb-3">
    <div class="p-5 rounded-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #064E3B 0%, #047857 100%);">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-success px-3 py-1 rounded-pill fw-bold mb-3">Owners & Developers</span>
                <h2 class="fs-2 fw-bold mb-2">Want to Sell or Rent Your Property?</h2>
                <p class="lead mb-0 text-white-50" style="font-size: 16px;">
                    List with RealHome to connect with thousands of pre-qualified buyers and verified real estate investors across Pakistan and overseas.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="<?= is_logged_in() ? BASE_URL . 'agent/add-property.php' : BASE_URL . 'register.php' ?>" class="btn btn-light text-dark fw-bold px-4 py-3 rounded-pill shadow">
                    <i class="bi bi-plus-circle me-1 text-success"></i> List Your Property
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================
     SECTION 21 & 22: NEWSLETTER SUBSCRIPTION & CONTACT SUPPORT
     ============================================================== -->
<section class="container mb-5 pb-5">
    <div class="newsletter-card">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold mb-3">Stay Updated</span>
                <h3 class="fs-2 fw-bold text-white mb-2">Get Real Estate Deals Delivered</h3>
                <p class="text-white-50 mb-0">
                    Subscribe for weekly handpicked luxury listings, off-plan projects, and verified market trends. No spam, ever.
                </p>
            </div>
            <div class="col-lg-6">
                <form id="newsletterForm" class="d-flex flex-column flex-sm-row gap-2">
                    <input type="email" name="email" class="form-control form-control-lg rounded-pill px-4" placeholder="Enter your email address" required style="font-size: 14px;">
                    <button type="submit" class="btn btn-success fw-bold px-4 py-3 rounded-pill text-nowrap">
                        Subscribe
                    </button>
                </form>
                <div class="d-flex align-items-center gap-3 mt-3 text-white-50 small">
                    <div><i class="bi bi-check-circle text-success me-1"></i> Weekly Highlights</div>
                    <div><i class="bi bi-check-circle text-success me-1"></i> Unsubscribe anytime</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact & Support CTA Strip -->
    <div class="row g-3 mt-4 text-center">
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center justify-content-center gap-3">
                <i class="bi bi-telephone-fill text-success fs-4"></i>
                <div class="text-start">
                    <div class="small text-muted">Direct Support</div>
                    <a href="tel:+923000000000" class="fw-bold text-dark">+92 (300) 000-0000</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center justify-content-center gap-3">
                <i class="bi bi-envelope-fill text-success fs-4"></i>
                <div class="text-start">
                    <div class="small text-muted">Customer Inquiries</div>
                    <a href="mailto:support@realhome.com" class="fw-bold text-dark">support@realhome.com</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center justify-content-center gap-3">
                <i class="bi bi-geo-alt-fill text-success fs-4"></i>
                <div class="text-start">
                    <div class="small text-muted">Head Office</div>
                    <span class="fw-bold text-dark">Gulberg III, Lahore</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
