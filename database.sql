-- 1. Create the Database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- 2. Create Users Table
-- This table stores both Students (members) and Admins (librarians)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    role VARCHAR(20) DEFAULT 'member', -- Can be 'admin' or 'member'
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Books Table
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    category VARCHAR(50),
    description TEXT,
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create Issued Books Table
CREATE TABLE issued_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    status VARCHAR(20) DEFAULT 'issued', -- 'issued' or 'returned'
    fine_amount DECIMAL(8,2) DEFAULT 0.00,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Add Indexes for Performance
CREATE INDEX idx_user_status ON issued_books (user_id, status);
CREATE INDEX idx_due_date ON issued_books (due_date);
CREATE INDEX idx_books_category ON books(category);
CREATE INDEX idx_books_available ON books(available_copies);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_issued_books_status ON issued_books(status);

-- =============================================
-- SAMPLE DATA (For Testing)
-- =============================================

-- Default Admin (Password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', 'binita123', 'Library Administrator', 'admin@library.com', 'admin');
-- Note: In your PHP code, the check is manual, but for security, use password_hash('admin123', PASSWORD_DEFAULT)

-- Sample Students (Password: student123)
INSERT INTO users (username, password, full_name, email, phone, role) VALUES
('student1', 'student123', 'John Doe', 'john@example.com', '9876543210', 'member'),
('alice', 'student123', 'Alice Brown', 'alice@example.com', '9876543213', 'member');

-- Sample Books
INSERT INTO books (title, author, isbn, category, total_copies, available_copies) VALUES
('Introduction to Algorithms', 'Thomas H. Cormen', '978-0262033848', 'Computer Science', 5, 5),
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 'Fiction', 3, 3),
('Database System Concepts', 'Abraham Silberschatz', '978-0078022159', 'Computer Science', 4, 4);