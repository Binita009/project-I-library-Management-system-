<?php
require_once '../config/db.php';
requireMember();

$user_id   = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

/* Issued books count */
$stmt = mysqli_prepare($conn,
    "SELECT COUNT(*) FROM issued_books 
     WHERE user_id=? AND status='issued'"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $issued_count);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

/* Overdue books count */
$stmt = mysqli_prepare($conn,
    "SELECT COUNT(*) FROM issued_books 
     WHERE user_id=? AND status='issued' AND due_date < CURDATE()"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $overdue_count);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="admin-container">

<!-- Sidebar -->
<div class="sidebar">
    <h3>Library Member</h3>
    <p><?= htmlspecialchars($full_name) ?></p>

    <a href="dashboard.php">Dashboard</a>
    <a href="books.php">Browse Books</a>
    <a href="issued_books.php">My Books</a>
    <a href="profile.php">Profile</a>
    <a href="../auth/logout.php" style="color:red">Logout</a>
</div>

<!-- Main -->
<div class="main-content">

<h1>Welcome, <?= htmlspecialchars($full_name) ?> 👋</h1>
<p><?= date('l, F j, Y') ?></p>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <h2><?= $issued_count ?></h2>
        <p>Books Issued</p>
    </div>

    <div class="stat-card">
        <h2><?= $overdue_count ?></h2>
        <p>Overdue Books</p>
    </div>
</div>

<!-- Issued Books -->
<div class="card">
<h3>My Issued Books</h3>

<?php
$stmt = mysqli_prepare($conn,
    "SELECT b.title, b.author, ib.issue_date, ib.due_date
     FROM issued_books ib
     JOIN books b ON ib.book_id=b.id
     WHERE ib.user_id=? AND ib.status='issued'
     ORDER BY ib.due_date ASC LIMIT 5"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0):
?>
<table class="table">
<tr>
    <th>Book</th><th>Author</th><th>Issue</th><th>Due</th><th>Status</th>
</tr>
<?php while ($r = mysqli_fetch_assoc($result)):
    $over = strtotime($r['due_date']) < time();
?>
<tr>
    <td><?= htmlspecialchars($r['title']) ?></td>
    <td><?= htmlspecialchars($r['author']) ?></td>
    <td><?= date('d M Y', strtotime($r['issue_date'])) ?></td>
    <td><?= date('d M Y', strtotime($r['due_date'])) ?></td>
    <td><?= $over ? 'Overdue' : 'Issued' ?></td>
</tr>
<?php endwhile; ?>
</table>

<a href="issued_books.php" class="btn btn-primary">View All</a>

<?php else: ?>
<p>You have not issued any books yet.
   <a href="books.php">Browse Books</a>
</p>
<?php endif; ?>
</div>

</div>
</div>

</body>
</html>
