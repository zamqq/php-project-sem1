<?php
session_start(); // Start the session

// Include the database functions
require 'db.php';

// Get the selected author from the query string (author's name)
$selectedAuthor = $_GET['author'] ?? '';

// Fetch paintings by the selected author
$filteredPaintings = get_paintings_by_author($selectedAuthor);

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paintings by <?= htmlspecialchars($selectedAuthor); ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Paintings by <?= htmlspecialchars($selectedAuthor); ?></h1>

    <div class="painting-list">
        <?php if (count($filteredPaintings) > 0): ?>
            <?php foreach ($filteredPaintings as $painting): ?>
                <div class='painting'>
                    <h3><?= htmlspecialchars($painting['title']); ?></h3>
                    <img class="image" src="<?= htmlspecialchars($painting['cover']); ?>" alt="<?= htmlspecialchars($painting['title']); ?> Cover" />
                    <p><strong>Description:</strong> <?= htmlspecialchars($painting['description']); ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($painting['category']); ?></p>
                    <?php if (isset($painting['discount_price'])): ?>
                        <p><strong>Price:</strong> <span style="text-decoration: line-through;">$<?= number_format($painting['price'], 2); ?></span>
                            <strong>Discounted Price:</strong> $<?= number_format($painting['discount_price'], 2); ?>
                        </p>
                    <?php else: ?>
                        <p><strong>Price:</strong> $<?= number_format($painting['price'], 2); ?></p>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No paintings found for this author.</p>
        <?php endif; ?>
    </div>
    <a class="back-link" href="index.php">Back to Store</a>
</body>

</html>