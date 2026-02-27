<?php
require_once '../config/db.php';
require_once '../config/validation.php';
requireAdmin();

if(!isset($_GET['id'])) {
    header("Location: manage_members.php");
    exit;
}

$id = $_GET['id'];
$error = '';
$success = '';

// Fetch member details
$sql = "SELECT * FROM users WHERE id = ? AND role = 'member'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);

if(!$member) {
    header("Location: manage_members.php?error=Student not found");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = Validation::sanitize($_POST['full_name']);
    $email = Validation::sanitize($_POST['email']);
    $phone = Validation::sanitize($_POST['phone']);
    $status = Validation::sanitize($_POST['status']);
    
    // Validate data
    $name_validation = Validation::validate('Full Name', $full_name, 'name');
    $email_validation = Validation::validate('Email', $email, 'email');
    
    if(isset($name_validation['error'])) {
        $error = $name_validation['error'];
    } elseif(isset($email_validation['error'])) {
        $error = $email_validation['error'];
    } else {
        // Check if email already exists (excluding current member)
        $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "si", $email, $id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if(mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Email already exists";
        } else {
            $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, status = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssssi", $full_name, $email, $phone, $status, $id);
            
            if(mysqli_stmt_execute($update_stmt)) {
                $success = "Student updated successfully!";
            } else {
                $error = "Error updating student: " . mysqli_error($conn);
            }
            mysqli_stmt_close($update_stmt);
        }
        mysqli_stmt_close($check_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Edit Student</h1>
                <a href="manage_members.php" class="btn">← Back to Students</a>
            </div>
            
            <div class="card">
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" class="form-control" 
                               value="<?php echo htmlspecialchars($member['username']); ?>" readonly>
                        <small class="help-text">Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($member['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($member['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Account Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="active" <?php echo ($member['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($member['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Member Since</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo date('F j, Y', strtotime($member['created_at'])); ?>" readonly>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Student</button>
                    <a href="manage_members.php" class="btn">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>