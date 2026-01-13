<?php
require_once '../config/db.php';
requireMember();

$user_id   = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Student';

// Issued Books Count
$issued_count = 0;
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM issued_books WHERE user_id=? AND status='issued'");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $issued_count);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Overdue Count
$overdue_count = 0;
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM issued_books WHERE user_id=? AND status='issued' AND due_date < CURDATE()");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $overdue_count);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-grid a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            border: 1px solid var(--primary);
        }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include 'member_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <div>
                <h1>Welcome, <?= htmlspecialchars($full_name) ?> 👋</h1>
                <p style="color: #7f8c8d;"><?= date('l, F j, Y') ?></p>
            </div>
        </div>

        <!-- Clickable Stats Grid -->
        <div class="stats-grid">
            <!-- Books Issued -->
            <a href="issued_books.php">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3498db;">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $issued_count ?></h3>
                        <p>Books Issued</p>
                    </div>
                </div>
            </a>

            <!-- Overdue -->
            <a href="issued_books.php">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e74c3c;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $overdue_count ?></h3>
                        <p>Overdue Books</p>
                    </div>
                </div>
            </a>
            
            <!-- Browse Library -->
            <a href="books.php">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2ecc71;">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="stat-info">
                        <h3 style="font-size: 1.2rem;">Browse</h3>
                        <p>Find New Books</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Current Readings</h3>
                <a href="issued_books.php" class="btn btn-primary btn-sm">View All</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $query = "SELECT b.title, b.author, ib.due_date 
                              FROM issued_books ib 
                              JOIN books b ON ib.book_id = b.id 
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
                            <td style="font-weight: 600;"><?= htmlspecialchars($r['title']) ?></td>
                            <td><?= htmlspecialchars($r['author']) ?></td>
                            <td><?= date('M d, Y', strtotime($r['due_date'])) ?></td>
                            <td>
                                <?php if($is_overdue): ?>
                                    <span class="badge badge-danger" style="background:#e74c3c; color:white; padding:4px 8px; border-radius:4px;">Overdue</span>
                                <?php else: ?>
                                    <span class="badge badge-success" style="background:#2ecc71; color:white; padding:4px 8px; border-radius:4px;">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 20px;">No books issued. <a href="books.php">Browse now</a></td>
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