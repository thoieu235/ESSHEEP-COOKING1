<?php
/**
 * File chứa các hàm tiện ích cho website
 */

/**
 * Hàm định dạng ngày tháng
 * 
 * @param string $date Chuỗi ngày tháng
 * @param string $format Định dạng đầu ra (mặc định: d/m/Y)
 * @return string Ngày tháng đã định dạng
 */
function formatDate($date, $format = 'd/m/Y') {
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Tạo slug từ chuỗi
 * 
 * @param string $string Chuỗi cần tạo slug
 * @return string Slug đã tạo
 */
function createSlug($string) {
    $string = trim($string);
    $string = preg_replace('/\s+/', '-', $string);
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '', $string);
    $string = preg_replace('/-+/', '-', $string);
    return $string;
}

/**
 * Kiểm tra người dùng đã đăng nhập hay chưa
 * 
 * @return bool True nếu đã đăng nhập, ngược lại false
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Kiểm tra người dùng có phải là admin không
 * 
 * @return bool True nếu là admin, ngược lại false
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Chuyển hướng đến URL cụ thể
 * 
 * @param string $url URL cần chuyển hướng đến
 * @return void
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Lấy URL hiện tại
 * 
 * @return string URL hiện tại
 */
function getCurrentUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

/**
 * Hiển thị thông báo lỗi
 * 
 * @param string $message Nội dung thông báo
 * @return string HTML thông báo lỗi
 */
function showError($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * Hiển thị thông báo thành công
 * 
 * @param string $message Nội dung thông báo
 * @return string HTML thông báo thành công
 */
function showSuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

/**
 * Xóa các ký tự đặc biệt khỏi chuỗi (bảo mật)
 * 
 * @param string $str Chuỗi cần làm sạch
 * @return string Chuỗi đã làm sạch
 */
function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Tạo chuỗi ngẫu nhiên
 * 
 * @param int $length Độ dài chuỗi (mặc định: 10)
 * @return string Chuỗi ngẫu nhiên
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Kiểm tra bài viết đã được yêu thích bởi người dùng hiện tại chưa
 * 
 * @param object $db Đối tượng Database
 * @param int $postId ID bài viết
 * @param int $userId ID người dùng
 * @return bool True nếu đã yêu thích, ngược lại false
 */
function isFavorite($db, $postId, $userId) {
    $db->query("SELECT * FROM favorites WHERE user_id = :user_id AND post_id = :post_id");
    $db->bind(':user_id', $userId);
    $db->bind(':post_id', $postId);
    $result = $db->getOne();
    
    return !empty($result);
}

/**
 * Chuyển đổi ký tự tiếng Việt có dấu sang không dấu
 * 
 * @param string $str Chuỗi cần chuyển đổi
 * @return string Chuỗi đã chuyển đổi
 */
function convertVietnamese($str) {
    $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
    $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
    $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
    $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
    $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
    $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
    $str = preg_replace("/(đ)/", "d", $str);
    $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
    $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
    $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
    $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
    $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
    $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
    $str = preg_replace("/(Đ)/", "D", $str);
    return $str;
} 