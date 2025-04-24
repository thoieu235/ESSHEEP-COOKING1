<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Khởi tạo database
$db = new Database();

// Lấy tham số từ URL
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$category_slug = isset($_GET['category']) ? trim($_GET['category']) : '';
$post_type_slug = isset($_GET['type']) ? trim($_GET['type']) : '';

// Xây dựng câu truy vấn
$sql = "SELECT p.*, u.username, u.full_name, fc.name AS category_name, fc.slug AS category_slug, 
        pt.name AS post_type_name, pt.slug AS post_type_slug,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        JOIN food_categories fc ON p.food_category_id = fc.id
        JOIN post_types pt ON p.post_type_id = pt.id
        WHERE 1=1";

$bindParams = []; // Array to store parameter values in order

// Thêm điều kiện tìm kiếm theo từ khóa
if (!empty($keyword)) {
    $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $search_term = "%$keyword%";
    $bindParams[] = $search_term; // For title
    $bindParams[] = $search_term; // For content
}

// Thêm điều kiện lọc theo danh mục
if (!empty($category_slug)) {
    $sql .= " AND fc.slug = ?";
    $bindParams[] = $category_slug;
}

// Thêm điều kiện lọc theo loại bài viết
if (!empty($post_type_slug)) {
    $sql .= " AND pt.slug = ?";
    $bindParams[] = $post_type_slug;
}

// Thêm sắp xếp
$sql .= " ORDER BY p.created_at DESC";

// Thực hiện truy vấn
$db->query($sql);

// Bind các tham số theo thứ tự
foreach ($bindParams as $param) {
    $db->bind(null, $param);
}

// Lấy kết quả
$posts = $db->getAll();

// Lấy tất cả danh mục món ăn
$db->query("SELECT * FROM food_categories ORDER BY name");
$food_categories = $db->getAll();

// Lấy tất cả loại bài viết
$db->query("SELECT * FROM post_types ORDER BY name");
$post_types = $db->getAll();

// Tạo tiêu đề trang
$page_title = 'Tìm kiếm';
if (!empty($keyword)) {
    $page_title = 'Kết quả tìm kiếm cho: ' . htmlspecialchars($keyword);
} elseif (!empty($category_slug)) {
    // Lấy tên danh mục
    foreach ($food_categories as $cat) {
        if ($cat['slug'] === $category_slug) {
            $page_title = 'Danh mục: ' . $cat['name'];
            break;
        }
    }
} elseif (!empty($post_type_slug)) {
    // Lấy tên loại bài viết
    foreach ($post_types as $type) {
        if ($type['slug'] === $post_type_slug) {
            $page_title = 'Loại bài viết: ' . $type['name'];
            break;
        }
    }
}

include 'layouts/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
            </div>
            <div class="card-body">
                <!-- Form tìm kiếm -->
                <form method="GET" action="<?php echo SITE_URL; ?>/search.php" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="keyword" placeholder="Từ khóa tìm kiếm..." value="<?php echo htmlspecialchars($keyword); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="category">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach($food_categories as $category): ?>
                                    <option value="<?php echo $category['slug']; ?>" <?php echo ($category_slug === $category['slug']) ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="type">
                                <option value="">Tất cả loại</option>
                                <?php foreach($post_types as $type): ?>
                                    <option value="<?php echo $type['slug']; ?>" <?php echo ($post_type_slug === $type['slug']) ? 'selected' : ''; ?>>
                                        <?php echo $type['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Kết quả tìm kiếm -->
                <div class="mt-4">
                    <?php if (count($posts) > 0): ?>
                        <p class="text-muted mb-4">Tìm thấy <?php echo count($posts); ?> kết quả</p>
                        
                        <?php foreach($posts as $post): ?>
                            <div class="card mb-3">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <img src="<?php echo SITE_URL . '/' . $post['thumbnail']; ?>" class="img-fluid rounded-start" alt="<?php echo $post['title']; ?>" style="height: 100%; object-fit: cover;">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <span class="category-label"><?php echo $post['category_name']; ?></span>
                                                <span class="type-label"><?php echo $post['post_type_name']; ?></span>
                                            </div>
                                            <h5 class="card-title">
                                                <a href="<?php echo SITE_URL . '/post/' . $post['slug']; ?>" class="text-decoration-none">
                                                    <?php echo $post['title']; ?>
                                                </a>
                                            </h5>
                                            <p class="card-text"><?php echo substr(strip_tags($post['content']), 0, 150) . '...'; ?></p>
                                            <div class="d-flex justify-content-between">
                                                <div class="text-muted">
                                                    <small><i class="far fa-user me-1"></i> <?php echo $post['full_name']; ?></small>
                                                </div>
                                                <div class="text-muted">
                                                    <small><i class="far fa-clock me-1"></i> <?php echo formatDate($post['created_at']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search fs-1 text-muted mb-3"></i>
                            <h4>Không tìm thấy kết quả nào</h4>
                            <p>Vui lòng thử lại với từ khóa khác hoặc bỏ bộ lọc.</p>
                            <a href="<?php echo SITE_URL; ?>" class="btn btn-primary mt-3">
                                <i class="fas fa-home me-1"></i> Quay lại trang chủ
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Danh mục món ăn -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Danh mục món ăn</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach($food_categories as $category): ?>
                        <li class="list-group-item">
                            <a href="<?php echo SITE_URL . '/search.php?category=' . $category['slug']; ?>" class="text-decoration-none d-flex justify-content-between align-items-center">
                                <?php echo $category['name']; ?>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Loại bài viết -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Loại bài viết</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach($post_types as $type): ?>
                        <li class="list-group-item">
                            <a href="<?php echo SITE_URL . '/search.php?type=' . $type['slug']; ?>" class="text-decoration-none d-flex justify-content-between align-items-center">
                                <?php echo $type['name']; ?>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?> 