<?php 
// Ensure session is started and path is set
if (session_status() === PHP_SESSION_NONE) session_start();
$path = (basename(dirname($_SERVER['PHP_SELF'])) == 'admin') ? '' : '../';
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-book-reader"></i> LMS Admin</h3>
    </div>
    
    <div class="user-info">
        <div class="user-avatar">
            <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)); ?>
        </div>
        <div class="user-details">
            <h4><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></h4>
            <span>Librarian</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="admin_dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-grid-2"></i>
            <span>Overview</span>
        </a>
        <a href="manage_book.php" class="menu-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['manage_book.php', 'add_book.php', 'edit_book.php']) ? 'active' : ''; ?>">
            <i class="fas fa-book"></i>
            <span>Books</span>
        </a>
        <a href="manage_members.php" class="menu-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['manage_members.php', 'add_member.php']) ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Students</span>
        </a>
        <a href="issue_book.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'issue_book.php' ? 'active' : ''; ?>">
            <i class="fas fa-hand-holding-box"></i>
            <span>Issue Books</span>
        </a>
        <a href="return_book.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'return_book.php' ? 'active' : ''; ?>">
            <i class="fas fa-undo-alt"></i>
            <span>Returns</span>
        </a>
        
        <!-- NEW FINES LINK -->
        <a href="fines.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'fines.php' ? 'active' : ''; ?>">
            <i class="fas fa-coins"></i>
            <span>Fines</span>
        </a>

        <a href="reports.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        
        <div style="margin-top: 40px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
            <a href="../auth/logout.php" class="menu-item" style="color: #ff6b6b;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>