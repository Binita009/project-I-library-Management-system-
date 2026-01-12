<?php
require_once '../config/db.php';
requireAdmin();

if(!isset($_GET['id'])) {
    header("Location: manage_members.php");
    exit;
}

$id = $_GET['id'];

// Check if member has issued books
$check_sql = "SELECT COUNT(*) as count FROM issued_books WHERE user_id = ? AND status = 'issued'";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "i", $id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_bind_result($check_stmt, $issued_count);
mysqli_stmt_fetch($check_stmt);
mysqli_stmt_close($check_stmt);

if($issued_count > 0) {
    header("Location: manage_members.php?error=Cannot delete student who has issued books. Return all books first.");
    exit;
}

// Delete member
$delete_sql = "DELETE FROM users WHERE id = ? AND role = 'member'";
$delete_stmt = mysqli_prepare($conn, $delete_sql);
mysqli_stmt_bind_param($delete_stmt, "i", $id);

if(mysqli_stmt_execute($delete_stmt)) {
    header("Location: manage_members.php?msg=Student deleted successfully");
} else {
    header("Location: manage_members.php?error=Error deleting student");
}

mysqli_stmt_close($delete_stmt);
?>