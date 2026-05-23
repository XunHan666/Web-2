# LibraryOS

Hệ thống quản lý thư viện nội bộ với các tính năng:
- Tra cứu sách
- Mượn/trả sách online và tại quầy
- Quản lý tài khoản và người dùng
- Duyệt yêu cầu mượn/trả
- Tính tiền phạt trễ hạn

## Vai trò người dùng
- **Admin**: Quản trị hệ thống (tài khoản, cài đặt, yêu cầu hệ thống)
- **Librarian**: Vận hành sách, mượn/trả, quản lý độc giả
- **Reader**: Tự phục vụ (mượn, theo dõi, yêu cầu trả)
- **Guest**: Chỉ xem danh mục sách

## Công nghệ
- PHP
- MySQL
- Vanilla CSS

## Cài đặt
1. Cấu hình database trong `env/config.php`
2. Import `env/database.sql` vào MySQL
3. Chạy trên XAMPP hoặc môi trường PHP tương tự
