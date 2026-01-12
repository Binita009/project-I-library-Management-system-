<?php
session_start();

/* Database connection */
$conn = mysqli_connect("localhost", "root", "", "library_db");
if (!$conn) {
    die("Database connection failed");
}

/* Login checks */
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isMember() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'member';
}

/* Access control */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../auth/login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: ../member/dashboard.php");
        exit();
    }
}

function requireMember() {
    requireLogin();
    if (!isMember()) {
        header("Location: ../admin/dashboard.php");
        exit();
    }
}
?>
