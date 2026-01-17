<?php
require_once '../config/db.php';
requireAdmin();

// Calculate Total Collected Fine
$total_sql = "SELECT SUM(fine_amount) as total FROM issued_books WHERE status='returned'";
$total_res = mysqli_query($conn, $total_sql);
$total_data = mysqli_fetch_assoc($total_res);
$total_collected = $total_data['total'] ?? 0;

// Fetch Fine History
$sql = "SELECT ib.*, b.title, u.full_name, u.username, bc.unique_code 
        FROM issued_books ib 
        JOIN books b ON ib.book_id = b.id 
        JOIN users u ON ib.user_id = u.id 
        LEFT JOIN book_copies bc ON ib.copy_id = bc.id
        WHERE ib.status = 'returned' AND ib.fine_amount > 0
        ORDER BY ib.return_date DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collected Fines</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Fines Collected</h1>
            </div>

            <!-- Total Card -->
            <div class="stats-grid">
                <div class="stat-card" style="border-left: 5px solid #f1c40f;">
                    <div class="stat-icon" style="background: #f1c40f; color: white;">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-info">
                        <h3>NRS <?= number_format($total_collected, 2) ?></h3>
                        <p>Total Revenue from Fines</p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h3>Fine History</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Returned Date</th>
                                <th>Student</th>
                                <th>Book Title</th>
                                <th>Unique Code</th>
                                <th>Fine Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($row['return_date'])) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                                        <small><?= htmlspecialchars($row['username']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td>
                                        <span class="badge" style="background:#e9ecef; color:#333; font-family:monospace;">
                                            <?= htmlspecialchars($row['unique_code'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color: #e74c3c; font-weight: bold;">
                                            NRS <?= $row['fine_amount'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center" style="padding: 30px; color: #7f8c8d;">
                                        No fines have been collected yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>