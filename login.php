<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('includes/header.php');
include('config/db.php');

$error = '';
$success = '';

// User Registration / Sign-up logic
if (isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Email already exists! Please login.";
    } else {
        $conn->query("INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')");
        $success = "Registration successful! You can now log in.";
    }
}

// User Authentication / Sign-in logic
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];

            // If user arrived from an add-to-cart click, resume adding the product directly
            if (isset($_SESSION['pending_cart_id'])) {
                $product_id = $_SESSION['pending_cart_id'];
                unset($_SESSION['pending_cart_id']);
                header("Location: cart_action.php?action=add&id=" . $product_id);
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<main class="container" style="max-width: 800px; margin: 50px auto; padding: 20px; display: flex; gap: 30px; flex-wrap: wrap;">
    
    <!-- Login Form -->
    <div style="flex: 1; min-width: 300px; background: #1c1c1c; padding: 30px; border-radius: 8px; border: 1px solid #333;">
        <h2 style="color: #ff4747; border-bottom: 1px solid #333; padding-bottom: 10px;">Sign In</h2>
        <?php if($error): ?><p style="color: #ff4747;"><?php echo $error; ?></p><?php endif; ?>
        
        <form method="POST" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
            <input type="email" name="email" placeholder="Email Address" required style="padding: 12px; background: #121212; border: 1px solid #444; color: #fff; border-radius: 4px;">
            <input type="password" name="password" placeholder="Password" required style="padding: 12px; background: #121212; border: 1px solid #444; color: #fff; border-radius: 4px;">
            <button type="submit" name="login" style="padding: 12px; background: #ff4747; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Login</button>
        </form>
    </div>

    <!-- Register Form -->
    <div style="flex: 1; min-width: 300px; background: #1c1c1c; padding: 30px; border-radius: 8px; border: 1px solid #333;">
        <h2 style="color: #fff; border-bottom: 1px solid #333; padding-bottom: 10px;">Create Account</h2>
        <?php if($success): ?><p style="color: #4CAF50;"><?php echo $success; ?></p><?php endif; ?>
        
        <form method="POST" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
            <input type="text" name="username" placeholder="Full Name" required style="padding: 12px; background: #121212; border: 1px solid #444; color: #fff; border-radius: 4px;">
            <input type="email" name="email" placeholder="Email Address" required style="padding: 12px; background: #121212; border: 1px solid #444; color: #fff; border-radius: 4px;">
            <input type="password" name="password" placeholder="Create Password" required style="padding: 12px; background: #121212; border: 1px solid #444; color: #fff; border-radius: 4px;">
            <button type="submit" name="register" style="padding: 12px; background: #333; color: white; border: 1px solid #555; border-radius: 4px; font-weight: bold; cursor: pointer;">Register</button>
        </form>
    </div>

</main>

<?php include('includes/footer.php'); ?>