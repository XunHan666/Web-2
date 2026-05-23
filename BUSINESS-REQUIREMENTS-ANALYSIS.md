# LibraryOS — Phân Tích Yêu Cầu Nghiệp Vụ (Business Requirements Analysis)

**Phiên bản:** 1.1  
**Nguồn tham chiếu:** `BA-VER1.md`, `BA-ver2.md`  
**Công nghệ:** PHP · MySQL · Vanilla CSS  
**Cập nhật:** 2026-05-23

---

## Mục lục

| # | Nội dung |
|---|---|
| 1 | [Tổng quan & vai trò](#1-tổng-quan--vai-trò) |
| **2** | **[PHẦN I — Functional Requirements (theo role)](#phần-i--functional-requirements-yêu-cầu-chức-năng)** |
| **3** | **[PHẦN II — Non-Functional Requirements (theo role)](#phần-ii--non-functional-requirements-yêu-cầu-phi-chức-năng)** |
| 4 | [Quy tắc nghiệp vụ (Business Rules)](#4-quy-tắc-nghiệp-vụ-business-rules) |
| 5 | [Phụ lục: Luồng nghiệp vụ & dữ liệu](#5-phụ-lục) |

---

## 1. Tổng quan & vai trò

### 1.1 Mục đích

**LibraryOS** là hệ thống quản lý thư viện nội bộ: tra cứu sách, mượn/trả (online + tại quầy), quản lý tài khoản, duyệt yêu cầu và tính tiền phạt trễ hạn.

### 1.2 Bảng vai trò

| Vai trò | Role ID | Portal | Mô tả |
|---|---:|---|---|
| **Admin** | 1 | Staff | Quản trị hệ thống: account, settings, system requests |
| **Librarian** | 2 | Staff | Vận hành sách, loan, reader, duyệt borrow/return |
| **Reader** | 3 | Reader | Tự phục vụ: mượn, theo dõi loan, yêu cầu trả |
| **Guest** | — | Public | Chỉ xem danh mục sách |

**Phân tách vai trò (v1.2):** Admin **không** vận hành sách/loan/reader; chỉ giám sát số liệu trên Dashboard và xử lý **System Requests** (`librarian_registration`, `password_reset`). Librarian đảm nhiệm toàn bộ circulation.

---

# PHẦN I — FUNCTIONAL REQUIREMENTS (Yêu cầu chức năng)

> Mô tả **hệ thống phải làm gì** — chức năng, hành vi, quy tắc nghiệp vụ gắn với từng vai trò.  
> Ký hiệu: **FR-{Role}-{số}** (ví dụ: `FR-R-03` = Functional Requirement của Reader).

---

## I.1 Guest

**Mô tả:** Người chưa đăng nhập; chỉ khám phá kho sách công khai.

| Mã | Chức năng | Mô tả | Tiêu chí chấp nhận |
|---|---|---|---|
| FR-G-01 | Duyệt danh mục sách | Xem toàn bộ sách trong thư viện | Không cần session; hiển thị ảnh bìa, tên, tác giả, thể loại |
| FR-G-02 | Tìm kiếm / lọc | Tìm theo tên sách, tác giả, thể loại | Kết quả cập nhật theo bộ lọc |
| FR-G-03 | Xem chi tiết sách | Thông tin đầy đủ + tình trạng kho | NXB, năm XB, mô tả, tác giả, thể loại, **số bản `available`** |
| FR-G-04 | Không mượn sách | Không tạo yêu cầu mượn | Nút Borrow ẩn hoặc chuyển hướng đăng nhập; không tạo `borrow_book` |

**Ràng buộc chức năng (Guest):**

- Không truy cập Reader Portal (`reader/`) hay Staff Portal.
- Không xem thông tin loan / hồ sơ cá nhân của người khác.

---

## I.2 Reader — Role ID: 3

**Mô tả:** Self-service qua **Reader Portal** (`reader/`).

### I.2.1 Xác thực & hồ sơ (Profile & Auth)

| Mã | Chức năng | Mô tả | Quy tắc / Ghi chú |
|---|---|---|---|
| FR-R-01 | Đăng ký tài khoản | Tự đăng ký account role Reader | Tự **đồng bộ** với `readers` đã có tại quầy nếu trùng **SĐT** hoặc **Email** → gán `account_id` |
| FR-R-02 | Đăng nhập | Đăng nhập vào Reader Portal | Tài khoản `inactive` không đăng nhập được (BR-08) |
| FR-R-03 | Quản lý hồ sơ | Cập nhật họ tên, địa chỉ, SĐT, email, ngày sinh, giới tính | Chỉ sửa hồ sơ của chính mình |
| FR-R-04 | Đổi mật khẩu | Đổi MK khi đã đăng nhập | — |
| FR-R-05 | Quên mật khẩu | Gửi request `password_reset` | Admin xử lý và cấp MK mới; Reader không tự reset |

### I.2.2 Bảng điều khiển (My Dashboard)

| Mã | Chức năng | Mô tả |
|---|---|---|
| FR-R-06 | Thống kê cá nhân | Tổng đã mượn, đang giữ, yêu cầu chờ duyệt, sách quá hạn |
| FR-R-07 | Sách đang mượn | Danh sách nhanh cuốn `borrowed` + **due_date** |
| FR-R-08 | Cảnh báo hạn | Hiển thị sách sắp hết hạn / quá hạn (`due_date` vs ngày hiện tại) |

### I.2.3 Tra cứu & mượn sách (Browse Books)

| Mã | Chức năng | Mô tả |
|---|---|---|
| FR-R-09 | Duyệt catalog | Tìm kiếm, lọc theo thể loại / tác giả |
| FR-R-10 | Chi tiết sách | Thông tin sách + số bản **`available` real-time** |
| FR-R-11 | Mượn sách online | Một click → tạo request `borrow_book` |

**Điều kiện khi gửi FR-R-11 (BR-01, BR-02, BR-05):**

| Điều kiện | Khi vi phạm |
|---|---|
| Còn ≥1 bản `available` | Từ chối; thông báo hết sách |
| Chưa mượn cùng **tựa sách** | Từ chối; thông báo trùng tựa |
| Đang mượn **< 5** cuốn | Từ chối; thông báo vượt giới hạn |
| Tài khoản `active` | Không cho thao tác |

**Hậu quả khi tạo request thành công:** 1 copy → `reserved`; request → `pending`.

### I.2.4 Lịch sử mượn trả (My Loans)

| Mã | Chức năng | Mô tả |
|---|---|---|
| FR-R-12 | Xem loan | Toàn bộ lịch sử loan của reader liên kết |
| FR-R-13 | Theo dõi trạng thái | Theo dõi từng loan và từng sách trong loan |
| FR-R-14 | Yêu cầu trả sách | Submit `return_book` cho loan `ongoing` / `partial` |

**Trạng thái hiển thị:** Chờ duyệt · Đang mượn · Đã trả · Quá hạn · Bị từ chối.

### I.2.5 Yêu cầu trả sách (Return Request)

| Mã | Chức năng | Mô tả | Quy tắc |
|---|---|---|---|
| FR-R-15 | Gửi yêu cầu trả | Chọn loan + **ngày dự kiến trả** | Mỗi loan chỉ 1 `return_book` `pending` (BR-04) |
| FR-R-16 | Preview tiền phạt | Ước tính phạt theo ngày reader chọn | **Chỉ tham khảo** — fine chính thức tính khi staff confirm (BR-03) |
| FR-R-17 | Theo dõi request | Xem pending / approved / rejected | Reject → loan không đổi; gửi lại được (BR-06) |

**Ràng buộc chức năng (Reader):**

- Giới hạn 5 cuốn đồng thời (BR-02); không trùng tựa (BR-01).
- Không truy cập module Staff: `book/`, `loan/`, `account/`, `request_management/` (filtered staff), v.v. (BR-10).

---

## I.3 Librarian — Role ID: 2

**Mô tả:** Vận hành nghiệp vụ hàng ngày qua **Staff Portal**.

### I.3.1 Quản lý sách (Book Management)

| Mã | Chức năng | Mô tả | Quy tắc |
|---|---|---|---|
| FR-L-01 | Thêm sách | Tiêu đề, tác giả (n-n), NXB, năm XB, thể loại, ảnh, mô tả | Tạo `book_copies` với **barcode** riêng |
| FR-L-02 | Cập nhật sách | Sửa metadata; thêm/xóa bản sao | Barcode unique |
| FR-L-03 | Xóa sách | Xóa khỏi hệ thống | Chỉ khi **mọi** copy đều `available` |
| FR-L-04 | Tra cứu sách | Danh sách + tìm theo tên, tác giả, thể loại | Hiển thị trạng thái từng copy |

### I.3.2 Quản lý hồ sơ độc giả (Reader Management)

| Mã | Chức năng | Mô tả |
|---|---|---|
| FR-L-05 | Danh sách hội viên | Xem/quản lý bảng `readers` |
| FR-L-06 | Tạo hồ sơ tại quầy | Tạo reader thủ công (`account_id` có thể NULL) |
| FR-L-07 | Cập nhật hồ sơ | Sửa thông tin; trạng thái `active` / `inactive` |
| FR-L-08 | Tra cứu chi tiết | Thông tin liên lạc + **lịch sử mượn/trả + tiền phạt** |
| FR-L-09 | Liên kết tài khoản | Gắn `readers.account_id` khi reader đăng ký online |

### I.3.3 Quản lý mượn / trả tại quầy (Loan Management)

| Mã | Chức năng | Mô tả | Quy tắc |
|---|---|---|---|
| FR-L-10 | Cho mượn tại quầy | Chọn reader → sách → gán copy | Tạo `loans` + `loan_details`; multi-copy/loan |
| FR-L-11 | Ghi nhận trả | Xác nhận trả từng cuốn; ghi `return_date` | Hỗ trợ **trả từng phần** (BR-07) |
| FR-L-12 | Kiểm tra sách | Ghi nhận tình trạng khi nhận trả | Copy → `available` khi `returned` |

**Due date (BR-09):** `due_date = borrow_date + max_loan_days` — set khi **approve**, mặc định `max_loan_days = 5`.

**Vòng đời loan:** `pending` → `ongoing` → `partial` → `closed` | `rejected`

### I.3.4 Duyệt yêu cầu mượn (Borrow Request)

| Mã | Chức năng | Mô tả | Hậu quả |
|---|---|---|---|
| FR-L-13 | Xem inbox borrow | `borrow_book` + `pending` | Badge số pending trên nav |
| FR-L-14 | Approve | Chấp thuận yêu cầu Reader | Loan → `ongoing`; copy → `borrowed`; tính `due_date` |
| FR-L-15 | Reject | Từ chối yêu cầu | Copy → `available`; request/loan → `rejected` |

### I.3.5 Duyệt yêu cầu trả (Return Request)

| Mã | Chức năng | Mô tả | Hậu quả |
|---|---|---|---|
| FR-L-16 | Xem inbox return | Tên sách, due date, ngày dự kiến trả, fine preview | Chỉ thấy borrow + return (không thấy librarian_reg, password_reset) |
| FR-L-17 | Confirm trả | Xác nhận nhận sách vật lý | Fine theo **ngày confirm** (BR-03); loan → `partial`/`closed` |
| FR-L-18 | Reject return | Từ chối yêu cầu trả | Loan giữ nguyên (BR-06) |

### I.3.6 Đăng ký Thủ thư (hạn chế)

| Mã | Chức năng | Mô tả |
|---|---|---|
| FR-L-19 | Tự đăng ký Librarian | Tạo account + request `librarian_registration` |
| FR-L-20 | Chờ phê duyệt | Không hoạt động đầy đủ cho đến khi Admin approve |

**Ràng buộc chức năng (Librarian):**

- Không quản lý account Admin/Librarian khác.
- Không sửa System Settings; không duyệt `password_reset` / `librarian_registration`.

---

## I.4 Admin — Role ID: 1

**Mô tả:** Quản trị hệ thống — **xem** sách & loan (read-only); **không** sửa/xóa/mượn/trả. Không truy cập `reader_management/`. Dashboard + menu Books/Loans ở chế độ xem.

### I.4.1 Quản lý tài khoản (Account Management)

| Mã | Chức năng | Mô tả | Ràng buộc |
|---|---|---|---|
| FR-A-01 | Quản lý tập trung | CRUD mọi `accounts` (Admin, Librarian, Reader) | — |
| FR-A-02 | Tạo & phân quyền | Tạo account; gán `role_id`; `active`/`inactive` | — |
| FR-A-03 | Chỉnh sửa / hỗ trợ | Cập nhật profile, liên lạc mọi user | — |
| FR-A-04 | Kiểm soát truy cập | Khóa / vô hiệu hóa tài khoản | **Không** tự khóa/xóa tài khoản Admin đang đăng nhập |
| FR-A-05 | Đặt lại mật khẩu | Xử lý `password_reset`; cấp MK mới | Chỉ Admin |

### I.4.2 Quản lý yêu cầu hệ thống (System Requests)

| Mã | Chức năng | Mô tả |
|---|---|---|
| FR-A-06 | System Requests inbox | Chỉ `librarian_registration` + `password_reset`; badge pending theo 2 loại này |
| FR-A-07 | Duyệt đăng ký Thủ thư | Approve → kích hoạt Librarian; Reject → từ chối |
| FR-A-08 | Duyệt quên mật khẩu | Xử lý `password_reset` cho mọi role |

| Loại request | Admin xử lý? |
|---|---|
| `librarian_registration` | ✓ |
| `password_reset` | ✓ |
| `borrow_book` / `return_book` | ✗ (Librarian) |

### I.4.3 Cấu hình hệ thống (System Settings)

| Mã | Chức năng | Mô tả |
|---|---|---|
| FR-A-09 | Xem / sửa settings | Tham số toàn thư viện |
| FR-A-10 | Áp dụng tham số | `max_loan_days` → due_date; `fine_per_day` → fine_amount |

| Key | Mô tả | Mặc định |
|---|---|---|
| `fine_per_day` | Tiền phạt/ngày trễ (VNĐ) | 5.000 |
| `max_loan_days` | Số ngày mượn tối đa | 5 |

---

### I.5 Ma trận Functional Requirements theo role

| Nhóm chức năng | Guest | Reader | Librarian | Admin |
|---|:---:|:---:|:---:|:---:|
| Duyệt / tìm sách | FR-G | FR-R-09,10 | FR-L-04 | — |
| Auth / hồ sơ | — | FR-R-01→05 | FR-L-19,20 | FR-A-01→05 |
| Dashboard cá nhân | — | FR-R-06→08 | — | — |
| Mượn / trả online | — | FR-R-11→17 | — | — |
| Quản lý sách | — | — | FR-L-01→04 | Xem only (FR xem danh mục) |
| Quản lý reader | — | — | FR-L-05→09 | — |
| Loan tại quầy | — | — | FR-L-10→12 | Xem only (lịch sử loan) |
| Duyệt borrow/return | — | — | FR-L-13→18 | — |
| Account / System Requests / Settings | — | — | — | FR-A-01→10 |

---

# PHẦN II — NON-FUNCTIONAL REQUIREMENTS (Yêu cầu phi chức năng)

> Mô tả **hệ thống phải hoạt động như thế nào** — bảo mật, hiệu năng, giao diện, triển khai… theo từng vai trò và phần dùng chung.  
> Ký hiệu: **NFR-{Role}-{số}** hoặc **NFR-SYS-{số}** (toàn hệ thống).

---

## II.1 Guest

| Mã | Hạng mục | Yêu cầu |
|---|---|---|
| NFR-G-01 | Khả dụng công khai | Trang danh mục / chi tiết sách truy cập **không cần đăng nhập** |
| NFR-G-02 | Bảo mật dữ liệu | Không hiển thị thông tin nội bộ: loan, reader profile, inbox request |
| NFR-G-03 | Trải nghiệm duyệt | Giao diện catalog **responsive**; tải danh sách / chi tiết ổn định trên mobile & desktop |
| NFR-G-04 | Phản hồi thao tác | Thông báo rõ khi cố mượn sách (chuyển login / ẩn nút Borrow) — dùng SweetAlert2 hoặc tương đương |
| NFR-G-05 | Hiệu năng tra cứu | Tìm kiếm / lọc sách tận dụng index DB (`book_id`, `status`, v.v.) — không full table scan trên dataset mẫu |

---

## II.2 Reader — Role ID: 3

| Mã | Hạng mục | Yêu cầu |
|---|---|---|
| NFR-R-01 | Tách portal | Reader chỉ truy cập `reader/`; **chặn** URL Staff (BR-10) |
| NFR-R-02 | Xác thực | Session-based login; guard mọi trang Reader Portal |
| NFR-R-03 | Trạng thái tài khoản | `inactive` → từ chối đăng nhập và mọi thao tác mượn (BR-08) |
| NFR-R-04 | Bảo mật session | Chỉ xem/sửa dữ liệu của **chính** reader liên kết `account_id` |
| NFR-R-05 | Mật khẩu | Lưu bcrypt; đổi MK yêu cầu xác thực phiên hiện tại |
| NFR-R-06 | Đồng bộ hồ sơ | Đăng ký match phone/email với `readers` — liên kết atomic, tránh trùng `account_id` |
| NFR-R-07 | Real-time kho | Số bản `available` phản ánh trạng thái copy sau reserve/borrow/return |
| NFR-R-08 | Thông báo UX | SweetAlert2 cho thành công / lỗi (hết sách, vượt 5 cuốn, trùng tựa…) |
| NFR-R-09 | Dashboard | Thống kê cá nhân tải trong thời gian chấp nhận được (index `loans(reader_id, status)`) |
| NFR-R-10 | Return request | DB enforce **1 pending return/loan** (unique `pending_return_loan_id`) — BR-04 |
| NFR-R-11 | Giao diện | Reader Portal responsive; font Montserrat; đồng bộ style với hệ thống |

---

## II.3 Librarian — Role ID: 2

| Mã | Hạng mục | Yêu cầu |
|---|---|---|
| NFR-L-01 | Phân quyền Staff | Chỉ `role_id = 2` hoặc `1` truy cập `book/`, `loan/`, `reader_management/` |
| NFR-L-02 | Request inbox | Lọc nhanh theo `type` + `status` (index `idx_req_type_status`); badge pending trên navigation |
| NFR-L-03 | Phạm vi inbox | Librarian **không** thấy `librarian_registration`, `password_reset` trong inbox |
| NFR-L-04 | Toàn vẹn nghiệp vụ | Approve/Reject borrow và Confirm/Reject return **transaction-safe** (copy status + loan status đồng bộ) |
| NFR-L-05 | Upload ảnh bìa | File lưu `uploads/`; validate loại/kích thước hợp lý; tên file không ghi đè bảo mật |
| NFR-L-06 | Xóa sách an toàn | Chặn xóa khi còn copy `borrowed` / `reserved` — thông báo lỗi rõ ràng |
| NFR-L-07 | Loan tại quầy | Gán copy `available` tự động; không double-assign cùng copy |
| NFR-L-08 | Fine khi confirm | Tính phạt theo ngày staff confirm, không theo ngày reader chọn (BR-03) |
| NFR-L-09 | Đăng ký chờ duyệt | Librarian mới (`librarian_registration` pending) bị giới hạn chức năng cho đến Admin approve |
| NFR-L-10 | Giao diện Staff | Layout Staff Portal responsive; SweetAlert2 xác nhận thao tác quan trọng (approve, reject, xóa) |

---

## II.4 Admin — Role ID: 1

| Mã | Hạng mục | Yêu cầu |
|---|---|---|
| NFR-A-01 | Toàn quyền Staff | Admin truy cập mọi module Staff + `account/` + `system/` |
| NFR-A-02 | Bảo vệ tài khoản gốc | Không cho Admin **tự** khóa / xóa chính session đang đăng nhập |
| NFR-A-03 | Account CRUD | Thao tác account qua prepared statements; output escape `htmlspecialchars` |
| NFR-A-04 | Reset mật khẩu | MK mới đủ mạnh / random; chỉ hiển thị một lần cho Admin hỗ trợ user |
| NFR-A-05 | Inbox đầy đủ | Admin thấy **4 loại** request; cùng tiêu chí hiệu năng như NFR-L-02 |
| NFR-A-06 | Settings | Thay đổi `fine_per_day`, `max_loan_days` có hiệu lực cho loan **mới** approve; không làm hỏng loan đang `ongoing` |
| NFR-A-07 | Audit / vận hành | Thao tác approve librarian / reset password có phản hồi UI rõ (thành công / thất bại) |

---

## II.5 Toàn hệ thống (Shared / System-wide)

| Mã | Hạng mục | Yêu cầu | Áp dụng |
|---|---|---|---|
| NFR-SYS-01 | Bảo mật SQL | Prepared statements cho mọi truy vấn có input người dùng | Tất cả role |
| NFR-SYS-02 | Bảo mật output | `htmlspecialchars` chống XSS trên dữ liệu hiển thị | Tất cả role |
| NFR-SYS-03 | Mật khẩu | Bcrypt hash cho `accounts.password` | Auth |
| NFR-SYS-04 | Session | Session-based authentication; timeout hợp lý | Reader, Librarian, Admin |
| NFR-SYS-05 | Role guard | Kiểm tra `role_id` trên mỗi trang / action nhạy cảm | Staff vs Reader |
| NFR-SYS-06 | Toàn vẹn CSDL | FK constraints; ENUM đúng schema; unique pending return/loan | Backend |
| NFR-SYS-07 | Index hiệu năng | `book_copies(book_id, status)`, `accounts(role_id, status)`, `requests(type, status)`, `loans(reader_id, status)`, `loan_details(status)` | Backend |
| NFR-SYS-08 | Triển khai | Chạy XAMPP (localhost) và InfinityFree (production); `env/config.php` auto-detect môi trường | DevOps |
| NFR-SYS-09 | Charset | MySQL `utf8mb4_unicode_ci` — hỗ trợ tiếng Việt đầy đủ | DB |
| NFR-SYS-10 | UI chuẩn | Responsive; SweetAlert2; font Montserrat | Toàn UI |
| NFR-SYS-11 | Cấu trúc mã | Thư mục module: `authen/`, `account/`, `book/`, `loan/`, `reader/`, `reader_management/`, `request_management/`, `system/`, `inc/`, `env/` | Maintainability |
| NFR-SYS-12 | Khả dụng | Hệ thống hoạt động ổn định trên stack PHP + MySQL trong phạm vi đồ án / nội bộ | — |

---

### II.6 Ma trận Non-Functional Requirements theo role

| Hạng mục NFR | Guest | Reader | Librarian | Admin | Shared |
|---|:---:|:---:|:---:|:---:|:---:|
| Truy cập công khai / không login | ✓ | — | — | — | — |
| Portal & session | — | ✓ | ✓ | ✓ | ✓ |
| Phân quyền / guard | — | ✓ | ✓ | ✓ | ✓ |
| Bảo mật (bcrypt, SQL, XSS) | ✓ | ✓ | ✓ | ✓ | ✓ |
| Inbox & badge | — | — | ✓ | ✓ | — |
| Settings | — | — | — | ✓ | — |
| DB integrity & index | — | — | — | — | ✓ |
| UI / Responsive / Alert | ✓ | ✓ | ✓ | ✓ | ✓ |
| Triển khai môi trường | — | — | — | — | ✓ |

---

## 4. Quy tắc nghiệp vụ (Business Rules)

> Các quy tắc gắn với **Functional Requirements** — tham chiếu khi dev & UAT.

| Mã | Quy tắc | FR liên quan |
|---|---|---|
| BR-01 | Không mượn cùng 1 tựa sách 2 lần đồng thời | FR-R-11, FR-L-10, FR-L-14 |
| BR-02 | Tối đa 5 cuốn mượn cùng lúc | FR-R-11 |
| BR-03 | Fine tính khi **staff confirm trả**, không phải ngày reader submit | FR-R-16, FR-L-17 |
| BR-04 | Mỗi loan chỉ 1 pending `return_book` | FR-R-15, NFR-R-10 |
| BR-05 | Copy: `reserved` (request) → `borrowed` (approve) → `available` (trả/reject) | FR-R-11, FR-L-14, FR-L-15 |
| BR-06 | Reject return → loan không đổi; reader gửi lại được | FR-R-17, FR-L-18 |
| BR-07 | Loan `partial` / `closed` theo số sách đã trả | FR-L-11 |
| BR-08 | `inactive` không đăng nhập | FR-R-02, NFR-R-03 |
| BR-09 | `due_date = borrow_date + max_loan_days` (lúc approve) | FR-L-14, FR-A-10 |
| BR-10 | Reader Portal tách Staff Portal | NFR-R-01, NFR-L-01 |

**Công thức tiền phạt:**

```
Fine = max(0, Ngày staff confirm − Due date) × fine_per_day
```

Trả đúng/trước hạn → Fine = 0. Lưu tại `loan_details.fine_amount`.

---

## 5. Phụ lục

### 5.1 Luồng nghiệp vụ chính

**Mượn online (Reader → Staff):**

```
Reader: Borrow → Kiểm tra BR-01,02,05 → request pending + copy reserved
Staff: Approve → loan ongoing + copy borrowed + due_date
Staff: Reject → copy available
```

**Trả qua request:**

```
Reader: Request Return + ngày dự kiến → fine preview (tham khảo)
Staff: Confirm → fine chính thức (BR-03) → returned → loan partial/closed
Staff: Reject → loan giữ nguyên (BR-06)
```

**Đồng bộ hồ sơ:**

```
Librarian tạo readers (account_id NULL) → Reader đăng ký (match phone/email) → liên kết account_id
```

### 5.2 Trạng thái dữ liệu (tóm tắt)

| Thực thể | Giá trị |
|---|---|
| `book_copies.status` | `available`, `reserved`, `borrowed` |
| `loans.status` | `pending`, `ongoing`, `partial`, `closed`, `rejected` |
| `loan_details.status` | `pending`, `borrowed`, `returned`, `rejected` |
| `requests` | type: `borrow_book`, `return_book`, `librarian_registration`, `password_reset` — status: `pending`, `approved`, `rejected` |

### 5.3 Thuật ngữ

| Thuật ngữ | Định nghĩa |
|---|---|
| **Tựa sách** | Một `books`; nhiều `book_copies` |
| **Copy** | Bản vật lý, barcode riêng |
| **Loan** | Phiếu mượn một reader, có thể nhiều sách |
| **Request** | Yêu cầu chờ Staff/Admin xử lý |
| **Due date** | Hạn trả sau khi approve mượn |
| **Fine** | Tiền phạt trễ hạn khi staff confirm trả |

---

*Tài liệu phiên bản 1.1: tách rõ **Functional Requirements** (Phần I) và **Non-Functional Requirements** (Phần II); mỗi phần được tổ chức theo Guest → Reader → Librarian → Admin (+ Shared cho NFR).*
