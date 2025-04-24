# Esheep Kitchen - Hệ thống chia sẻ công thức nấu ăn

Xây dựng bằng PHP thuần, MySQL, HTML, CSS, JavaScript với Bootstrap, jQuery và FontAwesome.

## Tính năng chính

### Admin
- Quản lý bài viết: đăng bài, sửa, xóa (phân loại theo món ăn và loại bài viết)
- Quản lý người dùng: chặn/bỏ chặn
- Quản lý bình luận: tìm kiếm, xóa

### Người dùng
- Xem bài viết trên trang chủ
- Lọc bài viết theo danh mục món ăn và loại bài viết
- Xem bài viết chi tiết
- Thêm/xóa bài viết yêu thích
- Bình luận vào bài viết
- Quản lý thông tin cá nhân

## Cài đặt

### Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache
- Xampp

### Các bước cài đặt

1. **Chuẩn bị môi trường**
   - Cài đặt Xampp
   - Khởi động Apache và MySQL

2. **Cài đặt dự án**
   - Tải xuống dự án vào thư mục htdocs
   - Truy cập vào đường dẫn dự án `localhost/EsheepKitchen`

3. **Cấu hình cơ sở dữ liệu**
   - Tạo cơ sở dữ liệu mới trong phpMyAdmin
   - Import file `esheep_kitchen.sql` để tạo các bảng và dữ liệu mẫu
   - File cấu hình hệ thống `config/config.php`

## Sử dụng

### Tài khoản mặc định
- **Admin**: 
  - Tên đăng nhập: admin
  - Mật khẩu: 123123
- **Người dùng**:
  - Tên đăng nhập: user1
  - Mật khẩu: 123123

## Cấu trúc thư mục

EsheepKitchen/
├── admin/                 # Các tệp quản trị viên
├── assets/                # Tài nguyên tĩnh (CSS, JS, images)
│   ├── css/               # File CSS
│   ├── js/                # File JavaScript
│   ├── images/            # Hình ảnh
├── config/                # Cấu hình hệ thống
├── includes/              # File PHP hàm tương tác với CSDL
├── layouts/               # Các bố cục trang (header, footer)
├── uploads/               # Chứa hình ảnh của bài viết
├── .htaccess              # Cấu hình Apache
├── index.php              # Trang chủ
├── 404.php                # Trang báo lỗi
├── 405.php                # Trang báo lỗi
├── post.php               # Trang chi tiết bài viết
├── login.php              # Trang đăng nhập
├── register.php           # Trang đăng ký
├── logout.php             # Đăng xuất
├── favorites.php          # Trang bài viết yêu thích
├── search.php             # Trang kết quả tìm kiếm
└── README.md              # Tài liệu hướng dẫn


## Ghi chú

- Mật khẩu được mã hóa bằng hàm `password_hash()` và xác thực bằng `password_verify()`
- Sử dụng URL thân thiện thông qua cấu hình .htaccess
- Tích hợp Bootstrap 5 và jQuery để thiết kế giao diện người dùng
 