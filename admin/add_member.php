<?php
require_once '../config/db.php';
requireAdmin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username   = $_POST['username'];
    $password   = $_POST['password'];
    $full_name  = $_POST['full_name'];
    $email      = $_POST['email'];
    $phone      = $_POST['phone'];

    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        setAlert('error', 'Error', 'Username or email already exists');
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, 'member')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $username, $hashed, $full_name, $email, $phone);

        if (mysqli_stmt_execute($stmt)) {
            setAlert('success', 'Success', 'Student added successfully');
            header("Location: manage_members.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-container">
    <?php include '../includes/admin_sidebar.php'; ?>
    <div class="main-content">
        <div class="content-header">
            <h1>Add New Student</h1>
            <a href="manage_members.php" class="btn">Back</a>
        </div>
        <div class="card">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" 
                               pattern="[a-zA-Z0-9_]{3,20}" 
                               title="3-20 characters, no spaces."
                               required>
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" 
                               pattern=".{6,}" 
                               title="Min 6 characters."
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" class="form-control" 
                           pattern="[a-zA-Z\s]+" 
                           title="Letters and spaces only."
                           required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" 
                               pattern="[0-9]{10}" 
                               title="10 digit phone number"
                               required>
                    </div>
                </div>

                <button class="btn btn-primary">Add Student</button>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>