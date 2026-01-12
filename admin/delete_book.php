<?php
require_once '../config/db.php';
requireAdmin();

if(!isset($_GET['id'])) {
    header("Location: manage_book.php");
    exit;
}

$id = $_GET['id'];

// Check if book exists and has no issued copies
$sql = "SELECT available_copies, total_copies FROM books WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $available_copies, $total_copies);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if($available_copies != $total_copies) {
    header("Location: manage_book.php?error=Cannot delete book that has been issued to members");
    exit;
}

// Delete book
$delete_sql = "DELETE FROM books WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_sql);
mysqli_stmt_bind_param($delete_stmt, "i", $id);

if(mysqli_stmt_execute($delete_stmt)) {
    header("Location: manage_book.php?msg=Book deleted successfully");
} else {
    header("Location: manage_book.php?error=Error deleting book");
}

mysqli_stmt_close($delete_stmt);
?>