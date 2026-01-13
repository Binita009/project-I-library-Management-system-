<?php
require_once '../config/db.php';
requireAdmin();

// Calculate Stats
$books_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM books"))['c'];
$students_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='member'"))['c'];
$issued_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM issued_books WHERE status='issued'"))['c'];
$overdue_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM issued_books WHERE status='issued' AND due_date < CURDATE()"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Ensures the links don't change text color */
        .stats-grid a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .stat-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: 1px solid var(--primary);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <div>
                    <h1>Dashboard</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Welcome back, here's what's happening today.</p>
                </div>
                <div>
                    <a href="issue_book.php" class="btn btn-primary"><i class="fas fa-plus"></i> Issue New Book</a>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <!-- Total Books -->
                <a href="manage_book.php">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #4361ee;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $books_count ?></h3>
                            <p>Total Books</p>
                        </div>
                    </div>
                </a>
                
                <!-- Students -->
                <a href="manage_members.php">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #3f37c9;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $students_count ?></h3>
                            <p>Students</p>
                        </div>
                    </div>
                </a>
                
                <!-- Issued Now -->
                <a href="return_book.php">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #4cc9f0;">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $issued_count ?></h3>
                            <p>Issued Now</p>
                        </div>
                    </div>
                </a>
                
                <!-- Overdue -->
                <a href="return_book.php">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #f72585;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $overdue_count ?></h3>
                            <p>Overdue</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Recent Activity Table -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Recent Issues</h3>
                    <a href="return_book.php" style="color: var(--primary); text-decoration: none; font-size: 14px;">View All</a>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Book Title</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent = mysqli_query($conn, "
                            SELECT ib.*, u.full_name, b.title 
                            FROM issued_books ib
                            JOIN users u ON ib.user_id = u.id
                            JOIN books b ON ib.book_id = b.id
                            ORDER BY ib.issue_date DESC LIMIT 5
                        ");
                        while($row = mysqli_fetch_assoc($recent)):
                            $is_overdue = ($row['status'] == 'issued' && strtotime($row['due_date']) < time());
                        ?>
                        <tr>
                            <td style="font-weight: 500;"><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['issue_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($row['due_date'])) ?></td>
                            <td>
                                <?php if($row['status'] == 'returned'): ?>
                                    <span class="badge badge-success">Returned</span>
                                <?php elseif($is_overdue): ?>
                                    <span class="badge badge-danger">Overdue</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Issued</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>