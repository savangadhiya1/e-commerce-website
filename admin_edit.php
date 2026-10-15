<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
include('config/db.php');

$id = intval($_GET['id']);
$product_res = $conn->query("SELECT * FROM products WHERE id = $id");
$product = $product_res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = floatval($_POST['original_price']);
    $discount = $conn->real_escape_string($_POST['discount']);
    $category_id = intval($_POST['category_id']);
    $image = $conn->real_escape_string($_POST['image']);
    
    $sql = "UPDATE products SET 
            name='$name', description='$description', price=$price, 
            original_price=$original_price, discount='$discount', 
            image='$image', category_id=$category_id 
            WHERE id=$id";
            
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
    <title>Modify Product Parameters & Offers</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-wrap { max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); color:#333; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
        .btn-submit { background: #0984e3; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; display: block; width: 100%; }
        .offer-highlight { background: #fff9db; border: 1px dashed #f59f00; padding: 15px; border-radius: 6px; }
    </style>
</head>
<body style="background:#f4f5f7;">

<div class="form-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
        <h2 style="margin: 0; font-size: 20px;">🛠️ Edit Item & Apply Offers</h2>
        <a href="admin_dashboard.php" style="color: #64748b; text-decoration: none; font-size: 13px; font-weight: 600;">← Back to Dashboard</a>
    </div>
    <form action="admin_edit.php?id=<?php echo $id; ?>" method="POST">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Category Assignment</label>
            <select name="category_id" required>
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Product Image Resource Filename</label>
            <input type="text" name="image" value="<?php echo htmlspecialchars($product['image']); ?>" required>
        </div>
        
        <!-- Target Offer Operations Block Box -->
        <div class="offer-highlight">
            <h4>🎯 Product-Specific Offer Control Engine</h4>
            <div class="form-group" style="margin-top: 10px;">
                <label>Offer Text (e.g., 60% off, Lightning Deal)</label>
                <input type="text" name="discount" value="<?php echo htmlspecialchars($product['discount']); ?>">
            </div>
            <div class="form-group">
                <label>Current Sale Price (₹)</label>
                <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Standard Base Retail Price (₹)</label>
                <input type="number" step="0.01" name="original_price" value="<?php echo $product['original_price']; ?>" required>
            </div>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label>Hardware Specifications Details Text</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        <button type="submit" class="btn-submit">Apply Changes & Save Settings</button>
    </form>
</div>

</body>
</html>