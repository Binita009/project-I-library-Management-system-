<?php
require_once '../config/db.php';
requireAdmin();

$error = '';
$success = '';

// Handle the Return Logic
if(isset($_GET['return_id'])) {
    $return_id = (int)$_GET['return_id'];
    
    // Start Transaction
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Get Details (Book, Copy, Due Date)
        $get_sql = "SELECT book_id, copy_id, due_date FROM issued_books WHERE id = ? AND status = 'issued'";
        $get_stmt = mysqli_prepare($conn, $get_sql);
        mysqli_stmt_bind_param($get_stmt, "i", $return_id);
        mysqli_stmt_execute($get_stmt);
        $result_vars = mysqli_stmt_get_result($get_stmt);
        $issue_data = mysqli_fetch_assoc($result_vars);
        mysqli_stmt_close($get_stmt);
        
        if($issue_data) {
            $book_id = $issue_data['book_id'];
            $copy_id = $issue_data['copy_id'];
            
            // --- FINE CALCULATION START ---
            $due_date = new DateTime($issue_data['due_date']);
            $today = new DateTime(); // Current date/time
            $fine_amount = 0;

            // Only calculate if Today is AFTER Due Date
            if ($today > $due_date) {
                $diff = $today->diff($due_date);
                $days_overdue = $diff->days;
                $fine_amount = $days_overdue * 2; // NRS 2 per day
            }
            // --- FINE CALCULATION END ---

            // 2. Update issued_books (Status + Return Date + Fine)
            $update_issue = "UPDATE issued_books SET status = 'returned', return_date = CURDATE(), fine_amount = ? WHERE id = ?";
            $stmt1 = mysqli_prepare($conn, $update_issue);
            // 'd' for double/decimal, 'i' for integer
            mysqli_stmt_bind_param($stmt1, "di", $fine_amount, $return_id);
            mysqli_stmt_execute($stmt1);
            mysqli_stmt_close($stmt1);
            
            // 3. Update specific BOOK COPY status
            if($copy_id) {
                $update_copy = "UPDATE book_copies SET status = 'available' WHERE id = ?";
                $stmt2 = mysqli_prepare($conn, $update_copy);
                mysqli_stmt_bind_param($stmt2, "i", $copy_id);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
            }

            // 4. Update general book inventory
            $update_book = "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?";
            $stmt3 = mysqli_prepare($conn, $update_book);
            mysqli_stmt_bind_param($stmt3, "i", $book_id);
            mysqli_stmt_execute($stmt3);
            mysqli_stmt_close($stmt3);
            
            mysqli_commit($conn);
            
            $msg = "Book returned successfully!";
            if($fine_amount > 0) {
                $msg .= " <strong>Fine Collected: NRS " . $fine_amount . "</strong>";
            }
            $success = $msg;

        } else {
            throw new Exception("Invalid record.");
        }
    } catch(Exception $e) {
        mysqli_rollback($conn);
        $error = "Error returning book: " . $e->getMessage();
    }
}

// Fetch Currently Issued Books
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
    <meta charset="UTF-8">
    <title>Return Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Return Book</h1>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <h3>Currently Issued Books</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Book Details</th>
                                <th>Due Date</th>
                                <th>Fine (Pending)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): 
                                    // Calculate Pending Fine for Display
                                    $due_date = new DateTime($row['due_date']);
                                    $today = new DateTime();
                                    $is_overdue = $today > $due_date;
                                    $pending_fine = 0;
                                    $days_late = 0;

                                    if($is_overdue) {
                                        $diff = $today->diff($due_date);
                                        $days_late = $diff->days;
                                        $pending_fine = $days_late * 2;
                                    }
                                ?>
                                <tr style="<?= $is_overdue ? 'background-color: #fff5f5;' : '' ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                                        <small><?= htmlspecialchars($row['username']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['title']) ?><br>
                                        <small class="badge" style="background:#e9ecef; color:#333; font-family:monospace;"><?= $row['unique_code'] ?></small>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($row['due_date'])) ?><br>
                                        <?php if($is_overdue): ?>
                                            <span style="color:red; font-size:12px; font-weight:bold;">(<?= $days_late ?> days late)</span>
                                        <?php else: ?>
                                            <span style="color:green; font-size:12px;">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($pending_fine > 0): ?>
                                            <span class="badge badge-danger" style="font-size:14px;">NRS <?= $pending_fine ?></span>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?return_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"
                                           onclick="return confirm('Please collect fine of NRS <?= $pending_fine ?> (if any) before returning.\n\nConfirm Return?')">
                                           Return Book
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No issued books found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>