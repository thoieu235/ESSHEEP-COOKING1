<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

// Kiểm tra ID bài viết
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID bài viết không hợp lệ';
    redirect(SITE_URL . '/admin/posts.php');
}

$post_id = (int)$_GET['id'];

// Khởi tạo database
$db = new Database();

// Lấy thông tin bài viết
$db->query("SELECT * FROM posts WHERE id = :id");
$db->bind(':id', $post_id);
$post = $db->getOne();

if (!$post) {
    $_SESSION['error_message'] = 'Bài viết không tồn tại';
    redirect(SITE_URL . '/admin/posts.php');
}

// Lấy danh sách danh mục món ăn
$db->query("SELECT * FROM food_categories ORDER BY name");
$categories = $db->getAll();

// Lấy danh sách loại bài viết
$db->query("SELECT * FROM post_types ORDER BY name");
$post_types = $db->getAll();

// Khởi tạo biến
$title = $post['title'];
$slug = $post['slug'];
$content = $post['content'];
$food_category_id = $post['food_category_id'];
$post_type_id = $post['post_type_id'];
$is_featured = $post['is_featured'];
$thumbnail = $post['thumbnail'];
$error = '';
$success = '';

// Xử lý cập nhật bài viết
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $content = $_POST['content'];
    $food_category_id = (int)$_POST['food_category_id'];
    $post_type_id = (int)$_POST['post_type_id'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Kiểm tra dữ liệu
    if (empty($title)) {
        $error = 'Vui lòng nhập tiêu đề bài viết';
    } elseif (empty($slug)) {
        $error = 'Vui lòng nhập slug cho bài viết';
    } elseif (empty($content)) {
        $error = 'Vui lòng nhập nội dung bài viết';
    } elseif (empty($food_category_id)) {
        $error = 'Vui lòng chọn danh mục món ăn';
    } elseif (empty($post_type_id)) {
        $error = 'Vui lòng chọn loại bài viết';
    } else {
        // Kiểm tra slug đã tồn tại chưa (không tính bài viết hiện tại)
        $db->query("SELECT id FROM posts WHERE slug = :slug AND id != :id");
        $db->bind(':slug', $slug);
        $db->bind(':id', $post_id);
        $db->execute();
        if ($db->rowCount() > 0) {
            $error = 'Slug đã tồn tại, vui lòng chọn slug khác';
        } else {
            // Xử lý upload hình ảnh thumbnail nếu có
            $thumbnail_path = $thumbnail;
            
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/thumbnails/';
                
                // Tạo thư mục nếu chưa tồn tại
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Xử lý tên file
                $file_name = $_FILES['thumbnail']['name'];
                $file_tmp = $_FILES['thumbnail']['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Kiểm tra định dạng file hợp lệ
                $allowed_exts = array('jpg', 'jpeg', 'png', 'gif');
                
                if (in_array($file_ext, $allowed_exts)) {
                    // Tạo tên file ngẫu nhiên để tránh trùng lặp
                    $new_file_name = uniqid('thumbnail_', true) . '.' . $file_ext;
                    $destination = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $destination)) {
                        // Lưu đường dẫn thumbnail mới
                        $thumbnail_path = 'uploads/thumbnails/' . $new_file_name;
                        
                        // Xóa file thumbnail cũ nếu có
                        if (!empty($thumbnail) && file_exists('../' . $thumbnail) && $thumbnail != $thumbnail_path) {
                            unlink('../' . $thumbnail);
                        }
                    } else {
                        $error = 'Có lỗi khi upload hình ảnh thumbnail';
                    }
                } else {
                    $error = 'Định dạng file không hợp lệ. Chỉ chấp nhận jpg, jpeg, png, gif';
                }
            }
            
            // Nếu không có lỗi, cập nhật bài viết vào database
            if (empty($error)) {
                $db->query("UPDATE posts SET 
                            title = :title, 
                            slug = :slug, 
                            content = :content, 
                            thumbnail = :thumbnail, 
                            food_category_id = :food_category_id, 
                            post_type_id = :post_type_id, 
                            is_featured = :is_featured, 
                            updated_at = NOW() 
                            WHERE id = :id");
                
                $db->bind(':title', $title);
                $db->bind(':slug', $slug);
                $db->bind(':content', $content);
                $db->bind(':thumbnail', $thumbnail_path);
                $db->bind(':food_category_id', $food_category_id);
                $db->bind(':post_type_id', $post_type_id);
                $db->bind(':is_featured', $is_featured);
                $db->bind(':id', $post_id);
                
                if ($db->execute()) {
                    // Cập nhật thành công
                    $success = 'Cập nhật bài viết thành công';
                    
                    // Cập nhật lại biến post
                    $db->query("SELECT * FROM posts WHERE id = :id");
                    $db->bind(':id', $post_id);
                    $post = $db->getOne();
                    
                    // Cập nhật lại các biến
                    $title = $post['title'];
                    $slug = $post['slug'];
                    $content = $post['content'];
                    $food_category_id = $post['food_category_id'];
                    $post_type_id = $post['post_type_id'];
                    $is_featured = $post['is_featured'];
                    $thumbnail = $post['thumbnail'];
                } else {
                    $error = 'Đã xảy ra lỗi khi cập nhật bài viết';
                }
            }
        }
    }
}

// Lấy thông báo lỗi từ session nếu có
if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Tiêu đề trang
$page_title = 'Sửa bài viết';
include 'layouts/header.php';
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Sửa bài viết</h6>
        <div>
            <a href="<?php echo SITE_URL . '/post/' . $slug; ?>" class="btn btn-info" target="_blank">
                <i class="fas fa-eye"></i> Xem bài viết
            </a>
            <a href="posts.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($slug); ?>" required>
                        <small class="form-text text-muted">Slug sẽ được sử dụng trong URL của bài viết. Ví dụ: bai-viet-moi</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Nội dung bài viết <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="editor" name="content" rows="10"><?php echo htmlspecialchars($content); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Hình ảnh thumbnail</label>
                        <?php if (!empty($thumbnail)): ?>
                            <div class="mb-2">
                                <img src="<?php echo SITE_URL . '/' . $thumbnail; ?>" alt="Current Thumbnail" class="img-thumbnail" style="max-height: 200px;">
                                <p class="small text-muted mt-1">Thumbnail hiện tại. Tải lên hình mới để thay đổi.</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*" onchange="previewImage(this, 'thumbnailPreview')">
                        <div class="mt-2">
                            <img id="thumbnailPreview" src="#" alt="Thumbnail Preview" class="img-thumbnail" style="max-height: 200px; display: none;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="food_category_id" class="form-label">Danh mục món ăn <span class="text-danger">*</span></label>
                        <select class="form-control" id="food_category_id" name="food_category_id" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($food_category_id == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="post_type_id" class="form-label">Loại bài viết <span class="text-danger">*</span></label>
                        <select class="form-control" id="post_type_id" name="post_type_id" required>
                            <option value="">-- Chọn loại bài viết --</option>
                            <?php foreach($post_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" <?php echo ($post_type_id == $type['id']) ? 'selected' : ''; ?>>
                                    <?php echo $type['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" <?php echo ($is_featured) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_featured">
                                Đánh dấu là bài viết nổi bật
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-muted small">
                            <i class="fas fa-info-circle"></i> Bài viết được tạo lúc: <?php echo formatDate($post['created_at'], 'd/m/Y H:i:s'); ?><br>
                            <i class="fas fa-info-circle"></i> Cập nhật lần cuối: <?php echo formatDate($post['updated_at'], 'd/m/Y H:i:s'); ?><br>
                            <i class="fas fa-eye"></i> Lượt xem: <?php echo $post['view_count']; ?>
                        </p>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật bài viết
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Import CKEditor -->
<script src="https://cdn.ckeditor.com/4.16.2/standard-all/ckeditor.js"></script>
<script>
    // Khởi tạo CKEditor
    CKEDITOR.replace('editor', {
        filebrowserUploadUrl: '<?php echo SITE_URL; ?>/admin/upload_image.php',
        height: 400,
        toolbarGroups: [
            { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
            { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
            { name: 'forms', groups: [ 'forms' ] },
            '/',
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
            { name: 'links', groups: [ 'links' ] },
            { name: 'insert', groups: [ 'insert' ] },
            '/',
            { name: 'styles', groups: [ 'styles' ] },
            { name: 'colors', groups: [ 'colors' ] },
            { name: 'tools', groups: [ 'tools' ] },
            { name: 'others', groups: [ 'others' ] },
            { name: 'about', groups: [ 'about' ] }
        ],
        removeButtons: 'Save,NewPage,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Subscript,Superscript,CopyFormatting,RemoveFormat,Blockquote,CreateDiv,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,BidiLtr,BidiRtl,Language,Anchor,Flash,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Maximize,ShowBlocks,About',
        extraPlugins: 'image2,uploadimage',
        removePlugins: 'image',
        contentsCss: ['https://cdn.ckeditor.com/4.16.2/full-all/contents.css']
    });
    
    // Tạo slug từ tiêu đề
    document.getElementById('title').addEventListener('keyup', function() {
        // Chỉ tạo slug tự động nếu chưa thay đổi slug
        if (document.getElementById('slug').getAttribute('data-modified') !== 'true') {
            var title = this.value;
            var slug = createSlug(title);
            document.getElementById('slug').value = slug;
        }
    });
    
    // Đánh dấu slug đã được sửa đổi thủ công
    document.getElementById('slug').addEventListener('keyup', function() {
        this.setAttribute('data-modified', 'true');
    });
    
    // Hàm tạo slug
    function createSlug(text) {
        // Chuyển về chữ thường và loại bỏ khoảng trắng ở 2 đầu
        text = text.toLowerCase().trim();
        
        // Chuyển đổi có dấu thành không dấu
        text = text.replace(/[áàảãạâấầẩẫậăắằẳẵặ]/g, 'a');
        text = text.replace(/[éèẻẽẹêếềểễệ]/g, 'e');
        text = text.replace(/[íìỉĩị]/g, 'i');
        text = text.replace(/[óòỏõọôốồổỗộơớờởỡợ]/g, 'o');
        text = text.replace(/[úùủũụưứừửữự]/g, 'u');
        text = text.replace(/[ýỳỷỹỵ]/g, 'y');
        text = text.replace(/đ/g, 'd');
        
        // Thay thế ký tự đặc biệt bằng khoảng trắng
        text = text.replace(/[^a-z0-9\s-]/g, ' ');
        
        // Thay thế khoảng trắng bằng dấu gạch ngang
        text = text.replace(/\s+/g, '-');
        
        // Thay thế nhiều dấu gạch ngang liên tiếp thành 1 dấu gạch ngang
        text = text.replace(/-+/g, '-');
        
        return text;
    }
    
    // Hiển thị preview hình ảnh khi chọn file
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById(previewId).style.display = 'block';
                document.getElementById(previewId).src = e.target.result;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php include 'layouts/footer.php'; ?> 