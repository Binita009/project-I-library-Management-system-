<?php
require_once '../config/db.php';
require_once '../config/validation.php';
requireAdmin();

$error = '';
$success = '';

// 1. Fetch Students for Dropdown
$members = mysqli_query($conn, "SELECT id, full_name, username FROM users WHERE role='member' ORDER BY full_name ASC");

// 2. Fetch Available Copies (The Critical Part)
// We join 'book_copies' with 'books' to get the Title AND the Unique Code
$sql_copies = "SELECT bc.id as copy_id, bc.unique_code, b.title 
               FROM book_copies bc
               JOIN books b ON bc.book_id = b.id
               WHERE bc.status = 'available'
               ORDER BY b.title ASC";
$copies_result = mysqli_query($conn, $sql_copies);

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $copy_id = $_POST['copy_id']; // This is the ID from book_copies table
    $user_id = $_POST['user_id'];
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];

    if($copy_id && $user_id && $issue_date && $due_date) {
        
        // Check availability
        $check = mysqli_query($conn, "SELECT book_id FROM book_copies WHERE id='$copy_id' AND status='available'");
        
        if(mysqli_num_rows($check) > 0) {
            $row = mysqli_fetch_assoc($check);
            $book_id = $row['book_id'];

            mysqli_begin_transaction($conn);
            try {
                // Insert Record with Copy ID
                $stmt = mysqli_prepare($conn, "INSERT INTO issued_books (book_id, copy_id, user_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, ?, 'issued')");
                mysqli_stmt_bind_param($stmt, "iiiss", $book_id, $copy_id, $user_id, $issue_date, $due_date);
                mysqli_stmt_execute($stmt);

                // Update Copy Status
                mysqli_query($conn, "UPDATE book_copies SET status = 'issued' WHERE id='$copy_id'");

                // Update General Stock Count
                mysqli_query($conn, "UPDATE books SET available_copies = available_copies - 1 WHERE id='$book_id'");

                mysqli_commit($conn);
                $success = "Book copy issued successfully.";
                
                // Refresh list
                $copies_result = mysqli_query($conn, $sql_copies);
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Error: " . $e->getMessage();
            }
        } else {
            $error = "This specific book copy is no longer available.";
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
    <!-- Searchable Dropdown Style -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class="admin-container">
    <?php include '../includes/admin_sidebar.php'; ?>
    <div class="main-content">
        <h1>Issue Book Copy</h1>
        <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>
        <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        
        <form method="POST" class="card">
            <div class="form-group">
                <label>Select Book Copy (Search by Code or Title)</label>
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

<!-- Scripts for Searchable Dropdown -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#bookSelect').select2({
            placeholder: "Search for a book..."
        });
        $('#studentSelect').select2({
            placeholder: "Search for a student..."
        });
    });
</script>
</body>
</html>