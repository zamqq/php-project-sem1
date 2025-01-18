<?php
// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'paintings_store'; // Change the database name here

// Establish the connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to retrieve all paintings
function get_all_paintings()
{
    global $conn;
    $sql = "SELECT id, title, description, category, price, discount_price, cover FROM paintings";
    $result = mysqli_query($conn, $sql);

    $paintings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $painting = [
            "id" => $row['id'],
            "title" => $row['title'],
            "description" => $row['description'],
            "category" => $row['category'],
            "price" => (float)$row['price'],
            "discount_price" => isset($row['discount_price']) ? (float)$row['discount_price'] : null,
            "cover" => $row['cover']
        ];
        $paintings[] = $painting;
    }
    return $paintings;
}

function get_authors_by_painting($paintingId)
{
    global $conn;
    $sql = "
        SELECT authors.name, authors.bio 
        FROM authors
        INNER JOIN painting_authors ON authors.id = painting_authors.author_id
        WHERE painting_authors.painting_id = ?
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $paintingId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $authors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $authors[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $authors;
}

// Function to retrieve paintings by category
function get_paintings_by_category($category)
{
    global $conn;
    $sql = "SELECT title, description, cover, price, discount_price, category FROM paintings WHERE category = ?"; // Changed to paintings
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $category);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $paintings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $painting = [
            "title" => $row['title'],
            "description" => $row['description'],
            "cover" => $row['cover'],
            "price" => (float)$row['price'],
            "discount_price" => isset($row['discount_price']) ? (float)$row['discount_price'] : null,
            "category" => $row['category']
        ];
        $paintings[] = $painting;
    }

    mysqli_stmt_close($stmt);
    return $paintings;
}

// Function to retrieve a single painting by title
function get_painting_by_title($title)
{
    global $conn;
    $sql = "SELECT title, description, cover, price, discount_price, category FROM paintings WHERE title = ?"; // Changed to paintings
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $title);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $painting = mysqli_fetch_assoc($result);
    if ($painting) {
        $painting['price'] = (float)$painting['price'];
        $painting['discount_price'] = isset($painting['discount_price']) ? (float)$painting['discount_price'] : null;
    }

    mysqli_stmt_close($stmt);
    return $painting;
}

function get_paintings_by_author($authorName)
{
    global $conn;
    $sql = "
        SELECT paintings.id, paintings.title, paintings.description, paintings.category, 
               paintings.price, paintings.discount_price, paintings.cover 
        FROM paintings
        INNER JOIN painting_authors ON paintings.id = painting_authors.painting_id
        INNER JOIN authors ON authors.id = painting_authors.author_id
        WHERE authors.name = ?
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $authorName);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $paintings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $paintings[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $paintings;
}


function get_all_authors()
{
    global $conn;
    $sql = "SELECT id, name FROM authors";  // Include 'id' in the query
    $result = mysqli_query($conn, $sql);

    $authors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $authors[] = $row;  // Push both id and name to the array
    }
    return $authors;
}

function get_comments_by_painting($painting_id)
{
    global $conn;
    $sql = "
        SELECT comments.id, users.username, comments.comment_text, comments.created_at 
        FROM comments 
        INNER JOIN users ON comments.user_id = users.id 
        WHERE comments.painting_id = ? 
        ORDER BY comments.created_at DESC
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $painting_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $comments;
}

function delete_comment($comment_id)
{
    global $conn;
    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $comment_id);

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $success;
}

function add_comment($painting_id, $user_id, $comment_text)
{
    global $conn;
    $sql = "INSERT INTO comments (painting_id, user_id, comment_text) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $painting_id, $user_id, $comment_text);

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $success;
}

function get_author_by_id($authorId)
{
    global $conn;
    $sql = "SELECT name FROM authors WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $authorId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $author = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $author;
}

function get_average_rating($painting_id)
{
    global $conn; // Utilizează conexiunea mysqli globală

    $sql = "SELECT AVG(rating) AS average_rating FROM ratings WHERE painting_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Leagă parametrii și execută interogarea
        mysqli_stmt_bind_param($stmt, "i", $painting_id);
        mysqli_stmt_execute($stmt);

        // Obține rezultatul
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        // Returnează ratingul mediu sau 0 dacă nu există
        return $row['average_rating'] ?? 0;
    } else {
        // Aruncă o eroare dacă interogarea eșuează
        die("Database query error: " . mysqli_error($conn));
    }
}

// Function to retrieve a painting by its ID
function get_painting_by_id($painting_id)
{
    global $conn;
    $sql = "SELECT id, title, description, category, price, discount_price, cover 
            FROM paintings WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $painting_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $painting = mysqli_fetch_assoc($result);
    if ($painting) {
        $painting['price'] = (float)$painting['price'];
        $painting['discount_price'] = $painting['discount_price'] !== null ? (float)$painting['discount_price'] : null;
    }

    mysqli_stmt_close($stmt);
    return $painting;
}

// Function to update a painting's details by title
function update_painting($title, $newData)
{
    global $conn;

    // Check if a new cover URL is provided, otherwise retain the old cover
    $coverPath = isset($newData['cover']) ? $newData['cover'] : '';

    // Prepare update statement
    $sql = "UPDATE paintings SET title = ?, description = ?, category = ?, price = ?, cover = ? WHERE title = ?"; // Changed to paintings
    $stmt = mysqli_prepare($conn, $sql);

    // Bind the parameters to the prepared statement
    mysqli_stmt_bind_param(
        $stmt,
        "sssdss", // String, String, String, Double, String, String
        $newData['title'],
        $newData['description'],
        $newData['category'],
        $newData['price'],
        $coverPath,
        $title
    );


    // Execute the statement and close the statement
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $success;
}
