<?php
require_once '../config/db.php';
requireAdmin();

// Fetch all books currently issued to students
$sql = "SELECT ib.*, b.title, u.full_name, u.username, bc.unique_code 
        FROM issued_books ib 
        JOIN books b ON ib.book_id = b.id 
        JOIN users u ON ib.user_id = u.id 
        LEFT JOIN book_copies bc ON ib.copy_id = bc.id
        WHERE ib.status = 'issued' 
        ORDER BY ib.due_date ASC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Active Issues</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Active Issues</h1>
                <p style="color: #7f8c8d; margin-top: 5px;">See which student is currently holding which book.</p>
            </div>
            
            <div class="card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student Info</th>
                                <th>Book Details</th>
                                <th>Issued On</th>
                                <th>Due Date</th>
                                <th>Status / Fine</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): 
                                    $due_date = new DateTime($row['due_date']);
                                    $today = new DateTime();
                                    $is_overdue = $today > $due_date;
                                    $pending_fine = $is_overdue ? ($today->diff($due_date)->days * 2) : 0;
                                ?>
                                <tr style="<?= $is_overdue ? 'background-color: #fff5f5;' : '' ?>">
                                    <td>
                                        <strong><i class="fas fa-user" style="color:#7f8c8d;"></i> <?= htmlspecialchars($row['full_name']) ?></strong><br>
                                        <small style="color: #3498db;">@<?= htmlspecialchars($row['username']) ?></small>
                                    </td>
                                    <td>
                                        <strong style="color: #2c3e50;"><?= htmlspecialchars($row['title']) ?></strong><br>
                                        <small class="badge" style="background:#e9ecef; color:#333; font-family:monospace; margin-top:4px; display:inline-block;">Code: <?= $row['unique_code'] ?></small>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($row['issue_date'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['due_date'])) ?></td>
                                    <td>
                                        <?php if($is_overdue): ?>
                                            <span class="badge badge-danger" style="margin-bottom: 5px; display:inline-block;">Overdue</span><br>
                                            <small style="color: #e74c3c; font-weight: bold;">Fine: NRS. <?= $pending_fine ?></small>
                                        <?php else: ?>
                                            <span class="badge badge-success">Safe</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #7f8c8d;">
                                        <i class="fas fa-book-open" style="font-size: 24px; margin-bottom: 10px; display:block;"></i>
                                        No books are currently issued to any student.
                                    </td>
                                </tr>
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