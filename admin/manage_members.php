<?php
require_once '../config/db.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Students</title>
    <!-- v=3.0 forces the browser to reload the CSS -->
    <link rel="stylesheet" href="../assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- 1. Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- 2. Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Manage Students</h1>
                <a href="add_member.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Student</a>
            </div>
            
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM users WHERE role = 'member' ORDER BY id DESC";
                        $result = mysqli_query($conn, $sql);
                        
                        if(mysqli_num_rows($result) > 0):
                            while($m = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($m['full_name']) ?></td>
                            <td><?= htmlspecialchars($m['username']) ?></td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td><?= htmlspecialchars($m['phone']) ?></td>
                            <td>
                                <a href="edit_member.php?id=<?= $m['id'] ?>" class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                                <a href="generate_id.php?id=<?= $m['id'] ?>" target="_blank" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">ID Card</a>
                                <a href="delete_member.php?id=<?= $m['id'] ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Delete this student?');">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="5" style="text-align:center; padding: 20px;">No students found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>