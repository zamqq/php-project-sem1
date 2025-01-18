<?php
session_start(); // Start the session

// Include the database functions
require 'db.php';

// Fetch all paintings and their authors
$paintings = get_all_paintings();
$authors = get_all_authors();

// Prepare data for the chart
$paintingTitles = [];
$paintingRatings = [];

foreach ($paintings as $painting) {
  $paintingTitles[] = $painting['title']; // Add the painting title to the array
  $paintingRatings[] = get_average_rating($painting['id']); // Fetch and add the average rating
}

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Helper function to display comments and replies
function display_comments($comments, $parent_comment_id = null, $replying_to = null, $painting_id)
{
  foreach ($comments as $comment) {
    if ($comment['parent_comment_id'] === $parent_comment_id) {
      echo "<div class='comment'>";
      echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['comment_text']) . "</p>";

      // If a user is replying, show the reply form
      if (isset($_SESSION['user_id']) && $replying_to === $comment['id']) {
        echo "<form method='post' action='add_reply.php'>
                        <input type='hidden' name='parent_comment_id' value='" . $comment['id'] . "'>
                        <input type='hidden' name='painting_id' value='" . $painting_id . "'>
                        <textarea name='reply_text' placeholder='Write your reply here...'></textarea>
                        <button type='submit'>Reply</button>
                      </form>";
      } else {
        // Display a "Reply" button to trigger the reply form display
        if (isset($_SESSION['user_id'])) {
          echo "<form method='get' action=''>
                            <input type='hidden' name='replying_to' value='" . $comment['id'] . "'>
                            <button type='submit'>Reply</button>
                          </form>";
        }
      }

      // Recursively display replies to this comment
      display_comments($comments, $comment['id'], $replying_to, $painting_id);

      echo "</div>"; // Close comment div
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Paintings Store</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <!-- Navbar -->
  <div class="navbar">
    <div class="logo">
      <a href="index.php">Paintings Store</a>
    </div>
    <div class="filters">
      <!-- Filter by Category -->
      <form action="category.php" method="get">
        <label for="category" style="color: white;">Category:</label>
        <select name="category" id="category">
          <option value="Landscapes">Landscapes</option>
          <option value="Portraits">Portraits</option>
          <option value="Abstract">Abstract</option>
          <option value="Still Life">Still Life</option>
        </select>
        <button type="submit">Go</button>
      </form>

      <!-- Filter by Author -->
      <form action="author.php" method="get">
        <label for="author" style="color: white;">Author:</label>
        <select name="author" id="author">
          <?php foreach ($authors as $author): ?>
            <option value="<?= htmlspecialchars($author['name']); ?>"><?= htmlspecialchars($author['name']); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Go</button>
      </form>
    </div>

    <!-- Admin Login -->
    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
      <a href="logout.php">Log out</a>
    <?php else: ?>
      <a href="login.php">Log in</a>
    <?php endif; ?>
  </div>

  <!-- Featured Paintings -->
  <h2>Our Featured Paintings</h2>
  <div class="painting-list">
    <?php foreach ($paintings as $painting): ?>
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

        <p><strong>Authors:</strong>
          <?php
          $paintingAuthors = get_authors_by_painting($painting['id']);
          foreach ($paintingAuthors as $author): ?>
            <?= htmlspecialchars($author['name']); ?> - <?= htmlspecialchars($author['bio']); ?>
          <?php endforeach; ?>
        </p>
        <!-- Comment Section -->
        <h4>Comments</h4>
        <div class="comments">
          <?php
          $painting_id = $painting['id'];
          $query = "SELECT c.id, c.comment_text, u.username, c.parent_comment_id
              FROM comments c 
              INNER JOIN users u ON c.user_id = u.id 
              WHERE c.painting_id = ? 
              ORDER BY c.id ASC";  // Reverting to ordering by id
          $stmt = mysqli_prepare($conn, $query);
          mysqli_stmt_bind_param($stmt, 'i', $painting_id);
          mysqli_stmt_execute($stmt);
          $result = mysqli_stmt_get_result($stmt);
          $comments = mysqli_fetch_all($result, MYSQLI_ASSOC);

          // Get the 'replying_to' parameter from the URL
          $replying_to = isset($_GET['replying_to']) ? (int)$_GET['replying_to'] : null;

          // Display comments and their replies
          display_comments($comments, null, $replying_to, $painting_id);
          ?>
        </div>

        <!-- Comment Submission Form -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <form method="post" action="add_comment.php">
            <input type="hidden" name="painting_id" value="<?= htmlspecialchars($painting['id']); ?>">
            <textarea name="comment_text" placeholder="Write your comment here..." required></textarea>
            <button type="submit">Add Comment</button>
          </form>
        <?php else: ?>
          <p>Please <a href="login.php">log in</a> to post a comment.</p>
        <?php endif; ?>
        <p><strong>Average Rating:</strong>
          <?= number_format(get_average_rating($painting['id']), 1); ?> / 5
        </p>
        <?php if (isset($_SESSION['user_id'])): ?>
          <form method="post" action="rate_painting.php">
            <input type="hidden" name="painting_id" value="<?= $painting['id']; ?>">
            <label for="rating">Rate this painting:</label>
            <select name="rating" id="rating">
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
            <button type="submit">Submit Rating</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Chart Section -->
  <h2>Painting Ratings Chart</h2>
  <div style="width: 80%; margin: 20px auto;">
    <canvas id="paintingChart"></canvas>
  </div>

  <script>
    // Pass PHP data to JavaScript
    const paintingTitles = <?= json_encode($paintingTitles); ?>;
    const paintingRatings = <?= json_encode($paintingRatings); ?>;

    // Create the bar chart
    const ctx = document.getElementById('paintingChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: paintingTitles, // Painting titles on the x-axis
        datasets: [{
          label: 'Average Rating',
          data: paintingRatings, // Ratings on the y-axis
          backgroundColor: 'rgba(75, 192, 192, 0.5)', // Bar color
          borderColor: 'rgba(75, 192, 192, 1)', // Border color
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: {
          x: {
            title: {
              display: true,
              text: 'Paintings'
            }
          },
          y: {
            title: {
              display: true,
              text: 'Average Rating'
            },
            beginAtZero: true,
            max: 5,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  </script>
</body>

</html>