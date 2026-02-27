<?php
require_once '../config/db.php';
requireAdmin();

// Fetch Categories for Dropdown
$cat_query = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();

    $title    = $_POST['title'];
    $author   = $_POST['author'];
    $isbn     = $_POST['isbn'];
    $category = $_POST['category']; 
    $copies   = (int)$_POST['copies'];
    
    $cover = 'default.png';
    if(isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . "." . $ext;
        
        // Use an absolute path to save the file
        // Safe relative directory creation
$target_dir = __DIR__ . "/../assets/uploads/";

// Automatically create the uploads folder if it doesn't exist yet
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}
        if(move_uploaded_file($_FILES['cover']['tmp_name'], $target_dir . $new_name)) {
            $cover = $new_name;
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
    <style>
        .category-group { display: flex; gap: 10px; }
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 15% auto; padding: 20px; border-radius: 8px; width: 300px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        <div class="main-content">
            <div class="content-header"><h1>Add Book</h1></div>
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex:1"><label>Author</label><input type="text" name="author" class="form-control" required></div>
                        <div class="form-group" style="flex:1"><label>ISBN</label><input type="text" name="isbn" class="form-control" required></div>
                    </div>
                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex:1">
                            <label>Category</label>
                            <div class="category-group">
                                <select name="category" id="category_dropdown" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php while($c = mysqli_fetch_assoc($cat_query)): ?>
                                        <option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="button" class="btn btn-secondary" onclick="openModal()">+</button>
                            </div>
                        </div>
                        <div class="form-group" style="flex:1"><label>Copies</label><input type="number" name="copies" class="form-control" value="1" min="1" required></div>
                    </div>
                    <div class="form-group"><label>Cover Image</label><input type="file" name="cover" class="form-control" accept="image/*"></div>
                    <button class="btn btn-primary">Add Book</button>
                </form>
            </div>
        </div>
    </div>

    <div id="catModal" class="modal">
        <div class="modal-content">
            <h3>Add Category</h3>
            <input type="text" id="new_cat_name" class="form-control" placeholder="Name" style="margin: 15px 0;">
            <div style="display:flex; gap:10px;">
                <button type="button" class="btn btn-primary" onclick="submitCategory()">Add</button>
                <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script>
        function openModal() { document.getElementById('catModal').style.display = 'block'; }
        function closeModal() { document.getElementById('catModal').style.display = 'none'; }
        function submitCategory() {
            const name = document.getElementById('new_cat_name').value;
            if(!name) return;
            const fd = new FormData(); fd.append('name', name);
            fetch('ajax_add_category.php', { method: 'POST', body: fd })
            .then(res => res.json()).then(data => {
                if(data.status === 'success'){
                    const opt = new Option(data.name, data.name);
                    const sel = document.getElementById('category_dropdown');
                    sel.add(opt); sel.value = data.name;
                    closeModal();
                } else { alert(data.message); }
            });
        }
    </script>
</body>
</html>