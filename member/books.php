<?php
require_once '../config/db.php';
requireMember();

// Wrap them in mysqli_real_escape_string
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$cat = mysqli_real_escape_string($conn, $_GET['category'] ?? '');

$sql = "SELECT * FROM books WHERE 1=1";
if ($search) $sql .= " AND title LIKE '%$search%'";
if ($cat) $sql .= " AND category = '$cat'";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
            <h1>Browse Books</h1>
        </div>

        <div class="card">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="books-grid">
            <?php while($book = mysqli_fetch_assoc($result)): 
                $img = getBookCover($book['cover_image']);
            ?>
            <div class="book-card">
                <img src="<?= $img ?>" class="book-cover-img" alt="Cover">
                <div class="book-info">
                    <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                    <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                    
                    <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center;">
                        <?php if($book['available_copies'] > 0): ?>
                            <span class="stock-badge in-stock">Available</span>
                        <?php else: ?>
                            <span class="stock-badge out-stock">Out of Stock</span>
                        <?php endif; ?>
                        
                        <a href="view_book.php?id=<?= $book['id'] ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 13px;">View</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>