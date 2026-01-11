<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Management System</title>
    <style>
        /* Reset some default styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }

        /* Header */
        header {
            background: linear-gradient(90deg, #4b79a1, #283e51);
            color: #fff;
            padding: 30px 20px;
            text-align: center;
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        header p {
            font-size: 1.1rem;
            color: #ddd;
        }

        /* Navigation */
        nav {
            background-color: #34495e;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            padding: 12px 0;
        }

        nav a {
            color: #fff;
            margin: 5px 20px;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }

        nav a:hover {
            background-color: #2c3e50;
        }

        /* Main container */
        .container {
            max-width: 900px;
            margin: 50px auto;
            text-align: center;
            padding: 0 20px;
        }

        .container h2 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .container p {
            font-size: 1rem;
            color: #555;
            line-height: 1.8;
        }

        /* Admin Section */
        .admin-menu {
            margin-top: 40px;
        }

        .admin-menu h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #e74c3c;
        }

        .admin-menu a {
            display: inline-block;
            margin: 10px 10px;
            padding: 12px 25px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .admin-menu a:hover {
            background-color: #c0392b;
        }

        /* Footer */
        footer {
            background-color: #283e51;
            color: #fff;
            text-align: center;
            padding: 15px 10px;
            position: relative;
            bottom: 0;
            width: 100%;
            margin-top: 50px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
            }
            nav a {
                margin: 8px 0;
            }
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
        <a href="auth/login.php">Member Login</a>
        <a href="auth/register.php">Member Register</a>
        <a href="books.php">Books</a>
        <a href="contact.php">Contact</a>
    </nav>

    <div class="container">
        <h2>Welcome to Our Library</h2>
        <p>
            Our library management system allows the school to efficiently handle books, students, teachers, 
            and daily operations including book issuance, returns, and fine calculations in a simple and streamlined way.
        </p>

        <!-- Admin Menu -->
        <div class="admin-menu">
            <h3>Admin Section</h3>
            <a href="admin/admin_login.php">Admin Login</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 School Library Management System. All Rights Reserved.</p>
    </footer>
</body>
</html>
