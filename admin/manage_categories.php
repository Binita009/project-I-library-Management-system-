<?php
require_once '../config/db.php';
requireAdmin();

// Handle Add
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_cat'])) {
    verify_csrf();
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    
    if(!empty($name)) {
        // Check duplicate
        $check = mysqli_query($conn, "SELECT id FROM categories WHERE name = '$name'");
        if(mysqli_num_rows($check) > 0) {
            setAlert('error', 'Error', 'Category already exists');
        } else {
            mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$name')");
            setAlert('success', 'Success', 'Category added');
            header("Location: manage_categories.php");
            exit;
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    setAlert('success', 'Deleted', 'Category removed successfully');
    header("Location: manage_categories.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Categories</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Manage Categories</h1>
            </div>
            
            <div class="form-row" style="gap: 30px; align-items: flex-start;">
                
                <!-- ADD FORM -->
                <div class="card" style="flex: 1;">
                    <h3>Add New Category</h3>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Science Fiction" required>
                        </div>
                        <button type="submit" name="add_cat" class="btn btn-primary">Add Category</button>
                    </form>
                </div>

                <!-- LIST / DELETE -->
                <div class="card" style="flex: 1;">
                    <h3>Existing Categories</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
                            if(mysqli_num_rows($cats) > 0):
                                while($c = mysqli_fetch_assoc($cats)): 
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td>
                                    <a href="manage_categories.php?delete=<?= $c['id'] ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 10px; font-size: 12px;"
                                       onclick="return confirm('Are you sure? This will remove the category from the dropdown list.')">
                                       <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="2">No categories found. Add one!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>