<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

// Add Book
if(isset($_POST['add_book'])){
    $title = $_POST['book_title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $isbn = $_POST['isbn'];
    $quantity = $_POST['quantity'];

    $sql = "INSERT INTO books (book_title, author, category, isbn, quantity, available_qty) 
            VALUES ('$title', '$author', '$category', '$isbn', $quantity, $quantity)";
    $conn->query($sql);
}

// Fetch Books
$books = $conn->query("SELECT * FROM books");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Books</title>
</head>
<body>
<h2>Manage Books</h2>
<a href="dashboard.php">Back to Dashboard</a>

<h3>Add Book</h3>
<form method="post" action="">
    <input type="text" name="book_title" placeholder="Book Title" required><br>
    <input type="text" name="author" placeholder="Author" required><br>
    <input type="text" name="category" placeholder="Category"><br>
    <input type="text" name="isbn" placeholder="ISBN"><br>
    <input type="number" name="quantity" placeholder="Quantity" required><br>
    <button type="submit" name="add_book">Add Book</button>
</form>

<h3>All Books</h3>
<table border="1" cellpadding="5">
<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Author</th>
    <th>Category</th>
    <th>ISBN</th>
    <th>Total Qty</th>
    <th>Available Qty</th>
</tr>
<?php while($row = $books->fetch_assoc()){ ?>
<tr>
    <td><?php echo $row['book_id']; ?></td>
    <td><?php echo $row['book_title']; ?></td>
    <td><?php echo $row['author']; ?></td>
    <td><?php echo $row['category']; ?></td>
    <td><?php echo $row['isbn']; ?></td>
    <td><?php echo $row['quantity']; ?></td>
    <td><?php echo $row['available_qty']; ?></td>
</tr>
<?php } ?>
</table>
</body>
</html>
