<?php
require_once '../config/db.php';
requireMember();

$user_id   = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Student';

// Initialize Counters
$issued_count = 0;
$overdue_count = 0;
$total_pending_fine = 0;

// Calculate stats
$sql_stats = "SELECT due_date FROM issued_books WHERE user_id = $user_id AND status = 'issued'";
$result_stats = mysqli_query($conn, $sql_stats);

while($row = mysqli_fetch_assoc($result_stats)) {
    $issued_count++;
    $due_date = new DateTime($row['due_date']);
    $due_date->setTime(0, 0, 0);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if($today > $due_date) {
        $overdue_count++;
        $days_late = $today->diff($due_date)->days;
        $total_pending_fine += ($days_late * 2); // NRS 2 per day
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-grid a { text-decoration: none; color: inherit; display: block; }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); border: 1px solid #ddd; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'member_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1>Welcome, <?= htmlspecialchars($full_name) ?> 👋</h1>
        </div>

        <div class="stats-grid">
            <a href="issued_books.php">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3498db;"><i class="fas fa-book-reader"></i></div>
                    <div class="stat-info">
                        <h3><?= $issued_count ?></h3>
                        <p>Books Issued</p>
                    </div>
                </div>
            </a>
            
            <a href="issued_books.php">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e74c3c;"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="stat-info">
                        <h3><?= $overdue_count ?></h3>
                        <p>Overdue Books</p>
                    </div>
                </div>
            </a>

            <a href="issued_books.php">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f1c40f; color: #fff;">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-info">
                        <h3><small style="font-size: 14px;">NRS.</small> <?= number_format($total_pending_fine, 2) ?></h3>
                        <p>Pending Fine</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="card">
            <h3>Current Readings</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Book Title</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $query = "SELECT b.title, ib.due_date, bc.unique_code 
                              FROM issued_books ib 
                              JOIN books b ON ib.book_id = b.id 
                              LEFT JOIN book_copies bc ON ib.copy_id = bc.id
                              WHERE ib.user_id = ? AND ib.status = 'issued' 
                              ORDER BY ib.due_date ASC LIMIT 5";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);

                    if (mysqli_num_rows($result) > 0):
                        while ($r = mysqli_fetch_assoc($result)): 
                            $due = new DateTime($r['due_date']);
                            $due->setTime(0,0,0);
                            $now = new DateTime();
                            $now->setTime(0,0,0);
                            $is_overdue = $now > $due;
                            $fine = $is_overdue ? ($now->diff($due)->days * 2) : 0;
                    ?>
                        <tr>
                            <td><span style="background: #f1f3f5; padding: 3px 6px; border-radius: 4px; font-family: monospace; font-size: 12px;"><?= $r['unique_code'] ?? 'N/A' ?></span></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($r['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($r['due_date'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $is_overdue ? 'danger' : 'success' ?>">
                                    <?= $is_overdue ? 'Overdue' : 'Active' ?>
                                </span>
                            </td>
                            <td>
                                <?php if($fine > 0): ?>
                                    <span style="color: #e74c3c; font-weight: bold;"><small>NRS.</small> <?= $fine ?></span>
                                <?php else: ?>
                                    <span style="color: #2ecc71;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center">No books issued.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>