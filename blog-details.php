<?php
/**
 * RealHome - Blog Article Details
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . 'blog.php');
    exit;
}

$db = getDB();

// Increment views
$db->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?")->execute([$id]);

$stmt = $db->prepare("
    SELECT b.*, u.name AS author_name, u.avatar AS author_avatar
    FROM blog_posts b
    JOIN users u ON b.author_id = u.id
    WHERE b.id = ? AND b.status = 'published'
");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: ' . BASE_URL . '404.php');
    exit;
}

// Recent posts for sidebar
$recentPosts = $db->query("SELECT id, title, image, created_at FROM blog_posts WHERE status = 'published' AND id != $id ORDER BY id DESC LIMIT 3")->fetchAll();

$pageTitle = $post['title'];
include __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<div class="bg-light py-3 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>blog.php" class="text-decoration-none">Blog</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($post['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">
            <article class="bg-white p-4 p-md-5 rounded-4 border shadow-sm">
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold mb-3">
                    <?= e($post['category']) ?>
                </span>
                
                <h1 class="display-6 fw-bold text-dark mb-3"><?= e($post['title']) ?></h1>

                <div class="d-flex align-items-center gap-3 text-muted small pb-4 border-bottom mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <img src="<?= BASE_URL . ($post['author_avatar'] ?: 'assets/images/avatars/admin.jpg') ?>" alt="Author" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                        <span class="fw-bold text-dark"><?= e($post['author_name']) ?></span>
                    </div>
                    <span>&bull;</span>
                    <div><i class="bi bi-calendar3 me-1"></i> <?= date('M d, Y', strtotime($post['created_at'])) ?></div>
                    <span>&bull;</span>
                    <div><i class="bi bi-eye me-1"></i> <?= (int)$post['views'] ?> views</div>
                </div>

                <div class="rounded-4 overflow-hidden mb-4 shadow-sm border">
                    <img src="<?= BASE_URL . e($post['image']) ?>" alt="<?= e($post['title']) ?>" class="w-100" style="max-height: 420px; object-fit: cover;">
                </div>

                <div class="article-content text-secondary" style="font-size: 16px; line-height: 1.8;">
                    <?= $post['content'] ?>
                </div>

                <hr class="my-5">

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-dark small">Share Article:</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="navigator.clipboard.writeText(window.location.href); window.showToast('Article link copied!', 'success');">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    </div>
                    <a href="<?= BASE_URL ?>blog.php" class="btn btn-outline-success btn-sm rounded-pill px-4 fw-bold">
                        &larr; Back to All Guides
                    </a>
                </div>
            </article>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border mb-4">
                <h3 class="fs-5 fw-bold text-dark mb-3">Recent Guides</h3>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($recentPosts as $rp): ?>
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= BASE_URL . e($rp['image']) ?>" alt="Post" class="rounded-3" style="width: 64px; height: 64px; object-fit: cover; flex-shrink: 0;">
                            <div>
                                <h4 class="fs-6 fw-bold mb-1">
                                    <a href="<?= BASE_URL ?>blog-details.php?id=<?= $rp['id'] ?>" class="text-dark text-decoration-none">
                                        <?= e($rp['title']) ?>
                                    </a>
                                </h4>
                                <small class="text-muted"><?= date('M d, Y', strtotime($rp['created_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-success text-white text-center">
                <i class="bi bi-house-heart fs-1 mb-2"></i>
                <h3 class="fs-5 fw-bold mb-2">Looking for a Home?</h3>
                <p class="small text-white-50 mb-3">Browse our top rated villas, luxury apartments, and corporate offices.</p>
                <a href="<?= BASE_URL ?>properties.php" class="btn btn-light text-success fw-bold rounded-pill">
                    Search Properties
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
