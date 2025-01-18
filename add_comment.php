<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $painting_id = intval($_POST['painting_id']);
    $comment_text = mysqli_real_escape_string($conn, $_POST['comment_text']);

    $query = "INSERT INTO comments (user_id, painting_id, comment_text) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'iis', $user_id, $painting_id, $comment_text);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request or not logged in.";
}
