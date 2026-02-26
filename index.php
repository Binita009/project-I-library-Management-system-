<?php
require_once 'config/db.php';
$page_title = "Home";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 100px 0; text-align: center; }
        .hero h1 { font-size: 48px; margin-bottom: 20px; }
        .hero p { font-size: 20px; margin-bottom: 30px; max-width: 700px; margin-left: auto; margin-right: auto; }
        .features { padding: 80px 0; background: #f8f9fa; }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 50px; }
        .feature-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s; }
        .feature-card:hover { transform: translateY(-10px); }
        .feature-icon { font-size: 48px; color: #667eea; margin-bottom: 20px; }
        .stats { background: #2c3e50; color: white; padding: 60px 0; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; color: #3498db; }
        .cta { padding: 80px 0; text-align: center; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="hero">
        <div class="container">
            <h1>Welcome to Our Library</h1>
            <p>Explore thousands of books, manage your reading, and join our community of readers. Perfect for students and book lovers.</p>
            <div style="display: flex; gap: 20px; justify-content: center;">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <a href="admin/admin_dashboard.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 18px;">
                            Go to Librarian Panel
                        </a>
                    <?php else: ?>
                        <a href="member/dashboard.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 18px;">
                            Go to My Account
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 18px;">
                        Join Now
                    </a>
                    <a href="member/books.php" class="btn" style="padding: 15px 30px; font-size: 18px; background: white; color: #667eea;">
                        Browse Books
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <section class="features">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px; color: #2c3e50;">Features</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Book Management</h3>
                    <p>Add, edit, and manage books in the library collection with easy CRUD operations.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h3>Member Management</h3>
                    <p>Manage student accounts, track borrowing history, and monitor library usage.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3>Book Issuing System</h3>
                    <p>Simple book issuing and returning system with due date tracking.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="stats">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 50px;">Library Statistics</h2>
            <div class="stat-grid">
                <?php
                // Get statistics
                $total_books = mysqli_query($conn, "SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
                $total_members = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='member'")->fetch_assoc()['count'];
                $issued_books = mysqli_query($conn, "SELECT COUNT(*) as count FROM issued_books WHERE status='issued'")->fetch_assoc()['count'];
                $available_books = mysqli_query($conn, "SELECT SUM(available_copies) as total FROM books")->fetch_assoc()['total'];
                ?>
                <div>
                    <div class="stat-number"><?php echo $total_books; ?></div>
                    <p>Total Books</p>
                </div>
                <div>
                    <div class="stat-number"><?php echo $total_members; ?></div>
                    <p>Registered Students</p>
                </div>
                <div>
                    <div class="stat-number"><?php echo $issued_books; ?></div>
                    <p>Books Issued</p>
                </div>
                <div>
                    <div class="stat-number"><?php echo $available_books; ?></div>
                    <p>Available Books</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="cta">
        <div class="container">
            <h2 style="color: #2c3e50; margin-bottom: 30px;">Ready to Start?</h2>
            <p style="font-size: 18px; color: #666; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">
                Join our library community today and access thousands of books. Whether you're a student or a librarian, we have the tools you need.
            </p>
            <div style="display: flex; gap: 20px; justify-content: center;">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="auth/register.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 18px;">
                        <i class="fas fa-user-plus"></i> Sign Up Free
                    </a>
                    <a href="auth/login.php" class="btn" style="padding: 15px 40px; font-size: 18px; background: #2c3e50; color: white;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php else: ?>
                    <a href="member/books.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 18px;">
                        <i class="fas fa-book-open"></i> Browse Books
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/validation.js"></script>
</body>
</html>