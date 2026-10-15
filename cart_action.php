<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================= LOGIN INTERCEPT =================
// If user attempts to "Add" item and is not logged in, redirect to login.php
if (isset($_GET['action']) && $_GET['action'] == 'add' && !isset($_SESSION['user_logged_in'])) {
    $_SESSION['pending_cart_id'] = $_GET['id']; // Preserve target product ID
    header("Location: login.php");
    exit();
}
// ===================================================

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. ADD ITEM
if ($action == 'add' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $item_exists = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['quantity'] += 1;
            $item_exists = true;
            break;
        }
    }
    if (!$item_exists) {
        $_SESSION['cart'][] = array('id' => $product_id, 'quantity' => 1);
    }
    header("Location: cart.php");
    exit();
}

// 2. REMOVE ITEM
if ($action == 'remove' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $product_id) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

// 3. UPDATE QUANTITY
if ($action == 'update' && isset($_POST['id']) && isset($_POST['quantity'])) {
    $product_id = intval($_POST['id']);
    $new_quantity = intval($_POST['quantity']);
    if ($new_quantity > 0) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] = $new_quantity;
                break;
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// 4. APPLY COUPON
if ($action == 'coupon' && isset($_POST['coupon_code'])) {
    $code = strtoupper(trim($_POST['coupon_code']));
    if ($code === 'VOLT20') {
        $_SESSION['discount'] = 20;
        $_SESSION['coupon_msg'] = "🎉 20% Discount added.";
    } elseif ($code === 'VOLT50') {
        $_SESSION['discount'] = 50;
        $_SESSION['coupon_msg'] = "⚡ Flash Sale 50% Discount added.";
    } else {
        $_SESSION['discount'] = 0;
        $_SESSION['coupon_msg'] = "❌ Invalid coupon.";
    }
    header("Location: cart.php");
    exit();
}

// ================= 5. CHECKOUT (Complete Order & Payment) =================
if ($action == 'checkout') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    include('config/db.php');
    $user_id = intval($_SESSION['user_id']);
    $subtotal = 0;

    // 1. Calculate order subtotal
    foreach ($_SESSION['cart'] as $item) {
        $pid = intval($item['id']);
        $qty = intval($item['quantity']);
        $res = $conn->query("SELECT price FROM products WHERE id=$pid");
        if ($row = $res->fetch_assoc()) {
            $subtotal += ($row['price'] * $qty);
        }
    }

    // 2. Calculate coupon discount and round total
    $discount_pct = isset($_SESSION['discount']) ? $_SESSION['discount'] : 0;
    $discount_amount = round(($subtotal * $discount_pct) / 100);
    $final_total = $subtotal - $discount_amount;

    // 3. Save final total in orders table
    $conn->query("INSERT INTO orders (user_id, total_amount) VALUES ($user_id, $final_total)");
    $order_id = $conn->insert_id;

    // 4. Save order items with itemized prices
    foreach ($_SESSION['cart'] as $item) {
        $pid = intval($item['id']);
        $qty = intval($item['quantity']);
        $res = $conn->query("SELECT price FROM products WHERE id=$pid");
        if ($row = $res->fetch_assoc()) {
            $original_price = $row['price'];
            
            // Deduct rounded discount from single product price as well
            $item_discount = round(($original_price * $discount_pct) / 100);
            $discounted_price = $original_price - $item_discount;
            
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $pid, $qty, $discounted_price)");
        }
    }

    // 5. Clear cart session state after purchase completion
    unset($_SESSION['cart']);
    unset($_SESSION['discount']);
    unset($_SESSION['coupon_msg']);
    $_SESSION['checkout_success'] = true;
    
    header("Location: cart.php");
    exit();
}
?>