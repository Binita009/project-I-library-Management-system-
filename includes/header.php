<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logic to check if we are in a subfolder or root
$dir_level = dirname($_SERVER['PHP_SELF']);
$base_dir = basename($dir_level);
$path = "";

// If inside admin/member/auth, go back one step
if ($base_dir === 'admin' || $base_dir === 'member' || $base_dir === 'auth') {
    $path = "../";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Library System</title>
    
    <!-- Link to the ONE Master CSS File -->
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css">
    
    <!-- FontAwesome (Keep this online or download locally if needed) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo $path; ?>index.php" class="nav-brand">
                <i class="fas fa-book-reader"></i> LMS
            </a>
            <ul class="nav-menu">
                <li><a href="<?php echo $path; ?>index.php">Home</a></li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <li><a href="<?php echo $path; ?>admin/admin_dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $path; ?>member/dashboard.php">My Dashboard</a></li>
                        <li><a href="<?php echo $path; ?>member/books.php">Books</a></li>
                    <?php endif; ?>
                    
                    <li><a href="<?php echo $path; ?>auth/logout.php" class="btn btn-danger" style="padding: 5px 15px;">Logout</a></li>
                
                <?php else: ?>
                    <li><a href="<?php echo $path; ?>auth/login.php">Login</a></li>
                    <li><a href="<?php echo $path; ?>auth/register.php" class="btn btn-primary" style="padding: 5px 15px; color: white;">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>