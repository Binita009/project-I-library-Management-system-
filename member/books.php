<?php
require_once '../config/db.php';
requireMember();

// Force UTF-8 to prevent search encoding errors
mysqli_set_charset($conn, "utf8");

// 1. Capture Search Input
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$debug_message = "";

// 2. Build Query
$sql = "SELECT * FROM books";

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    
    // Using parentheses ( ) around OR conditions is safer practice
    $sql .= " WHERE (title LIKE '%$search_safe%' 
              OR author LIKE '%$search_safe%' 
              OR category LIKE '%$search_safe%' 
              OR isbn LIKE '%$search_safe%')";
              
    // Debug feedback (optional, you can remove this later)
    $debug_message = "Searching for: <strong>" . htmlspecialchars($search) . "</strong>";
}

$sql .= " ORDER BY title ASC";

// Execute
$result = mysqli_query($conn, $sql);

// Check for SQL syntax errors
if (!$result) {
    die("Database Query Error: " . mysqli_error($conn));
}
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
            <h1>Browse Library Books</h1>
        </div>

        <!-- Debug Message Area -->
        <?php if($debug_message): ?>
            <div style="background: #e7f1ff; color: #0c5460; padding: 10px; margin-bottom: 20px; border: 1px solid #b8daff; border-radius: 5px;">
                <i class="fas fa-info-circle"></i> <?= $debug_message ?>
            </div>
        <?php endif; ?>

        <!-- Search Bar Section -->
        <div class="card">
            <form method="GET" action="books.php" style="display: flex; gap: 10px;">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by Title, Author, ISBN..." 
                       value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if(!empty($search)): ?>
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($book = mysqli_fetch_assoc($result)): 
                            $is_available = $book['available_copies'] > 0;
                        ?>
                        <tr>
                            <td style="font-family: monospace; color: #666;"><?= htmlspecialchars($book['isbn']) ?></td>
                            <td style="font-weight: bold; color: #2c3e50;">
                                <?= htmlspecialchars($book['title']) ?>
                            </td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><span class="badge-category"><?= htmlspecialchars($book['category']) ?></span></td>
                            <td>
                                <?php if ($is_available): ?>
                                    <span class="status-badge available">
                                        Available (<?= $book['available_copies'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge out-of-stock">
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view_book.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #7f8c8d;">
                                <i class="fas fa-book-open" style="font-size: 32px; margin-bottom: 15px; display:block; opacity: 0.5;"></i>
                                <?php if($search): ?>
                                    No results found for "<strong><?= htmlspecialchars($search) ?></strong>"
                                <?php else: ?>
                                    No books available in the library yet.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .badge-category {
        background: #ecf0f1; color: #2c3e50; padding: 4px 8px; border-radius: 4px; font-size: 12px; border: 1px solid #bdc3c7;
    }
    .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 13px; font-weight: 500; }
    .status-badge.available { background-color: #d4edda; color: #155724; }
    .status-badge.out-of-stock { background-color: #f8d7da; color: #721c24; }
</style>

</body>
</html>