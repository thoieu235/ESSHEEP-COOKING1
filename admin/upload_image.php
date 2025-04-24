<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn() || !isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => ['message' => 'Bạn không có quyền truy cập!']]);
    exit;
}

// Thiết lập header
header('Content-Type: application/json');

// Thư mục lưu ảnh
$upload_dir = '../uploads/content/';

// Tạo thư mục nếu chưa tồn tại
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Xử lý upload ảnh
if (isset($_FILES['upload']) && $_FILES['upload']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['upload'];
    
    // Kiểm tra định dạng file
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode([
            'uploaded' => 0,
            'error' => [
                'message' => 'Chỉ chấp nhận file hình ảnh (jpg, jpeg, png, gif, webp)'
            ]
        ]);
        exit;
    }
    
    // Kiểm tra kích thước file (giới hạn 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode([
            'uploaded' => 0,
            'error' => [
                'message' => 'Kích thước file không được vượt quá 5MB'
            ]
        ]);
        exit;
    }
    
    // Tạo tên file ngẫu nhiên để tránh trùng lặp
    $new_filename = uniqid('img_', true) . '.' . $file_extension;
    $destination = $upload_dir . $new_filename;
    
    // Di chuyển file tạm lên server
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Trả về URL của ảnh đã upload
        echo json_encode([
            'uploaded' => 1,
            'fileName' => $new_filename,
            'url' => SITE_URL . '/uploads/content/' . $new_filename
        ]);
    } else {
        echo json_encode([
            'uploaded' => 0,
            'error' => [
                'message' => 'Có lỗi xảy ra khi upload file'
            ]
        ]);
    }
} else {
    // Xử lý lỗi upload
    $error_message = 'Không thể upload file';
    
    if (isset($_FILES['upload'])) {
        switch ($_FILES['upload']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $error_message = 'Kích thước file vượt quá giới hạn upload_max_filesize trong php.ini';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = 'Kích thước file vượt quá giới hạn MAX_FILE_SIZE trong HTML form';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = 'File chỉ được upload một phần';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = 'Không có file nào được upload';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message = 'Không tìm thấy thư mục tạm';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message = 'Không thể ghi file lên server';
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message = 'Upload bị dừng bởi extension';
                break;
        }
    }
    
    echo json_encode([
        'uploaded' => 0,
        'error' => [
            'message' => $error_message
        ]
    ]);
} 