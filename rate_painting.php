<?php
session_start();
require 'db.php'; // Asigură-te că `db.php` conține conexiunea mysqli ($conn)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obține ID-ul picturii și ratingul din formular
    $painting_id = $_POST['painting_id'];
    $rating = $_POST['rating'];

    // Verifică dacă utilizatorul este conectat
    if (!isset($_SESSION['user_id'])) {
        die("You must be logged in to rate paintings.");
    }

    $user_id = $_SESSION['user_id'];

    // Inserează sau actualizează ratingul utilizatorului pentru această pictură
    $sql = "INSERT INTO ratings (user_id, painting_id, rating) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE rating = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Leagă parametrii
        mysqli_stmt_bind_param($stmt, "iiii", $user_id, $painting_id, $rating, $rating);

        // Execută interogarea
        if (mysqli_stmt_execute($stmt)) {
            // Redirecționează utilizatorul înapoi la pagina principală (index.php)
            header("Location: index.php");
            exit; // Asigură-te că scriptul nu continuă după redirecționare
        } else {
            echo "Error: Could not save your rating. " . mysqli_error($conn);
        }

        // Închide declarația pregătită
        mysqli_stmt_close($stmt);
    } else {
        die("Database query error: " . mysqli_error($conn));
    }
} else {
    die("Invalid request.");
}
