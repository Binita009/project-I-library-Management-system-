<?php
require_once '../config/db.php';
requireAdmin();

$error = '';
$success = '';

// Handle the Return Logic
if(isset($_GET['return_id'])) {
    $return_id = (int)$_GET['return_id'];
    
    // Start Transaction to ensure data integrity
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Get book_id and copy_id from the issued record
        $get_sql = "SELECT book_id, copy_id FROM issued_books WHERE id = ? AND status = 'issued'";
        $get_stmt = mysqli_prepare($conn, $get_sql);
        mysqli_stmt_bind_param($get_stmt, "i", $return_id);
        mysqli_stmt_execute($get_stmt);
        $result_vars = mysqli_stmt_get_result($get_stmt);
        $issue_data = mysqli_fetch_assoc($result_vars);
        mysqli_stmt_close($get_stmt);
        
        if($issue_data) {
            $book_id = $issue_data['book_id'];
            $copy_id = $issue_data['copy_id'];

            // 2. Update issued_books status to 'returned' and set return date
            // Note: You can add fine calculation logic here if needed later
            $update_issue = "UPDATE issued_books SET status = 'returned', return_date = CURDATE() WHERE id = ?";
            $stmt1 = mysqli_prepare($conn, $update_issue);
            mysqli_stmt_bind_param($stmt1, "i", $return_id);
            mysqli_stmt_execute($stmt1);
            mysqli_stmt_close($stmt1);
            
            // 3. Update the specific BOOK COPY status back to 'available'
            if($copy_id) {
                $update_copy = "UPDATE book_copies SET status = 'available' WHERE id = ?";
                $stmt2 = mysqli_prepare($conn, $update_copy);
                mysqli_stmt_bind_param($stmt2, "i", $copy_id);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
            }

            // 4. Update the general book inventory count (increment available copies)
            $update_book = "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?";
            $stmt3 = mysqli_prepare($conn, $update_book);
            mysqli_stmt_bind_param($stmt3, "i", $book_id);
            mysqli_stmt_execute($stmt3);
            mysqli_stmt_close($stmt3);
            
            // Commit all changes
            mysqli_commit($conn);
            $success = "Book returned successfully!";
        } else {
            throw new Exception("Invalid or already returned book issue record.");
        }
    } catch(Exception $e) {
        mysqli_rollback($conn);
        $error = "Error returning book: " . $e->getMessage();
    }
}

// Fetch Currently Issued Books to Display
// We Join 'book_copies' to get the unique code
$sql = "SELECT ib.*, b.title, b.author, u.full_name, u.username, bc.unique_code 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book - Library System</title>
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
                <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>
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
                                <th>Issue ID</th>
                                <th>Book Details</th>
                                <th>Member</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): 
                                    $due_date = new DateTime($row['due_date']);
                                    $today = new DateTime();
                                    $interval = $today->diff($due_date);
                                    // %r returns - if negative (overdue)
                                    $days_diff = (int)$interval->format('%r%a');
                                    $is_overdue = $days_diff < 0;
                                ?>
                                <tr class="<?php echo $is_overdue ? 'overdue' : ''; ?>">
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($row['title']); ?></div>
                                        <div style="font-size: 12px; color: #555;">
                                            Code: <span style="background: #e9ecef; padding: 2px 5px; border-radius: 3px; font-family: monospace;">
                                                <?php echo $row['unique_code'] ? htmlspecialchars($row['unique_code']) : 'N/A'; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($row['full_name']); ?></div>
                                        <small style="color: #777;">(<?php echo htmlspecialchars($row['username']); ?>)</small>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['issue_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['due_date'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $is_overdue ? 'badge-danger' : ($days_diff <= 3 ? 'badge-warning' : 'badge-success'); ?>">
                                            <?php echo $is_overdue ? 'Overdue (' . abs($days_diff) . ' days)' : $days_diff . ' days left'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?return_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"
                                           onclick="return confirm('Confirm Return?\n\nBook: <?php echo addslashes($row['title']); ?>\nCode: <?php echo $row['unique_code']; ?>')">
                                           <i class="fas fa-undo"></i> Return
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No issued books found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .overdue {
            background-color: #fff5f5;
        }
        .overdue td {
            color: #c0392b;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        /* Specific tweaks for admin table */
        .table td { vertical-align: middle; }
    </style>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>