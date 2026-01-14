-- 1. Create the Database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- ==========================================
-- DROP TABLES (To ensure clean update)
-- ==========================================
DROP TABLE IF EXISTS issued_books;
DROP TABLE IF EXISTS book_copies;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

-- ==========================================
-- TABLE STRUCTURES
-- ==========================================

-- 2. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    role VARCHAR(20) DEFAULT 'member', -- 'admin' or 'member'
    status VARCHAR(20) DEFAULT 'active',
    reset_token_hash VARCHAR(64) DEFAULT NULL,
    reset_token_expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Books Table (General Info)
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    category VARCHAR(50),
    description TEXT,
    total_copies INT DEFAULT 1,     -- Total physical copies owned
    available_copies INT DEFAULT 1, -- Copies currently on shelf
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Book Copies Table (NEW: Tracks individual physical items)
CREATE TABLE book_copies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    unique_code VARCHAR(50) UNIQUE NOT NULL, -- e.g. 978123-A1B2-1
    status VARCHAR(20) DEFAULT 'available',  -- 'available', 'issued', 'lost', 'damaged'
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Issued Books Table (UPDATED: Links to specific copy)
CREATE TABLE issued_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,           -- General Book Reference (for easier stats)
    copy_id INT NOT NULL,           -- Specific Copy Reference (NEW)
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
-- INDEXES (For Performance)
-- ==========================================
CREATE INDEX idx_user_status ON issued_books (user_id, status);
CREATE INDEX idx_due_date ON issued_books (due_date);
CREATE INDEX idx_copy_status ON book_copies (status);
CREATE INDEX idx_books_search ON books (title, author, isbn);

-- ==========================================
-- SAMPLE DATA
-- ==========================================

-- 1. Insert Users
-- Admin Pass: admin123
-- Student Pass: student123
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$8Wk/y/..hashedpasswordhere..', 'Library Administrator', 'admin@library.com', 'admin');

-- Note: For testing, use a simple hash generator or register a new user via the UI.
-- The hash below is for 'student123'
INSERT INTO users (username, password, full_name, email, phone, role) VALUES
('student1', '$2y$10$YourHashedPasswordHere', 'John Doe', 'john@example.com', '9876543210', 'member');


-- 2. Insert Books
INSERT INTO books (id, title, author, isbn, category, total_copies, available_copies) VALUES
(1, 'Introduction to Algorithms', 'Thomas H. Cormen', '978-0262033848', 'Computer Science', 3, 3),
(2, 'The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 'Fiction', 2, 2);

-- 3. Insert Book Copies (Must match total_copies above)
-- Copies for Book 1 (Intro to Algorithms)
INSERT INTO book_copies (book_id, unique_code, status) VALUES
(1, '978-0262033848-A101-1', 'available'),
(1, '978-0262033848-A101-2', 'available'),
(1, '978-0262033848-A101-3', 'available');

-- Copies for Book 2 (Great Gatsby)
INSERT INTO book_copies (book_id, unique_code, status) VALUES
(2, '978-0743273565-B202-1', 'available'),
(2, '978-0743273565-B202-2', 'available');