<?php if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../auth/login.php");
    exit;
} ?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-book"></i> <span>Library Member</span></h3>
    </div>
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
        <div class="user-role">Student</div>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="books.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>">
            <i class="fas fa-book-open"></i>
            <span>Browse Books</span>
        </a>
        <a href="issued_books.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'issued_books.php' ? 'active' : ''; ?>">
            <i class="fas fa-book-reader"></i>
            <span>My Books</span>
        </a>
        <a href="profile.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>My Profile</span>
        </a>
        <a href="../auth/logout.php" class="menu-item" style="margin-top: 20px; color: #e74c3c;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>