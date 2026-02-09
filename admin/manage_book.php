<?php
require_once '../config/db.php';
requireAdmin();

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Build Query
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

// Fetch categories for the filter dropdown
$cat_query = mysqli_query($conn, "SELECT name FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Books</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                <!-- Search & Filter Bar -->
                <form method="GET" style="margin-bottom: 25px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="search" class="form-control" style="flex: 2; min-width: 200px;" 
                           placeholder="Search by title, author, or ISBN..." value="<?= htmlspecialchars($search) ?>">
                    
                    <select name="category" class="form-control" style="flex: 1; min-width: 150px;">
                        <option value="">All Categories</option>
                        <?php while($c = mysqli_fetch_assoc($cat_query)): ?>
                            <option value="<?= htmlspecialchars($c['name']) ?>" <?= ($category_filter == $c['name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <button class="btn btn-primary">Filter</button>
                    
                    <?php if($search || $category_filter): ?>
                        <a href="manage_book.php" class="btn" style="background: #e9ecef;">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ISBN</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($books) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($books)): 
                                    $stock_percent = ($row['total_copies'] > 0) ? ($row['available_copies'] / $row['total_copies']) * 100 : 0;
                                    $stock_color = $stock_percent < 20 ? '#f72585' : '#4cc9f0';
                                ?>
                                <tr>
                                    <td style="color: #7f8c8d; font-family: monospace;"><?= $row['isbn'] ?></td>
                                    <td style="font-weight: 600;"><?= $row['title'] ?></td>
                                    <td><?= $row['author'] ?></td>
                                    <td><span class="badge" style="background: #f1f3f5; color: #495057;"><?= $row['category'] ?></span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-weight: bold; color: <?= $stock_color ?>"><?= $row['available_copies'] ?></span>
                                            <div style="width: 50px; height: 4px; background: #eee; border-radius: 2px;">
                                                <div style="width: <?= $stock_percent ?>%; height: 100%; background: <?= $stock_color ?>; border-radius: 2px;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="edit_book.php?id=<?= $row['id'] ?>" style="color: var(--primary); margin-right: 10px;"><i class="fas fa-edit"></i></a>
                                        <a href="delete_book.php?id=<?= $row['id'] ?>" style="color: var(--danger);" onclick="return confirm('Delete this book?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No books found in this category.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>