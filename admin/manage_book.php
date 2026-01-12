<?php
require_once '../config/db.php';
requireAdmin();

$result = mysqli_query($conn, "SELECT * FROM books ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <h1>Manage Books</h1>
        <a href="add_book.php" class="btn btn-primary">+ Add Book</a>

        <?php if (!empty($_GET['msg'])): ?>
            <p class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></p>
        <?php endif; ?>

        <?php if (!empty($_GET['error'])): ?>
            <p class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <table class="table" id="booksTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Category</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($b = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <td><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['author']) ?></td>
                    <td><?= htmlspecialchars($b['isbn']) ?></td>
                    <td><?= htmlspecialchars($b['category']) ?></td>
                    <td>
                        <?= $b['available_copies'] ?>/<?= $b['total_copies'] ?>
                    </td>
                    <td>
                        <a href="edit_book.php?id=<?= $b['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete_book.php?id=<?= $b['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No books found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
