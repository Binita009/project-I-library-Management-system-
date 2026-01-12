<?php
// Prevent session errors if db.php was already included
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dynamic path helper: allows header to be used in subfolders
$dir_level = dirname($_SERVER['PHP_SELF']);
$base_dir = basename($dir_level);
$path = "";

// If we are inside 'admin', 'member', or 'auth' folders, go back one level
if ($base_dir === 'admin' || $base_dir === 'member' || $base_dir === 'auth') {
    $path = "../";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Library Management System</title>
    
    <!-- Use dynamic path for CSS -->
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo $path; ?>index.php" class="nav-brand">
                <i class="fas fa-book"></i> Library System
            </a>
            <ul class="nav-menu">
                <!-- Navigation Links with dynamic paths -->
                <li>
                    <a href="<?php echo $path; ?>index.php" 
                       class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                       Home
                    </a>
                </li>
                <li>
                    <a href="<?php echo $path; ?>member/books.php" 
                       class="<?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>">
                       Books
                    </a>
                </li>

                <!-- Auth Logic -->
                <?php if(isset($_SESSION['user_id'])): ?>
                    
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <li><a href="<?php echo $path; ?>admin/admin_dashboard.php">Admin Panel</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $path; ?>member/dashboard.php">My Account</a></li>
                    <?php endif; ?>
                    
                    <li><a href="<?php echo $path; ?>auth/logout.php" style="color: #e74c3c;">Logout</a></li>
                
                <?php else: ?>
                    
                    <li><a href="<?php echo $path; ?>auth/login.php">Login</a></li>
                    <li><a href="<?php echo $path; ?>auth/register.php" class="btn btn-primary" style="padding: 5px 15px; color: white;">Register</a></li>
                
                <?php endif; ?>
            </ul>
        </div>
    </nav>