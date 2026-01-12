<?php
// config/db.php

// Only start session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect("localhost", "root", "", "library_db");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

/* Auth Helpers */
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isMember() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'member';
}

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
        header("Location: ../admin/admin_dashboard.php"); // Fixed redirect
        exit();
    }
}
?>