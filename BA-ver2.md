# LibraryOS — Business Requirements Document

**Version:** 1.0  
**Stack:** PHP · MySQL · Vanilla CSS  
**Last Updated:** 2026-05-23

---

## 1. Overview

LibraryOS là hệ thống quản lý thư viện nội bộ, hỗ trợ tra cứu sách, mượn/trả sách, quản lý tài khoản và theo dõi tiền phạt. Hệ thống phục vụ 3 nhóm người dùng với quyền hạn khác nhau.

---

## 2. Roles & Access

| Role | ID | Mô tả |
|---|---|---|
| **Admin** | 1 | Toàn quyền hệ thống: quản lý tài khoản, cài đặt, xem tất cả requests |
| **Librarian** | 2 | Quản lý sách, loan, readers; xử lý borrow/return requests |
| **Reader** | 3 | Duyệt/tìm kiếm sách, mượn sách, theo dõi loan, yêu cầu trả sách |
| **Guest** | — | Chỉ xem danh sách sách, không thể mượn |

---

## 3. Modules & Requirements

### 3.1 Book Catalog
- Quản lý sách theo: tên, tác giả, nhà xuất bản, năm xuất bản, thể loại, ảnh bìa
- Mỗi sách có nhiều **book copies** (bản sao vật lý) với barcode riêng
- Trạng thái bản sao: `available` / `borrowed` / `reserved`
- Reader và Guest đều có thể duyệt & tìm kiếm sách (không cần đăng nhập)
- Chi tiết sách hiển thị: thông tin sách, danh sách tác giả, thể loại, số bản còn lại

### 3.2 Loan Management (Staff)
- Staff tạo loan thủ công: chọn reader → chọn sách → hệ thống gán bản sao
- Mỗi loan có `borrow_date`, `due_date` (mặc định +5 ngày từ ngày approve)
- Loan có thể chứa nhiều sách (multi-copy loan)
- Trạng thái loan: `pending` → `ongoing` → `partial` → `closed` | `rejected`
- Staff có thể return từng sách riêng lẻ (partial return)

### 3.3 Borrow Request (Reader → Staff)
- Reader bấm "Borrow" trên trang sách → tạo request `borrow_book`
- Hệ thống tự kiểm tra:
  - Sách còn bản `available` không
  - Reader đang mượn sách này chưa
  - Reader đang mượn ≥ 5 quyển chưa (giới hạn 5 cuốn/lúc)
- Bản sao được đánh dấu `reserved` ngay khi request tạo
- Staff approve → loan chuyển `ongoing`, bản sao chuyển `borrowed`
- Staff reject → bản sao trả về `available`

### 3.4 Return Request (Reader → Staff)
- Reader submit "Request Return" cho một loan đang `ongoing`/`partial`
- Reader chọn **ngày dự kiến trả** → hệ thống preview tiền phạt ước tính
- Mỗi loan chỉ được có **1 pending return request** tại một thời điểm (enforced bởi DB unique index)
- Staff confirm → fine tính theo **ngày staff xác nhận** (không phải ngày reader chọn)
- Staff reject → loan giữ nguyên trạng thái, reader có thể submit lại

### 3.5 Request Inbox (Staff)
- Admin thấy tất cả requests: borrow, return, librarian registration, password reset
- Librarian thấy: borrow + return requests
- Badge số pending hiện trên nav
- Mỗi return request hiển thị: tên sách, due date, ngày reader dự kiến trả, fine preview

### 3.6 Fine Calculation
```
Fine = (Ngày staff confirm - Due date) × fine_per_day
```
- `fine_per_day` cấu hình trong Settings (mặc định: 5.000 VNĐ/ngày)
- Fine = 0 nếu trả đúng hoặc trước hạn
- Fine lưu vào `loan_details.fine_amount`
- Tiền phạt chỉ tính chính thức khi staff confirm, không phải khi reader submit request

### 3.7 Account Management (Admin)
- Tạo, chỉnh sửa, kích hoạt/vô hiệu hóa tài khoản
- Đặt lại mật khẩu thông qua request `password_reset`
- Phân quyền role: Admin / Librarian / Reader
- Tài khoản Librarian mới cần Admin approve (`librarian_registration`)

### 3.8 Reader Management (Librarian)
- Quản lý danh sách reader: thông tin cá nhân, trạng thái tài khoản
- Xem lịch sử mượn/trả, tiền phạt của từng reader
- Reader profile có thể được liên kết với account (account_id)

### 3.9 Reader Portal
- **Dashboard:** số sách đang mượn, sắp hết hạn, thông báo
- **Browse Books:** tìm kiếm, lọc theo thể loại/tác giả, xem chi tiết
- **My Loans:** danh sách loan, trạng thái từng sách, nút Request Return
- **My Profile:** xem và cập nhật thông tin cá nhân
- Reader không truy cập được trang Admin/Librarian

### 3.10 System Settings (Admin)
- `fine_per_day`: tiền phạt mỗi ngày trễ (VNĐ)
- `max_loan_days`: số ngày mượn tối đa (mặc định: 5)

---

## 4. Business Rules

| # | Rule |
|---|---|
| BR-01 | Reader không thể mượn cùng 1 tựa sách 2 lần đồng thời |
| BR-02 | Reader không thể mượn quá 5 quyển cùng lúc |
| BR-03 | Fine tính tại thời điểm staff confirm trả, không phải ngày reader submit |
| BR-04 | Mỗi loan chỉ có 1 pending return request tại một thời điểm |
| BR-05 | Bản sao sách chuyển `reserved` khi borrow request tạo, `borrowed` khi approved |
| BR-06 | Staff reject return request → loan không thay đổi, reader có thể submit lại |
| BR-07 | Loan `partial` khi còn ≥1 sách chưa trả; `closed` khi tất cả đã trả |
| BR-08 | Tài khoản `inactive` không thể đăng nhập |
| BR-09 | Due date = borrow_date + max_loan_days (set lúc staff approve, không phải lúc request) |
| BR-10 | Reader Portal hoàn toàn tách biệt với Admin/Librarian Portal |

---

## 5. Non-Functional Requirements

| Mục | Yêu cầu |
|---|---|
| **Security** | Mật khẩu bcrypt, prepared statements, htmlspecialchars output |
| **Auth** | Session-based, guard trên mọi trang, role check inline |
| **DB Integrity** | FK constraints, ENUM validation, unique index cho pending return |
| **Performance** | Indexes trên các cột thường query (status, role_id, book_id+status) |
| **Compatibility** | XAMPP localhost + InfinityFree production (auto-detect) |
| **UI** | Responsive, SweetAlert2 cho notifications, Montserrat font |

---

## 6. File Structure

```
Web-2/
├── authen/          Login, Register, Forgot Password
├── account/         Account CRUD (Admin)
├── book/            Book & Copy management
├── loan/            Borrow/Return processing (Staff)
├── reader/          Reader Portal (role 3)
├── reader_management/ Reader CRUD (Librarian)
├── request_management/ Unified Request Inbox
├── views/           Shared views (dashboard.php)
├── system/          Settings, sys_rules helper
├── inc/             Header, Footer
├── env/             config.php, database.sql, data.sql
├── css/             style.css, auth.css
└── uploads/         Book cover images
```
