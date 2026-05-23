CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

-- ============================================================
--  TABLE DEFINITIONS
-- ============================================================

-- 1. Metadata
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE authors (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL
);

CREATE TABLE publishers (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE
);

-- 2. Books
CREATE TABLE books (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    publisher_id INT,
    pub_year     INT,
    description  TEXT,
    cover_image  VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id)
);

CREATE TABLE book_author (
    book_id   INT,
    author_id INT,
    PRIMARY KEY (book_id, author_id),
    FOREIGN KEY (book_id)   REFERENCES books(id)   ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
);

CREATE TABLE book_category (
    book_id     INT,
    category_id INT,
    PRIMARY KEY (book_id, category_id),
    FOREIGN KEY (book_id)     REFERENCES books(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- 3. Book Copies
CREATE TABLE book_copies (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    barcode VARCHAR(50) UNIQUE NOT NULL,
    status  ENUM('available', 'borrowed', 'reserved') DEFAULT 'available',
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- 4. Roles & Accounts
CREATE TABLE roles (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE accounts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    full_name  VARCHAR(100) NOT NULL,
    phone      VARCHAR(20)  UNIQUE,
    email      VARCHAR(100) UNIQUE,
    address    TEXT,
    dob        DATE,
    gender     ENUM('male', 'female', 'other') DEFAULT 'male',
    role_id    INT,
    status     ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- 5. Requests
CREATE TABLE requests (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    type       ENUM('borrow_book', 'return_book', 'librarian_registration', 'password_reset') NOT NULL,
    account_id INT NOT NULL,
    target_id  INT NULL,
    notes      TEXT NULL,
    status     ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    -- Generated virtual column dùng cho unique constraint bên dưới
    pending_return_loan_id INT GENERATED ALWAYS AS (
        CASE WHEN type = 'return_book' AND status = 'pending' THEN target_id ELSE NULL END
    ) VIRTUAL
);

-- 6. Readers
CREATE TABLE readers (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    phone      VARCHAR(20)  UNIQUE,
    email      VARCHAR(100) UNIQUE,
    address    TEXT,
    dob        DATE,
    gender     ENUM('male', 'female', 'other') DEFAULT 'male',
    status     ENUM('active', 'inactive') DEFAULT 'active',
    account_id INT NULL DEFAULT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- 7. Loans
CREATE TABLE loans (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    reader_id   INT,
    borrow_date DATE DEFAULT (CURRENT_DATE),
    due_date    DATE NOT NULL,
    status      ENUM('pending', 'ongoing', 'partial', 'closed', 'rejected') DEFAULT 'pending',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reader_id) REFERENCES readers(id)
);

-- 8. Loan Details
CREATE TABLE loan_details (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    loan_id      INT,
    book_copy_id INT,
    return_date  DATE NULL,
    fine_amount  DECIMAL(10,2) DEFAULT 0,
    status       ENUM('pending', 'borrowed', 'returned', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (loan_id)      REFERENCES loans(id)       ON DELETE CASCADE,
    FOREIGN KEY (book_copy_id) REFERENCES book_copies(id)
);

-- 9. Settings
CREATE TABLE settings (
    setting_key   VARCHAR(50)  PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL,
    description   TEXT
);


-- ============================================================
--  INDEXES
-- ============================================================

-- book_copies: tìm bản sao available của một sách
CREATE INDEX idx_bc_book_status
    ON book_copies (book_id, status);

-- accounts: đếm user theo role + status (dashboard stats)
CREATE INDEX idx_accounts_role_status
    ON accounts (role_id, status);

-- requests: lọc nhanh theo type + status (staff panel)
CREATE INDEX idx_req_type_status
    ON requests (type, status);

-- requests: 1 loan chỉ được có 1 pending return request tại một thời điểm
--   pending_return_loan_id = target_id khi (return_book + pending), còn lại = NULL
--   UNIQUE cho phép nhiều NULL → approved/rejected không bao giờ conflict
ALTER TABLE requests
    ADD UNIQUE KEY uq_one_pending_return_per_loan (pending_return_loan_id);

-- loans: My Loans page — filter theo reader + status
CREATE INDEX idx_loans_reader_status
    ON loans (reader_id, status);

-- loan_details: đếm số lượng borrowed/returned per loan
CREATE INDEX idx_ld_status
    ON loan_details (status);
