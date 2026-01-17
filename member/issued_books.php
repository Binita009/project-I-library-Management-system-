<?php
require_once '../config/db.php';
requireMember();

$user_id = $_SESSION['user_id'];

// 1. Currently Issued
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

// 2. History
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

        <!-- Currently Reading -->
        <div class="card">
            <h3>Currently Reading</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Code</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Est. Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($res_active) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_active)): 
                            $due_date = new DateTime($row['due_date']);
                            $today = new DateTime();
                            $is_overdue = $today > $due_date;
                            $est_fine = 0;
                            if($is_overdue) {
                                $days = $today->diff($due_date)->days;
                                $est_fine = $days * 2;
                            }
                        ?>
                        <tr style="<?= $is_overdue ? 'background-color: #fff5f5;' : '' ?>">
                            <td style="font-weight:600"><?= htmlspecialchars($row['title']) ?></td>
                            <td><span style="background: #e1f5fe; padding: 2px 6px; border-radius: 4px; font-family: monospace;"><?= htmlspecialchars($row['unique_code'] ?? 'N/A') ?></span></td>
                            <td><?= date('M d, Y', strtotime($row['due_date'])) ?></td>
                            <td>
                                <?php if ($is_overdue): ?>
                                    <span style="color: red; font-weight: bold;">Overdue</span>
                                <?php else: ?>
                                    <span style="color: green;">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($est_fine > 0): ?>
                                    <span style="color: red; font-weight: bold;">NRS <?= $est_fine ?></span>
                                <?php else: ?>
                                    -
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
        
        <!-- History -->
        <div class="card" style="margin-top: 30px;">
            <h3>History</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Returned On</th>
                            <th>Fine Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($res_history)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['return_date'])) ?></td>
                            <td>
                                <?php if ($row['fine_amount'] > 0): ?>
                                    <span class="badge badge-danger">NRS <?= $row['fine_amount'] ?></span>
                                <?php else: ?>
                                    <span style="color: #999;">0</span>
                                <?php endif; ?>
                            </td>
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