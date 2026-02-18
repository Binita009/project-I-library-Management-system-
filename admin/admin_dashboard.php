<?php
require_once '../config/db.php';
requireAdmin();

// --- 1. EXISTING COUNTERS ---
$books_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM books"))['c'];
$students_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='member'"))['c'];
$issued_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM issued_books WHERE status='issued'"))['c'];
$overdue_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM issued_books WHERE status='issued' AND due_date < CURDATE()"))['c'];

// --- 2. NEW: DATA FOR CHARTS ---

// Chart A: Top 5 Most Issued Books
$top_books_labels = [];
$top_books_data = [];
$sql_top = "SELECT b.title, COUNT(ib.id) as issue_count 
            FROM issued_books ib 
            JOIN books b ON ib.book_id = b.id 
            GROUP BY ib.book_id 
            ORDER BY issue_count DESC LIMIT 5";
$res_top = mysqli_query($conn, $sql_top);
while($row = mysqli_fetch_assoc($res_top)) {
    // Shorten title if it's too long (e.g., "Harry Potter..." becomes "Harry P...")
    $title = strlen($row['title']) > 15 ? substr($row['title'], 0, 15) . '...' : $row['title'];
    $top_books_labels[] = $title;
    $top_books_data[] = $row['issue_count'];
}

// Chart B: Books per Category
$cat_labels = [];
$cat_data = [];
$sql_cat = "SELECT category, COUNT(*) as count FROM books GROUP BY category";
$res_cat = mysqli_query($conn, $sql_cat);
while($row = mysqli_fetch_assoc($res_cat)) {
    $cat_labels[] = $row['category'];
    $cat_data[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* Stats Grid Fix */
        .stats-grid a { text-decoration: none; color: inherit; display: block; }
        .stat-card { transition: all 0.3s ease; cursor: pointer; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        
        /* Chart Container Styling */
        .charts-container {
            display: grid;
            grid-template-columns: 2fr 1fr; /* 2 parts width, 1 part width */
            gap: 25px;
            margin-bottom: 30px;
        }
        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        /* Make charts responsive on mobile */
        @media (max-width: 900px) {
            .charts-container { grid-template-columns: 1fr; }
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
                    <p style="color: #7f8c8d; margin-top: 5px;">Overview & Analytics</p>
                </div>
                <div>
                    <a href="issue_book.php" class="btn btn-primary"><i class="fas fa-plus"></i> Issue New Book</a>
                </div>
            </div>
            
            <!-- 1. Stats Grid -->
            <div class="stats-grid">
                <a href="manage_book.php">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #4361ee;"><i class="fas fa-book"></i></div>
                        <div class="stat-info">
                            <h3><?= $books_count ?></h3>
                            <p>Total Books</p>
                        </div>
                    </div>
                </a>
                
                <a href="manage_members.php">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #3f37c9;"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3><?= $students_count ?></h3>
                            <p>Students</p>
                        </div>
                    </div>
                </a>
                
                <a href="issue_book.php">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #4cc9f0;"><i class="fas fa-book-reader"></i></div>
                        <div class="stat-info">
                            <h3><?= $issued_count ?></h3>
                            <p>Issued Now</p>
                        </div>
                    </div>
                </a>
                
                <a href="return_book.php">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #f72585;"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <h3><?= $overdue_count ?></h3>
                            <p>Overdue</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- 2. Charts Section (NEW) -->
            <div class="charts-container">
                <!-- Bar Chart: Most Popular Books -->
                <div class="chart-card">
                    <h3 style="margin-bottom: 15px; color: #444;">Most Popular Books (Top 5)</h3>
                    <canvas id="topBooksChart"></canvas>
                </div>
                
                <!-- Doughnut Chart: Categories -->
                <div class="chart-card">
                    <h3 style="margin-bottom: 15px; color: #444;">Library Composition</h3>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            
            <!-- 3. Recent Activity Table -->
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
                            <th>Issued On</th>
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
                        if(mysqli_num_rows($recent) > 0):
                            while($row = mysqli_fetch_assoc($recent)):
                                $is_overdue = ($row['status'] == 'issued' && strtotime($row['due_date']) < time());
                        ?>
                        <tr>
                            <td style="font-weight: 500;"><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= date('M d', strtotime($row['issue_date'])) ?></td>
                            <td><?= date('M d', strtotime($row['due_date'])) ?></td>
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
                        <?php endwhile; 
                        else: ?>
                            <tr><td colspan="5" style="text-align:center;">No recent activity</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <!-- JAVASCRIPT FOR CHARTS -->
    <script>
        // Data from PHP
        const topBooksLabels = <?php echo json_encode($top_books_labels); ?>;
        const topBooksData = <?php echo json_encode($top_books_data); ?>;
        
        const catLabels = <?php echo json_encode($cat_labels); ?>;
        const catData = <?php echo json_encode($cat_data); ?>;

        // 1. Bar Chart Config
        const ctx1 = document.getElementById('topBooksChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: topBooksLabels,
                datasets: [{
                    label: 'Times Issued',
                    data: topBooksData,
                    backgroundColor: '#4361ee',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

        // 2. Doughnut Chart Config
        const ctx2 = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: [
                        '#4cc9f0', '#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4895ef'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
                }
            }
        });
    </script>
</body>
</html>