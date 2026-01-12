-- Library Management System Database
-- 4th Semester Project

-- Create database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- Users table (for both admin and members)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    role VARCHAR(20) DEFAULT 'member',
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Books table
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

-- Issued books table
CREATE TABLE issued_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    status VARCHAR(20) DEFAULT 'issued',
    fine_amount DECIMAL(8,2) DEFAULT 0.00,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', 'admin123', 'Library Administrator', 'admin@library.com', 'admin');

-- Insert sample students (password: student123)
INSERT INTO users (username, password, full_name, email, phone, role) VALUES
('student1', 'student123', 'John Doe', 'john@example.com', '9876543210', 'member'),
('student2', 'student123', 'Jane Smith', 'jane@example.com', '9876543211', 'member'),
('student3', 'student123', 'Robert Johnson', 'robert@example.com', '9876543212', 'member'),
('alice', 'student123', 'Alice Brown', 'alice@example.com', '9876543213', 'member'),
('bob', 'student123', 'Bob Wilson', 'bob@example.com', '9876543214', 'member');

-- Insert sample books
INSERT INTO books (title, author, isbn, category, total_copies, available_copies) VALUES
('Introduction to Algorithms', 'Thomas H. Cormen', '978-0262033848', 'Computer Science', 5, 5),
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 'Fiction', 3, 3),
('Database System Concepts', 'Abraham Silberschatz', '978-0078022159', 'Computer Science', 4, 4),
('To Kill a Mockingbird', 'Harper Lee', '978-0446310789', 'Fiction', 2, 2),
('Physics for Scientists', 'Paul A. Tipler', '978-1429238898', 'Physics', 3, 3),
('Organic Chemistry', 'Paula Yurkanis Bruice', '978-0321811058', 'Chemistry', 4, 4),
('Mathematics for Engineers', 'Erwin Kreyszig', '978-0470458365', 'Mathematics', 3, 3),
('Biology: The Unity of Life', 'Cecie Starr', '978-1337408592', 'Biology', 2, 2),
('Data Structures in C', 'Aaron M. Tenenbaum', '978-0131997462', 'Computer Science', 3, 3),
('Modern Operating Systems', 'Andrew S. Tanenbaum', '978-0133591620', 'Computer Science', 2, 2);

-- Insert sample issued books
INSERT INTO issued_books (book_id, user_id, issue_date, due_date, status) VALUES
(1, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 'issued'),
(3, 3, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'issued'),
(5, 4, DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'issued'),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 13 DAY), 'returned'),
(4, 3, DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(CURDATE(), INTERVAL 18 DAY), 'returned');

-- Update book available copies based on issued books
UPDATE books SET available_copies = available_copies - 1 WHERE id IN (1, 3, 5);

-- Create indexes for better performance
CREATE INDEX idx_books_category ON books(category);
CREATE INDEX idx_books_available ON books(available_copies);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_issued_books_status ON issued_books(status);