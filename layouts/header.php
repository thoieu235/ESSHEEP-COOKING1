<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esheep Kitchen - Chia sẻ công thức nấu ăn</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Additional CSS -->
    <?php if(isset($_SESSION['additional_css'])): ?>
        <?php echo $_SESSION['additional_css']; ?>
        <?php unset($_SESSION['additional_css']); // Clear after use ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="Esheep Kitchen" height="50">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>">Trang chủ</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="foodCategoryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Món ăn
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="foodCategoryDropdown">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/category/banh-mi">Bánh mì</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/category/banh-ngot">Bánh ngọt</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/category/bua-sang">Bữa sáng</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/category/do-uong">Đồ uống</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/category/khai-vi">Khai vị</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="postTypeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Loại bài viết
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="postTypeDropdown">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/type/cong-thuc">Công thức</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/type/meo">Mẹo</a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/type/blog">Blog</a></li>
                            </ul>
                        </li>
                    </ul>
                    <form class="d-flex" method="GET" action="<?php echo SITE_URL; ?>/search.php">
                        <input class="form-control me-2" type="search" name="keyword" placeholder="Tìm kiếm công thức..." aria-label="Search">
                        <button class="btn btn-outline-success" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    <ul class="navbar-nav ms-3">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle"></i> <?php echo $_SESSION['username']; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <?php if($_SESSION['role'] === 'admin'): ?>
                                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/users.php">Quản trị</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/favorites.php">Yêu thích</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Đăng xuất</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/login.php">Đăng nhập</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/register.php">Đăng ký</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container my-4"> 