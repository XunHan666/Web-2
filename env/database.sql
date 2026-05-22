CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

-- 1. Metadata Tables (Categories, Authors, Publishers)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL
);

CREATE TABLE publishers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE
);

-- 2. Table: books
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    publisher_id INT,
    pub_year INT,
    description TEXT,
    cover_image VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id)
);

-- Junction tables for Many-to-Many relationships
CREATE TABLE book_author (
    book_id INT,
    author_id INT,
    PRIMARY KEY (book_id, author_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
);

CREATE TABLE book_category (
    book_id INT,
    category_id INT,
    PRIMARY KEY (book_id, category_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- 3. Table: book_copies (Track physical book assets)
CREATE TABLE book_copies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    barcode VARCHAR(50) UNIQUE NOT NULL, -- Barcode label on the book spine
    status ENUM('available', 'borrowed', 'reserved') DEFAULT 'available',
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- 4. Authorization & System Users
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- 5. Table: readers
CREATE TABLE readers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) UNIQUE,
    email VARCHAR(100) UNIQUE,
    address TEXT,
    dob DATE,
    gender ENUM('male', 'female', 'other') DEFAULT 'male',
    status ENUM('active', 'inactive') DEFAULT 'active', -- Account status control
    user_id INT NULL DEFAULT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- 6. Table: loans (Loan Transaction - Master)
CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reader_id INT,
    borrow_date DATE DEFAULT (CURRENT_DATE),
    due_date DATE NOT NULL,
    status ENUM('pending', 'ongoing', 'partial', 'closed', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reader_id) REFERENCES readers(id)
);

-- 7. Table: loan_details (Circulation Details & Returns)
CREATE TABLE loan_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT,
    book_copy_id INT, -- Physical asset borrowed
    return_date DATE NULL, -- NULL means not yet returned
    fine_amount DECIMAL(10,2) DEFAULT 0, -- Late return or damage fine
    status ENUM('pending', 'borrowed', 'returned', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (book_copy_id) REFERENCES book_copies(id)
);

-- 8. System Configuration
CREATE TABLE settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL,
    description TEXT
);
