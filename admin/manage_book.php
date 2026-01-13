<?php
require_once '../config/db.php';
requireAdmin();

$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM books";
if($search) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%'";
}
$sql .= " ORDER BY id DESC";
$books = mysqli_query($conn, $sql);
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
                <a href="add_book.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Book</a>\
             
            </div>
            
            <div class="card">
                <!-- Search Bar -->
                <form method="GET" style="margin-bottom: 25px; display: flex; gap: 10px;">
                    <input type="text" name="search" class="form-control" style="flex: 1;" 
                           placeholder="Search by title, author, or ISBN..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary">Search</button>
                    <?php if($search): ?>
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
                            <?php while($row = mysqli_fetch_assoc($books)): 
                                $stock_percent = ($row['available_copies'] / $row['total_copies']) * 100;
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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>