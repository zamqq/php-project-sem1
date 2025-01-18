<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database using mysqli
    $sql = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $passwordHash);

        if (mysqli_stmt_execute($stmt)) {
            echo "Registration successful. <a href='login.php'>Log in</a>";
        } else {
            echo "Error: Could not register user. " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error: Could not prepare the statement. " . mysqli_error($conn);
    }
}
?>

<form method="post">
    <label>Username: <input type="text" name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button type="submit">Register</button>
</form>