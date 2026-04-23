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
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    // FIX: Handle cases where a file upload exceeds PHP's 'post_max_size' limit.
    // When this happens, PHP drops the entire $_POST array, including our csrf_token.
    if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0 && empty($_POST)) {
        die("Security Validation Failed: The uploaded file exceeds the server's maximum size limit. Please go back, refresh, and try a smaller file.");
    }

    // FIX: Check if the session token is set to prevent "Undefined array key" PHP warnings.
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
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
    $server_path = __DIR__ . "/../assets/uploads/" . $img;

    if (!empty($img) && $img !== 'default.png' && file_exists($server_path)) {
        // Find if the current script is running inside a sub-folder (admin, member, auth)
        $in_subdir = in_array(basename(dirname($_SERVER['PHP_SELF'])), ['admin', 'member', 'auth']);
        $prefix = $in_subdir ? '../' : ''; // Step back one folder if true
        
        return $prefix . "assets/uploads/" . $img;
    }
    
    // High-quality fallback placeholder
    return "https://via.placeholder.com/300x400?text=No+Cover+Found";
}
?>