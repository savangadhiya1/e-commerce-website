<?php
session_start();
include('config/db.php');

$error = '';

// Login authentication process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']); // Prevent SQL injection

    // Verify password match using SHA2 (256-bit) encryption inside SQL query
    $query = $conn->query("SELECT * FROM admins WHERE username = '$username' AND password = SHA2('$password', 256) LIMIT 1");
    
    if ($query && $query->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Incorrect username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volt | Admin Login</title>
    <style>
        body { background: #0a0a0a; color: #fff; font-family: sans-serif; display: flex; justify-content: center; padding-top: 100px; }
        .login-box { width: 350px; background: #121212; padding: 30px; border-radius: 10px; border: 1px solid #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; background: #1c1c1c; border: 1px solid #333; color: #fff; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #ff4747; border: none; color: white; cursor: pointer; font-weight: bold; }
        .error { color: #ff4747; font-size: 14px; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="login-box">
    <h2 style="text-align:center;">VOLT <span style="color:#ff4747;">ADMIN</span></h2>
    <?php if($error) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">LOGIN</button>
    </form>
</div>
</body>
</html>