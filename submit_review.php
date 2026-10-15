<?php
session_start();
include('config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_logged_in'])) {
    $user_id = intval($_SESSION['user_id']);
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $review_text = $conn->real_escape_string($_POST['review_text']);

    // Check if user has already submitted a review for this product
    $check = $conn->query("SELECT id FROM reviews WHERE user_id = $user_id AND product_id = $product_id");
    
    if ($check->num_rows > 0) {
        // If existing review found, update rating and text
        $conn->query("UPDATE reviews SET rating = $rating, review_text = '$review_text' WHERE user_id = $user_id AND product_id = $product_id");
    } else {
        // Insert new review record
        $conn->query("INSERT INTO reviews (user_id, product_id, rating, review_text) VALUES ($user_id, $product_id, $rating, '$review_text')");
    }
    
    // Redirect back to product detail page
    header("Location: product_detail.php?id=" . $product_id);
    exit();
} else {
    // If user is not authenticated, redirect to login page
    header("Location: login.php");
    exit();
}
?>