-- ==========================================
-- 1. NUKE THE OLD DATABASE & CREATE A NEW ONE
-- ==========================================
DROP DATABASE IF EXISTS library_db;
CREATE DATABASE library_db;
USE library_db;

-- ==========================================
-- 2. TABLE STRUCTURES
-- ==========================================

-- Categories Table (For the dropdown and Quick Add feature)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users Table (Stores both Students and Librarians)
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

-- Books Table (General Book Information WITH E-BOOK SUPPORT)
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    category VARCHAR(100), 
    description TEXT,
    cover_image VARCHAR(255) DEFAULT 'default.png',
    ebook_file VARCHAR(255) DEFAULT NULL,  -- NEW COLUMN ADDED FOR E-BOOKS
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Book Copies Table (Tracks individual physical books with unique codes)
CREATE TABLE book_copies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    unique_code VARCHAR(50) UNIQUE NOT NULL, 
    status VARCHAR(20) DEFAULT 'available', -- 'available', 'issued', 'lost'
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Book Requests Table (Allows students to request books)
CREATE TABLE book_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Issued Books Table (Tracks borrowed books and fines)
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
-- 3. DEFAULT / INITIAL DATA
-- ==========================================

-- Default Librarian Account
-- Username: librarian
-- Password: admin 
INSERT INTO users (username, password, full_name, email, role) VALUES
('librarian', '$2y$10$8Wk/y/rQ6lB.0k0vMv5XEOpG9XnLwR6k/m6e2e2e2e2e2e2e2e2e2', 'Head Librarian', 'librarian@library.com', 'admin');

-- Default Student Account
-- Username: student
-- Password: password123
INSERT INTO users (username, password, full_name, email, role) VALUES
('student', '$2y$10$mN3QY6tD2s2qB05hL.W8Ieu6r0kG1G2n4Y5G6G7G8G9G0G1G2G3G4', 'John Doe', 'student@library.com', 'member');

-- Default Categories
INSERT INTO categories (name) VALUES 
('Computer Science'), 
('Mathematics'), 
('Fiction'), 
('Science Fiction'), 
('Biography'), 
('History');

-- Sample Book
INSERT INTO books (title, author, isbn, category, total_copies, available_copies) VALUES
('Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 'Computer Science', 2, 2);

-- Sample Book Copies
INSERT INTO book_copies (book_id, unique_code, status) VALUES
(1, '9780262033848-101-1', 'available'),
(1, '9780262033848-101-2', 'available');

-- ==========================================
-- 4. INDEXES FOR PERFORMANCE
-- ==========================================
CREATE INDEX idx_book_isbn ON books(isbn);
CREATE INDEX idx_issue_status ON issued_books(status);
CREATE INDEX idx_copy_code ON book_copies(unique_code);
CREATE INDEX idx_request_status ON book_requests(status);