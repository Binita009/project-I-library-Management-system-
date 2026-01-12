<?php if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
} ?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-book"></i> <span>Library Admin</span></h3>
    </div>
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
        <div class="user-role">Librarian</div>
    </div>
    <div class="sidebar-menu">
        <a href="admin_dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="manage_book.php" class="menu-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['manage_book.php', 'add_book.php', 'edit_book.php']) ? 'active' : ''; ?>">
            <i class="fas fa-book"></i>
            <span>Manage Books</span>
        </a>
        <a href="manage_members.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage_members.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Manage Students</span>
        </a>
        <a href="issue_book.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'issue_book.php' ? 'active' : ''; ?>">
            <i class="fas fa-book-medical"></i>
            <span>Issue Book</span>
        </a>
        <a href="return_book.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'return_book.php' ? 'active' : ''; ?>">
            <i class="fas fa-book-return"></i>
            <span>Return Book</span>
        </a>
        <a href="../auth/logout.php" class="menu-item" style="margin-top: 20px; color: #e74c3c;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>