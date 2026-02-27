<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

$conn = mysqli_connect("localhost", "root", "", "library_db");

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Check for Admin (Librarian)
function requireAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../auth/admin_login.php");
        exit();
    }
}

// Check for Member (Student)
function requireMember() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
        header("Location: ../auth/student_login.php");
        exit();
    }
}
?>