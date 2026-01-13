<?php
require_once '../config/db.php';
require_once '../config/validation.php'; // Using your validation class
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Sanitize Inputs
    $title    = Validation::sanitize($_POST['title']);
    $author   = Validation::sanitize($_POST['author']);
    $isbn     = Validation::sanitize($_POST['isbn']);
    $category = Validation::sanitize($_POST['category']);
    $copies   = (int)$_POST['copies'];

    // 2. Server-side Validation
    if (empty($title) || empty($author) || empty($isbn) || $copies < 1) {
        $error = "Please fill all required fields correctly.";
    } else {
        // 3. Check if ISBN already exists
        $check_sql = "SELECT id FROM books WHERE isbn = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $isbn);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "A book with this ISBN already exists in the system.";
        } else {
            // 4. Insert into Database
            // Note: available_copies is set equal to total_copies on initial add
            $insert_sql = "INSERT INTO books (title, author, isbn, category, total_copies, available_copies) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            
            if ($stmt = mysqli_prepare($conn, $insert_sql)) {
                mysqli_stmt_bind_param($stmt, "ssssii", $title, $author, $isbn, $category, $copies, $copies);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Book added successfully!";
                    // Clear post data so form is empty after success
                    $_POST = array(); 
                } else {
                    $error = "Something went wrong. Please try again.";
                }
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_stmt_close($check_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Book - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="main-content">
            <div class="content-header">
                <h1>Add New Book</h1>
                <a href="manage_book.php" class="btn"><i class="fas fa-arrow-left"></i> Back to List</a>
            </div>

            <div class="card">
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title">Book Title *</label>
                        <input type="text" name="title" id="title" class="form-control" 
                               value="<?php echo $_POST['title'] ?? ''; ?>" required>
                    </div>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="author">Author *</label>
                            <input type="text" name="author" id="author" class="form-control" 
                                   value="<?php echo $_POST['author'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="isbn">ISBN Number *</label>
                            <input type="text" name="isbn" id="isbn" class="form-control" 
                                   value="<?php echo $_POST['isbn'] ?? ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control">
                                <option value="Computer Science">Computer Science</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Physics">Physics</option>
                                <option value="Fiction">Fiction</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="copies">Total Copies *</label>
                            <input type="number" name="copies" id="copies" min="1" class="form-control" 
                                   value="<?php echo $_POST['copies'] ?? '1'; ?>" required>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Book to Library
                        </button>
                        <button type="reset" class="btn btn-secondary">Clear Form</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>