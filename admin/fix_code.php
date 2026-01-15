<?php
// admin/fix_codes.php
require_once '../config/db.php';

echo "<h1>Generating Unique Codes for Existing Books...</h1>";

// 1. Get all books
$sql = "SELECT * FROM books";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($book = mysqli_fetch_assoc($result)) {
        $book_id = $book['id'];
        $isbn = $book['isbn'];
        $total_copies = $book['total_copies'];
        
        // 2. Check if this book already has copies in the new table
        $check_sql = "SELECT COUNT(*) as count FROM book_copies WHERE book_id = $book_id";
        $check_res = mysqli_query($conn, $check_sql);
        $count_data = mysqli_fetch_assoc($check_res);
        
        if ($count_data['count'] == 0) {
            // 3. If no copies exist, create them
            echo "Processing: <strong>" . $book['title'] . "</strong> ($total_copies copies)<br>";
            
            for ($i = 1; $i <= $total_copies; $i++) {
                // Generate Unique Code
                $random_str = strtoupper(substr(md5(time() . rand()), 0, 4));
                $unique_code = $isbn . "-" . $random_str . "-" . $i;
                
                // Insert
                $insert_sql = "INSERT INTO book_copies (book_id, unique_code, status) VALUES ('$book_id', '$unique_code', 'available')";
                if (mysqli_query($conn, $insert_sql)) {
                    echo "&nbsp;&nbsp;&nbsp; - Generated Code: $unique_code <span style='color:green'>[OK]</span><br>";
                } else {
                    echo "&nbsp;&nbsp;&nbsp; - Error: " . mysqli_error($conn) . "<br>";
                }
            }
            echo "<hr>";
        } else {
            echo "Skipping: " . $book['title'] . " (Codes already exist)<br><hr>";
        }
    }
    echo "<h2>Done! You can now delete this file and go to Issue Book page.</h2>";
} else {
    echo "No books found in database.";
}
?>