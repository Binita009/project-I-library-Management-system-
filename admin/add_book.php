<?php
require_once '../config/db.php';
requireAdmin();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title    = trim($_POST['title']);
    $author   = trim($_POST['author']);
    $isbn     = trim($_POST['isbn']);
    $category = $_POST['category'];
    $copies   = (int)$_POST['copies'];

    if ($title && $author && $isbn && $copies > 0) {

        // Check ISBN
        $check = mysqli_prepare($conn, "SELECT id FROM books WHERE isbn=?");
        mysqli_stmt_bind_param($check, "s", $isbn);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "ISBN already exists";
        } else {
            // Insert book
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO books (title, author, isbn, category, total_copies, available_copies)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "ssssii",
                $title, $author, $isbn, $category, $copies, $copies
            );

            if (mysqli_stmt_execute($stmt)) {
                $success = "Book added successfully";
                $_POST = [];
            } else {
                $error = "Failed to add book";
            }
        }
    } else {
        $error = "Please fill all required fields";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="admin-container">
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
<h1>Add New Book</h1>
<a href="manage_book.php" class="btn">← Back</a>

<?php if ($error): ?><p class="alert alert-error"><?= $error ?></p><?php endif; ?>
<?php if ($success): ?><p class="alert alert-success"><?= $success ?></p><?php endif; ?>

<form method="POST">
    <input type="text" name="title" placeholder="Book Title" required value="<?= $_POST['title'] ?? '' ?>">
    <input type="text" name="author" placeholder="Author" required value="<?= $_POST['author'] ?? '' ?>">
    <input type="text" name="isbn" placeholder="ISBN" required value="<?= $_POST['isbn'] ?? '' ?>">

    <select name="category">
        <option>Computer Science</option>
        <option>Mathematics</option>
        <option>Physics</option>
        <option>Fiction</option>
        <option>Other</option>
    </select>

    <input type="number" name="copies" min="1" value="<?= $_POST['copies'] ?? 1 ?>" required>

    <button type="submit" class="btn btn-primary">Add Book</button>
    <a href="manage_book.php" class="btn">Cancel</a>
</form>
</div>
</div>

</body>
</html>
