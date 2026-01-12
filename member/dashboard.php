<?php
require_once '../config/db.php';
requireMember();

$user_id   = $_SESSION['user_id'];
// Defensive coding: Fallback if session name is missing
$full_name = $_SESSION['full_name'] ?? 'Student';

/* 1. Get Issued Books Count */
$issued_count = 0;
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM issued_books WHERE user_id=? AND status='issued'");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $issued_count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

/* 2. Get Overdue Books Count */
$overdue_count = 0;
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM issued_books WHERE user_id=? AND status='issued' AND due_date < CURDATE()");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $overdue_count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Core Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- FIX: Include admin.css because it contains the Sidebar and Stats layout -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-container">

    <!-- Sidebar -->
    <?php include 'member_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <h1>Welcome, <?= htmlspecialchars($full_name) ?> 👋</h1>
            <div class="date-display"><?= date('l, F j, Y') ?></div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #3498db;">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div class="stat-number"><?= $issued_count ?></div>
                <div class="stat-label">Books Issued</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #e74c3c;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-number"><?= $overdue_count ?></div>
                <div class="stat-label">Overdue Books</div>
            </div>
            
             <div class="stat-card">
                 <!-- Link to browse books -->
                <a href="books.php" style="text-decoration:none; color:inherit; display:block;">
                    <div class="stat-icon" style="background: #2ecc71;">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="stat-label" style="font-weight:bold; margin-top:10px;">Browse Library</div>
                </a>
            </div>
        </div>

        <!-- Recent Issued Books Table -->
        <div class="card">
            <div class="content-header" style="border:none; margin-bottom:10px; padding-bottom:0;">
                <h3>My Issued Books</h3>
                <a href="issued_books.php" class="btn btn-primary btn-sm">View All</a>
            </div>

            <?php
            $query = "SELECT b.title, b.author, ib.issue_date, ib.due_date 
                      FROM issued_books ib 
                      JOIN books b ON ib.book_id = b.id 
                      WHERE ib.user_id = ? AND ib.status = 'issued' 
                      ORDER BY ib.due_date ASC LIMIT 5";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            ?>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Author</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($r = mysqli_fetch_assoc($result)): 
                            $is_overdue = strtotime($r['due_date']) < time();
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($r['title']) ?></td>
                            <td><?= htmlspecialchars($r['author']) ?></td>
                            <td><?= date('M d, Y', strtotime($r['issue_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($r['due_date'])) ?></td>
                            <td>
                                <?php if($is_overdue): ?>
                                    <span style="color:white; background:#e74c3c; padding:4px 8px; border-radius:4px; font-size:12px;">Overdue</span>
                                <?php else: ?>
                                    <span style="color:white; background:#2ecc71; padding:4px 8px; border-radius:4px; font-size:12px;">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 20px;">
                                No books currently issued. <a href="books.php" style="color: #3498db;">Browse Books</a>
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