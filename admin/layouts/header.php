<?php
// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

// Tiêu đề trang mặc định
if (!isset($page_title)) {
    $page_title = 'Trang Quản trị';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Esheep Kitchen Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts - Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/admin-style.css" rel="stylesheet">

    <!-- Summernote Editor CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
</head>

<body>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark border-right" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 text-white fs-4 fw-bold">
                <i class="fas fa-utensils me-2"></i> Admin Panel
            </div>
            <div class="list-group list-group-flush">
                <a href="<?php echo SITE_URL; ?>/admin/posts.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' || basename($_SERVER['PHP_SELF']) == 'post_add.php' || basename($_SERVER['PHP_SELF']) == 'post_edit.php' ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper me-2"></i> Quản lý bài viết
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i> Quản lý người dùng
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/comments.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-comments me-2"></i> Quản lý bình luận
                </a>
                <a href="<?php echo SITE_URL; ?>" class="list-group-item list-group-item-action bg-transparent text-white">
                    <i class="fas fa-home me-2"></i> Về trang chủ
                </a>
                <a href="<?php echo SITE_URL; ?>/logout.php" class="list-group-item list-group-item-action bg-transparent text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                </a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-dark" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-1"></i> <?php echo $_SESSION['full_name']; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Đăng xuất</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <h1 class="mb-4"><?php echo $page_title; ?></h1>