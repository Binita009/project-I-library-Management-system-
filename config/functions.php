<?php
// CSRF Security
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security Validation Failed. Please go back and refresh.");
    }
}

// Set Alert for Custom Toast
function setAlert($type, $title, $message) {
    $_SESSION['alert'] = [
        'type' => $type, // success, error
        'title' => $title,
        'message' => $message
    ];
}

// Image Helper
function getBookCover($img) {
    // Define the path relative to the root of the site
    $relative_path = "../assets/uploads/" . $img;
    $server_path = $_SERVER['DOCUMENT_ROOT'] . "/library management system/assets/uploads/" . $img;

    if (!empty($img) && file_exists($server_path)) {
        return $relative_path;
    }
    
    // Fallback to a valid placeholder URL
    return "https://via.placeholder.com/150x200?text=No+Cover";
}
?>