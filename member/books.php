<?php
require_once '../config/db.php';
requireMember();

$search = $_GET['search'] ?? '';

// Build Query
$sql = "SELECT * FROM books";
if ($search) {
    $sql .= " WHERE title LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' 
              OR author LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' 
              OR category LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'";
}
$sql .= " ORDER BY title ASC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Books</title>
    <!-- Include BOTH style.css and admin.css for the layout -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <!-- Include Sidebar -->
    <?php include 'member_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1>Browse Library Books</h1>
        </div>

        <!-- Search Bar -->
        <div class="card">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by Title, Author, or Category..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if($search): ?>
                    <a href="books.php" class="btn btn-danger">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Books Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($book = mysqli_fetch_assoc($result)): 
                            $is_available = $book['available_copies'] > 0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($book['isbn']) ?></td>
                            <td style="font-weight: bold; color: #2c3e50;">
                                <?= htmlspecialchars($book['title']) ?>
                            </td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><span class="badge-category"><?= htmlspecialchars($book['category']) ?></span></td>
                            <td>
                                <?php if ($is_available): ?>
                                    <span class="status-badge available">
                                        <i class="fas fa-check-circle"></i> Available (<?= $book['available_copies'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge out-of-stock">
                                        <i class="fas fa-times-circle"></i> Out of Stock
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">No books found matching your search.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Add some specific styles for this page inline or in css file */
    .badge-category {
        background: #ecf0f1;
        color: #2c3e50;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        border: 1px solid #bdc3c7;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 13px;
        font-weight: 500;
    }
    .status-badge.available {
        background-color: #d4edda;
        color: #155724;
    }
    .status-badge.out-of-stock {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>

</body>
</html>