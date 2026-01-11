<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Management System</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
        }

        header {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
        }

        nav {
            background-color: #34495e;
            padding: 10px;
            text-align: center;
        }

        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            padding: 40px;
            text-align: center;
        }

        .container h2 {
            color: #2c3e50;
        }

        .container p {
            font-size: 16px;
            color: #555;
            max-width: 700px;
            margin: auto;
        }

        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

<header>
    <h1>School Library Management System</h1>
    <p>Manage books, users, and transactions efficiently</p>
</header>

<nav>
    <a href="index.php">Home</a>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
    <a href="books.php">Books</a>
    <a href="contact.php">Contact</a>
</nav>

<div class="container">
    <h2>Welcome to the Library Management System</h2>
    <p>
        This application helps the school library to manage books, students,
        teachers, and daily library operations such as book issue, return,
        and fine calculation in a simple and efficient way.
    </p>
</div>

<footer>
    <p>&copy; 2026 School Library Management System</p>
</footer>

</body>
</html>
