<?php
// Thông tin kết nối cơ sở dữ liệu
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'esheep_kitchen');

// Đường dẫn website
define('SITE_URL', 'http://localhost:8080/EsheepKitchen');

// Cấu hình thời gian
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Cấu hình hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session
session_start(); 