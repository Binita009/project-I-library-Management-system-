<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

// Return Book
if(isset($_GET['return'])){
    $issue_id = $_GET['return'];
    // Update return date and status
    $conn->query("UPDATE issue_books SET return_date=CURDATE(), status='Returned' WHERE issue_id=$issue_id");
    // Increase available_qty
    $book_id = $conn->query("SELECT book_id FROM issue_books WHERE issue_id=$issue_id")->fetch_assoc()['book_id'];
    $conn->query("UPDATE books SET available_qty = available_qty + 1 WHERE book_id=$book_id");
    $success = "Book returned successfully!";
}

// Fetch issued books
$issued = $conn->query("SELECT ib.*, b.book_title, m.full_name 
                        FROM issue_books ib 
                        JOIN books b ON ib.book_id = b.book_id
                        JOIN members m ON ib.member_id = m.member_id
                        WHERE ib.status='Issued'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Return Books</title>
</head>
<body>
<h2>Return Books</h2>
<a href="dashboard.php">Back to Dashboard</a>

<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

<table border="1" cellpadding="5">
<tr>
    <th>Book</th>
    <th>Member</th>
    <th>Issue Date</th>
    <th>Due Date</th>
    <th>Action</th>
</tr>
<?php while($row = $issued->fetch_assoc()){ ?>
<tr>
    <td><?php echo $row['book_title']; ?></td>
    <td><?php echo $row['full_name']; ?></td>
    <td><?php echo $row['issue_date']; ?></td>
    <td><?php echo $row['due_date']; ?></td>
    <td><a href="return_book.php?return=<?php echo $row['issue_id']; ?>">Return</a></td>
</tr>
<?php } ?>
</table>
</body>
</html>
