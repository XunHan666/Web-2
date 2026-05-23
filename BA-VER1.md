# Tài Liệu Yêu Cầu Chức Năng Nghiệp Vụ (Theo Vai Trò)
*Dựa trên phiên bản cập nhật mới nhất của Hệ thống Quản lý Thư viện (LibraryOS)*

Hệ thống được chia thành 3 vai trò chính với các chức năng nghiệp vụ chuyên biệt được phân cấp từ dưới lên như sau:

## 1. Độc Giả (Reader - Role ID: 3)
Độc giả sử dụng cổng thông tin trực tuyến (Reader Portal) tự phục vụ (Self-Service) để tương tác với thư viện.

**👤 Quản Lý Cá Nhân (Profile & Auth)**
*   **Đăng ký tài khoản (Register):** Tự đăng ký tài khoản trực tuyến. Hệ thống thông minh tự động dò tìm và đồng bộ (Sync) với hồ sơ độc giả (nếu Thủ thư đã tạo trước đó tại quầy) thông qua SĐT hoặc Email.
*   **Quản lý Hồ sơ:** Tự cập nhật thông tin cá nhân, địa chỉ, số điện thoại.
*   **Đổi/Quên Mật Khẩu:** Đổi mật khẩu định kỳ hoặc gửi yêu cầu "Quên mật khẩu" đến bộ phận Admin để được cấp lại.

**🏠 Bảng Điều Khiển (My Dashboard)**
*   Theo dõi trực quan các thống kê cá nhân: Tổng số sách đã mượn, Sách đang giữ, Yêu cầu đang chờ duyệt, và Sách quá hạn.
*   Xem nhanh danh sách các cuốn sách đang mượn (Currently Borrowing) và hạn trả.

**🔍 Tra Cứu Kho Sách (Browse Books)**
*   Khám phá toàn bộ kho sách của thư viện với giao diện thân thiện.
*   Xem chi tiết số lượng sách còn sẵn sàng trong kho (Available) theo thời gian thực.
*   **Mượn sách Online (Request Borrow):** Gửi yêu cầu mượn sách trực tuyến chỉ với 1 click. Hệ thống tự động kiểm tra các điều kiện nghiệp vụ:
    *   Sách phải còn sẵn sàng trong kho.
    *   Không được mượn trùng cuốn sách đang mượn.
    *   Không được mượn quá giới hạn (tối đa 5 cuốn cùng lúc).

**🔖 Lịch Sử Mượn Trả (My Loans)**
*   Xem toàn bộ lịch sử các phiên mượn sách từ trước đến nay.
*   Theo dõi tiến độ, trạng thái (Đang mượn, Đã trả, Chờ duyệt, Quá hạn, Bị từ chối) của từng cuốn sách và từng đơn mượn.

---

## 2. Thủ Thư (Librarian - Role ID: 2)
Thủ thư là người trực tiếp vận hành các nghiệp vụ cốt lõi của thư viện hàng ngày.

**📚 Quản lý Sách (Book Management)**
*   **Thêm sách mới:** Nhập thông tin sách (Tiêu đề, tác giả, thể loại, hình ảnh, năm xuất bản...). Hệ thống tự động quản lý các bản sao vật lý (copies) tương ứng thông qua mã vạch (barcode).
*   **Cập nhật sách:** Chỉnh sửa thông tin chi tiết hoặc bổ sung/loại bỏ số lượng bản sao vật lý của sách.
*   **Xóa sách:** Xóa sách khỏi hệ thống. *Điều kiện bắt buộc:* Chỉ được phép xóa nếu tất cả các bản sao của sách đó đều đang ở trong kho (trạng thái 'Available', không có cuốn nào đang bị mượn).
*   **Tra cứu sách:** Xem danh sách trực quan, tìm kiếm nhanh theo tên sách, tên tác giả hoặc thể loại.

**👥 Quản lý Hồ sơ Độc giả (Reader Management - Offline)**
*   **Quản lý hội viên:** Xem và quản lý danh sách hồ sơ độc giả của thư viện.
*   **Tạo mới/Cập nhật:** Hỗ trợ tạo hồ sơ độc giả thủ công tại quầy.
*   **Tra cứu thông tin:** Xem chi tiết thông tin cá nhân của người mượn để tiện liên lạc.

**🔄 Quản lý Mượn / Trả (Loan Management)**
*   **Cho mượn sách (Borrow):** Tạo phiếu mượn (Loan Ticket) ghi nhận ai mượn cuốn nào, ngày mượn, ngày trả dự kiến.
*   **Nhận trả sách (Return):** Xác nhận sách được trả về thư viện, ghi nhận ngày trả thực tế và kiểm soát tình trạng sách.

**📥 Quản lý Yêu cầu Mượn Sách (Borrow Request Management)**
*   **Duyệt đơn mượn sách:** Tiếp nhận, Chấp thuận (Approve) hoặc Từ chối (Reject) các yêu cầu mượn sách trực tuyến gửi từ Độc giả. Khi duyệt, hệ thống tự động tạo phiếu mượn và tính toán ngày trả (Due Date). Khi từ chối, hệ thống tự động giải phóng sách về lại kho.

---

## 3. Quản Trị Viên (Admin - Role ID: 1)
Admin là vai trò cao nhất, chịu trách nhiệm quản lý tài khoản và cấu hình toàn bộ hoạt động của hệ thống.

Admin **kế thừa toàn bộ các chức năng nghiệp vụ của Thủ thư (Librarian)**, bao gồm:
*   ✔ Quản lý Sách (Book Management)
*   ✔ Quản lý Hồ sơ Độc giả (Reader Management)
*   ✔ Quản lý Mượn / Trả (Loan Management)
*   ✔ Quản lý Yêu cầu Mượn Sách (Borrow Request Management)

Đồng thời, Admin có thêm các **đặc quyền riêng biệt** sau:

**💼 Quản lý Tài khoản (Account Management)**
*   **Quản lý tập trung:** Quản lý tất cả tài khoản đăng nhập của hệ thống (bao gồm Admin, Librarian, và Reader).
*   **Đăng ký & Cấp quyền:** Tạo tài khoản mới, phân quyền (Role), và định danh trạng thái hoạt động (Active/Inactive).
*   **Chỉnh sửa & Hỗ trợ:** Cập nhật thông tin hồ sơ, thông tin liên lạc cho bất kỳ tài khoản nào.
*   **Kiểm soát truy cập:** Khóa hoặc vô hiệu hóa tài khoản (Admin không thể tự khóa/xóa chính tài khoản Admin gốc của mình).

**📥 Quản lý Yêu cầu Hệ thống (System Request Management)**
*   Xử lý 2 loại yêu cầu cấp cao mà **chỉ Admin mới có quyền duyệt**:
    *   **Librarian Registration:** Phê duyệt (Approve) kích hoạt tài khoản cho các Thủ thư mới đăng ký tự do, hoặc Từ chối (Reject).
    *   **Password Reset:** Xử lý các yêu cầu quên mật khẩu và cấp lại mật khẩu mới an toàn cho người dùng.

**⚙️ Quản lý Hệ thống (System Settings)**
*   Quản lý cấu hình và các tham số vận hành chung của toàn thư viện.

<br>
<br>

---

# Business Functional Requirements Document (By Role)
*Based on the latest updated version of the LibraryOS Management System*

The system is divided into 3 main roles with specific business functions structured hierarchically as follows:

## 1. Reader (Role ID: 3)
Readers use the self-service Online Portal to interact with the library.

**👤 Profile & Authentication**
*   **Register:** Self-register for an online account. The smart system automatically detects and syncs with the reader's offline profile (if previously created by a Librarian at the counter) via Phone Number or Email.
*   **Profile Management:** Update personal information, address, and phone number independently.
*   **Change/Forgot Password:** Change passwords periodically or submit a "Forgot Password" request to the Admin department for a reset.

**🏠 My Dashboard**
*   Visually track personal statistics: Total borrowed books, Books in hand, Pending requests, and Overdue books.
*   Quickly view the list of Currently Borrowing books and their respective due dates.

**🔍 Browse Books**
*   Explore the entire library catalog with a user-friendly interface.
*   View the real-time availability of physical book copies.
*   **Online Borrow Request:** Submit online book borrowing requests with a single click. The system automatically validates business rules:
    *   The book must have available copies.
    *   Cannot borrow a duplicate of a book currently held.
    *   Cannot exceed the borrowing limit (maximum of 5 active books at a time).

**🔖 My Loans**
*   View the complete history of all past and present loan sessions.
*   Track the progress and status (Borrowing, Returned, Pending, Overdue, Rejected) of each book and loan ticket.

---

## 2. Librarian (Role ID: 2)
The librarian is the person directly operating the core daily operations of the library.

**📚 Book Management**
*   **Add Book:** Enter book information (Title, author, genre, image, publication year...). The system automatically manages the corresponding physical copies via barcode.
*   **Edit Book:** Edit detailed information or add/remove the number of physical copies of the book.
*   **Delete Book:** Delete a book from the system. *Required condition:* Deletion is only permitted if all copies of that book are in the inventory ('Available' status, no copies are currently borrowed).
*   **Book Search:** View a visual list, quickly search by book title, author name, or genre.

**👥 Reader Profile Management (Offline)**
*   **Membership Management:** View and manage the list of library reader profiles.
*   **Create/Update:** Assist in creating manual reader profiles at the counter.
*   **Information Lookup:** View detailed personal information of borrowers for easy contact.

**🔄 Loan Management**
*   **Borrowing:** Create a loan ticket recording who borrowed which book, the borrowing date, and the expected return date.
*   **Returning:** Confirm that books have been returned to the library, record the actual return date, and monitor book conditions.

**📥 Borrow Request Management**
*   **Approve Borrow Requests:** Receive, Approve or Reject online book borrowing requests sent from Readers. Upon approval, the system automatically creates a loan ticket and calculates the Due Date. Upon rejection, the system releases the book copy back to the inventory.

---

## 3. Administrator (Admin - Role ID: 1)
Admin is the highest role, responsible for managing accounts and configuring all library operations. 

The Admin **inherits all the professional functions of the Librarian**, including:
*   ✔ Book Management
*   ✔ Reader Profile Management
*   ✔ Loan Management
*   ✔ Borrow Request Management

In addition, the Admin has the following **exclusive privileges**:

**💼 Account Management**
*   **Centralized Management:** Manage all system login accounts (including Admin, Librarian, and Reader roles).
*   **Registration & Authorization:** Create new accounts, assign roles, and define active/inactive statuses.
*   **Edit & Support:** Update profile information, contact details for any account.
*   **Access Control:** Lock or disable accounts (Admins are restricted from locking/deleting their own root Admin account).

**📥 System Request Management (Admin Only)**
*   Process two high-level requests that **only Admins can approve**:
    *   **Librarian Registration:** Approve to activate newly self-registered Librarian accounts, or Reject them.
    *   **Password Reset:** Process forgotten password requests and securely issue new passwords for users.

**⚙️ System Settings**
*   Manage system-wide configuration and general operational parameters of the library.
