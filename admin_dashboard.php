<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Include the database functions
require 'db.php';

// Fetch all paintings from the database
$paintings = get_all_paintings();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>Admin Dashboard</h1>
        <a href="add_painting.php">Add new Painting</a>
        <a href="logout.php">Log out</a>
    </div>

    <h2 style="text-align: center;">Manage Paintings</h2>
    <div class="painting-list">
        <?php foreach ($paintings as $painting): ?>
            <div class="painting">
                <h3><?= htmlspecialchars($painting['title']); ?></h3>
                <img src="<?= htmlspecialchars($painting['cover']); ?>" alt="<?= htmlspecialchars($painting['title']); ?> Cover" />
                <p><strong>Description:</strong> <?= htmlspecialchars($painting['description']); ?></p>
                <p><strong>Category:</strong> <?= htmlspecialchars($painting['category']); ?></p>
                <?php if (isset($painting['discount_price']) && $painting['discount_price'] !== null): ?>
                    <p>
                        <strong>Price:</strong>
                        <span style="text-decoration: line-through;">$<?= number_format($painting['price'], 2); ?></span>
                        <strong>Discounted Price:</strong> $<?= number_format($painting['discount_price'], 2); ?>
                    </p>
                <?php else: ?>
                    <p><strong>Price:</strong> $<?= number_format($painting['price'], 2); ?></p>
                <?php endif; ?>

                <!-- Edit button -->
                <form action="edit_painting.php" method="get">
                    <input type="hidden" name="painting_id" value="<?= $painting['id']; ?>">
                    <button type="submit">Edit</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>