<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

// Fetch books
$books = $conn->query("SELECT * FROM books WHERE available_qty > 0");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Books</title>
</head>
<body>
<h2>Available Books</h2>
<a href="dashboard.php">Back to Dashboard</a>
<table border="1" cellpadding="5">
<tr>
    <th>Title</th>
    <th>Author</th>
    <th>Category</th>
    <th>Available Qty</th>
</tr>
<?php while($b = $books->fetch_assoc()){ ?>
<tr>
    <td><?php echo $b['book_title']; ?></td>
    <td><?php echo $b['author']; ?></td>
    <td><?php echo $b['category']; ?></td>
    <td><?php echo $b['available_qty']; ?></td>
</tr>
<?php } ?>
</table>
</body>
</html>
