<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];
$issued = $conn->query("SELECT ib.*, b.book_title 
                        FROM issue_books ib 
                        JOIN books b ON ib.book_id = b.book_id
                        WHERE ib.member_id=$member_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Issued Books</title>
</head>
<body>
<h2>My Issued Books</h2>
<a href="dashboard.php">Back to Dashboard</a>
<table border="1" cellpadding="5">
<tr>
    <th>Book</th>
    <th>Issue Date</th>
    <th>Due Date</th>
    <th>Return Date</th>
    <th>Status</th>
</tr>
<?php while($row = $issued->fetch_assoc()){ ?>
<tr>
    <td><?php echo $row['book_title']; ?></td>
    <td><?php echo $row['issue_date']; ?></td>
    <td><?php echo $row['due_date']; ?></td>
    <td><?php echo $row['return_date'] ? $row['return_date'] : '-'; ?></td>
    <td><?php echo $row['status']; ?></td>
</tr>
<?php } ?>
</table>
</body>
</html>
