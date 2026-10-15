<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('includes/header.php');
include('config/db.php');

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

// Query product details along with real-time sales volume and customer reviews from database
if ($product_id > 0) {
    $sql = "SELECT p.*, 
            (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE product_id = p.id) as total_sold,
            (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE product_id = p.id) as avg_rating,
            (SELECT COUNT(id) FROM reviews WHERE product_id = p.id) as review_count
            FROM products p WHERE p.id = $product_id LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
}

if (!$product) {
    echo "<h2 style='color:white; text-align:center;'>Product not found!</h2>";
    exit();
}

$img_src = (strpos($product['image'], 'http') === 0) ? $product['image'] : 'assets/images/' . $product['image'];
$avg_rating = number_format($product['avg_rating'], 1);
$total_sold = $product['total_sold'];
?>

<main class="container" style="padding: 40px 20px;">
    <div style="display: flex; flex-wrap: wrap; gap: 40px; background: #1c1c1c; padding: 30px; border-radius: 10px; border: 1px solid #333;">
        
        <!-- Product Image -->
        <div style="flex: 1; min-width: 300px; text-align: center;">
            <img src="<?php echo htmlspecialchars($img_src); ?>" style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);" alt="Product Image">
        </div>
        
        <!-- Product Details -->
        <div style="flex: 1; min-width: 300px; color: #fff;">
            <h1 style="color: #ff4747; margin-top: 0;"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <!-- Real-time Stats -->
            <div style="margin-bottom: 15px; background: #222; display: inline-block; padding: 8px 15px; border-radius: 20px; font-size: 14px; border: 1px solid #444;">
                <span style="color: gold;">⭐ <?php echo $avg_rating; ?> (<?php echo $product['review_count']; ?> Reviews)</span> | 
                <span style="color: #4CAF50; font-weight: bold;">🛒 <?php echo $total_sold; ?>+ Sold Recent</span>
            </div>

            <p style="color: #bbb; font-size: 16px; line-height: 1.6;"><?php echo htmlspecialchars($product['description']); ?></p>
            
            <div style="margin: 20px 0; font-size: 24px;">
                <span style="color: #fff; font-weight: bold;">₹<?php echo number_format($product['price']); ?></span>
                <span style="color: #888; text-decoration: line-through; font-size: 18px; margin-left: 10px;">₹<?php echo number_format($product['original_price']); ?></span>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="cart_action.php?action=add&id=<?php echo $product['id']; ?>" style="background: #ff4747; color: white; padding: 15px 30px; font-size: 16px; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: bold;">🛒 Add To Cart</a>
            </div>

            <!-- ================= USER FEEDBACK / REVIEW SECTION ================= -->
            <hr style="border-color: #333; margin: 40px 0 20px 0;">
            <h3 style="color: #fff;">Leave a Review</h3>
            
            <?php if(isset($_SESSION['user_logged_in'])): ?>
                <form action="submit_review.php" method="POST" style="background: #121212; padding: 20px; border-radius: 8px; border: 1px solid #333;">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <label style="color: #bbb; display: block; margin-bottom: 5px;">Rate this product:</label>
                    <select name="rating" required style="width: 100%; padding: 10px; margin-bottom: 15px; background: #222; color: #fff; border: 1px solid #444; border-radius: 4px;">
                        <option value="5">⭐⭐⭐⭐⭐ (5/5) - Excellent</option>
                        <option value="4">⭐⭐⭐⭐ (4/5) - Very Good</option>
                        <option value="3">⭐⭐⭐ (3/5) - Average</option>
                        <option value="2">⭐⭐ (2/5) - Poor</option>
                        <option value="1">⭐ (1/5) - Terrible</option>
                    </select>

                    <label style="color: #bbb; display: block; margin-bottom: 5px;">Your Feedback:</label>
                    <textarea name="review_text" rows="3" placeholder="Write your experience here..." style="width: 100%; padding: 10px; background: #222; color: #fff; border: 1px solid #444; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box;"></textarea>

                    <button type="submit" style="background: #333; color: white; padding: 10px 20px; border: 1px solid #555; border-radius: 4px; cursor: pointer; font-weight: bold;">Submit Review</button>
                </form>
            <?php else: ?>
                <div style="background: #121212; padding: 20px; border-radius: 8px; border: 1px solid #333; text-align: center;">
                    <p style="color: #bbb; margin-bottom: 15px;">Please log in to leave a review and rating.</p>
                    <a href="login.php" style="background: #ff4747; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block;">Login to Review</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>