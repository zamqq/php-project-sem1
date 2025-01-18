<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require 'db.php';

// Fetch painting details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['painting_id'])) {
    $painting_id = $_GET['painting_id'];
    $painting = get_painting_by_id($painting_id);
    $all_authors = get_all_authors();
    $painting_authors = get_authors_by_painting($painting_id);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update painting details in the database
    $painting_id = $_POST['painting_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $discount_price = $_POST['discount_price'];
    $cover = $_POST['cover'];
    $authors = $_POST['authors'];

    // Update painting details
    $sql = "UPDATE paintings SET title = ?, description = ?, category = ?, price = ?, discount_price = ?, cover = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssddsi", $title, $description, $category, $price, $discount_price, $cover, $painting_id);
    mysqli_stmt_execute($stmt);

    // Update authors
    $delete_authors_sql = "DELETE FROM painting_authors WHERE painting_id = ?";
    $stmt = mysqli_prepare($conn, $delete_authors_sql);
    mysqli_stmt_bind_param($stmt, "i", $painting_id);
    mysqli_stmt_execute($stmt);

    $insert_authors_sql = "INSERT INTO painting_authors (painting_id, author_id) VALUES (?, ?)";
    foreach ($authors as $author_id) {
        $stmt = mysqli_prepare($conn, $insert_authors_sql);
        mysqli_stmt_bind_param($stmt, "ii", $painting_id, $author_id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: admin_dashboard.php");
    exit;
} else {
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Painting</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Edit Painting</h1>

    <?php if (!empty($painting)): ?>
        <form method="post">
            <input type="hidden" name="painting_id" value="<?= htmlspecialchars($painting['id']); ?>">

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($painting['title']); ?>" required><br><br>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($painting['description']); ?></textarea><br><br>

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($painting['category']); ?>" required><br><br>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($painting['price']); ?>" required><br><br>

            <label for="discount_price">Discount Price:</label>
            <input type="number" id="discount_price" name="discount_price" step="0.01" value="<?= htmlspecialchars($painting['discount_price']); ?>"><br><br>

            <label for="cover">Image URL:</label>
            <input type="text" id="cover" name="cover" value="<?= htmlspecialchars($painting['cover']); ?>" required><br><br>

            <label for="authors">Authors:</label>
            <?php
            // Ensure that $painting_authors is an array of author IDs
            $selected_author_ids = array_column($painting_authors, 'id');
            ?>

            <select id="authors" name="authors[]" multiple>
                <?php foreach ($all_authors as $author): ?>
                    <option value="<?= $author['id']; ?>" <?= in_array($author['id'], $selected_author_ids) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($author['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <button type="submit">Save Changes</button>
        </form>
    <?php else: ?>
        <p>Painting not found.</p>
    <?php endif; ?>

</body>

</html>