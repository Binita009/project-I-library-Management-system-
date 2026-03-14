<?php
require_once '../config/db.php';
requireMember();

$search = $_GET['search'] ?? '';
$cat = $_GET['category'] ?? '';

// SQL: Fetch ONLY books that have an uploaded E-book (PDF)
$sql = "SELECT * FROM books WHERE ebook_file IS NOT NULL AND ebook_file != ''";

if ($search) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (title LIKE '%$search%' OR author LIKE '%$search%')";
}
if ($cat) {
    $cat = mysqli_real_escape_string($conn, $cat);
    $sql .= " AND category = '$cat'";
}

$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>E-Library (Digital Books)</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .book-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #eee;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        /* Adds a little digital badge to the top corner */
        .digital-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 2;
        }

        .book-cover-img {
            width: 100%;
            height: 280px; 
            object-fit: cover; 
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }

        .book-info {
            padding: 18px;
            display: flex;
            flex-direction: column;
            flex-grow: 1; 
        }

        .book-title {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .book-author {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 15px;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 12px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'member_sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-header">
            <div>
                <h1 style="color: #2c3e50;"><i class="fas fa-tablet-alt" style="color: #3498db;"></i> E-Library Collection</h1>
                <p style="color: #7f8c8d; margin-top: 5px;">Read full digital books instantly. No borrowing required.</p>
            </div>
        </div>

        <div class="card">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="search" class="form-control" placeholder="Search digital books..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary">Search</button>
                <?php if($search || $cat): ?>
                    <a href="e_library.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="books-grid">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($book = mysqli_fetch_assoc($result)): 
                    $img = getBookCover($book['cover_image']);
                ?>
                <div class="book-card">
                    <div class="digital-badge"><i class="fas fa-file-pdf"></i> PDF Available</div>
                    <img src="<?= $img ?>" class="book-cover-img" alt="Cover">
                    <div class="book-info">
                        <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                        <div class="book-author">by <?= htmlspecialchars($book['author']) ?></div>
                        
                        <div style="margin-top: auto; display: flex; flex-direction: column; gap: 8px;">
                            <a href="../assets/ebooks/<?= htmlspecialchars($book['ebook_file']) ?>" target="_blank" class="btn btn-success" style="width: 100%; text-align: center; background: #27ae60;">
                                <i class="fas fa-book-reader"></i> Read Online
                            </a>
                            <a href="view_book.php?id=<?= $book['id'] ?>" class="btn btn-secondary" style="width: 100%; text-align: center; background: #ecf0f1; color: #2c3e50;">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open" style="font-size: 40px; color: #bdc3c7; margin-bottom: 15px;"></i>
                    <h3>No Digital Books Found</h3>
                    <p>The librarian hasn't uploaded any PDF E-books yet, or your search didn't match anything.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>