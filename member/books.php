<?php
require_once '../config/db.php';
requireMember();

mysqli_set_charset($conn, "utf8");

// 1. Capture Inputs
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

// 2. Build Query
$sql = "SELECT * FROM books WHERE 1=1";

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (title LIKE '%$search_safe%' 
              OR author LIKE '%$search_safe%' 
              OR isbn LIKE '%$search_safe%')";
}

if (!empty($category_filter)) {
    $cat_safe = mysqli_real_escape_string($conn, $category_filter);
    $sql .= " AND category = '$cat_safe'";
}

$sql .= " ORDER BY title ASC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Books</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <?php include 'member_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1>
                <?php if($category_filter): ?>
                    Category: <?= htmlspecialchars($category_filter) ?>
                <?php else: ?>
                    Browse Library Books
                <?php endif; ?>
            </h1>
        </div>

        <!-- Search Bar Section -->
        <div class="card">
            <form method="GET" action="books.php" style="display: flex; gap: 10px;">
                <!-- Keep category filter if searching within a category -->
                <?php if($category_filter): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($category_filter) ?>">
                <?php endif; ?>

                <input type="text" name="search" class="form-control" 
                       placeholder="Search books..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                
                <?php if(!empty($search) || !empty($category_filter)): ?>
                    <a href="books.php" class="btn btn-danger">Clear All</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Books Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($book = mysqli_fetch_assoc($result)): 
                            $is_available = $book['available_copies'] > 0;
                        ?>
                        <tr>
                            <td style="font-weight: bold; color: #2c3e50;">
                                <?= htmlspecialchars($book['title']) ?>
                            </td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><span style="background: #ecf0f1; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?= htmlspecialchars($book['category']) ?></span></td>
                            <td>
                                <?php if ($is_available): ?>
                                    <span style="color: green; font-weight: 500;">Available (<?= $book['available_copies'] ?>)</span>
                                <?php else: ?>
                                    <span style="color: red; font-weight: 500;">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view_book.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #7f8c8d;">
                                No books found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>