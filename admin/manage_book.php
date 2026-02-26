<?php
require_once '../config/db.php';
requireAdmin();

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$sql = "SELECT * FROM books WHERE 1=1";

if($search) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%')";
}
if($category_filter) {
    $cat_safe = mysqli_real_escape_string($conn, $category_filter);
    $sql .= " AND category = '$cat_safe'";
}
$sql .= " ORDER BY id DESC";
$books = mysqli_query($conn, $sql);
$cat_query = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Books</title>
    <!-- Add BOTH CSS files -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css"> <!-- THIS WAS MISSING -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Manage Books</h1>
                <a href="add_book.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Book</a>
            </div>
            
            <div class="card">
                <!-- Search -->
                <form method="GET" style="margin-bottom: 25px; display: flex; gap: 10px;">
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                    <select name="category" class="form-control" style="width: 200px;">
                        <option value="">All Categories</option>
                        <?php while($c = mysqli_fetch_assoc($cat_query)): ?>
                            <option value="<?= htmlspecialchars($c['name']) ?>" <?= ($category_filter == $c['name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button class="btn btn-primary">Filter</button>
                    <?php if($search || $category_filter): ?>
                        <a href="manage_book.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php if(mysqli_num_rows($books) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($books)): 
            // Correct way to call the image
            $img_path = getBookCover($row['cover_image']);
        ?>
        <tr>
            <td>
                <div style="width: 50px; height: 70px; background: #eee; border-radius: 4px; overflow: hidden; border: 1px solid #ddd;">
                    <img src="<?= $img_path ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Cover">
                </div>
            </td>
            <td style="font-family: monospace; color: #666;"><?= $row['isbn'] ?></td>
            <td>
                <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                <small><?= htmlspecialchars($row['author']) ?></small>
            </td>
            <td>
                <?php if($row['available_copies'] > 0): ?>
                    <span class="badge badge-success"><?= $row['available_copies'] ?> Available</span>
                <?php else: ?>
                    <span class="badge badge-danger">Out of Stock</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="edit_book.php?id=<?= $row['id'] ?>" class="btn btn-primary" style="padding: 5px 10px;"><i class="fas fa-edit"></i></a>
                <a href="delete_book.php?id=<?= $row['id'] ?>" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirm('Delete?');"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5" style="text-align: center;">No books found.</td></tr>
    <?php endif; ?>
</tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>