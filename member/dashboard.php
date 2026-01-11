<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$member_id = $_SESSION['user_id'];
$member_username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Member Dashboard</title>
</head>
<body>
<h1>Welcome, <?php echo $member_username; ?></h1>

<nav>
    <a href="dashboard.php">Home</a> |
    <a href="books.php">View Books</a> |
    <a href="issued_books.php">My Issued Books</a> |
    <a href ="return_book.php">Return Books</a>
    <a href="../auth/logout.php">Logout</a>
</nav>

<p>Here members can view available books and their issued books.</p>
</body>
</html>
