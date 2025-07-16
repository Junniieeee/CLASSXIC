<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'tutor' || $_SESSION['status'] !== 'pending') {
    header("Location: loginpage.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Application Pending</title>
    <style>
        body {
            background-color: #f2f3f8;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: white;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            width: 350px;
            border: 1px solid #aaa;
        }

        h2 {
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        p {
            font-size: 16px;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .thanks {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .btn-secondary {
            display: inline-block;
            background-color: red;
            color: white;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 30px;
            transition: background-color 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Application for tutor is still pending</h2>
        <p>Please wait for the admin to review and approve your account.</p>
        <div class="thanks">Thanks you!</div>
        <a href="logout.php" class="btn btn-secondary">Log out</a>
    </div>
</body>
</html>
