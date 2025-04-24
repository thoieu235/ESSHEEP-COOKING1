<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

// Khởi tạo database
$db = new Database();

// Thiết lập các tham số phân trang và tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Số bài viết trên mỗi trang
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn tìm kiếm
$search_condition = '';
$params = [];
$where_added = false;

if (!empty($search)) {
    $search_condition = " WHERE (p.title LIKE :search_title OR p.content LIKE :search_content)";
    $params[':search_title'] = "%$search%";
    $params[':search_content'] = "%$search%";
    $where_added = true;
}

if (!empty($category)) {
    if ($where_added) {
        $search_condition .= " AND fc.slug = :category";
    } else {
        $search_condition .= " WHERE fc.slug = :category";
        $where_added = true;
    }
    $params[':category'] = $category;
}

if (!empty($type)) {
    if ($where_added) {
        $search_condition .= " AND pt.slug = :type";
    } else {
        $search_condition .= " WHERE pt.slug = :type";
    }
    $params[':type'] = $type;
}

// Lấy tổng số bài viết
$db->query("SELECT COUNT(*) as total FROM posts p 
            JOIN food_categories fc ON p.food_category_id = fc.id 
            JOIN post_types pt ON p.post_type_id = pt.id" . $search_condition);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$total_posts = $db->getOne()['total'];

// Tính tổng số trang
$total_pages = ceil($total_posts / $limit);

// Đảm bảo trang hiện tại không vượt quá tổng số trang
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Lấy danh sách bài viết với phân trang
$sql = "SELECT p.*, u.username, fc.name AS category_name, pt.name AS type_name, 
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        JOIN food_categories fc ON p.food_category_id = fc.id
        JOIN post_types pt ON p.post_type_id = pt.id" . 
        $search_condition . 
        " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
$db->query($sql);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$db->bind(':limit', $limit);
$db->bind(':offset', $offset);
$posts = $db->getAll();

// Lấy danh sách danh mục món ăn cho dropdown tìm kiếm
$db->query("SELECT * FROM food_categories ORDER BY name");
$categories = $db->getAll();

// Lấy danh sách loại bài viết cho dropdown tìm kiếm
$db->query("SELECT * FROM post_types ORDER BY name");
$post_types = $db->getAll();

// Xử lý xóa bài viết
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    
    // Kiểm tra bài viết tồn tại
    $db->query("SELECT * FROM posts WHERE id = :id");
    $db->bind(':id', $post_id);
    $post = $db->getOne();
    
    if (!$post) {
        $error = 'Bài viết không tồn tại';
    } else {
        // Bắt đầu transaction
        $db->beginTransaction();
        
        try {
            // Xóa các bình luận liên quan
            $db->query("DELETE FROM comments WHERE post_id = :post_id");
            $db->bind(':post_id', $post_id);
            $db->execute();
            
            // Xóa các yêu thích liên quan
            $db->query("DELETE FROM favorites WHERE post_id = :post_id");
            $db->bind(':post_id', $post_id);
            $db->execute();
            
            // Xóa bài viết
            $db->query("DELETE FROM posts WHERE id = :post_id");
            $db->bind(':post_id', $post_id);
            $db->execute();
            
            // Nếu có hình ảnh thumbnail, xóa file
            if (!empty($post['thumbnail']) && file_exists('../' . $post['thumbnail'])) {
                unlink('../' . $post['thumbnail']);
            }
            
            // Commit transaction
            $db->commit();
            
            // Lưu thông báo thành công vào session
            $_SESSION['success_message'] = 'Xóa bài viết thành công';
            
            // Xây dựng URL chuyển hướng với các tham số hiện tại
            $redirect_url = 'posts.php';
            $params_url = [];
            
            if (!empty($search)) {
                $params_url[] = 'search=' . urlencode($search);
            }
            
            if (!empty($category)) {
                $params_url[] = 'category=' . urlencode($category);
            }
            
            if (!empty($type)) {
                $params_url[] = 'type=' . urlencode($type);
            }
            
            if ($page > 1) {
                $params_url[] = 'page=' . $page;
            }
            
            if (!empty($params_url)) {
                $redirect_url .= '?' . implode('&', $params_url);
            }
            
            redirect(SITE_URL . '/admin/' . $redirect_url);
            exit;
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $db->rollBack();
            $error = 'Đã xảy ra lỗi khi xóa bài viết: ' . $e->getMessage();
        }
    }
}

// Lấy thông báo từ session nếu có
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Tiêu đề trang
$page_title = 'Quản lý bài viết';
include 'layouts/header.php';
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách bài viết</h6>
        <a href="post_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm bài viết mới
        </a>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Form tìm kiếm -->
        <form method="GET" action="" class="mb-4">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <input type="text" class="form-control" name="search" placeholder="Tìm kiếm theo tiêu đề hoặc nội dung..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-control" name="category">
                        <option value="">-- Tất cả danh mục --</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['slug']; ?>" <?php echo ($category == $cat['slug']) ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-control" name="type">
                        <option value="">-- Tất cả loại --</option>
                        <?php foreach($post_types as $pt): ?>
                            <option value="<?php echo $pt['slug']; ?>" <?php echo ($type == $pt['slug']) ? 'selected' : ''; ?>><?php echo $pt['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>
            <?php if (!empty($search) || !empty($category) || !empty($type)): ?>
                <div class="mt-2">
                    <a href="<?php echo SITE_URL; ?>/admin/posts.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                </div>
            <?php endif; ?>
        </form>
        
        <!-- Kết quả -->
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="15%">Hình ảnh</th>
                        <th width="25%">Tiêu đề</th>
                        <th width="10%">Danh mục</th>
                        <th width="10%">Loại</th>
                        <th width="10%">Tác giả</th>
                        <th width="10%">Ngày đăng</th>
                        <th width="15%">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo $post['id']; ?></td>
                                <td>
                                    <img src="<?php echo SITE_URL . '/' . $post['thumbnail']; ?>" alt="<?php echo $post['title']; ?>" class="img-thumbnail" style="max-height: 80px;">
                                </td>
                                <td>
                                    <strong><?php echo $post['title']; ?></strong>
                                    <div class="small text-muted">
                                        <i class="fas fa-eye"></i> <?php echo $post['view_count']; ?> lượt xem
                                        <i class="fas fa-comments ml-2"></i> <?php echo $post['comment_count']; ?> bình luận
                                    </div>
                                </td>
                                <td><?php echo $post['category_name']; ?></td>
                                <td><?php echo $post['type_name']; ?></td>
                                <td><?php echo $post['username']; ?></td>
                                <td><?php echo formatDate($post['created_at']); ?></td>
                                <td>
                                    <a href="<?php echo SITE_URL . '/post/' . $post['slug']; ?>" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="post_edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" name="delete_post" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Không tìm thấy bài viết nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($type) ? '&type=' . urlencode($type) : ''; ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($type) ? '&type=' . urlencode($type) : ''; ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fas fa-angle-double-left"></i></span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fas fa-angle-left"></i></span>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $start_page + 4);
                    if ($end_page - $start_page < 4 && $total_pages > 5) {
                        $start_page = max(1, $end_page - 4);
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($type) ? '&type=' . urlencode($type) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($type) ? '&type=' . urlencode($type) : ''; ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($type) ? '&type=' . urlencode($type) : ''; ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fas fa-angle-right"></i></span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fas fa-angle-double-right"></i></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
