<?php
// Include the database functions
require 'db.php';

// Get the selected category from the query string
$selectedCategory = $_GET['category'] ?? '';

// Fetch paintings by category using the database function
$filteredPaintings = get_paintings_by_category($selectedCategory);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paintings in <?= htmlspecialchars($selectedCategory); ?> Category</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="navbar">
        <h1>Paintings in <?= htmlspecialchars($selectedCategory); ?> Category</h1>
    </div>
    <div class="painting-list">
        <?php if (count($filteredPaintings) > 0): ?>
            <?php foreach ($filteredPaintings as $painting): ?>
                <div class="painting">
                    <h3><?= htmlspecialchars($painting['title']); ?></h3>
                    <img src="<?= htmlspecialchars($painting['cover']); ?>" alt="<?= htmlspecialchars($painting['title']); ?> Cover" />
                    <p><strong>Description:</strong> <?= htmlspecialchars($painting['description']); ?></p>
                    <p><strong>Price:</strong> $<?= number_format($painting['price'], 2); ?></p>
                    <?php if (isset($painting['discount_price']) && $painting['discount_price'] !== null): ?>
                        <p><strong>Discounted Price:</strong> $<?= number_format($painting['discount_price'], 2); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No paintings found in this category.</p>
        <?php endif; ?>
    </div>

    <a class="back-link" href="index.php">Back to Store</a>
</body>

</html>