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
    $path = "../assets/uploads/" . $img;
    // Return path if file exists, else placeholder
    if (!empty($img) && file_exists(__DIR__ . "/../assets/uploads/" . $img)) {
        return $path;
    }
    // Simple placeholder using text if image missing
    return "https://via.placeholder.com/220x280.png?text=No+Cover";
}
?>