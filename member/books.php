<?php
require_once '../config/db.php';
requireMember();

$search = $_GET['search'] ?? '';
$cat = $_GET['category'] ?? '';

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
    
    <!-- ADDED INTERNAL STYLES TO FORCE THE FIX -->
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
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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

        .stock-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .in-stock {
            background: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }

        .out-stock {
            background: rgba(231, 76, 60, 0.15);
            color: #c0392b;
        }
    </style>
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