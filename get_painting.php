<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Include the database functions
require 'db.php';

// Get the painting title from the query string
$titleToEdit = $_GET['title'] ?? '';
$paintingToEdit = get_painting_by_title($titleToEdit);

if (!$paintingToEdit) {
    die("Painting not found.");
}

// Handle form submission for editing the painting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create an array to hold updated data
    $updatedData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'category' => $_POST['category'],
        'price' => (float)$_POST['price'],
    ];

    // Check if a new cover URL is provided
    if (!empty($_POST['cover_url']) && filter_var($_POST['cover_url'], FILTER_VALIDATE_URL) && strpos($_POST['cover_url'], 'https://') === 0) {
        // Assign the new cover URL if it's valid
        $updatedData['cover'] = $_POST['cover_url']; // Set cover to the provided HTTPS URL
    } else {
        // Keep the existing cover if no valid URL is provided
        $updatedData['cover'] = $paintingToEdit['cover']; // Retain the current cover image URL
    }

    // Check for the discount price
    if (!empty($_POST['discount_price'])) {
        $updatedData['discount_price'] = (float)$_POST['discount_price'];
    } else {
        $updatedData['discount_price'] = null; // Keep the discount_price as null if not provided
    }

    // Update the painting in the database
    $updateSuccess = update_painting($titleToEdit, $updatedData);

    if ($updateSuccess) {
        header("Location: admin_dashboard.php"); // Redirect to the admin dashboard after successful update
        exit;
    } else {
        echo "Error updating the painting.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Painting - <?= htmlspecialchars($paintingToEdit['title']); ?></title>
</head>

<body>
    <h1>Edit Painting - <?= htmlspecialchars($paintingToEdit['title']); ?></h1>
    <form method="post">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($paintingToEdit['title']); ?>" required><br><br>

        <label for="description">Description:</label>
        <textarea name="description" id="description" required><?= htmlspecialchars($paintingToEdit['description']); ?></textarea><br><br>

        <label for="category">Category:</label>
        <input type="text" name="category" id="category" value="<?= htmlspecialchars($paintingToEdit['category']); ?>" required><br><br>

        <label for="price">Price:</label>
        <input type="number" name="price" id="price" step="0.01" value="<?= htmlspecialchars($paintingToEdit['price']); ?>" required><br><br>

        <label for="discount_price">Discount Price:</label>
        <input type="number" name="discount_price" id="discount_price" step="0.01" value="<?= htmlspecialchars($paintingToEdit['discount_price'] ?? ''); ?>" placeholder="Enter discounted price if available"><br><br>

        <label for="cover_url">Cover Image URL (HTTPS):</label>
        <input type="text" name="cover_url" id="cover_url" value="<?= htmlspecialchars($paintingToEdit['cover']); ?>" placeholder="https://example.com/cover.jpg"><br><br>

        <button type="submit">Save Changes</button>
    </form>
</body>

</html>