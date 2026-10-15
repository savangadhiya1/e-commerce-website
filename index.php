<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('includes/header.php');
include('config/db.php');

$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Dynamic query: calculates real-time total sold count and average review rating
$sql = "SELECT p.*, 
        (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE product_id = p.id) as total_sold,
        (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE product_id = p.id) as avg_rating,
        (SELECT COUNT(id) FROM reviews WHERE product_id = p.id) as review_count
        FROM products p WHERE 1=1";

if ($category_id > 0) {
    $sql .= " AND category_id = " . $category_id;
}
if (!empty($search_query)) {
    $safe_search = $conn->real_escape_string($search_query);
    $sql .= " AND (name LIKE '%$safe_search%' OR description LIKE '%$safe_search%')";
}

$result = $conn->query($sql);
$products_to_display = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products_to_display[] = $row;
    }
}
?>

<main class="container">
    <section class="category-pills-row">
        <a href="index.php" class="pill-btn <?php echo $category_id === 0 ? 'active' : ''; ?>">⚡ All Gear</a>
        <a href="index.php?category=1" class="pill-btn <?php echo $category_id === 1 ? 'active' : ''; ?>">⌚ Smartwatches</a>
        <a href="index.php?category=2" class="pill-btn <?php echo $category_id === 2 ? 'active' : ''; ?>">🎧 Headphones</a>
        <a href="index.php?category=3" class="pill-btn <?php echo $category_id === 3 ? 'active' : ''; ?>">📶 Neckbands</a>
        <a href="index.php?category=4" class="pill-btn <?php echo $category_id === 4 ? 'active' : ''; ?>">🎵 Earbuds</a>
        <a href="index.php?category=5" class="pill-btn <?php echo $category_id === 5 ? 'active' : ''; ?>">🔊 Speakers</a>
    </section>

    <div class="filter-ribbon">
        <h2 class="section-title">
            <?php echo !empty($search_query) ? 'Search Results for "' . htmlspecialchars($search_query) . '"' : 'Trending Storefront'; ?>
        </h2>
    </div>

    <div class="product-grid">
        <?php if (!empty($products_to_display)): ?>
            <?php foreach ($products_to_display as $product): ?>
                <?php 
                    $img_src = (strpos($product['image'], 'http') === 0) ? $product['image'] : 'assets/images/' . $product['image'];
                    
                    // Prepare formatted rating and sales metrics
                    $avg_rating = $product['avg_rating'] > 0 ? number_format($product['avg_rating'], 1) : 'New';
                    $total_sold = $product['total_sold'];
                ?>
                <div class="product-card">
                    <div class="img-wrap">
                        <img src="<?php echo htmlspecialchars($img_src); ?>" onerror="this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'" alt="">
                    </div>
                    
                    <div class="card-details">
                        <!-- Dynamic rating and total sales metrics -->
                        <div class="rating-bar" style="font-size: 13px; color: #bbb; margin-bottom: 8px;">
                            <span style="color: gold;">⭐ <?php echo $avg_rating; ?></span> | 
                            <span style="color: #4CAF50; font-weight: bold;">🛒 <?php echo $total_sold; ?> Sold</span>
                        </div>
                        
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <div class="price-row">
                            <span class="current-price">₹<?php echo number_format($product['price']); ?></span>
                            <span class="old-price">₹<?php echo number_format($product['original_price']); ?></span>
                            <span class="discount-tag"><?php echo htmlspecialchars($product['discount']); ?></span>
                        </div>
                        
                        <div class="button-group">
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-view-details">View Details</a>
                            <a href="cart_action.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-add-cart">Add To Cart</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products-box">
                <p>No products found.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include('includes/footer.php'); ?>