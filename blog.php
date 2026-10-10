<?php
/**
 * RealHome - Real Estate Blog & Market Insights
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$db = getDB();
$category = sanitize_input($_GET['category'] ?? '');
$search = sanitize_input($_GET['q'] ?? '');

$where = ["b.status = 'published'"];
$params = [];

if (!empty($category)) {
    $where[] = "b.category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where[] = "(b.title LIKE ? OR b.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = implode(' AND ', $where);

$stmt = $db->prepare("
    SELECT b.*, u.name AS author_name 
    FROM blog_posts b
    JOIN users u ON b.author_id = u.id
    WHERE $whereSql
    ORDER BY b.id DESC
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get categories
$categories = $db->query("SELECT DISTINCT category FROM blog_posts WHERE status = 'published'")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = "Real Estate Blog & Insights";
include __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="py-5 text-white position-relative" style="background: linear-gradient(135deg, #0A1120 0%, #0F172A 100%);">
    <div class="container py-4 text-center">
        <span class="badge bg-success px-3 py-1 rounded-pill fw-bold mb-3">Knowledge Base</span>
        <h1 class="display-5 fw-bold text-white mb-2">Real Estate Insights & Guides</h1>
        <p class="text-white-50 mx-auto mb-0" style="max-width: 650px;">
            In-depth analysis of property price trends, buying checklists, smart home tech, and rental laws in Pakistan.
        </p>
    </div>
</div>

<div class="container py-5">
    <!-- Search & Filter Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-5 p-3 bg-light rounded-4 border">
        <!-- Categories Pills -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>blog.php" class="btn btn-sm rounded-pill px-3 fw-bold <?= empty($category) ? 'btn-success' : 'btn-outline-secondary' ?>">
                All Categories
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>blog.php?category=<?= urlencode($cat) ?>" class="btn btn-sm rounded-pill px-3 fw-bold <?= $category === $cat ? 'btn-success' : 'btn-outline-secondary' ?>">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Form -->
        <form action="<?= BASE_URL ?>blog.php" method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control form-control-sm rounded-pill px-3" placeholder="Search guides..." value="<?= e($search) ?>" style="width: 220px;">
            <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

    <!-- Blog Posts Grid -->
    <?php if (empty($posts)): ?>
        <div class="text-center py-5">
            <i class="bi bi-journal-text fs-1 text-muted d-block mb-3"></i>
            <h3 class="fs-4 fw-bold text-dark">No Articles Found</h3>
            <p class="text-muted">No guides match your search criteria. Try browsing all categories.</p>
            <a href="<?= BASE_URL ?>blog.php" class="btn btn-success fw-bold rounded-pill px-4">All Articles</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($posts as $post): ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white border">
                        <a href="<?= BASE_URL ?>blog-details.php?id=<?= $post['id'] ?>">
                            <img src="<?= BASE_URL . e($post['image']) ?>" alt="<?= e($post['title']) ?>" class="card-img-top" style="height: 220px; object-fit: cover;">
                        </a>
                        <div class="card-body p-4 d-flex flex-column">
                            <span class="badge bg-light text-success border border-success-subtle mb-2 align-self-start fw-bold">
                                <?= e($post['category']) ?>
                            </span>
                            <h3 class="fs-5 fw-bold mb-2">
                                <a href="<?= BASE_URL ?>blog-details.php?id=<?= $post['id'] ?>" class="text-dark text-decoration-none">
                                    <?= e($post['title']) ?>
                                </a>
                            </h3>
                            <p class="small text-muted mb-4 flex-grow-1">
                                <?= e($post['excerpt']) ?>
                            </p>
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top small text-muted">
                                <div><i class="bi bi-calendar3 me-1"></i> <?= date('M d, Y', strtotime($post['created_at'])) ?></div>
                                <a href="<?= BASE_URL ?>blog-details.php?id=<?= $post['id'] ?>" class="fw-bold text-success text-decoration-none">
                                    Read Article &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
