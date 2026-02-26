<?php
require_once '../config/db.php';
requireMember();

$user_id = $_SESSION['user_id'];

$sql = "SELECT br.*, b.title, b.author 
        FROM book_requests br 
        JOIN books b ON br.book_id = b.id 
        WHERE br.user_id = ? 
        ORDER BY br.request_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Book Requests</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <?php include 'member_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1>My Book Requests</h1>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Requested On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td style="font-weight:600"><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['author']) ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($row['request_date'])) ?></td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php elseif($row['status'] == 'approved'): ?>
                                    <span class="badge badge-success">Approved (Issued)</span>
                                <?php elseif($row['status'] == 'rejected'): ?>
                                    <span class="badge badge-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">You have no book requests.</td></tr>
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