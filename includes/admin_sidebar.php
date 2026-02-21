<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-book-reader"></i> LMS Admin</h3>
    </div>
    
    <div class="user-info">
        <div class="user-details">
            <h4 style="margin:0; font-size:16px; color: white;">
                <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>
            </h4>
            <span style="font-size:12px; color:#bdc3c7;">Librarian</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="admin_dashboard.php" class="menu-item">
            <i class="fas fa-home"></i> <span>Dashboard</span>
        </a>
        <a href="manage_book.php" class="menu-item">
            <i class="fas fa-book"></i> <span>Manage Books</span>
        </a>
        <a href="manage_members.php" class="menu-item">
            <i class="fas fa-users"></i> <span>Manage Students</span>
        </a>
        <a href="issue_book.php" class="menu-item">
            <i class="fas fa-hand-holding"></i> <span>Issue Book</span>
        </a>
        <a href="return_book.php" class="menu-item">
            <i class="fas fa-undo"></i> <span>Return Book</span>
        </a>
        <a href="fines.php" class="menu-item">
            <i class="fas fa-coins"></i> <span>Fines</span>
        </a>
        <a href="manage_categories.php" class="menu-item">
            <i class="fas fa-list"></i> <span>Categories</span>
        </a>
        <a href="reports.php" class="menu-item">
            <i class="fas fa-chart-line"></i> <span>Reports</span>
        </a>
         <a href="profile.php" class="menu-item">
            <i class="fas fa-user-cog"></i> <span>My Profile</span>
        </a>
        
        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="../auth/logout.php" class="menu-item" style="color: #e74c3c;">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </div>
</div>