<?php
session_start();
include('../config/db.php');

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

// Issue book
if(isset($_POST['issue'])){
    $book_id = $_POST['book_id'];
    $member_id = $_POST['member_id'];
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];

    // Check if book is available
    $book = $conn->query("SELECT available_qty FROM books WHERE book_id=$book_id")->fetch_assoc();
    if($book['available_qty'] > 0){
        // Insert into issue_books
        $conn->query("INSERT INTO issue_books (book_id, member_id, issue_date, due_date) 
                      VALUES ($book_id, $member_id, '$issue_date', '$due_date')");
        // Reduce available_qty
        $conn->query("UPDATE books SET available_qty = available_qty - 1 WHERE book_id=$book_id");
        $success = "Book issued successfully!";
    } else {
        $error = "No available copies for this book!";
    }
}

// Fetch books and members
$books = $conn->query("SELECT * FROM books WHERE available_qty > 0");
$members = $conn->query("SELECT * FROM members");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Issue Books</title>
</head>
<body>
<h2>Issue Book</h2>
<a href="dashboard.php">Back to Dashboard</a>

<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

<form method="post" action="">
    <label>Select Book:</label>
    <select name="book_id" required>
        <option value="">--Select Book--</option>
        <?php while($b = $books->fetch_assoc()){ ?>
            <option value="<?php echo $b['book_id']; ?>">
                <?php echo $b['book_title']." (".$b['available_qty']." available)"; ?>
            </option>
        <?php } ?>
    </select><br>

    <label>Select Member:</label>
    <select name="member_id" required>
        <option value="">--Select Member--</option>
        <?php while($m = $members->fetch_assoc()){ ?>
            <option value="<?php echo $m['member_id']; ?>">
                <?php echo $m['full_name']; ?>
            </option>
        <?php } ?>
    </select><br>

    <label>Issue Date:</label>
    <input type="date" name="issue_date" required><br>

    <label>Due Date:</label>
    <input type="date" name="due_date" required><br>

    <button type="submit" name="issue">Issue Book</button>
</form>
</body>
</html>
