<?php
require_once '../config/db.php';
require_once '../config/validation.php'; // Included validation for sanitation
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
    $title  = Validation::sanitize($_POST['title']);
    $author = Validation::sanitize($_POST['author']);
    $isbn   = Validation::sanitize($_POST['isbn']);
    $cat    = Validation::sanitize($_POST['category']);
    $total  = (int)$_POST['total_copies'];

    // Calculate available copies based on current issued count
    $issued_count = $book['total_copies'] - $book['available_copies'];
    $new_available = $total - $issued_count;

    if ($new_available < 0) {
        $error = "Cannot reduce total copies below the number currently issued ($issued_count).";
    } elseif ($title && $author && $isbn && $total > 0) {
        // Check ISBN uniqueness (exclude current book)
        $check = mysqli_prepare($conn, "SELECT id FROM books WHERE isbn=? AND id!=?");
        mysqli_stmt_bind_param($check, "si", $isbn, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "ISBN already exists for another book.";
        } else {
            $update = mysqli_prepare($conn, 
                "UPDATE books SET title=?, author=?, isbn=?, category=?, total_copies=?, available_copies=? WHERE id=?"
            );
            mysqli_stmt_bind_param($update, "ssssiii", $title, $author, $isbn, $cat, $total, $new_available, $id);

            if (mysqli_stmt_execute($update)) {
                $success = "Book updated successfully!";
                // Refresh data
                $book['title'] = $title;
                $book['author'] = $author;
                $book['isbn'] = $isbn;
                $book['category'] = $cat;
                $book['total_copies'] = $total;
                $book['available_copies'] = $new_available;
            } else {
                $error = "Update failed: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "Please fill all required fields correctly.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book - Admin Panel</title>
    <!-- FIX: Added missing CSS links -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="main-content">
            <div class="content-header">
                <h1>Edit Book</h1>
                <a href="manage_book.php" class="btn"><i class="fas fa-arrow-left"></i> Back to List</a>
            </div>

            <div class="card">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="title">Book Title *</label>
                        <input type="text" name="title" id="title" class="form-control" 
                               value="<?= htmlspecialchars($book['title']) ?>" required>
                    </div>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="author">Author *</label>
                            <input type="text" name="author" id="author" class="form-control" 
                                   value="<?= htmlspecialchars($book['author']) ?>" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="isbn">ISBN Number *</label>
                            <input type="text" name="isbn" id="isbn" class="form-control" 
                                   value="<?= htmlspecialchars($book['isbn']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control">
                                <?php
                                // FIX: Use dynamic categories from database
                                $cat_query = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
                                if(mysqli_num_rows($cat_query) > 0) {
                                    while($c = mysqli_fetch_assoc($cat_query)) {
                                        $selected = ($book['category'] == $c['name']) ? 'selected' : '';
                                        echo '<option value="'.htmlspecialchars($c['name']).'" '.$selected.'>'.htmlspecialchars($c['name']).'</option>';
                                    }
                                } else {
                                    // Fallback if no categories in DB yet
                                    echo '<option value="'.$book['category'].'" selected>'.$book['category'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="total_copies">Total Copies *</label>
                            <input type="number" name="total_copies" id="total_copies" min="1" class="form-control"
                                   value="<?= $book['total_copies'] ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Current Status</label>
                        <input type="text" class="form-control" style="background: #e9ecef;" 
                               value="Available on Shelf: <?= $book['available_copies'] ?>" readonly>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Update Book</button>
                        <a href="manage_book.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>