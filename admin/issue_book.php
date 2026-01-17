<?php
require_once '../config/db.php';
require_once '../config/validation.php';
requireAdmin();

$error = '';
$success = '';

// 1. Fetch Students for Dropdown
$members = mysqli_query($conn, "SELECT id, full_name, username FROM users WHERE role='member' ORDER BY full_name ASC");

// 2. Fetch Available Copies
$sql_copies = "SELECT bc.id as copy_id, bc.unique_code, b.title 
               FROM book_copies bc
               JOIN books b ON bc.book_id = b.id
               WHERE bc.status = 'available'
               ORDER BY b.title ASC";
$copies_result = mysqli_query($conn, $sql_copies);

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $copy_id = (int)$_POST['copy_id']; 
    $user_id = (int)$_POST['user_id'];
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];

    if($copy_id && $user_id && $issue_date && $due_date) {
        
        // STEP 1: Get the Main Book ID from the selected Copy ID
        // We need to know which "Title" this copy belongs to (e.g., Harry Potter)
        $check_stmt = mysqli_prepare($conn, "SELECT book_id FROM book_copies WHERE id = ? AND status = 'available'");
        mysqli_stmt_bind_param($check_stmt, "i", $copy_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if($row = mysqli_fetch_assoc($check_result)) {
            $book_id = (int)$row['book_id'];
            mysqli_stmt_close($check_stmt); // Close previous statement to avoid sync errors

            // STEP 2: DUPLICATE CHECK
            // Check if this user ALREADY has a book with this 'book_id' that is 'issued'
            $dup_sql = "SELECT COUNT(*) FROM issued_books WHERE user_id = ? AND book_id = ? AND status = 'issued'";
            $dup_stmt = mysqli_prepare($conn, $dup_sql);
            mysqli_stmt_bind_param($dup_stmt, "ii", $user_id, $book_id);
            mysqli_stmt_execute($dup_stmt);
            mysqli_stmt_bind_result($dup_stmt, $count);
            mysqli_stmt_fetch($dup_stmt);
            mysqli_stmt_close($dup_stmt);

            // DEBUGGING: Uncomment the line below if it still fails to see what's happening
            // die("Checking User: $user_id, Book: $book_id. Found active copies: $count");

            if ($count > 0) {
                // ERROR: Stop the process
                $error = "OPERATION FAILED: This student already has an issued copy of this book title. They must return it before borrowing another copy.";
            } else {
                // STEP 3: Proceed to Issue (Success Path)
                mysqli_begin_transaction($conn);
                try {
                    // A. Create Issue Record
                    $stmt = mysqli_prepare($conn, "INSERT INTO issued_books (book_id, copy_id, user_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, ?, 'issued')");
                    mysqli_stmt_bind_param($stmt, "iiiss", $book_id, $copy_id, $user_id, $issue_date, $due_date);
                    mysqli_stmt_execute($stmt);

                    // B. Mark specific copy as 'issued'
                    $update_copy = mysqli_prepare($conn, "UPDATE book_copies SET status = 'issued' WHERE id = ?");
                    mysqli_stmt_bind_param($update_copy, "i", $copy_id);
                    mysqli_stmt_execute($update_copy);

                    // C. Decrease general stock count
                    $update_stock = mysqli_prepare($conn, "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
                    mysqli_stmt_bind_param($update_stock, "i", $book_id);
                    mysqli_stmt_execute($update_stock);

                    mysqli_commit($conn);
                    $success = "Book issued successfully!";
                    
                    // Refresh dropdown list
                    $copies_result = mysqli_query($conn, $sql_copies);
                    
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error = "Database Error: " . $e->getMessage();
                }
            }
        } else {
            $error = "This specific book copy is no longer available (it might have just been taken).";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Issue Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class="admin-container">
    <?php include '../includes/admin_sidebar.php'; ?>
    <div class="main-content">
        <h1>Issue Book Copy</h1>
        <?php if($error): ?>
            <div class="alert alert-error" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong><i class="fas fa-exclamation-triangle"></i> Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="card">
            <div class="form-group">
                <label>Select Book Copy</label>
                <select name="copy_id" class="form-control" id="bookSelect" required>
                    <option value="">Select Book Copy</option>
                    <?php 
                    if(mysqli_num_rows($copies_result) > 0) {
                        while($row = mysqli_fetch_assoc($copies_result)): 
                    ?>
                        <option value="<?=$row['copy_id']?>">
                            [<?=$row['unique_code']?>] - <?=$row['title']?>
                        </option>
                    <?php 
                        endwhile; 
                    } else {
                        echo "<option disabled>No available book copies found</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Student</label>
                <select name="user_id" class="form-control" id="studentSelect" required>
                    <option value="">Select Student</option>
                    <?php 
                    mysqli_data_seek($members, 0); 
                    while($row = mysqli_fetch_assoc($members)): ?>
                        <option value="<?=$row['id']?>"><?=$row['full_name']?> (<?=$row['username']?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Issue Date</label>
                    <input type="date" name="issue_date" value="<?=date('Y-m-d')?>" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" name="due_date" value="<?=date('Y-m-d', strtotime('+15 days'))?>" class="form-control" required>
                </div>
            </div>
            <button class="btn btn-primary">Issue Book</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#bookSelect').select2({ placeholder: "Search for a book..." });
        $('#studentSelect').select2({ placeholder: "Search for a student..." });
    });
</script>
</body>
</html>