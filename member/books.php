<?php
require_once '../config/db.php';
requireMember();

$user_id = $_SESSION['user_id'];

// Query: Active Books (Issued) - LEFT JOIN with book_copies to get unique_code
$sql_active = "SELECT ib.*, b.title, b.author, bc.unique_code 
               FROM issued_books ib 
               JOIN books b ON ib.book_id = b.id 
               LEFT JOIN book_copies bc ON ib.copy_id = bc.id
               WHERE ib.user_id = ? AND ib.status = 'issued' 
               ORDER BY ib.due_date ASC";
$stmt = mysqli_prepare($conn, $sql_active);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res_active = mysqli_stmt_get_result($stmt);

// Query: History
$sql_history = "SELECT ib.*, b.title, b.author, bc.unique_code 
                FROM issued_books ib 
                JOIN books b ON ib.book_id = b.id 
                LEFT JOIN book_copies bc ON ib.copy_id = bc.id
                WHERE ib.user_id = ? AND ib.status = 'returned' 
                ORDER BY ib.return_date DESC LIMIT 10";
$stmt2 = mysqli_prepare($conn, $sql_history);
mysqli_stmt_bind_param($stmt2, "i", $user_id);
mysqli_stmt_execute($stmt2);
$res_history = mysqli_stmt_get_result($stmt2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Books</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <?php include 'member_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1>My Books</h1>
        </div>

        <!-- SECTION 1: ACTIVELY ISSUED BOOKS -->
        <div class="card">
            <h3><i class="fas fa-book-reader"></i> Currently Reading</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Unique Code</th> <!-- Added Column -->
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($res_active) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_active)): 
                            $is_overdue = strtotime($row['due_date']) < time();
                        ?>
                        <tr style="<?= $is_overdue ? 'background-color: #fff5f5;' : '' ?>">
                            <td style="font-weight:600">
                                <?= htmlspecialchars($row['title']) ?>
                                <div style="font-size:12px; color:#666;"><?= htmlspecialchars($row['author']) ?></div>
                            </td>
                            <td>
                                <!-- Unique Code Display -->
                                <span style="background: #e1f5fe; color: #0277bd; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 13px;">
                                    <?= $row['unique_code'] ? htmlspecialchars($row['unique_code']) : 'N/A' ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($row['issue_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($row['due_date'])) ?></td>
                            <td>
                                <?php if ($is_overdue): ?>
                                    <span class="badge badge-danger">Overdue</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No books currently issued.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- History Section (Optional, included for consistency) -->
        <div class="card" style="margin-top: 30px;">
            <h3><i class="fas fa-history"></i> History</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Unique Code</th>
                            <th>Returned On</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res_history)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td style="color: #666; font-family: monospace;"><?= $row['unique_code'] ?></td>
                            <td><?= date('M d, Y', strtotime($row['return_date'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</body>
</html>