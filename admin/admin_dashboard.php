<?php
require_once '../config/db.php';
requireAdmin();

// Get statistics
$total_books = mysqli_query($conn, "SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$total_members = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='member'")->fetch_assoc()['count'];
$issued_books = mysqli_query($conn, "SELECT COUNT(*) as count FROM issued_books WHERE status='issued'")->fetch_assoc()['count'];
$overdue_books = mysqli_query($conn, "SELECT COUNT(*) as count FROM issued_books WHERE status='issued' AND due_date < CURDATE()")->fetch_assoc()['count'];

// Recent activities
$recent_issues = mysqli_query($conn, "SELECT ib.*, b.title, u.full_name 
                                     FROM issued_books ib 
                                     JOIN books b ON ib.book_id = b.id 
                                     JOIN users u ON ib.user_id = u.id 
                                     ORDER BY ib.issue_date DESC 
                                     LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Admin Dashboard</h1>
                <div class="date-display"><?php echo date('l, F j, Y'); ?></div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon books">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_books; ?></div>
                    <div class="stat-label">Total Books</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_members; ?></div>
                    <div class="stat-label">Students</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon issued">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="stat-number"><?php echo $issued_books; ?></div>
                    <div class="stat-label">Books Issued</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon overdue">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-number"><?php echo $overdue_books; ?></div>
                    <div class="stat-label">Overdue Books</div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="add_book.php" class="action-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add New Book</span>
                </a>
                <a href="issue_book.php" class="action-btn">
                    <i class="fas fa-book-medical"></i>
                    <span>Issue Book</span>
                </a>
                <a href="manage_members.php" class="action-btn">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Student</span>
                </a>
                <a href="return_book.php" class="action-btn">
                    <i class="fas fa-book-return"></i>
                    <span>Process Returns</span>
                </a>
            </div>
            
            <div class="content-row">
                <!-- Recent Issues -->
                <div class="card">
                    <h3>Recent Book Issues</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Book</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($recent_issues) > 0): ?>
                                    <?php while($issue = mysqli_fetch_assoc($recent_issues)): 
                                        $due_date = new DateTime($issue['due_date']);
                                        $today = new DateTime();
                                        $is_overdue = $due_date < $today;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($issue['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($issue['title']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($issue['issue_date'])); ?></td>
                                        <td><?php echo date('d M Y', strtotime($issue['due_date'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $is_overdue ? 'badge-danger' : 'badge-success'; ?>">
                                                <?php echo $is_overdue ? 'Overdue' : 'Active'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No recent issues</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="card">
                    <h3>System Status</h3>
                    <div class="status-list">
                        <div class="status-item">
                            <span class="status-label">Database Connection</span>
                            <span class="status-indicator active">✓ Connected</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Session Status</span>
                            <span class="status-indicator active">✓ Active</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Last Backup</span>
                            <span class="status-indicator"><?php echo date('Y-m-d H:i'); ?></span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Users Online</span>
                            <span class="status-indicator">1</span>
                        </div>
                    </div>
                    <div class="system-info">
                        <h4>Quick Tips:</h4>
                        <ul>
                            <li>Always check book availability before issuing</li>
                            <li>Monitor overdue books daily</li>
                            <li>Regularly backup the database</li>
                            <li>Update book quantities when new stock arrives</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .content-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-top: 30px;
        }
        .status-list {
            margin-bottom: 25px;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .status-indicator {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        .status-indicator.active {
            background: #d4edda;
            color: #155724;
        }
        .system-info ul {
            padding-left: 20px;
            color: #555;
        }
        .system-info li {
            margin-bottom: 8px;
        }
        @media (max-width: 992px) {
            .content-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>