<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('includes/header.php');
include('config/db.php'); // Database connection

// Session variables handling
$cart_session = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$discount_pct = isset($_SESSION['discount']) ? $_SESSION['discount'] : 0;
$coupon_msg = isset($_SESSION['coupon_msg']) ? $_SESSION['coupon_msg'] : '';
unset($_SESSION['coupon_msg']); // Clear message on reload

$checkout_success = isset($_SESSION['checkout_success']) ? $_SESSION['checkout_success'] : false;
unset($_SESSION['checkout_success']); // Clear message on reload

// 1. Fetch current cart items from database
$cart_display = [];
$subtotal = 0;

if (!empty($cart_session)) {
    foreach ($cart_session as $item) {
        $product_id = intval($item['id']);
        $quantity = intval($item['quantity']);
        
        $sql = "SELECT * FROM products WHERE id = $product_id LIMIT 1";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $cart_display[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
            $subtotal += ($product['price'] * $quantity);
        } else {
            $cart_display[] = [
                'id' => $product_id,
                'name' => 'Volt Premium Gear (ID: ' . $product_id . ')',
                'price' => 2499,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500',
                'quantity' => $quantity
            ];
            $subtotal += (2499 * $quantity);
        }
    }
}

// Round calculation to avoid rounding discrepancies in billing
$discount_amount = round(($subtotal * $discount_pct) / 100);
$total = $subtotal - $discount_amount;

// 2. Fetch user purchase history from database
$order_history = [];
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $history_sql = "SELECT o.id as order_id, o.total_amount, o.order_date, 
                           oi.quantity, oi.price as item_price, 
                           p.name as product_name, p.image as product_image
                    FROM orders o
                    JOIN order_items oi ON o.id = oi.order_id
                    JOIN products p ON oi.product_id = p.id
                    WHERE o.user_id = $uid
                    ORDER BY o.order_date DESC";
    $hist_res = $conn->query($history_sql);
    if ($hist_res && $hist_res->num_rows > 0) {
        while ($h_row = $hist_res->fetch_assoc()) {
            $order_history[] = $h_row;
        }
    }
}
?>

<!-- Styling -->
<style>
    .cart-container { max-width: 1100px; margin: 40px auto 20px auto; padding: 20px; font-family: sans-serif; color: #fff; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
    @media (max-width: 768px) { .cart-container { grid-template-columns: 1fr; } }
    .cart-items-box { background: #121212; padding: 20px; border-radius: 8px; border: 1px solid #222; }
    .cart-item { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #222; padding: 15px 0; }
    .cart-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 6px; }
    .item-info { flex: 2; margin-left: 20px; }
    .item-info h4 { margin: 0 0 5px 0; color: #fff; }
    .item-info p { margin: 0; color: #ff4747; font-weight: bold; }
    .qty-form { display: flex; align-items: center; }
    .qty-input { width: 50px; padding: 5px; background: #222; border: 1px solid #333; color: #fff; text-align: center; border-radius: 4px; }
    .remove-btn { color: #ff4747; text-decoration: none; margin-left: 15px; font-size: 14px; }
    .summary-box { background: #1c1c1c; padding: 20px; border-radius: 8px; border: 1px solid #333; height: fit-content; }
    .summary-box h3 { border-bottom: 1px solid #333; padding-bottom: 10px; color: #ff4747; }
    .summary-row { display: flex; justify-content: space-between; margin: 15px 0; color: #bbb; }
    .total-row { display: flex; justify-content: space-between; margin: 20px 0 10px 0; font-size: 18px; font-weight: bold; color: #fff; border-top: 1px dashed #444; padding-top: 15px; }
    .coupon-box input { width: 70%; padding: 8px; background: #121212; border: 1px solid #333; color: white; border-radius: 4px; }
    .coupon-box button { width: 25%; padding: 8px; background: #333; color: white; border: none; cursor: pointer; border-radius: 4px; }
    .coupon-box button:hover { background: #444; }
    .checkout-btn { width: 100%; padding: 12px; background: #ff4747; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.2s; }
    .checkout-btn:hover { background: #e03e3e; }
    .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    .alert-info { background: #222; color: #ff4747; border: 1px solid #333; }
    .alert-success { background: #1b5e20; color: #fff; border: 1px solid #2e7d32; text-align: center; grid-column: 1/-1; padding: 30px; }

    /* History Table Styles */
    .history-container { max-width: 1100px; margin: 20px auto 60px auto; padding: 20px; font-family: sans-serif; color: #fff; }
    .history-box { background: #121212; border: 1px solid #222; border-radius: 8px; padding: 25px; }
    .history-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .history-table th { background: #1c1c1c; color: #ff4747; text-align: left; padding: 12px; border: 1px solid #2a2a2a; }
    .history-table td { padding: 12px; border: 1px solid #222; color: #ccc; vertical-align: middle; }
    .history-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
</style>

<div class="cart-container">
    
    <!-- If Checkout Transaction Process Complete Message -->
    <?php if ($checkout_success): ?>
        <div class="alert alert-success">
            <h2>⚡ Order Transacted Successfully!</h2>
            <p>Thank you for choosing Volt. Your product purchase has been recorded below in your purchase history!</p>
            <a href="index.php" style="display: inline-block; margin-top: 15px; text-decoration: none; padding: 10px 20px; background: #ff4747; color: white; border-radius: 4px; font-weight:bold;">Continue Shopping</a>
        </div>
    <?php endif; ?>

    <!-- Current Cart Items Column -->
    <div class="cart-items-box">
        <h2 style="margin-top:0; color:#fff;">🛒 Your Current Cart</h2>
        
        <?php if (empty($cart_display)): ?>
            <p style="color:#bbb; padding: 20px 0;">Your shopping cart matrix is empty.</p>
            <a href="index.php" style="color:#ff4747; text-decoration:none;">← Return to Storefront</a>
        <?php else: ?>
            <?php foreach ($cart_display as $item): 
                $img_src = (strpos($item['image'], 'http') === 0) ? $item['image'] : 'assets/images/' . $item['image'];
            ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($img_src); ?>" onerror="this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'" alt="">
                    
                    <div class="item-info">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p>₹<?php echo number_format($item['price']); ?></p>
                    </div>
                    
                    <!-- Quantity modifiers -->
                    <form action="cart_action.php?action=update" method="POST" class="qty-form">
                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                        <input type="number" name="quantity" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" onchange="this.form.submit()">
                    </form>
                    
                    <a href="cart_action.php?action=remove&id=<?php echo $item['id']; ?>" class="remove-btn">✕ Remove</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Order Summary Sidebar -->
    <div class="summary-box">
        <h3>Order Summary</h3>
        
        <?php if ($coupon_msg): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($coupon_msg); ?></div>
        <?php endif; ?>

        <div class="summary-row">
            <span>Subtotal</span>
            <span>₹<?php echo number_format($subtotal); ?></span>
        </div>
        
        <div class="summary-row">
            <span>Discount (<?php echo $discount_pct; ?>%)</span>
            <span style="color:#4CAF50;">- ₹<?php echo number_format($discount_amount); ?></span>
        </div>
        
        <div class="summary-row">
            <span>Shipping</span>
            <span style="color:#4CAF50;">FREE</span>
        </div>
        
        <!-- Promo Code Box -->
        <form action="cart_action.php?action=coupon" method="POST" class="coupon-box" style="margin: 20px 0;">
            <input type="text" name="coupon_code" placeholder="PROMO CODE" required>
            <button type="submit">Apply</button>
        </form>

        <div class="total-row">
            <span>Total Payload</span>
            <span style="color: #ff4747;">₹<?php echo number_format($total); ?></span>
        </div>

        <?php if (!empty($cart_display)): ?>
            <form action="cart_action.php?action=checkout" method="POST">
                <button type="submit" class="checkout-btn">SECURE PURCHASE ⚡</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- ================= USER PURCHASE HISTORY SECTION ================= -->
<div class="history-container">
    <div class="history-box">
        <h3 style="margin-top: 0; color: #ff4747; border-bottom: 1px solid #222; padding-bottom: 10px;">
            📦 My Purchased Products History
        </h3>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <p style="color: #bbb;">Please <a href="login.php" style="color: #ff4747;">Login</a> to view your purchase history.</p>
        <?php elseif (empty($order_history)): ?>
            <p style="color: #888;">No orders purchased yet.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Purchase Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_history as $order): 
                            $h_img = (strpos($order['product_image'], 'http') === 0) ? $order['product_image'] : 'assets/images/' . $order['product_image'];
                        ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($h_img); ?>" onerror="this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'" class="history-thumb">
                                </td>
                                <td style="color: #fff; font-weight: bold;"><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td><?php echo $order['quantity']; ?>x</td>
                                <td style="color: #4CAF50; font-weight: bold;">₹<?php echo number_format($order['item_price']); ?></td>
                                <td><?php echo date("d M, Y h:i A", strtotime($order['order_date'])); ?></td>
                                <td><span style="background: #1b5e20; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Purchased ✔</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>