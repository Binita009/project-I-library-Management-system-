<?php
session_start();
include('../config/db.php'); // Database connection

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit();
}

// Handle Add Book
if(isset($_POST['add_book'])){
    $title = $_POST['book_title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $isbn = $_POST['isbn'];
    $quantity = $_POST['quantity'];

    // Insert into books table
    $sql = "INSERT INTO books (book_title, author, category, isbn, quantity, available_qty, status) 
            VALUES ('$title', '$author', '$category', '$isbn', '$quantity', '$quantity', 'Available')";
    if($conn->query($sql)){
        $success = "Book added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Fetch all books to display
$books = $conn->query("SELECT * FROM books ORDER BY created_at DESC");
?>

<h2>Add New Book</h2>

<?php 
if(isset($error)) echo "<p style='color:red;'>$error</p>"; 
if(isset($success)) echo "<p style='color:green;'>$success</p>"; 
?>

<form method="post" action="">
    <label>Book Title:</label><br>
    <input type="text" name="book_title" required><br>

    <label>Author:</label><br>
    <input type="text" name="author" required><br>

    <label>Category:</label><br>
    <input type="text" name="category"><br>

    <label>ISBN:</label><br>
    <input type="text" name="isbn"><br>

    <label>Quantity:</label><br>
    <input type="number" name="quantity" required min="1"><br>

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
    <th>Status</th>
    <th>Created At</th>
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
    <td><?php echo $row['status']; ?></td>
    <td><?php echo $row['created_at']; ?></td>
</tr>
<?php } ?>
</table>
