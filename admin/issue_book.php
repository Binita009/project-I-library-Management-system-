<?php
// admin/issue_book.php
require_once '../config/db.php';
require_once '../config/validation.php';
requireAdmin();

$error = '';
$success = '';

// Fetch Books & Members for Dropdowns
$books = mysqli_query($conn, "SELECT id, title FROM books WHERE available_copies > 0");
$members = mysqli_query($conn, "SELECT id, full_name, username FROM users WHERE role='member'");

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_id = $_POST['book_id'];
    $user_id = $_POST['user_id'];
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];

    if($book_id && $user_id && $issue_date && $due_date) {
        // Double check availability
        $check = mysqli_query($conn, "SELECT available_copies FROM books WHERE id=$book_id");
        $avail = mysqli_fetch_assoc($check)['available_copies'];

        if($avail > 0) {
            mysqli_begin_transaction($conn);
            try {
                // Issue Book
                $stmt = mysqli_prepare($conn, "INSERT INTO issued_books (book_id, user_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'issued')");
                mysqli_stmt_bind_param($stmt, "iiss", $book_id, $user_id, $issue_date, $due_date);
                mysqli_stmt_execute($stmt);

                // Decrease Inventory
                mysqli_query($conn, "UPDATE books SET available_copies = available_copies - 1 WHERE id=$book_id");

                mysqli_commit($conn);
                $success = "Book issued successfully.";
                // Refresh dropdown
                $books = mysqli_query($conn, "SELECT id, title FROM books WHERE available_copies > 0");
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Error: " . $e->getMessage();
            }
        } else {
            $error = "Book is out of stock.";
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
</head>
<body>
<div class="admin-container">
    <?php include '../includes/admin_sidebar.php'; ?>
    <div class="main-content">
        <h1>Issue Book</h1>
        <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>
        <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        
        <form method="POST" class="card">
            <div class="form-group">
                <label>Book</label>
                <select name="book_id" class="form-control" required>
                    <option value="">Select Book</option>
                    <?php while($row = mysqli_fetch_assoc($books)): ?>
                        <option value="<?=$row['id']?>"><?=$row['title']?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Student</label>
                <select name="user_id" class="form-control" required>
                    <option value="">Select Student</option>
                    <?php while($row = mysqli_fetch_assoc($members)): ?>
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
</body>
</html>