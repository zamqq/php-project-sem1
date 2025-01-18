<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$all_authors = get_all_authors(); // Fetch all authors for selection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $discount_price = $_POST['discount_price'];
    $cover = $_POST['cover'];
    $authors = $_POST['authors'];

    // Check if new authors are provided
    if (!empty($_POST['new_author_name'])) {
        // Add new author
        $new_author_name = $_POST['new_author_name'];
        $new_author_bio = $_POST['new_author_bio'];

        $sql = "INSERT INTO authors (name, bio) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $new_author_name, $new_author_bio);
        mysqli_stmt_execute($stmt);
        $new_author_id = mysqli_insert_id($conn); // Get the ID of the newly inserted author

        // Add the new author to the authors list
        $authors[] = $new_author_id; // Add new author to the list of selected authors
    }

    // Insert the painting into the paintings table
    $sql = "INSERT INTO paintings (title, description, category, price, discount_price, cover) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssdss", $title, $description, $category, $price, $discount_price, $cover);
    mysqli_stmt_execute($stmt);
    $painting_id = mysqli_insert_id($conn); // Get the ID of the newly inserted painting

    // Insert authors into the painting_authors table
    $insert_authors_sql = "INSERT INTO painting_authors (painting_id, author_id) VALUES (?, ?)";
    foreach ($authors as $author_id) {
        $stmt = mysqli_prepare($conn, $insert_authors_sql);
        mysqli_stmt_bind_param($stmt, "ii", $painting_id, $author_id);
        mysqli_stmt_execute($stmt);
    }

    // Redirect to the admin dashboard or another page
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Painting</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Add Painting</h1>
    <form method="post">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="category">Category:</label>
        <input type="text" id="category" name="category" required><br><br>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required><br><br>

        <label for="discount_price">Discount Price:</label>
        <input type="number" id="discount_price" name="discount_price" step="0.01"><br><br>

        <label for="cover">Image URL:</label>
        <input type="text" id="cover" name="cover" required><br><br>

        <label for="new_author_name">Author Name:</label>
        <input type="text" id="new_author_name" name="new_author_name"><br><br>

        <label for="new_author_bio">Author Bio:</label>
        <textarea id="new_author_bio" name="new_author_bio"></textarea><br><br>

        <button type="submit">Add Painting</button>
    </form>
</body>

</html>