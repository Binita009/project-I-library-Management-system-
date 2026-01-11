<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$admin_username = $_SESSION['admin_username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
<h1>Welcome, <?php echo $admin_username; ?></h1>

<nav>
    <a href="dashboard.php">Home</a> |
    <a href="books.php">Books</a> |
    <a href="members.php">Members</a> |
    <a href ="add_book.php">Add Books</a>
    <a href="issue_book.php">Issue Books</a> |
    <a href="return_book.php">Return Books</a> |
    <a href="../auth/logout.php">Logout</a>
</nav>

<p>Admin can manage books, members, and issue/return operations here.</p>
</body>
</html>
