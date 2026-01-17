<?php
require_once '../config/db.php';
requireMember();

if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit;
}

$book_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Fetch Book Details
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $book_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);

if (!$book) {
    echo "Book not found.";
    exit;
}

// 2. Check if the current user has this book issued
$status_sql = "SELECT * FROM issued_books WHERE user_id = ? AND book_id = ? AND status = 'issued'";
$stmt2 = mysqli_prepare($conn, $status_sql);
mysqli_stmt_bind_param($stmt2, "ii", $user_id, $book_id);
mysqli_stmt_execute($stmt2);
$is_borrowed = mysqli_stmt_fetch($stmt2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?> - Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <?php include 'member_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <a href="books.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
        </div>

        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h1 style="margin-bottom: 10px; color: var(--primary);"><?= htmlspecialchars($book['title']) ?></h1>
                    <h3 style="color: #7f8c8d; font-weight: 500;">by <?= htmlspecialchars($book['author']) ?></h3>
                </div>
                
                <!-- Availability Badge -->
                <div style="text-align: right;">
                    <?php if($book['available_copies'] > 0): ?>
                        <span class="badge badge-success" style="font-size: 14px; padding: 10px 15px;">
                            <i class="fas fa-check-circle"></i> Available
                        </span>
                    <?php else: ?>
                        <span class="badge badge-danger" style="font-size: 14px; padding: 10px 15px;">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

            <!-- User Borrow Status Notification -->
            <?php if($is_borrowed): ?>
                <div class="alert alert-warning" style="background: #fff3cd; color: #856404; border: 1px solid #ffeeba;">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> You currently have a copy of this book borrowed. 
                    <a href="issued_books.php">Check Due Date</a>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
                <!-- Left Column -->
                <div>
                    <p><strong>Category:</strong> <br> <?= htmlspecialchars($book['category']) ?></p>
                    <p style="margin-top: 15px;"><strong>ISBN:</strong> <br> <span style="font-family: monospace; background: #f8f9fa; padding: 2px 6px;"><?= htmlspecialchars($book['isbn']) ?></span></p>
                </div>

                <!-- Right Column -->
                <div>
                    <p><strong>Stock Info:</strong></p>
                    <ul style="list-style: none; padding: 0; color: #666;">
                        <li>Total Copies: <?= $book['total_copies'] ?></li>
                        <li>Currently Available: <strong><?= $book['available_copies'] ?></strong></li>
                    </ul>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <h4 style="margin-bottom: 10px;">Description</h4>
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; line-height: 1.6; color: #444;">
                    <?php 
                    if (!empty($book['description'])) {
                        echo nl2br(htmlspecialchars($book['description'])); 
                    } else {
                        echo "<em>No description available for this book.</em>";
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .badge-success { background: #d4edda; color: #155724; }
    .badge-danger { background: #f8d7da; color: #721c24; }
</style>

</body>
</html>