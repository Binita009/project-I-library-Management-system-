<?php
require_once '../config/db.php';
requireAdmin();

$error = '';
$success = '';

if(isset($_GET['return_id'])) {
    $return_id = $_GET['return_id'];
    
    mysqli_begin_transaction($conn);
    
    try {
        // Get book ID from issued record
        $get_sql = "SELECT book_id FROM issued_books WHERE id = ? AND status = 'issued'";
        $get_stmt = mysqli_prepare($conn, $get_sql);
        mysqli_stmt_bind_param($get_stmt, "i", $return_id);
        mysqli_stmt_execute($get_stmt);
        mysqli_stmt_bind_result($get_stmt, $book_id);
        mysqli_stmt_fetch($get_stmt);
        mysqli_stmt_close($get_stmt);
        
        if($book_id) {
            // Update issued book status
            $update_sql = "UPDATE issued_books SET status = 'returned', return_date = CURDATE() WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $return_id);
            mysqli_stmt_execute($update_stmt);
            
            // Update book available copies
            $book_sql = "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?";
            $book_stmt = mysqli_prepare($conn, $book_sql);
            mysqli_stmt_bind_param($book_stmt, "i", $book_id);
            mysqli_stmt_execute($book_stmt);
            
            mysqli_commit($conn);
            $success = "Book returned successfully!";
        } else {
            $error = "Invalid book issue record";
        }
    } catch(Exception $e) {
        mysqli_rollback($conn);
        $error = "Error returning book: " . $e->getMessage();
    }
}

// Fetch issued books
$sql = "SELECT ib.*, b.title, b.author, u.full_name, u.username 
        FROM issued_books ib 
        JOIN books b ON ib.book_id = b.id 
        JOIN users u ON ib.user_id = u.id 
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
                                <th>Book</th>
                                <th>Member</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Days Left</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): 
                                    $due_date = new DateTime($row['due_date']);
                                    $today = new DateTime();
                                    $interval = $today->diff($due_date);
                                    $days_left = $interval->days;
                                    $is_overdue = $due_date < $today;
                                ?>
                                <tr class="<?php echo $is_overdue ? 'overdue' : ''; ?>">
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['issue_date'])); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['due_date'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $is_overdue ? 'badge-danger' : ($days_left <= 3 ? 'badge-warning' : 'badge-success'); ?>">
                                            <?php echo $is_overdue ? 'Overdue by ' . $days_left . ' days' : $days_left . ' days'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?return_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"
                                           onclick="return confirm('Mark this book as returned?')">Return</a>
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
            background-color: #ffe6e6;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
    </style>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>