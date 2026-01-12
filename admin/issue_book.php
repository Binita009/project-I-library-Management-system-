<?php
require_once '../config/db.php';
requireAdmin();

$error = '';
$success = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_id = Validation::sanitize($_POST['book_id']);
    $user_id = Validation::sanitize($_POST['user_id']);
    $issue_date = Validation::sanitize($_POST['issue_date']);
    $due_date = Validation::sanitize($_POST['due_date']);
    
    // Validate dates
    if(!Validation::validate('Issue Date', $issue_date, 'date')['success'] || 
       !Validation::validate('Due Date', $due_date, 'date')['success']) {
        $error = "Invalid date format";
    } else {
        // Check if book is available
        $check_sql = "SELECT available_copies FROM books WHERE id = ? AND available_copies > 0";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $book_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_bind_result($check_stmt, $available_copies);
        mysqli_stmt_fetch($check_stmt);
        mysqli_stmt_close($check_stmt);
        
        if(!$available_copies) {
            $error = "Book is not available";
        } else {
            // Start transaction
            mysqli_begin_transaction($conn);
            
            try {
                // Insert issue record
                $issue_sql = "INSERT INTO issued_books (book_id, user_id, issue_date, due_date, status) 
                              VALUES (?, ?, ?, ?, 'issued')";
                $issue_stmt = mysqli_prepare($conn, $issue_sql);
                mysqli_stmt_bind_param($issue_stmt, "iiss", $book_id, $user_id, $issue_date, $due_date);
                mysqli_stmt_execute($issue_stmt);
                
                // Update book available copies
                $update_sql = "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "i", $book_id);
                mysqli_stmt_execute($update_stmt);
                
                mysqli_commit($conn);
                $success = "Book issued successfully!";
            } catch(Exception $e) {
                mysqli_rollback($conn);
                $error = "Error issuing book: " . $e->getMessage();
            }
        }
    }
}

// Fetch available books
$books_sql = "SELECT id, title, author FROM books WHERE available_copies > 0 ORDER BY title";
$books_result = mysqli_query($conn, $books_sql);

// Fetch members
$members_sql = "SELECT id, username, full_name FROM users WHERE role = 'member' ORDER BY full_name";
$members_result = mysqli_query($conn, $members_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Book - Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Issue Book</h1>
                <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>
            </div>
            
            <div class="card">
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" onsubmit="return validateIssueForm()">
                    <div class="form-group">
                        <label for="book_id">Select Book *</label>
                        <select id="book_id" name="book_id" class="form-control" required>
                            <option value="">-- Select Book --</option>
                            <?php while($book = mysqli_fetch_assoc($books_result)): ?>
                                <option value="<?php echo $book['id']; ?>">
                                    <?php echo htmlspecialchars($book['title']); ?> by <?php echo htmlspecialchars($book['author']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_id">Select Member *</label>
                        <select id="user_id" name="user_id" class="form-control" required>
                            <option value="">-- Select Member --</option>
                            <?php while($member = mysqli_fetch_assoc($members_result)): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['full_name']); ?> (<?php echo htmlspecialchars($member['username']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="issue_date">Issue Date *</label>
                            <input type="date" id="issue_date" name="issue_date" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="due_date">Due Date *</label>
                            <input type="date" id="due_date" name="due_date" class="form-control" 
                                   value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Issue Book</button>
                    <a href="admin_dashboard.php" class="btn">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>