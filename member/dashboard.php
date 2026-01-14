<?php
require_once '../config/db.php';
requireMember();

$user_id   = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Student';

// Counts
$issued_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM issued_books WHERE user_id=$user_id AND status='issued'"))['c'];
$overdue_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM issued_books WHERE user_id=$user_id AND status='issued' AND due_date < CURDATE()"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <?php include 'member_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1>Welcome, <?= htmlspecialchars($full_name) ?> 👋</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #3498db;"><i class="fas fa-book-reader"></i></div>
                <div class="stat-info"><h3><?= $issued_count ?></h3><p>Books Issued</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e74c3c;"><i class="fas fa-exclamation-circle"></i></div>
                <div class="stat-info"><h3><?= $overdue_count ?></h3><p>Overdue Books</p></div>
            </div>
        </div>

        <div class="card">
            <h3>Current Readings</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th> <!-- Added Column Header -->
                            <th>Book Title</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // JOIN book_copies to get unique_code
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
                            $is_overdue = strtotime($r['due_date']) < time();
                    ?>
                        <tr>
                            <td>
                                <span style="background: #f1f3f5; padding: 3px 6px; border-radius: 4px; font-family: monospace; font-size: 12px; font-weight: bold;">
                                    <?= $r['unique_code'] ? htmlspecialchars($r['unique_code']) : 'N/A' ?>
                                </span>
                            </td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($r['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($r['due_date'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $is_overdue ? 'danger' : 'success' ?>">
                                    <?= $is_overdue ? 'Overdue' : 'Active' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center">No books issued.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>