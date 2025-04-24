<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Khởi tạo kết nối database
$db = new Database();

// Lấy bài viết mới nhất
$db->query("SELECT p.*, u.username, u.full_name, fc.name AS category_name, fc.slug AS category_slug, 
            pt.name AS post_type_name, pt.slug AS post_type_slug,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            JOIN food_categories fc ON p.food_category_id = fc.id
            JOIN post_types pt ON p.post_type_id = pt.id
            ORDER BY p.created_at DESC
            LIMIT 6");
$latest_posts = $db->getAll();

// Lấy bài viết nổi bật (có nhiều bình luận nhất trong tuần)
$db->query("SELECT p.*, u.username, u.full_name, fc.name AS category_name, fc.slug AS category_slug, 
            pt.name AS post_type_name, pt.slug AS post_type_slug,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            JOIN food_categories fc ON p.food_category_id = fc.id
            JOIN post_types pt ON p.post_type_id = pt.id
            ORDER BY comment_count DESC
            LIMIT 3");
$featured_posts = $db->getAll();

// Lấy tất cả các danh mục món ăn
$db->query("SELECT * FROM food_categories ORDER BY name");
$food_categories = $db->getAll();

// Lấy tất cả các loại bài viết
$db->query("SELECT * FROM post_types ORDER BY name");
$post_types = $db->getAll();

include 'layouts/header.php';
?>

<!-- Slideshow -->
<div id="homeCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="<?php echo SITE_URL; ?>/assets/images/slide1.png" class="d-block w-100" alt="Esheep Kitchen">
            <div class="carousel-caption">
                <h2>Chào mừng đến với Esheep Kitchen</h2>
                <p>Khám phá thế giới ẩm thực đầy màu sắc và hương vị</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="<?php echo SITE_URL; ?>/assets/images/slide2.png" class="d-block w-100" alt="Công thức">
            <div class="carousel-caption">
                <h2>Công Thức Độc Đáo</h2>
                <p>Những công thức nấu ăn được chia sẻ từ cộng đồng yêu bếp</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="<?php echo SITE_URL; ?>/assets/images/slide3.png" class="d-block w-100" alt="Mẹo nấu ăn">
            <div class="carousel-caption">
                <h2>Mẹo Nấu Ăn Hữu Ích</h2>
                <p>Khám phá những bí quyết nấu ăn từ đầu bếp chuyên nghiệp</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="<?php echo SITE_URL; ?>/assets/images/slide4.png" class="d-block w-100" alt="Blog ẩm thực">
            <div class="carousel-caption">
                <h2>Blog Ẩm Thực</h2>
                <p>Cập nhật tin tức và xu hướng ẩm thực mới nhất</p>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- Lọc bài viết -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Lọc bài viết</h5>
        <form method="GET" action="<?php echo SITE_URL; ?>/search.php" class="row g-3">
            <div class="col-md-4">
                <label for="foodCategory" class="form-label">Danh mục món ăn</label>
                <select class="form-select" id="foodCategory" name="category">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach($food_categories as $category): ?>
                        <option value="<?php echo $category['slug']; ?>"><?php echo $category['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="postType" class="form-label">Loại bài viết</label>
                <select class="form-select" id="postType" name="type">
                    <option value="">Tất cả loại</option>
                    <?php foreach($post_types as $type): ?>
                        <option value="<?php echo $type['slug']; ?>"><?php echo $type['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Lọc bài viết</button>
            </div>
        </form>
    </div>
</div>

<!-- Bài viết mới nhất -->
<section class="mb-5">
    <h2 class="mb-4">Bài viết mới nhất</h2>
    <div class="row">
        <?php if(count($latest_posts) > 0): ?>
            <?php foreach($latest_posts as $post): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="<?php echo SITE_URL . '/' . $post['thumbnail']; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>">
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="category-label"><?php echo $post['category_name']; ?></span>
                                <span class="type-label"><?php echo $post['post_type_name']; ?></span>
                            </div>
                            <h5 class="card-title">
                                <a href="<?php echo SITE_URL . '/post/' . $post['slug']; ?>">
                                    <?php echo $post['title']; ?>
                                </a>
                            </h5>
                            <p class="card-text"><?php echo substr(strip_tags($post['content']), 0, 100) . '...'; ?></p>
                        </div>
                        <div class="card-footer text-muted">
                            <div class="d-flex justify-content-between align-items-center">
                                <small>Đăng bởi: <?php echo $post['full_name']; ?></small>
                                <div>
                                    <i class="far fa-comment"></i> <?php echo $post['comment_count']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">Chưa có bài viết nào.</div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Bài viết nổi bật -->
<section>
    <h2 class="mb-4">Các bài viết nổi bật</h2>
    <div class="row">
        <?php if(count($featured_posts) > 0): ?>
            <?php foreach($featured_posts as $post): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge bg-danger">Hot</span>
                        </div>
                        <img src="<?php echo SITE_URL . '/' . $post['thumbnail']; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>">
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="category-label"><?php echo $post['category_name']; ?></span>
                                <span class="type-label"><?php echo $post['post_type_name']; ?></span>
                            </div>
                            <h5 class="card-title">
                                <a href="<?php echo SITE_URL . '/post/' . $post['slug']; ?>">
                                    <?php echo $post['title']; ?>
                                </a>
                            </h5>
                            <p class="card-text"><?php echo substr(strip_tags($post['content']), 0, 100) . '...'; ?></p>
                        </div>
                        <div class="card-footer text-muted">
                            <div class="d-flex justify-content-between align-items-center">
                                <small>Bình luận: <?php echo $post['comment_count']; ?></small>
                                <small><i class="far fa-clock"></i> <?php echo formatDate($post['created_at']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">Chưa có bài viết nổi bật nào.</div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Back to Top Button -->
<a href="#" class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
</a>

<?php include 'layouts/footer.php'; ?> 