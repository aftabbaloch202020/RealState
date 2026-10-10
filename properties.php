<?php
/**
 * RealHome - Properties Listing & Advanced Search
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$db = getDB();

// Input parameters
$keyword = sanitize_input($_GET['keyword'] ?? '');
$purpose = sanitize_input($_GET['purpose'] ?? '');
$categoryId = (int)($_GET['category'] ?? 0);
$locationParam = sanitize_input($_GET['location'] ?? '');
$priceRange = sanitize_input($_GET['price_range'] ?? '');
$minPrice = !empty($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$bedrooms = (int)($_GET['bedrooms'] ?? 0);
$bathrooms = (int)($_GET['bathrooms'] ?? 0);
$minArea = !empty($_GET['min_area']) ? (float)$_GET['min_area'] : 0;
$maxArea = !empty($_GET['max_area']) ? (float)$_GET['max_area'] : 0;
$selectedFeatures = isset($_GET['features']) && is_array($_GET['features']) ? array_map('intval', $_GET['features']) : [];
$sort = sanitize_input($_GET['sort'] ?? 'newest');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

// Parse price range presets if given
if (!empty($priceRange)) {
    if (strpos($priceRange, '+') !== false) {
        $minPrice = (float)str_replace('+', '', $priceRange);
    } elseif (strpos($priceRange, '-') !== false) {
        [$minP, $maxP] = explode('-', $priceRange);
        $minPrice = (float)$minP;
        $maxPrice = (float)$maxP;
    }
}

// Build query
$where = ["p.status = 'approved'"];
$params = [];

if (!empty($keyword)) {
    $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

if (!empty($purpose)) {
    $where[] = "p.purpose = ?";
    $params[] = $purpose;
}

if ($categoryId > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryId;
}

if (!empty($locationParam)) {
    $where[] = "(l.name LIKE ? OR p.city LIKE ? OR p.address LIKE ?)";
    $params[] = "%$locationParam%";
    $params[] = "%$locationParam%";
    $params[] = "%$locationParam%";
}

if ($minPrice > 0) {
    $where[] = "p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $where[] = "p.price <= ?";
    $params[] = $maxPrice;
}

if ($bedrooms > 0) {
    $where[] = "p.bedrooms >= ?";
    $params[] = $bedrooms;
}

if ($bathrooms > 0) {
    $where[] = "p.bathrooms >= ?";
    $params[] = $bathrooms;
}

if ($minArea > 0) {
    $where[] = "p.area >= ?";
    $params[] = $minArea;
}

if ($maxArea > 0) {
    $where[] = "p.area <= ?";
    $params[] = $maxArea;
}

if (!empty($selectedFeatures)) {
    $inClause = implode(',', array_fill(0, count($selectedFeatures), '?'));
    $where[] = "p.id IN (SELECT property_id FROM property_feature_map WHERE feature_id IN ($inClause) GROUP BY property_id HAVING COUNT(DISTINCT feature_id) = " . count($selectedFeatures) . ")";
    foreach ($selectedFeatures as $sf) {
        $params[] = $sf;
    }
}

$whereSql = implode(' AND ', $where);

// Sorting
$orderBy = match($sort) {
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'views' => 'p.views_count DESC',
    default => 'p.id DESC'
};

// Count total matching
$countStmt = $db->prepare("
    SELECT COUNT(*) 
    FROM properties p
    JOIN property_categories c ON p.category_id = c.id
    JOIN locations l ON p.location_id = l.id
    WHERE $whereSql
");
$countStmt->execute($params);
$totalResults = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalResults / $limit);

// Fetch paginated results
$stmt = $db->prepare("
    SELECT p.*, c.name AS category_name, l.name AS location_name 
    FROM properties p
    JOIN property_categories c ON p.category_id = c.id
    JOIN locations l ON p.location_id = l.id
    WHERE $whereSql
    ORDER BY $orderBy
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$properties = $stmt->fetchAll();

// Fetch filter options
$categories = $db->query("SELECT id, name FROM property_categories ORDER BY name ASC")->fetchAll();
$locations = $db->query("SELECT id, name, city FROM locations ORDER BY name ASC")->fetchAll();
$features = $db->query("SELECT id, name FROM property_features ORDER BY name ASC")->fetchAll();

$pageTitle = "Explore Properties";
include __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb Header -->
<div class="bg-light py-4 border-bottom">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Properties For Sale & Rent</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Properties</li>
                    </ol>
                </nav>
            </div>
            <div class="text-muted small">
                Showing <strong><?= count($properties) ?></strong> of <strong><?= $totalResults ?></strong> properties
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar Filter Form -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="fs-5 fw-bold text-dark mb-0">Filters</h3>
                    <a href="<?= BASE_URL ?>properties.php" class="small text-danger fw-semibold text-decoration-none">Reset All</a>
                </div>

                <form action="<?= BASE_URL ?>properties.php" method="GET">
                    <!-- Keyword -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Keyword</label>
                        <input type="text" name="keyword" class="form-control form-control-sm rounded-3" placeholder="Search title, address..." value="<?= e($keyword) ?>">
                    </div>

                    <!-- Purpose -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Purpose</label>
                        <select name="purpose" class="form-select form-select-sm rounded-3">
                            <option value="">All Purposes</option>
                            <option value="sale" <?= $purpose === 'sale' ? 'selected' : '' ?>>For Sale</option>
                            <option value="rent" <?= $purpose === 'rent' ? 'selected' : '' ?>>For Rent</option>
                        </select>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Property Type</label>
                        <select name="category" class="form-select form-select-sm rounded-3">
                            <option value="">All Types</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Location</label>
                        <select name="location" class="form-select form-select-sm rounded-3">
                            <option value="">All Cities / Areas</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= e($loc['name']) ?>" <?= $locationParam === $loc['name'] ? 'selected' : '' ?>><?= e($loc['name']) ?>, <?= e($loc['city']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Bedrooms -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Bedrooms</label>
                        <select name="bedrooms" class="form-select form-select-sm rounded-3">
                            <option value="">Any Bedrooms</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= $bedrooms === $i ? 'selected' : '' ?>><?= $i ?>+ Beds</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Price Min / Max -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Price Range (PKR)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control form-control-sm rounded-3" placeholder="Min Price" value="<?= $minPrice > 0 ? (int)$minPrice : '' ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control form-control-sm rounded-3" placeholder="Max Price" value="<?= $maxPrice > 0 ? (int)$maxPrice : '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Amenities / Features -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark mb-2">Amenities</label>
                        <div style="max-height: 150px; overflow-y: auto;">
                            <?php foreach ($features as $f): ?>
                                <div class="form-check small mb-1">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="<?= $f['id'] ?>" id="feat_<?= $f['id'] ?>" <?= in_array((int)$f['id'], $selectedFeatures) ? 'checked' : '' ?>>
                                    <label class="form-check-label text-secondary" for="feat_<?= $f['id'] ?>">
                                        <?= e($f['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold rounded-3 py-2 shadow-sm">
                        <i class="bi bi-filter me-1"></i> Apply Filters
                    </button>
                </form>
            </div>
        </div>

        <!-- Property Grid & Sorting -->
        <div class="col-lg-9">
            <!-- Sorting Bar -->
            <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-4 border shadow-sm mb-4 flex-wrap gap-2">
                <div class="text-muted small">
                    Found <strong><?= $totalResults ?></strong> listing<?= $totalResults === 1 ? '' : 's' ?>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted fw-bold text-nowrap">Sort By:</label>
                    <select class="form-select form-select-sm rounded-3" style="width: auto;" onchange="location = this.value;">
                        <?php 
                        $queryParams = $_GET;
                        $makeSortUrl = function($s) use ($queryParams) {
                            $queryParams['sort'] = $s;
                            return '?' . http_build_query($queryParams);
                        };
                        ?>
                        <option value="<?= $makeSortUrl('newest') ?>" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="<?= $makeSortUrl('price_asc') ?>" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="<?= $makeSortUrl('price_desc') ?>" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="<?= $makeSortUrl('views') ?>" <?= $sort === 'views' ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                </div>
            </div>

            <!-- Listings Cards -->
            <?php if (empty($properties)): ?>
                <div class="text-center py-5 bg-white rounded-4 border shadow-sm">
                    <div class="trust-icon-box mx-auto mb-3" style="width: 64px; height: 64px; font-size: 28px; background: #FEE2E2; color: #EF4444;">
                        <i class="bi bi-search"></i>
                    </div>
                    <h3 class="fs-4 fw-bold text-dark">No Properties Found</h3>
                    <p class="text-muted">We couldn't find any listings matching your specific filter criteria.</p>
                    <a href="<?= BASE_URL ?>properties.php" class="btn btn-outline-success fw-bold rounded-pill px-4">
                        Clear All Filters
                    </a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    <?php foreach ($properties as $property): ?>
                        <div class="col">
                            <?php include __DIR__ . '/includes/property-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-5" aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <?php $queryParams['page'] = $page - 1; ?>
                                <li class="page-item">
                                    <a class="page-link rounded-circle m-1" href="?<?= http_build_query($queryParams) ?>" aria-label="Previous">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <?php $queryParams['page'] = $p; ?>
                                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link rounded-circle m-1 <?= $p === $page ? 'bg-success border-success text-white' : '' ?>" href="?<?= http_build_query($queryParams) ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <?php $queryParams['page'] = $page + 1; ?>
                                <li class="page-item">
                                    <a class="page-link rounded-circle m-1" href="?<?= http_build_query($queryParams) ?>" aria-label="Next">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
