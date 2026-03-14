<?php
require_once '../config/db.php';
requireAdmin();

if (!isset($_GET['id'])) { header("Location: manage_book.php"); exit; }
$id = (int)$_GET['id'];

$book_q = mysqli_query($conn, "SELECT * FROM books WHERE id = $id");
$book = mysqli_fetch_assoc($book_q);

if (!$book) { setAlert('error', 'Not Found', 'Book not found'); header("Location: manage_book.php"); exit; }

$cat_query = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $cat = $_POST['category'];
    $total = (int)$_POST['total_copies'];

    $issued_count = $book['total_copies'] - $book['available_copies'];
    $new_available = $total - $issued_count;

    if ($new_available < 0) {
        setAlert('error', 'Error', "Cannot reduce copies below issued amount ($issued_count).");
    } else {
        $cover_sql_part = "";
        if(isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $new_name = uniqid() . "." . $ext;
            if(move_uploaded_file($_FILES['cover']['tmp_name'], "../assets/uploads/" . $new_name)) {
                $cover_sql_part = ", cover_image = '$new_name'";
            }
        }

        $ebook_sql_part = "";
        if(isset($_FILES['ebook']) && $_FILES['ebook']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['ebook']['name'], PATHINFO_EXTENSION));
            if($ext == 'pdf') {
                $ebook_new_name = uniqid() . "_ebook.pdf";
                $ebook_dir = __DIR__ . "/../assets/ebooks/";
                if (!is_dir($ebook_dir)) { mkdir($ebook_dir, 0777, true); }
                if(move_uploaded_file($_FILES['ebook']['tmp_name'], $ebook_dir . $ebook_new_name)) {
                    $ebook_sql_part = ", ebook_file = '$ebook_new_name'";
                }
            }
        }

        $sql = "UPDATE books SET title=?, author=?, isbn=?, category=?, total_copies=?, available_copies=? $cover_sql_part $ebook_sql_part WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssiii", $title, $author, $isbn, $cat, $total, $new_available, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            setAlert('success', 'Updated', 'Book updated successfully');
            header("Location: manage_book.php");
            exit;
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
            <div class="content-header"><h1>Edit Book</h1></div>
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required></div>
                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex:1"><label>Author</label><input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>" required></div>
                        <div class="form-group" style="flex:1"><label>ISBN</label><input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn']) ?>" required></div>
                    </div>
                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex:1">
                            <label>Category</label>
                            <div class="category-group">
                                <select name="category" id="category_dropdown" class="form-control" required>
                                    <?php while($c = mysqli_fetch_assoc($cat_query)): ?>
                                        <option value="<?= htmlspecialchars($c['name']) ?>" <?= ($c['name'] == $book['category']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="button" class="btn btn-secondary" onclick="openModal()">+</button>
                            </div>
                        </div>
                        <div class="form-group" style="flex:1"><label>Total Copies</label><input type="number" name="total_copies" class="form-control" value="<?= $book['total_copies'] ?>" required></div>
                    </div>
                    
                    <div class="form-row" style="display: flex; gap: 20px; align-items: flex-end;">
                        <div class="form-group" style="flex:1">
                            <label>Cover Image (Leave blank to keep current)</label>
                            <input type="file" name="cover" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group" style="flex:1">
                            <label>E-Book File (PDF)</label>
                            <?php if(!empty($book['ebook_file'])): ?>
                                <p style="margin-bottom: 5px; color: #27ae60; font-size: 13px;"><i class="fas fa-check-circle"></i> Current E-book is uploaded.</p>
                            <?php endif; ?>
                            <input type="file" name="ebook" class="form-control" accept=".pdf">
                        </div>
                    </div>

                    <button class="btn btn-primary" style="margin-top: 15px;">Update Book</button>
                    <a href="manage_book.php" class="btn btn-danger" style="margin-top: 15px;">Cancel</a>
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