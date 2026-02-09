<?php
require_once '../config/db.php';
requireAdmin();

if (empty($_GET['id'])) {
    header("Location: manage_book.php");
    exit;
}

$id = (int)$_GET['id'];
$error = $success = '';

/* Fetch book */
$stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$book) {
    header("Location: manage_book.php?error=Book not found");
    exit;
}

/* Update book */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title  = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn   = trim($_POST['isbn']);
    $cat    = $_POST['category'];
    $total  = (int)$_POST['total_copies'];

    $issued = $book['total_copies'] - $book['available_copies'];
    $avail  = $total - $issued;

    if ($avail < 0) {
        $error = "Cannot reduce copies below issued books";
    } elseif ($title && $author && $isbn && $total > 0) {

        /* Check ISBN */
        $check = mysqli_prepare($conn,
            "SELECT id FROM books WHERE isbn=? AND id!=?"
        );
        mysqli_stmt_bind_param($check, "si", $isbn, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "ISBN already exists";
        } else {
            $update = mysqli_prepare($conn,
                "UPDATE books SET title=?, author=?, isbn=?, category=?,
                 total_copies=?, available_copies=? WHERE id=?"
            );
            mysqli_stmt_bind_param($update, "ssssiii",
                $title, $author, $isbn, $cat, $total, $avail, $id
            );

            if (mysqli_stmt_execute($update)) {
                $success = "Book updated successfully";
                $book['title'] = $title;
                $book['author'] = $author;
                $book['isbn'] = $isbn;
                $book['category'] = $cat;
                $book['total_copies'] = $total;
                $book['available_copies'] = $avail;
            } else {
                $error = "Update failed";
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
    <title>Edit Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="admin-container">
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
<h1>Edit Book</h1>
<a href="manage_book.php" class="btn">← Back</a>

<?php if ($error): ?><p class="alert alert-error"><?= $error ?></p><?php endif; ?>
<?php if ($success): ?><p class="alert alert-success"><?= $success ?></p><?php endif; ?>

<form method="POST">
    <input type="text" name="title" required value="<?= htmlspecialchars($book['title']) ?>">
    <input type="text" name="author" required value="<?= htmlspecialchars($book['author']) ?>">
    <input type="text" name="isbn" required value="<?= htmlspecialchars($book['isbn']) ?>">

<select name="category" class="form-control">
    <?php
    $cat_query = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
    while($c = mysqli_fetch_assoc($cat_query)):
    ?>
        <option value="<?= htmlspecialchars($c['name']) ?>" <?= ($book['category'] == $c['name']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
        </option>
    <?php endwhile; ?>
</select>

    <input type="number" name="total_copies" min="1"
           value="<?= $book['total_copies'] ?>" required>

    <input type="text" value="Available: <?= $book['available_copies'] ?>" readonly>

    <button class="btn btn-primary">Update Book</button>
    <a href="manage_book.php" class="btn">Cancel</a>
</form>

</div>
</div>

</body>
</html>
