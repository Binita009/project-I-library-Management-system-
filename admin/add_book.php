<?php
require_once '../config/db.php';
requireAdmin();

$cat_query = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();

    $title    = $_POST['title'];
    $author   = $_POST['author'];
    $isbn     = $_POST['isbn'];
    $category = $_POST['category'];
    $copies   = (int)$_POST['copies'];
    
    // Image Upload Logic
    $cover = 'default.png';
    if(isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        if(in_array(strtolower($ext), $allowed)) {
            $new_name = uniqid() . "." . $ext;
            if(move_uploaded_file($_FILES['cover']['tmp_name'], "../assets/uploads/" . $new_name)) {
                $cover = $new_name;
            }
        }
    }

    mysqli_begin_transaction($conn);
    try {
        $check = mysqli_query($conn, "SELECT id FROM books WHERE isbn = '$isbn'");
        if(mysqli_num_rows($check) > 0) throw new Exception("ISBN exists.");

        $sql = "INSERT INTO books (title, author, isbn, category, total_copies, available_copies, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssiis", $title, $author, $isbn, $category, $copies, $copies, $cover);
        mysqli_stmt_execute($stmt);
        $book_id = mysqli_insert_id($conn);
        
        $sql_copy = "INSERT INTO book_copies (book_id, unique_code, status) VALUES (?, ?, 'available')";
        $stmt_copy = mysqli_prepare($conn, $sql_copy);
        
        for ($i = 1; $i <= $copies; $i++) {
            $code = $isbn . "-" . rand(100,999) . "-" . $i;
            mysqli_stmt_bind_param($stmt_copy, "is", $book_id, $code);
            mysqli_stmt_execute($stmt_copy);
        }
        
        mysqli_commit($conn);
        setAlert('success', 'Success', 'Book added successfully!');
        header("Location: manage_book.php");
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        setAlert('error', 'Error', $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="main-content">
            <div class="content-header">
                <h1>Add Book</h1>
            </div>
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex:1">
                            <label>Author</label>
                            <!-- Regex: Letters, spaces, dots (for initials like J.K.) -->
                            <input type="text" name="author" class="form-control" 
                                   pattern="[a-zA-Z\s\.]+" 
                                   title="Author name should only contain letters, spaces, and dots."
                                   required>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>ISBN</label>
                            <!-- Regex: Numbers and Hyphens only, 10-17 chars -->
                            <input type="text" name="isbn" class="form-control" 
                                   pattern="[0-9\-]{10,17}" 
                                   title="ISBN should contain 10-13 digits (hyphens allowed)."
                                   required>
                        </div>
                    </div>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex:1">
                            <label>Category</label>
                            <select name="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php while($c = mysqli_fetch_assoc($cat_query)): ?>
                                    <option value="<?= $c['name'] ?>"><?= $c['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>Copies</label>
                            <input type="number" name="copies" class="form-control" value="1" min="1" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Cover Image</label>
                        <input type="file" name="cover" class="form-control" accept="image/*">
                    </div>

                    <button class="btn btn-primary">Add Book</button>
                </form>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>