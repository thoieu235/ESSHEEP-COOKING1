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

// Các tham số tìm kiếm và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn tìm kiếm
$search_condition = '';
$params = [];

if (!empty($search)) {
    $search_condition = " WHERE username LIKE :search_username OR email LIKE :search_email OR full_name LIKE :search_fullname";
    $params[':search_username'] = "%$search%";
    $params[':search_email'] = "%$search%";
    $params[':search_fullname'] = "%$search%";
}

// Lấy tổng số người dùng
$db->query("SELECT COUNT(*) as total FROM users" . $search_condition);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$total_users = $db->getOne()['total'];

// Tính tổng số trang
$total_pages = ceil($total_users / $limit);

// Đảm bảo trang hiện tại không vượt quá tổng số trang
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Lấy danh sách người dùng với phân trang
$sql = "SELECT * FROM users" . $search_condition . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$db->query($sql);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$db->bind(':limit', $limit);
$db->bind(':offset', $offset);
$users = $db->getAll();

// Xử lý chặn/bỏ chặn người dùng
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_block'])) {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $is_blocked = isset($_POST['is_blocked']) ? (int)$_POST['is_blocked'] : 0;
    
    // Kiểm tra người dùng tồn tại
    $db->query("SELECT * FROM users WHERE id = :id");
    $db->bind(':id', $user_id);
    $user = $db->getOne();
    
    if (!$user) {
        $error = 'Người dùng không tồn tại';
    } elseif ($user['role'] === 'admin') {
        $error = 'Không thể chặn tài khoản admin';
    } else {
        // Cập nhật trạng thái
        $new_status = $is_blocked ? 0 : 1;
        $db->query("UPDATE users SET is_blocked = :is_blocked WHERE id = :id");
        $db->bind(':is_blocked', $new_status);
        $db->bind(':id', $user_id);
        
        if ($db->execute()) {
            // Chuyển hướng sau khi cập nhật thành công để tránh POST lặp lại
            $_SESSION['success_message'] = 'Đã ' . ($new_status ? 'chặn' : 'bỏ chặn') . ' người dùng thành công';
            
            // Xây dựng URL chuyển hướng với các tham số hiện tại
            $redirect_url = 'users.php';
            $params = [];
            
            if (!empty($search)) {
                $params[] = 'search=' . urlencode($search);
            }
            
            if ($page > 1) {
                $params[] = 'page=' . $page;
            }
            
            if (!empty($params)) {
                $redirect_url .= '?' . implode('&', $params);
            }
            
            redirect(SITE_URL . '/admin/' . $redirect_url);
            exit;
        } else {
            $error = 'Đã xảy ra lỗi khi cập nhật trạng thái';
        }
    }
}

// Lấy thông báo từ session nếu có
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Tiêu đề trang
$page_title = 'Quản lý người dùng';
include 'layouts/header.php';
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách người dùng</h6>
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
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm theo tên đăng nhập, email, họ tên..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <?php if (!empty($search)): ?>
                    <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Kết quả -->
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="15%">Tên đăng nhập</th>
                        <th width="20%">Họ tên</th>
                        <th width="20%">Email</th>
                        <th width="10%">Vai trò</th>
                        <th width="10%">Trạng thái</th>
                        <th width="10%">Ngày đăng ký</th>
                        <th width="10%">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['full_name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <span class="badge <?php echo ($user['role'] === 'admin') ? 'bg-danger' : 'bg-info'; ?>">
                                        <?php echo ($user['role'] === 'admin') ? 'Admin' : 'Người dùng'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($user['is_blocked']) ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo ($user['is_blocked']) ? 'Đã chặn' : 'Hoạt động'; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="is_blocked" value="<?php echo $user['is_blocked']; ?>">
                                            
                                            <button type="submit" name="toggle_block" class="btn btn-sm <?php echo ($user['is_blocked']) ? 'btn-success' : 'btn-warning'; ?>" onclick="return confirm('Bạn có chắc chắn muốn <?php echo ($user['is_blocked']) ? 'bỏ chặn' : 'chặn'; ?> người dùng này?');">
                                                <?php if ($user['is_blocked']): ?>
                                                    <i class="fas fa-unlock"></i> Bỏ chặn
                                                <?php else: ?>
                                                    <i class="fas fa-ban"></i> Chặn
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-shield-alt"></i> Admin
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Không tìm thấy người dùng nào</td>
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
                            <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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