<?php
require_once '../config/db.php';
requireMember();

// Fetch categories and count books in each
$sql = "SELECT c.name, COUNT(b.id) as book_count 
        FROM categories c 
        LEFT JOIN books b ON c.name = b.category 
        GROUP BY c.id, c.name 
        ORDER BY c.name ASC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Browse Categories</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .category-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text);
            border: 1px solid transparent;
            display: block;
        }
        .category-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.15);
        }
        .cat-icon {
            font-size: 30px;
            color: var(--primary);
            margin-bottom: 15px;
            background: rgba(67, 97, 238, 0.1);
            width: 60px;
            height: 60px;
            line-height: 60px;
            border-radius: 50%;
            margin-left: auto;
            margin-right: auto;
        }
        .cat-count {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'member_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Browse by Category</h1>
            </div>

            <div class="category-grid">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <a href="books.php?category=<?= urlencode($row['name']) ?>" class="category-card">
                        <div class="cat-icon"><i class="fas fa-bookmark"></i></div>
                        <h3 style="margin:0; font-size: 18px;"><?= htmlspecialchars($row['name']) ?></h3>
                        <div class="cat-count"><?= $row['book_count'] ?> Books</div>
                    </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No categories found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>