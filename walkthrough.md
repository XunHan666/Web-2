# Web-2 — LibraryOS: Trạng thái dự án & Việc còn lại

> Cập nhật lần cuối: 2026-05-22

---

## ✅ Đã hoàn thành

### 1. Reader Portal (đã tối giản tối đa)
- `reader/dashboard.php` — thống kê sách đang mượn, hạn trả, hoạt động gần đây
- `reader/book.php` — đã gộp chung danh sách duyệt sách và chi tiết sách vào cùng 1 file duy nhất (tự động phân luồng dựa vào `id`).
- `reader/my_loans.php` — lịch sử mượn/trả của cá nhân
- `reader/profile.php` — xem & chỉnh sửa hồ sơ cá nhân
- `reader/request_borrow.php` — xử lý POST mượn sách (kiểm tra giới hạn 5 quyển, tạo loan tự động)
- Toàn bộ layout/giao diện (`header`, `footer`, `css`) đã được gộp chung vào cấu trúc chính của hệ thống (`inc/header.php`, `inc/footer.php`, `css/style.css`), thư mục `reader/inc` đã được xóa sạch.

### 2. Auth Pages
- `authen/login.php` — **67 dòng** (trước: 233), dùng `css/auth.css`
- `authen/register.php` — **73 dòng** (trước: 250), dùng `css/auth.css`
- `authen/reader_register.php` — **119 dòng** (trước: 207), dùng `css/auth.css`
- `authen/forgot_password.php` — Loại bỏ CSS inline, dùng chung `css/auth.css`
- `css/auth.css` — CSS chung 4 trang thay vì copy-paste nhiều lần

### 3. Clean Code Refactoring
| Trước | Sau |
|---|---|
| `inc/header.php` 316 dòng (CSS inline) | **134 dòng** |
| `book/books.php` 513 dòng (CSS+JS inline) | **228 dòng** |
| `Notification/` 3 file (2 là alias) | 1 file `notif_templates.php` |
| `book/book_edit.php` + `user/user_edit.php` (alias rỗng) | Đã xóa |
| `authen/views/login_display.php` (view cũ không dùng) | Đã xóa |
| CSS sidebar sống trong `<style>` header | Dời vào `css/style.css` |
| CSS book grid sống trong `<style>` books.php | Dời vào `css/style.css` |

### 4. Database Migration
- File `env/migration_reader_role.sql` đã được **xóa và gộp trực tiếp** vào `env/database.sql` (thêm khóa ngoại liên kết) và `env/data.sql` (thêm quyền reader mặc định).
- Cột `user_id` liên kết giữa bảng `users` và `readers` đã được cấu hình.
- 💡 **Chỉ cần import lại 2 file `database.sql` và `data.sql` là có đầy đủ môi trường.**

### 5. Role-based Access
- Reader (`role_id=3`) đăng nhập → redirect `reader/dashboard.php`
- Admin/Staff truy cập trang reader → redirect `index.php`
- `book/views/book_details_display.php` nhận flag `$is_reader_view` → đổi nút Edit/Loan thành nút Borrow

### 6. Loan Approval Workflow (Mới)
- Reader gửi yêu cầu mượn → tạo `loan` trạng thái `pending`.
- Admin xem tại `loan/requests.php` (kèm thông báo đỏ trên menu).
- Khi Admin duyệt (Approve) → trạng thái chuyển thành `ongoing`, sách chính thức bị giữ.
- Khi Admin từ chối (Reject) → sách được trả về kho tự động.

---

## 📁 Cấu trúc folder — Mỗi folder làm gì

```
Web-2/
│
├── authen/              # Đăng nhập, đăng ký, quên mật khẩu
│   ├── login.php
│   ├── logout.php
│   ├── register.php        # Staff/Admin tự đăng ký
│   ├── reader_register.php # Reader tự đăng ký
│   └── forgot_password.php
│
├── book/                # Quản lý sách (Admin/Staff)
│   ├── books.php           # Danh sách + tìm kiếm + quản lý số lượng
│   ├── book_add.php        # Thêm / sửa sách (dùng chung qua ?id=)
│   ├── book_detail.php     # Chi tiết sách (Admin view)
│   ├── book_delete.php     # Xóa sách
│   ├── inventory_sync.php  # Đồng bộ số lượng book_copies
│   └── views/
│       ├── book_editor.php         # Form thêm/sửa sách
│       ├── book_details_display.php # Chi tiết sách — dùng chung Admin + Reader
│       └── inventory_display.php   # Hiển thị danh sách kho
│
├── reader/              # Portal cho Reader (end-user)
│   ├── dashboard.php       # Tổng quan cá nhân
│   ├── books.php           # Duyệt sách (read-only)
│   ├── book_detail.php     # Chi tiết + nút Borrow
│   ├── my_loans.php        # Lịch sử mượn
│   ├── profile.php         # Hồ sơ cá nhân
│   ├── request_borrow.php  # POST handler mượn sách
│   └── inc/
│       ├── reader_header.php  # Guard + sidebar (gộp làm 1)
│       ├── reader_footer.php
│       └── reader.css
│
├── reader_management/   # Quản lý độc giả (Admin/Staff)
│   ├── readers.php         # Danh sách readers
│   ├── reader_add.php      # Thêm reader thủ công
│   ├── reader_add.php        # Thêm reader thủ công
│   ├── reader_delete.php   # Xóa reader
│   ├── get_reader_history.php # Lịch sử mượn của reader (dùng bởi Admin)
│   └── views/
│       ├── readers_display.php
│       └── reader_editor.php
│
├── loan/                # Quản lý giao dịch mượn/trả (Admin/Staff)
│   ├── loans.php           # Danh sách tất cả loans
│   ├── borrow.php          # Tạo loan mới
│   ├── return.php          # Xử lý trả sách
│   ├── loan_detail.php     # Chi tiết loan
│   ├── loan_delete.php     # Xóa loan
│   ├── requests.php        # Danh sách yêu cầu chờ duyệt
│   ├── process_request.php # Xử lý Approve/Reject
│   └── views/
├── user/                # Quản lý tài khoản hệ thống (Admin only)
│   ├── users.php
│   ├── user_add.php        # Thêm / sửa user (dùng chung qua ?id=)
│   ├── user_delete.php
│   └── views/
│
├── system/              # Cài đặt hệ thống (Admin only)
│   ├── settings.php
│   ├── sys_rules.php       # Quy tắc mượn sách (hạn mượn, giới hạn số lượng)
│   └── views/
│
├── Notification/        # JS helpers cho SweetAlert2
│   └── views/notif_templates.php  # confirmDelete(), confirmReturn()
│
├── css/
│   ├── style.css   # Toàn bộ style Admin portal (sidebar, grid, components)
│   └── auth.css    # Chỉ cho auth pages (login/register)
│
├── inc/
│   ├── header.php  # Guard + sidebar HTML cho Admin/Staff
│   └── footer.php  # Đóng div.container + </body></html>
│
├── env/
│   ├── config.php              # DB connection + BASE_URL
│   └── migration_reader_role.sql  # ⚠️ CẦN CHẠY THỦ CÔNG
│
├── views/
│   └── dashboard_display.php  # View cho trang index.php (Admin dashboard)
│
└── index.php            # Admin dashboard
```

---

## 🧪 Checklist test sau khi chạy migration

- [ ] **Đăng ký Reader**: `authen/reader_register.php` → tạo account → check DB bảng `users` + `readers`
- [ ] **Đăng nhập Reader**: dùng account vừa tạo → phải redirect `reader/dashboard.php`
- [ ] **Duyệt sách**: `reader/books.php` → click vào sách → xem detail → nút **Borrow** xuất hiện
- [ ] **Mượn sách**: click Borrow → confirm → kiểm tra `loans` + `loan_details` + `book_copies.status`
- [ ] **Giới hạn 5 quyển**: thử mượn quyển thứ 6 → phải báo lỗi
- [ ] **My Loans**: xem danh sách mượn cá nhân
- [ ] **Profile**: chỉnh sửa số điện thoại → lưu thành công
- [ ] **Admin đăng nhập**: phải vào `index.php`, **không** vào reader portal
- [ ] **Admin quản lý sách**: `book/books.php` → các nút Edit/Delete vẫn hoạt động
- [ ] **Admin quản lý readers**: `reader_management/readers.php` → CRUD bình thường

---

## 💡 Ghi chú kỹ thuật

| Điều cần biết | Chi tiết |
|---|---|
| Reader role_id | `3` (cứng trong code) |
| Giới hạn mượn | 5 quyển/lần (trong `request_borrow.php` dòng ~47) |
| Thời hạn mượn mặc định | 5 ngày (trong `request_borrow.php` dòng ~56) |
| Borrow flow | Reader → `request_borrow.php` POST → tạo `loans` + `loan_details` + update `book_copies.status='borrowed'` |
| Return flow | Admin/Staff dùng `loan/return.php` — Reader không tự trả được |
| CSS vars | `--primary-color: #1e4646` (xanh đậm) — đổi 1 chỗ là đổi toàn bộ |
| BASE_URL | Tự động detect localhost vs production (InfinityFree) trong `env/config.php` |
