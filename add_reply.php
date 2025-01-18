<?php
session_start();
require 'db.php';

if (isset($_POST['reply_text'], $_POST['parent_comment_id'], $_POST['painting_id'], $_SESSION['user_id'])) {
    $reply_text = $_POST['reply_text'];
    $parent_comment_id = $_POST['parent_comment_id'];
    $painting_id = $_POST['painting_id'];
    $user_id = $_SESSION['user_id'];

    // Insert the reply into the database
    $query = "INSERT INTO comments (comment_text, parent_comment_id, painting_id, user_id) 
              VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'siis', $reply_text, $parent_comment_id, $painting_id, $user_id);
    mysqli_stmt_execute($stmt);

    // Redirect back to the painting page after submitting the reply
    header("Location: index.php");
    exit();
} else {
    echo "Please log in to reply.";
}
