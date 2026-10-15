<?php
include('config/db.php');

// Retrieve user input message
$user_message = isset($_POST['message']) ? strtolower(trim($_POST['message'])) : '';
$bot_reply = "I'm still learning! ⚡ You can ask me about our 'headphones', 'smartwatches', 'prices', or 'shipping'.";

if (!empty($user_message)) {
    
    // 1. Handle Greetings
    if (preg_match("/\b(hi|hello|hey|help)\b/", $user_message)) {
        $bot_reply = "Hello there! Welcome to VOLT. ⚡ How can I upgrade your tech today?";
    }
    // 2. Handle Shipping & Delivery FAQs
    elseif (preg_match("/\b(shipping|delivery|time)\b/", $user_message)) {
        $bot_reply = "We offer FREE express shipping all over India! Delivery usually takes 2-4 business days.";
    }
    // 3. Handle Returns & Warranty FAQs
    elseif (preg_match("/\b(return|refund|warranty)\b/", $user_message)) {
        $bot_reply = "All Volt products come with a 1-year warranty and a 7-day easy return policy.";
    }
    // 4. Product Search & Price Lookup in Database
    else {
        // Extract keyword from user message and search database
        $search_keyword = $conn->real_escape_string($user_message);
        $sql = "SELECT name, price, discount FROM products WHERE name LIKE '%$search_keyword%' OR description LIKE '%$search_keyword%' LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $bot_reply = "Awesome choice! We have the **" . $product['name'] . "** for just ₹" . number_format($product['price']) . " (" . $product['discount'] . "). You can find it in our store!";
        } else {
            // Fallback category matching if direct product name is not found
            if (strpos($user_message, 'watch') !== false) {
                $bot_reply = "We have amazing Smartwatches starting from ₹1599. Check out our 'Smartwatches' category!";
            } elseif (strpos($user_message, 'headphone') !== false || strpos($user_message, 'earbud') !== false) {
                $bot_reply = "Looking for audio gear? Our Volt Earbuds and Headphones deliver crystal clear sound with deep bass. Browse the store to see the latest drops!";
            }
        }
    }
}

// Return JSON response for AJAX client
echo json_encode(['reply' => $bot_reply]);
?>