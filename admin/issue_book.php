<?php
require_once '../config/db.php';
requireAdmin();

// 1. Fetch Students
$members_res = mysqli_query($conn, "SELECT id, full_name, username FROM users WHERE role='member' ORDER BY full_name ASC");

// 2. Fetch Available Books
$books_res = mysqli_query($conn, "SELECT bc.id, bc.unique_code, b.title FROM book_copies bc JOIN books b ON bc.book_id = b.id WHERE bc.status='available' ORDER BY b.title ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $copy_id = (int)$_POST['copy_id'];
    $user_id = (int)$_POST['user_id'];
    $due_date = $_POST['due_date'];

    if ($copy_id && $user_id) {
        mysqli_begin_transaction($conn);
        try {
            $book_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT book_id FROM book_copies WHERE id=$copy_id AND status='available'"));
            if ($book_check) {
                $book_id = $book_check['book_id'];
                mysqli_query($conn, "INSERT INTO issued_books (book_id, copy_id, user_id, issue_date, due_date, status) VALUES ($book_id, $copy_id, $user_id, CURDATE(), '$due_date', 'issued')");
                mysqli_query($conn, "UPDATE book_copies SET status='issued' WHERE id=$copy_id");
                mysqli_query($conn, "UPDATE books SET available_copies=available_copies-1 WHERE id=$book_id");
                mysqli_commit($conn);
                setAlert('success', 'Success', 'Book issued successfully!');
                header("Location: issue_book.php");
                exit;
            } else {
                setAlert('error', 'Error', 'Book copy not available.');
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            setAlert('error', 'Error', 'Database error.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Issue Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css"> <!-- Add this line -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- 1. Include Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- 2. Main Content Area -->
        <div class="main-content">
            <div class="content-header">
                <h1>Issue Book</h1>
            </div>

            <div class="card">
                <?php if(mysqli_num_rows($books_res) == 0): ?>
                    <div style="padding:15px; background:#fff3cd; color:#856404; margin-bottom:15px; border-radius:4px;">
                        Warning: No available books found. <a href="manage_book.php">Add Books</a> or Edit Books to generate copies.
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label>Select Book Copy</label>
                        <select name="copy_id" class="form-control" required>
                            <option value="">-- Choose Book --</option>
                            <?php 
                            if(mysqli_num_rows($books_res) > 0) {
                                while($row = mysqli_fetch_assoc($books_res)) {
                                    echo '<option value="'.$row['id'].'">['.$row['unique_code'].'] - '.$row['title'].'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Select Student</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">-- Choose Student --</option>
                            <?php 
                            if(mysqli_num_rows($members_res) > 0) {
                                while($row = mysqli_fetch_assoc($members_res)) {
                                    echo '<option value="'.$row['id'].'">'.$row['full_name'].' ('.$row['username'].')</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+15 days')) ?>" required>
                    </div>

                    <button class="btn btn-primary">Issue Book</button>
                </form>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>