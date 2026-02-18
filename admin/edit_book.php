<?php
require_once '../config/db.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header("Location: manage_book.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch Book Data
$book_q = mysqli_query($conn, "SELECT * FROM books WHERE id = $id");
$book = mysqli_fetch_assoc($book_q);

if (!$book) {
    setAlert('error', 'Not Found', 'Book not found');
    header("Location: manage_book.php");
    exit;
}

// Fetch Categories for Dropdown
$cat_query = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();

    $title  = $_POST['title'];
    $author = $_POST['author'];
    $isbn   = $_POST['isbn'];
    $cat    = $_POST['category']; // Get updated category
    $total  = (int)$_POST['total_copies'];

    // Update logic
    $issued_count = $book['total_copies'] - $book['available_copies'];
    $new_available = $total - $issued_count;

    if ($new_available < 0) {
        setAlert('error', 'Error', "Cannot reduce copies below issued amount ($issued_count).");
    } else {
        // Handle Image Update (Optional)
        $cover_sql_part = "";
        if(isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $new_name = uniqid() . "." . $ext;
            if(move_uploaded_file($_FILES['cover']['tmp_name'], "../assets/uploads/" . $new_name)) {
                $cover_sql_part = ", cover_image = '$new_name'";
            }
        }

        $sql = "UPDATE books SET title=?, author=?, isbn=?, category=?, total_copies=?, available_copies=? $cover_sql_part WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssiii", $title, $author, $isbn, $cat, $total, $new_available, $id);

        if (mysqli_stmt_execute($stmt)) {
            setAlert('success', 'Updated', 'Book details updated successfully');
            header("Location: manage_book.php");
            exit;
        } else {
            setAlert('error', 'Error', 'Update failed');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="main-content">
            <div class="content-header">
                <h1>Edit Book</h1>
            </div>
            
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required>
                    </div>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex:1">
                            <label>Author</label>
                            <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>" required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>ISBN</label>
                            <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex:1">
                            <label>Category</label>
                            <!-- DYNAMIC CATEGORY DROPDOWN -->
                            <select name="category" class="form-control" required>
                                <?php 
                                while($c = mysqli_fetch_assoc($cat_query)): 
                                    // Check if this option matches the book's current category
                                    $selected = ($c['name'] == $book['category']) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($c['name']) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>Total Copies</label>
                            <input type="number" name="total_copies" class="form-control" value="<?= $book['total_copies'] ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Update Cover (Optional)</label>
                        <input type="file" name="cover" class="form-control" accept="image/*">
                        <?php if($book['cover_image']): ?>
                            <small>Current: <?= $book['cover_image'] ?></small>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-primary">Update Book</button>
                    <a href="manage_book.php" class="btn btn-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>