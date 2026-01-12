<?php
require_once '../config/db.php';
requireMember();

$user_id = $_SESSION['user_id'];

// Query 1: Active Books (Issued)
$sql_active = "SELECT ib.*, b.title, b.author, b.isbn 
               FROM issued_books ib 
               JOIN books b ON ib.book_id = b.id 
               WHERE ib.user_id = ? AND ib.status = 'issued' 
               ORDER BY ib.due_date ASC";
$stmt = mysqli_prepare($conn, $sql_active);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res_active = mysqli_stmt_get_result($stmt);

// Query 2: Returned History
$sql_history = "SELECT ib.*, b.title, b.author 
                FROM issued_books ib 
                JOIN books b ON ib.book_id = b.id 
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
            <h3 style="border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 20px;">
                <i class="fas fa-book-reader"></i> Currently Reading
            </h3>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($res_active) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_active)): 
                            $due = strtotime($row['due_date']);
                            $today = time();
                            $days_left = ceil(($due - $today) / 86400);
                            $is_overdue = $days_left < 0;
                        ?>
                        <tr style="<?= $is_overdue ? 'background-color: #fff5f5;' : '' ?>">
                            <td style="font-weight:600"><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['author']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['issue_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($row['due_date'])) ?></td>
                            <td>
                                <?php if ($is_overdue): ?>
                                    <span class="badge badge-danger">Overdue by <?= abs($days_left) ?> days</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?= $days_left ?> days left</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">You have no books currently issued.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 2: RETURN HISTORY -->
        <div class="card" style="margin-top: 30px;">
            <h3 style="border-bottom: 2px solid #95a5a6; padding-bottom: 10px; margin-bottom: 20px;">
                <i class="fas fa-history"></i> Return History
            </h3>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($res_history) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_history)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['issue_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($row['return_date'])) ?></td>
                            <td><span class="badge badge-gray">Returned</span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No history available.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<style>
    .badge { padding: 5px 10px; border-radius: 4px; color: white; font-size: 12px; font-weight: bold; }
    .badge-success { background: #2ecc71; }
    .badge-danger { background: #e74c3c; }
    .badge-gray { background: #95a5a6; }
    .text-center { text-align: center; }
</style>

</body>
</html>