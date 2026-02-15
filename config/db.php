<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';

$conn = mysqli_connect("localhost", "root", "", "library_db");

if (!$conn) {
    // Hide error from user for security
    error_log("Connection failed: " . mysqli_connect_error()); 
    die("System currently unavailable.");
}

function isLoggedIn() { return !empty($_SESSION['user_id']); }
function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
function isMember() { return isset($_SESSION['role']) && $_SESSION['role'] === 'member'; }

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: ../auth/login.php");
        exit();
    }
}
function requireMember() {
    if (!isMember()) {
        header("Location: ../auth/login.php");
        exit();
    }
}
?>