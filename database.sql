-- 1. Create the Database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- ==========================================
-- DROP TABLES (Ordered to handle dependencies)
-- ==========================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS issued_books;
DROP TABLE IF EXISTS book_requests;
DROP TABLE IF EXISTS book_copies;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;
SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- TABLE STRUCTURES
-- ==========================================

-- 2. Categories Table (For the dropdown and Quick Add feature)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Users Table (Stores both Students and Librarians)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    role VARCHAR(20) DEFAULT 'member', -- 'admin' (Librarian) or 'member' (Student)
    status VARCHAR(20) DEFAULT 'active',
    reset_token_hash VARCHAR(64) DEFAULT NULL,
    reset_token_expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Books Table (General Book Information)
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    category VARCHAR(100), 
    description TEXT,
    cover_image VARCHAR(255) DEFAULT 'default.png',
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Book Copies Table (Tracks individual physical books with unique codes)
CREATE TABLE book_copies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    unique_code VARCHAR(50) UNIQUE NOT NULL, 
    status VARCHAR(20) DEFAULT 'available', -- 'available', 'issued', 'lost'
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Book Requests Table (NEW: Allows students to request books that are out of stock/available)
CREATE TABLE book_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Issued Books Table (Tracks borrowed books and fines)
CREATE TABLE issued_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    copy_id INT NOT NULL,
    user_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'issued', -- 'issued' or 'returned'
    fine_amount DECIMAL(8,2) DEFAULT 0.00,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (copy_id) REFERENCES book_copies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- DEFAULT / SAMPLE DATA
-- ==========================================

-- 1. Default Librarian Account
-- Username: librarian
-- Password: admin
-- (The login file has a fallback allowing the word "admin" to work until you update it in the profile)
INSERT INTO users (username, password, full_name, email, role) VALUES
('librarian', 'admin', 'Head Librarian', 'librarian@library.com', 'admin');

-- 2. Default Student Account
-- Username: student
-- Password: password123
-- (Stored as plaintext for initial login, you should reset this from profile)
INSERT INTO users (username, password, full_name, email, role) VALUES
('student', 'password123', 'John Doe', 'student@library.com', 'member');

-- 3. Default Categories
INSERT INTO categories (name) VALUES 
('Computer Science'), 
('Mathematics'), 
('Fiction'), 
('Science Fiction'), 
('Biography'), 
('History');

-- 4. Sample Book
INSERT INTO books (title, author, isbn, category, total_copies, available_copies) VALUES
('Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 'Computer Science', 2, 2);

-- 5. Sample Book Copies
INSERT INTO book_copies (book_id, unique_code, status) VALUES
(1, '9780262033848-101-1', 'available'),
(1, '9780262033848-101-2', 'available');

-- ==========================================
-- INDEXES FOR PERFORMANCE
-- ==========================================
CREATE INDEX idx_book_isbn ON books(isbn);
CREATE INDEX idx_issue_status ON issued_books(status);
CREATE INDEX idx_copy_code ON book_copies(unique_code);
CREATE INDEX idx_request_status ON book_requests(status);