<?php
require_once '../config/db.php';
requireMember();

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Books</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="admin-container">
<?php include 'member_sidebar.php'; ?>

<div class="main-content">
<h1>My Issued Books</h1>

<!-- CURRENT BOOKS -->
<div class="card">
<h3>Currently Issued</h3>

<?php
$stmt = mysqli_prepare($conn,
    "SELECT b.title, b.author, b.isbn, ib.issue_date, ib.due_date
     FROM issued_books ib
     JOIN books b ON ib.book_id=b.id
     WHERE ib.user_id=? AND ib.status='issued'
     ORDER BY ib.due_date"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) > 0):
?>
<table class="table">
<tr>
    <th>Book</th><th>Author</th><th>ISBN</th>
    <th>Issue</th><th>Due</th><th>Status</th>
</tr>
<?php while ($r = mysqli_fetch_assoc($res)):
    $days = floor((strtotime($r['due_date']) - time()) / 86400);
?>
<tr>
    <td><?= htmlspecialchars($r['title']) ?></td>
    <td><?= htmlspecialchars($r['author']) ?></td>
    <td><?= htmlspecialchars($r['isbn']) ?></td>
    <td><?= date('d M Y', strtotime($r['issue_date'])) ?></td>
    <td><?= date('d M Y', strtotime($r['due_date'])) ?></td>
    <td>
        <?= $days < 0 ? "Overdue" : $days . " days left" ?>
    </td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No books currently issued.</p>
<?php endif; ?>
</div>

<!-- HISTORY -->
<div class="card">
<h3>Return History</h3>

<?php
$stmt = mysqli_prepare($conn,
    "SELECT b.title, b.author, ib.issue_date, ib.return_date
     FROM issued_books ib
     JOIN books b ON ib.book_id=b.id
     WHERE ib.user_id=? AND ib.status='returned'
     ORDER BY ib.return_date DESC LIMIT 10"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) > 0):
?>
<table class="table">
<tr>
    <th>Book</th><th>Author</th><th>Issued</th>
    <th>Returned</th><th>Days Kept</th>
</tr>
<?php while ($r = mysqli_fetch_assoc($res)):
    $kept = floor((strtotime($r['return_date']) - strtotime($r['issue_date'])) / 86400);
?>
<tr>
    <td><?= htmlspecialchars($r['title']) ?></td>
    <td><?= htmlspecialchars($r['author']) ?></td>
    <td><?= date('d M Y', strtotime($r['issue_date'])) ?></td>
    <td><?= date('d M Y', strtotime($r['return_date'])) ?></td>
    <td><?= $kept ?> days</td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No return history.</p>
<?php endif; ?>
</div>

</div>
</div>

</body>
</html>
