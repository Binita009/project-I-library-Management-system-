<?php
require_once '../config/db.php';
require_once '../config/validation.php';
requireAdmin();

$error = '';
$success = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $cat_name = Validation::sanitize($_POST['category_name']);
    
    if (!empty($cat_name)) {
        // Check duplicate
        $check = mysqli_query($conn, "SELECT id FROM categories WHERE name = '$cat_name'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Category already exists!";
        } else {
            $insert = mysqli_prepare($conn, "INSERT INTO categories (name) VALUES (?)");
            mysqli_stmt_bind_param($insert, "s", $cat_name);
            if (mysqli_stmt_execute($insert)) {
                $success = "Category added successfully!";
            } else {
                $error = "Error adding category.";
            }
        }
    } else {
        $error = "Category name cannot be empty.";
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
    header("Location: manage_categories.php?msg=Category deleted");
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

            <div class="form-row" style="display: flex; gap: 20px;">
                
                <!-- Add New Category Form -->
                <div class="card" style="flex: 1; height: fit-content;">
                    <h3>Add New Category</h3>
                    <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
                    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" name="category_name" class="form-control" required placeholder="e.g. History">
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </form>
                </div>

                <!-- List Categories -->
                <div class="card" style="flex: 1;">
                    <h3>Existing Categories</h3>
                    <?php if(isset($_GET['msg'])): ?><div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
                    
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
                            while($row = mysqli_fetch_assoc($cats)):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                    <a href="manage_categories.php?delete=<?= $row['id'] ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 10px; font-size: 12px;"
                                       onclick="return confirm('Delete this category?');">
                                       <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>