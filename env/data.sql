-- Safely clean up old data
DELETE FROM loan_details;
DELETE FROM loans;
DELETE FROM book_copies;
DELETE FROM book_author;
DELETE FROM book_category;
DELETE FROM books;
DELETE FROM authors;
DELETE FROM categories;
DELETE FROM publishers;
DELETE FROM readers;
DELETE FROM users;
DELETE FROM roles;
DELETE FROM settings;

-- 1. Insert Categories
INSERT INTO categories (id, name) VALUES
(5, 'Biography'),
(10, 'Dystopian'),
(1, 'Fiction'),
(6, 'History'),
(4, 'Mystery'),
(2, 'Non-Fiction'),
(7, 'Philosophy'),
(9, 'Romance'),
(3, 'Science Fiction'),
(8, 'Self-Help');

-- 2. Insert Authors
INSERT INTO authors (id, name) VALUES
(1, 'George Orwell'),
(2, 'James Clear'),
(3, 'Aldous Huxley'),
(4, 'Jane Austen'),
(5, 'Yuval Noah Harari'),
(6, 'Paulo Coelho'),
(7, 'J.D. Salinger'),
(8, 'Antoine de Saint-Exupéry'),
(9, 'Oscar Wilde'),
(10, 'F. Scott Fitzgerald'),
(11, 'Harper Lee'),
(12, 'ad'),
(13, 'adbfab');

-- 3. Insert Publishers
INSERT INTO publishers (id, name) VALUES
(13, 'àbafn'),
(2, 'Avery'),
(3, 'Chatto & Windus'),
(12, 'chém gió'),
(6, 'HarperTorch'),
(5, 'Harvill Secker'),
(11, 'J. B. Lippincott & Co.'),
(9, 'Lippincott''s Monthly Magazine'),
(7, 'Little, Brown and Company'),
(8, 'Reynal & Hitchcock'),
(10, 'Scribner'),
(1, 'Secker & Warburg'),
(4, 'T. Egerton');

-- 4. Insert Books
INSERT INTO books (id, title, publisher_id, pub_year, description, cover_image) VALUES
(1, '1984', 1, 1949, 'A dystopian social science fiction novel and cautionary tale.', 'uploads/books/1984.jpg'),
(2, 'Animal Farm', 1, 1945, 'A beast fable, in form of satirical allegorical novella, by George Orwell.', 'uploads/books/Animal Farm.jpg'),
(3, 'Atomic Habits', 2, 2018, 'An Easy & Proven Way to Build Good Habits & Break Bad Ones.', 'uploads/books/Atomic Habits.jpg'),
(4, 'Brave New World', 3, 1932, 'A dystopian fiction novel set in a futuristic World State.', 'uploads/books/brave new world.jpg'),
(5, 'Pride & Prejudice', 4, 1813, 'An 1813 novel of manners by Jane Austen.', 'uploads/books/Pride and Prejudice.jpg'),
(6, 'Sapiens: The Birth of Humankind', 5, 2011, 'A Graphic History detailing the evolution of humans.', 'uploads/books/Sapiens The Birth of Humankind.jpg'),
(7, 'The Alchemist', 6, 1988, 'A magical story about following your dreams.', 'uploads/books/the alchemist.jpg'),
(8, 'The Catcher in the Rye', 7, 1951, 'The story of teenage alienation and loss of innocence in the United States.', 'uploads/books/the catcher in the rye.jpg'),
(9, 'The Little Prince', 8, 1943, 'A philosophical tale, with humanist values, told by a pilot stranded in the desert.', 'uploads/books/the little prince.jpg'),
(10, 'The Picture of Dorian Gray', 9, 1890, 'A portrait reveals the true corruption of an eternally youthful man.', 'uploads/books/the picture of dorian gray.jpg'),
(11, 'The Great Gatsby', 10, 1925, 'A story of the fabulously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan.', 'uploads/books/the_great_gatsby.jpg'),
(12, 'To Kill a Mockingbird', 11, 1960, 'The story of a lawyer''s advice to his children as he defends the real mockingbird.', 'uploads/books/to kill a mockingbird.jpg');

-- 5. Link Books with Authors
INSERT INTO book_author (book_id, author_id) VALUES
(1, 1),
(2, 1),
(3, 2),
(4, 3),
(5, 4),
(6, 5),
(7, 6),
(8, 7),
(9, 8),
(10, 9),
(11, 10),
(12, 11);

-- 6. Link Books with Categories
INSERT INTO book_category (book_id, category_id) VALUES
(1, 1),
(1, 10),
(2, 1),
(2, 10),
(3, 2),
(3, 8),
(4, 1),
(4, 3),
(4, 10),
(5, 1),
(5, 9),
(6, 2),
(6, 6),
(7, 1),
(7, 7),
(8, 1),
(9, 1),
(9, 7),
(10, 1),
(10, 7),
(11, 1),
(12, 1),
(12, 6);

-- 7. Insert Book Copies
INSERT INTO book_copies (id, book_id, barcode, status) VALUES
(1, 1, 'BC1-001', 'available'),
(2, 1, 'BC1-002', 'available'),
(3, 1, 'BC1-003', 'available'),
(4, 1, 'BC1-004', 'available'),
(5, 1, 'BC1-005', 'available'),
(6, 2, 'BC2-001', 'available'),
(7, 2, 'BC2-002', 'borrowed'),
(8, 2, 'BC2-003', 'available'),
(9, 2, 'BC2-004', 'available'),
(10, 2, 'BC2-005', 'available'),
(11, 3, 'BC3-001', 'available'),
(12, 3, 'BC3-002', 'borrowed'),
(13, 3, 'BC3-003', 'available'),
(14, 3, 'BC3-004', 'available'),
(15, 3, 'BC3-005', 'available'),
(16, 4, 'BC4-001', 'borrowed'),
(17, 4, 'BC4-002', 'available'),
(18, 4, 'BC4-003', 'available'),
(19, 4, 'BC4-004', 'available'),
(20, 4, 'BC4-005', 'available'),
(21, 5, 'BC5-001', 'available'),
(22, 5, 'BC5-002', 'available'),
(23, 5, 'BC5-003', 'available'),
(24, 5, 'BC5-004', 'available'),
(25, 5, 'BC5-005', 'available'),
(26, 6, 'BC6-001', 'borrowed'),
(27, 6, 'BC6-002', 'available'),
(28, 6, 'BC6-003', 'available'),
(29, 6, 'BC6-004', 'available'),
(30, 6, 'BC6-005', 'available'),
(31, 7, 'BC7-001', 'available'),
(32, 7, 'BC7-002', 'borrowed'),
(33, 7, 'BC7-003', 'available'),
(34, 7, 'BC7-004', 'available'),
(35, 7, 'BC7-005', 'available'),
(36, 8, 'BC8-001', 'available'),
(37, 8, 'BC8-002', 'available'),
(38, 8, 'BC8-003', 'available'),
(39, 8, 'BC8-004', 'available'),
(40, 8, 'BC8-005', 'available'),
(41, 9, 'BC9-001', 'borrowed'),
(42, 9, 'BC9-002', 'borrowed'),
(43, 9, 'BC9-003', 'available'),
(44, 9, 'BC9-004', 'available'),
(45, 9, 'BC9-005', 'available'),
(46, 10, 'BC10-001', 'borrowed'),
(47, 10, 'BC10-002', 'available'),
(48, 10, 'BC10-003', 'available'),
(49, 10, 'BC10-004', 'available'),
(50, 10, 'BC10-005', 'available'),
(51, 11, 'BC11-001', 'borrowed'),
(52, 11, 'BC11-002', 'available'),
(53, 11, 'BC11-003', 'available'),
(54, 11, 'BC11-004', 'available'),
(55, 11, 'BC11-005', 'available'),
(56, 12, 'BC12-001', 'borrowed'),
(57, 12, 'BC12-002', 'available'),
(58, 12, 'BC12-003', 'available'),
(59, 12, 'BC12-004', 'available'),
(60, 12, 'BC12-005', 'available');

-- 8. Insert Readers
INSERT INTO readers (id, name, phone, email, address, dob, gender, status, created_at) VALUES
(1, 'An Nguyen', '0901234567', 'an.nguyen@gmail.com', '123 Le Loi, HCMC', '1995-05-15', 'male', 'active', '2026-04-26 10:04:05'),
(2, 'Binh Tran', '0912345678', 'binh.tran@gmail.com', '456 Tran Hung Dao, Hanoi', '1990-08-20', 'male', 'active', '2026-04-26 10:04:05'),
(3, 'Dung Pham', '0923456789', 'dung.pham@gmail.com', '789 Nguyen Hue, HCMC', '1998-11-10', 'male', 'active', '2026-04-26 10:04:05'),
(4, 'Hoa Vu', '0934567890', 'hoa.vu@gmail.com', '321 Pham Van Dong, Danang', '1985-02-28', 'male', 'active', '2026-04-26 10:04:05'),
(5, 'Khoa Dang', '0945678901', 'khoa.dang@gmail.com', '654 Nguyen Van Linh, HCMC', '2001-07-22', 'male', 'active', '2026-04-26 10:04:05'),
(6, 'Giang Do', '0956789012', 'giang.do@gmail.com', '987 Le Duan, Hue', '1992-12-05', 'male', 'active', '2026-04-26 10:04:05'),
(7, 'Huong Bui', '0967890156', 'huong.bui@gmail.com', '654 Ba Trieu, Hanoi', '1988-03-25', 'male', 'active', '2026-04-26 10:04:05'),
(8, 'Lam Truong', '0978901234', 'lam.truong@gmail.com', '120 Nguyen Dinh Chieu, HCMC', '1996-09-15', 'male', 'active', '2026-04-26 10:04:05'),
(9, 'Minh Le', '0989012345', 'minh.le@gmail.com', '741 Tran Phu, Danang', '1993-06-30', 'male', 'active', '2026-04-26 10:04:05'),
(10, 'Nhung Phan', '0990123456', 'nhung.phan@gmail.com', '852 Ly Thuong Kiet, Hanoi', '1999-01-12', 'male', 'active', '2026-04-26 10:04:05'),
(12, 'sầd', '0731031531', 'ava@gmail.com', NULL, NULL, 'male', 'active', '2026-05-15 09:45:37');

-- 9. Insert Loans
INSERT INTO loans (id, reader_id, borrow_date, due_date, status, created_at) VALUES
(1, 1, '2026-04-16', '2026-04-21', 'closed', '2026-04-26 10:04:05'),
(2, 2, '2026-04-16', '2026-04-21', 'closed', '2026-04-26 10:04:05'),
(3, 3, '2026-04-18', '2026-04-23', 'closed', '2026-04-26 10:04:05'),
(4, 4, '2026-04-20', '2026-04-25', 'closed', '2026-04-26 10:04:05'),
(5, 5, '2026-04-22', '2026-04-27', 'partial', '2026-04-26 10:04:05'),
(6, 6, '2026-04-23', '2026-04-28', 'ongoing', '2026-04-26 10:04:05'),
(7, 7, '2026-04-24', '2026-04-29', 'ongoing', '2026-04-26 10:04:05'),
(8, 8, '2026-04-25', '2026-04-30', 'ongoing', '2026-04-26 10:04:05'),
(9, 9, '2026-04-26', '2026-05-01', 'ongoing', '2026-04-26 10:04:05'),
(10, 10, '2026-04-26', '2026-05-01', 'ongoing', '2026-04-26 10:04:05'),
(11, 2, '2026-04-26', '2026-05-01', 'partial', '2026-04-26 10:14:38'),
(12, 12, '2026-05-15', '2026-05-20', 'closed', '2026-05-15 09:53:36');

-- 10. Insert Loan Details
INSERT INTO loan_details (id, loan_id, book_copy_id, return_date, fine_amount, status) VALUES
(1, 1, 1, '2026-04-19', 0.00, 'returned'),
(2, 2, 8, '2026-04-20', 0.00, 'returned'),
(3, 3, 2, '2026-05-15', 110000.00, 'returned'),
(5, 5, 26, NULL, 0.00, 'borrowed'),
(6, 5, 11, '2026-04-25', 0.00, 'returned'),
(7, 6, 7, NULL, 0.00, 'borrowed'),
(8, 7, 12, NULL, 0.00, 'borrowed'),
(9, 8, 16, NULL, 0.00, 'borrowed'),
(10, 9, 32, NULL, 0.00, 'borrowed'),
(11, 10, 41, NULL, 0.00, 'borrowed'),
(12, 10, 51, NULL, 0.00, 'borrowed'),
(13, 10, 56, NULL, 0.00, 'borrowed'),
(14, 11, 52, '2026-05-15', 70000.00, 'returned'),
(15, 11, 42, NULL, 0.00, 'borrowed'),
(16, 11, 46, NULL, 0.00, 'borrowed'),
(17, 12, 21, '2026-05-15', 0.00, 'returned'),
(18, 12, 31, '2026-05-15', 0.00, 'returned'),
(19, 12, 36, '2026-05-15', 0.00, 'returned'),
(20, 12, 52, '2026-05-15', 0.00, 'returned'),
(21, 12, 43, '2026-05-15', 0.00, 'returned');

-- 11. Insert Roles & Users
INSERT INTO roles (id, name) VALUES
(1, 'admin'),
(2, 'librarian');

INSERT INTO users (id, username, password, full_name, role_id, status, created_at) VALUES
(1, 'admin', '$2y$10$t8PFiYmvgfYNnctlQAdiM.wTQrFfbrqVRxM0QKZASt9A7yAq3IF5m', 'Administrator', 1, 'active', '2026-04-26 10:04:05'),
(2, 'daddy', '$2y$10$/sC4tJEmLaVgmcqiCngLNegSz9wRqJo4r9hHvkh6vA0xXO2rLFKZe', 'davd', 2, 'active', '2026-04-26 10:43:49');

-- 12. Insert Settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('fine_per_day', '5000', 'Late return fine amount per day in VND'),
('max_loan_days', '5', 'Standard number of days allowed for a loan');
