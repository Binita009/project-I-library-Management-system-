<?php
require_once '../config/db.php';
requireAdmin();

// Handle Approve / Reject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    verify_csrf();
    $req_id = (int)$_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Fetch request details
        $req_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id, book_id FROM book_requests WHERE id = $req_id AND status = 'pending'"));
        if($req_info) {
            $book_id = $req_info['book_id'];
            $user_id = $req_info['user_id'];
            
            // Look for an available physical copy
            $copy = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM book_copies WHERE book_id = $book_id AND status = 'available' LIMIT 1"));
            
            if($copy) {
                $copy_id = $copy['id'];
                $due_date = date('Y-m-d', strtotime('+15 days')); // Default 15 days borrowing period
                
                mysqli_begin_transaction($conn);
                try {
                    // Issue the book
                    mysqli_query($conn, "INSERT INTO issued_books (book_id, copy_id, user_id, issue_date, due_date, status) VALUES ($book_id, $copy_id, $user_id, CURDATE(), '$due_date', 'issued')");
                    mysqli_query($conn, "UPDATE book_copies SET status='issued' WHERE id=$copy_id");
                    mysqli_query($conn, "UPDATE books SET available_copies=available_copies-1 WHERE id=$book_id");
                    
                    // Mark request as approved
                    mysqli_query($conn, "UPDATE book_requests SET status = 'approved' WHERE id = $req_id");
                    
                    mysqli_commit($conn);
                    setAlert('success', 'Approved', 'Request approved. Book has been issued!');
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    setAlert('error', 'Error', 'Failed to issue the book.');
                }
            } else {
                setAlert('error', 'Out of Stock', 'No copies available to issue right now.');
            }
        }
    } elseif ($action === 'reject') {
        mysqli_query($conn, "UPDATE book_requests SET status = 'rejected' WHERE id = $req_id");
        setAlert('success', 'Rejected', 'Request has been rejected.');
    }
    
    header("Location: manage_requests.php");
    exit;
}

$sql = "SELECT br.*, b.title, u.full_name, u.username, b.available_copies
        FROM book_requests br 
        JOIN books b ON br.book_id = b.id 
        JOIN users u ON br.user_id = u.id
        ORDER BY FIELD(br.status, 'pending', 'approved', 'rejected'), br.request_date DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Book Requests</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1>Manage Book Requests</h1>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Requested Book</th>
                            <th>Stock</th>
                            <th>Date</th>
                            <th>Status / Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                                <small><?= htmlspecialchars($row['username']) ?></small>
                            </td>
                            <td style="font-weight:600"><?= htmlspecialchars($row['title']) ?></td>
                            <td>
                                <?php if($row['available_copies'] > 0): ?>
                                    <span class="badge badge-success"><?= $row['available_copies'] ?> Available</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y h:i A', strtotime($row['request_date'])) ?></td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                        
                                        <button type="submit" name="action" value="approve" class="btn btn-success" style="padding: 5px 10px; font-size:12px;" <?= ($row['available_copies'] <= 0) ? 'disabled' : '' ?>>
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        
                                        <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding: 5px 10px; font-size:12px;" onclick="return confirm('Are you sure you want to reject this request?');">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                <?php elseif($row['status'] == 'approved'): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No requests found.</td></tr>
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