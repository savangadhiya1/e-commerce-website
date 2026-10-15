<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
include('config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = floatval($_POST['original_price']);
    $discount = $conn->real_escape_string($_POST['discount']);
    $category_id = intval($_POST['category_id']);
    $image = $conn->real_escape_string($_POST['image']);
    
    $sql = "INSERT INTO products (name, description, price, original_price, discount, image, category_id) 
            VALUES ('$name', '$description', $price, $original_price, '$discount', '$image', $category_id)";
    
    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php");
        exit();
    }
}

$categories = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Dynamic Hardware Stock</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-wrap { max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); color: #333;}
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
        .btn-submit { background: #1a1a1a; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; display: block; width: 100%; }
    </style>
</head>
<body style="background:#f4f5f7;">

<div class="form-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
        <h2 style="margin: 0; font-size: 20px;">➕ Add Dynamic Hardware Asset</h2>
        <a href="admin_dashboard.php" style="color: #64748b; text-decoration: none; font-size: 13px; font-weight: 600;">← Back to Dashboard</a>
    </div>
    <form action="admin_add.php" method="POST">
        <div class="form-group">
            <label>Product Title Name</label>
            <input type="text" name="name" required placeholder="e.g. Volt BassBuds Future">
        </div>
        <div class="form-group">
            <label>Category Selection</label>
            <select name="category_id" required>
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Active Display Image Filename</label>
            <input type="text" name="image" required placeholder="e.g. volt-headphone-futuristic.png">
        </div>
        <div class="form-group">
            <label>Live Selling Price (₹)</label>
            <input type="number" step="0.01" name="price" required placeholder="2499">
        </div>
        <div class="form-group">
            <label>Standard Retail Price (₹)</label>
            <input type="number" step="0.01" name="original_price" required placeholder="4999">
        </div>
        <div class="form-group">
            <label>Target Offer Text Label</label>
            <input type="text" name="discount" value="50% off" placeholder="e.g. Limited 50% off">
        </div>
        <div class="form-group">
            <label>Hardware Description Details</label>
            <textarea name="description" rows="4"></textarea>
        </div>
        <button type="submit" class="btn-submit">Publish Item to Main Storefront</button>
    </form>
</div>

</body>
</html>