<?php
session_start();
require 'db.php'; // Make sure this includes the connection to your database

// Check if the admin is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php"); // Redirect to the main page if already logged in
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the user from the database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if the user exists and validate the password
    if ($user = mysqli_fetch_assoc($result)) {
        // Validate password
        if (password_verify($password, $user['password_hash'])) {
            // Store user information in the session
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Check if the user is an admin
            if ($user['is_admin'] == 1) {
                $_SESSION['admin_logged_in'] = true;
                header("Location: admin_dashboard.php"); // Redirect to the admin dashboard
            } else {
                header("Location: index.php"); // Redirect to the regular user page
            }
            exit;
        } else {
            $error_message = "Invalid username or password!";
        }
    } else {
        $error_message = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="navbar">
        <h1>User Login</h1>
    </div>
    <div class="login-form">
        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required><br><br>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required><br><br>

            <button type="submit">Login</button>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</body>

</html>