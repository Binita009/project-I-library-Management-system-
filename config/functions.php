<?php
// CSRF Security
function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) session_start();
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
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['alert'] = [
        'type' => $type, 
        'title' => $title,
        'message' => $message
    ];
}

// THE FIX FOR THE PHOTO HOLDER
function getBookCover($img) {
    // 1. Detect the project folder name dynamically
    $script_name = $_SERVER['SCRIPT_NAME']; 
    $parts = explode('/', trim($script_name, '/'));
    $project_folder = $parts[0]; 

    // 2. Browser path (for the <img> src)
    $browser_path = "/" . $project_folder . "/assets/uploads/" . $img;

    // 3. Physical path (for PHP to check if file exists)
    $server_path = $_SERVER['DOCUMENT_ROOT'] . "/" . $project_folder . "/assets/uploads/" . $img;

    // 4. Check if image exists and is not empty
    if (!empty($img) && $img !== 'default.png' && file_exists($server_path)) {
        return $browser_path;
    }
    
    // Fallback to a high-quality placeholder if image is missing
    return "https://via.placeholder.com/300x400?text=No+Cover+Found";
}
?>