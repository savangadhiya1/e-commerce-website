<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/db.php');

// Force Authorization
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Delete with Safety Check
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: admin_dashboard.php?msg=deleted");
        } else {
            header("Location: admin_dashboard.php?msg=error");
        }
        $stmt->close();
    } else {
        $conn->query("DELETE FROM products WHERE id = $id");
        header("Location: admin_dashboard.php?msg=deleted");
    }
    exit();
}

// Fetch Key Performance Metrics (KPIs)
$total_products = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'] ?? 0;
$total_revenue = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as s FROM orders")->fetch_assoc()['s'] ?? 0;

// Fetch Categories for Filtering
$categories_res = $conn->query("SELECT * FROM categories ORDER BY id ASC");
$categories_list = [];
if ($categories_res && $categories_res->num_rows > 0) {
    while ($cat = $categories_res->fetch_assoc()) {
        $categories_list[] = $cat;
    }
}

// Fetch Products with Category Name, Sales Count & Average Rating
$products_sql = "SELECT p.*, c.name as cat_name,
                 (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE product_id = p.id) as total_sold,
                 (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE product_id = p.id) as avg_rating,
                 (SELECT COUNT(id) FROM reviews WHERE product_id = p.id) as review_count
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 ORDER BY p.id DESC";
$result = $conn->query($products_sql);

// Fetch 5 Most Recent Orders for Admin situational awareness
$recent_orders_sql = "SELECT o.id, o.total_amount, o.order_date, u.username, u.email 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      ORDER BY o.order_date DESC LIMIT 5";
$recent_orders = $conn->query($recent_orders_sql);

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volt | Admin Control Center</title>
    <!-- Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --bg-base: #0b0c10;
            --bg-surface: #13141c;
            --bg-card: #181924;
            --bg-card-hover: #1f202e;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-focus: rgba(255, 71, 71, 0.5);
            --volt-red: #ff4747;
            --volt-red-hover: #ff2a2a;
            --volt-red-glow: rgba(255, 71, 71, 0.25);
            --accent-green: #10b981;
            --accent-green-bg: rgba(16, 185, 129, 0.12);
            --accent-blue: #3b82f6;
            --accent-blue-bg: rgba(59, 130, 246, 0.12);
            --accent-amber: #f59e0b;
            --accent-amber-bg: rgba(245, 158, 11, 0.12);
            --accent-purple: #8b5cf6;
            --accent-purple-bg: rgba(139, 92, 246, 0.12);
            --text-primary: #ffffff;
            --text-secondary: #9da3af;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-primary);
            font-family: 'Outfit', 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* ================= ADMIN NAVBAR ================= */
        .admin-nav {
            background: rgba(19, 20, 28, 0.94);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border-subtle);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .admin-nav-container {
            max-width: 1380px;
            margin: 0 auto;
            padding: 0 24px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .admin-brand-logo {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .admin-brand-logo .volt-bolt {
            color: var(--volt-red);
            font-size: 24px;
            animation: pulse-glow 2s infinite;
        }

        .admin-brand-tag {
            background: linear-gradient(135deg, rgba(255, 71, 71, 0.15), rgba(255, 71, 71, 0.05));
            border: 1px solid rgba(255, 71, 71, 0.3);
            color: var(--volt-red);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .admin-nav-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .nav-btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            border-color: var(--border-subtle);
        }

        .nav-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .nav-btn-primary {
            background: var(--volt-red);
            color: #ffffff;
            box-shadow: 0 4px 14px var(--volt-red-glow);
        }

        .nav-btn-primary:hover {
            background: var(--volt-red-hover);
            box-shadow: 0 6px 20px rgba(255, 71, 71, 0.4);
            transform: translateY(-1px);
        }

        .nav-btn-logout {
            background: transparent;
            color: #94a3b8;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .nav-btn-logout:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .badge-count {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 1px 7px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        /* ================= MAIN CONTAINER ================= */
        .dashboard-container {
            max-width: 1380px;
            margin: 0 auto;
            padding: 30px 24px 60px 24px;
        }

        /* ================= NOTIFICATIONS / ALERTS ================= */
        .alert-toast {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .alert-close {
            background: transparent;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 16px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .alert-close:hover { opacity: 1; }

        /* ================= DASHBOARD HEADER ================= */
        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 28px;
        }

        .header-title-box h1 {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-title-box p {
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 4px;
        }

        .quick-action-strip {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ================= STATS / KPI CARDS ================= */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .metric-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 18px;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            opacity: 0.8;
        }

        .metric-card.purple::before { background: var(--accent-purple); }
        .metric-card.blue::before { background: var(--accent-blue); }
        .metric-card.green::before { background: var(--accent-green); }
        .metric-card.amber::before { background: var(--accent-amber); }

        .metric-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .metric-card.purple .metric-icon { background: var(--accent-purple-bg); color: var(--accent-purple); }
        .metric-card.blue .metric-icon { background: var(--accent-blue-bg); color: var(--accent-blue); }
        .metric-card.green .metric-icon { background: var(--accent-green-bg); color: var(--accent-green); }
        .metric-card.amber .metric-icon { background: var(--accent-amber-bg); color: var(--accent-amber); }

        .metric-info {
            display: flex;
            flex-direction: column;
        }

        .metric-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-value {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            margin: 4px 0 2px 0;
            letter-spacing: -0.5px;
        }

        .metric-sub {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* ================= CONTENT SECTION CARD ================= */
        .section-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .section-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .section-title-wrap h2 {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title-wrap span {
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: normal;
        }

        /* ================= FILTER & SEARCH TOOLBAR ================= */
        .filter-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
        }

        .search-input-wrap {
            position: relative;
            flex: 1;
            min-width: 280px;
            max-width: 450px;
        }

        .search-input-wrap input {
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            padding: 10px 14px 10px 38px;
            color: #ffffff;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-input-wrap input:focus {
            border-color: var(--volt-red);
            box-shadow: 0 0 0 3px rgba(255, 71, 71, 0.15);
        }

        .search-input-wrap .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
            pointer-events: none;
        }

        .category-filter-pills {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-pill {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            color: var(--text-secondary);
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-pill:hover {
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.2);
        }

        .filter-pill.active {
            background: var(--volt-red);
            color: #ffffff;
            border-color: var(--volt-red);
            box-shadow: 0 2px 10px var(--volt-red-glow);
        }

        /* ================= DATA TABLE STYLING ================= */
        .table-responsive {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--border-subtle);
            background: var(--bg-card);
        }

        table.admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }

        table.admin-table th {
            background: #151620;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.8px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-subtle);
            white-space: nowrap;
        }

        table.admin-table td {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            vertical-align: middle;
        }

        table.admin-table tbody tr {
            transition: background-color 0.15s ease;
        }

        table.admin-table tbody tr:hover {
            background-color: var(--bg-card-hover);
        }

        table.admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Table Components */
        .product-meta-cell {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .product-thumb-wrap {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            background: #252636;
            border: 1px solid var(--border-subtle);
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-thumb-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-info-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .product-name-title {
            font-weight: 700;
            color: #ffffff;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s;
        }

        .product-name-title:hover {
            color: var(--volt-red);
        }

        .product-sub-id {
            font-size: 11px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .id-badge {
            background: rgba(255, 255, 255, 0.06);
            padding: 1px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 600;
        }

        .category-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.06);
            color: #cbd5e1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.08);
            white-space: nowrap;
        }

        .price-stack {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .current-price-val {
            font-weight: 800;
            color: #10b981;
            font-size: 14px;
        }

        .original-price-val {
            font-size: 12px;
            color: var(--text-muted);
            text-decoration: line-through;
        }

        .discount-chip {
            display: inline-block;
            background: rgba(255, 71, 71, 0.12);
            color: var(--volt-red);
            border: 1px solid rgba(255, 71, 71, 0.25);
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            margin-top: 3px;
            width: fit-content;
        }

        .stats-badge-wrap {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
        }

        .stat-sold {
            color: #10b981;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-rating {
            color: #f59e0b;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Action Buttons */
        .actions-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.12);
            color: #60a5fa;
            border-color: rgba(59, 130, 246, 0.25);
        }

        .btn-edit:hover {
            background: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
            transform: translateY(-1px);
        }

        .btn-view {
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            border-color: var(--border-subtle);
        }

        .btn-view:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border-color: rgba(239, 68, 68, 0.2);
        }

        .btn-delete:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            transform: translateY(-1px);
        }

        .empty-state-box {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-secondary);
        }

        .empty-state-box h3 {
            color: #ffffff;
            font-size: 16px;
            margin-bottom: 6px;
        }

        /* ================= BOTTOM SPLIT GRID (ORDERS & SHORTCUTS) ================= */
        .bottom-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        @media (max-width: 992px) {
            .bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        .quick-links-panel {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .shortcut-item {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .shortcut-item:hover {
            border-color: rgba(255, 255, 255, 0.2);
            background: var(--bg-card-hover);
            transform: translateX(4px);
        }

        .shortcut-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .shortcut-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: rgba(255, 255, 255, 0.05);
        }

        .shortcut-text h4 {
            font-size: 14px;
            color: #ffffff;
            font-weight: 600;
        }

        .shortcut-text p {
            font-size: 12px;
            color: var(--text-muted);
        }

        .shortcut-arrow {
            color: var(--text-muted);
            font-size: 16px;
        }

        /* Footer */
        .admin-footer {
            text-align: center;
            padding: 30px 20px;
            border-top: 1px solid var(--border-subtle);
            color: var(--text-muted);
            font-size: 12px;
            margin-top: 40px;
        }

        @keyframes pulse-glow {
            0% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.08); filter: drop-shadow(0 0 8px rgba(255,71,71,0.7)); }
            100% { opacity: 0.8; transform: scale(1); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- ================= TOP ADMIN NAVIGATION ================= -->
    <header class="admin-nav">
        <div class="admin-nav-container">
            <!-- Brand Logo -->
            <a href="admin_dashboard.php" class="admin-brand">
                <div class="admin-brand-logo">
                    <span class="volt-bolt">⚡</span>VOLT
                </div>
                <span class="admin-brand-tag">CONTROL CENTER</span>
            </a>

            <!-- Header Quick Actions -->
            <div class="admin-nav-actions">
                <a href="index.php" target="_blank" class="nav-btn nav-btn-secondary" title="View Customer Storefront">
                    🌐 <span>Storefront</span>
                </a>
                <a href="admin_users.php" class="nav-btn nav-btn-secondary" title="Manage Registered Users & Orders">
                    👥 <span>Users & Orders</span>
                    <span class="badge-count"><?php echo $total_users; ?></span>
                </a>
                <a href="admin_add.php" class="nav-btn nav-btn-primary">
                    ➕ <span>Add Product</span>
                </a>
                <a href="logout.php" class="nav-btn nav-btn-logout" title="Sign out of admin session">
                    🚪 <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ================= DASHBOARD MAIN WRAPPER ================= -->
    <main class="dashboard-container">

        <!-- ================= SYSTEM NOTIFICATIONS ================= -->
        <?php if ($msg === 'deleted'): ?>
            <div class="alert-toast alert-success" id="alertToast">
                <span>✓ Product asset has been permanently deleted from the database catalog.</span>
                <button class="alert-close" onclick="document.getElementById('alertToast').remove()">✕</button>
            </div>
        <?php elseif ($msg === 'error'): ?>
            <div class="alert-toast alert-danger" id="alertToast">
                <span>⚠ An error occurred while processing the request. Please check database constraints.</span>
                <button class="alert-close" onclick="document.getElementById('alertToast').remove()">✕</button>
            </div>
        <?php endif; ?>

        <!-- ================= DASHBOARD HEADER ================= -->
        <div class="dashboard-header">
            <div class="header-title-box">
                <h1>⚡ Inventory & Hardware Matrix</h1>
                <p>Welcome back, Administrator. Real-time telemetry, dynamic pricing & product management.</p>
            </div>
            <div class="quick-action-strip">
                <a href="admin_add.php" class="nav-btn nav-btn-primary">➕ New Hardware Asset</a>
                <a href="admin_users.php" class="nav-btn nav-btn-secondary">📋 Purchase Orders Matrix</a>
            </div>
        </div>

        <!-- ================= LIVE METRICS & KPIS ================= -->
        <section class="metrics-grid">
            <!-- 1. Total Products -->
            <div class="metric-card purple">
                <div class="metric-icon">📦</div>
                <div class="metric-info">
                    <span class="metric-label">Hardware Assets</span>
                    <span class="metric-value"><?php echo $total_products; ?></span>
                    <span class="metric-sub"><?php echo count($categories_list); ?> active categories</span>
                </div>
            </div>

            <!-- 2. Total Orders Placed -->
            <div class="metric-card blue">
                <div class="metric-icon">🛒</div>
                <div class="metric-info">
                    <span class="metric-label">Total Orders</span>
                    <span class="metric-value"><?php echo $total_orders; ?></span>
                    <span class="metric-sub">Completed transactions</span>
                </div>
            </div>

            <!-- 3. Total Gross Revenue -->
            <div class="metric-card green">
                <div class="metric-icon">💰</div>
                <div class="metric-info">
                    <span class="metric-label">Gross Revenue</span>
                    <span class="metric-value">₹<?php echo number_format($total_revenue, 2); ?></span>
                    <span class="metric-sub">Lifetime customer billing</span>
                </div>
            </div>

            <!-- 4. Registered Users -->
            <div class="metric-card amber">
                <div class="metric-icon">👥</div>
                <div class="metric-info">
                    <span class="metric-label">Registered Users</span>
                    <span class="metric-value"><?php echo $total_users; ?></span>
                    <span class="metric-sub">Active client accounts</span>
                </div>
            </div>
        </section>

        <!-- ================= PRODUCTS CATALOG MANAGEMENT ================= -->
        <section class="section-card">
            <!-- Section Header -->
            <div class="section-header-row">
                <div class="section-title-wrap">
                    <h2>📦 Product Inventory Catalog</h2>
                    <span id="productCounter">Displaying <?php echo $total_products; ?> registered products</span>
                </div>
                <a href="admin_add.php" class="nav-btn nav-btn-secondary" style="font-size: 12px; padding: 6px 12px;">
                    ➕ Quick Add
                </a>
            </div>

            <!-- Interactive Search & Filtering Bar -->
            <div class="filter-toolbar">
                <div class="search-input-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="productSearchInput" placeholder="Search by product name, category, or ID..." onkeyup="filterProductsTable()">
                </div>

                <div class="category-filter-pills">
                    <button class="filter-pill active" data-category="ALL" onclick="filterByCategory('ALL', this)">
                        All (<?php echo $total_products; ?>)
                    </button>
                    <?php foreach ($categories_list as $cat): ?>
                        <button class="filter-pill" data-category="<?php echo htmlspecialchars($cat['name']); ?>" onclick="filterByCategory('<?php echo htmlspecialchars($cat['name']); ?>', this)">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Products Data Table -->
            <div class="table-responsive">
                <table class="admin-table" id="productsTable">
                    <thead>
                        <tr>
                            <th style="width: 32%;">Product & Specifications</th>
                            <th style="width: 18%;">Category</th>
                            <th style="width: 18%;">Pricing & Deal</th>
                            <th style="width: 14%;">Market Performance</th>
                            <th style="width: 18%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                    $img_src = (strpos($row['image'], 'http') === 0) ? $row['image'] : 'assets/images/' . $row['image'];
                                    $cat_name = !empty($row['cat_name']) ? $row['cat_name'] : 'Uncategorized';
                                    $avg_rating = floatval($row['avg_rating']) > 0 ? number_format($row['avg_rating'], 1) : 'New';
                                    $total_sold = intval($row['total_sold']);
                                ?>
                                <tr class="product-row" data-name="<?php echo strtolower(htmlspecialchars($row['name'])); ?>" data-category="<?php echo htmlspecialchars($cat_name); ?>" data-id="<?php echo $row['id']; ?>">
                                    <!-- Product Info Cell -->
                                    <td>
                                        <div class="product-meta-cell">
                                            <div class="product-thumb-wrap">
                                                <img src="<?php echo htmlspecialchars($img_src); ?>" 
                                                     onerror="this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=200'" 
                                                     alt="<?php echo htmlspecialchars($row['name']); ?>">
                                            </div>
                                            <div class="product-info-text">
                                                <a href="product_detail.php?id=<?php echo $row['id']; ?>" target="_blank" class="product-name-title">
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </a>
                                                <div class="product-sub-id">
                                                    <span class="id-badge">#<?php echo $row['id']; ?></span>
                                                    <span>• Added <?php echo !empty($row['created_at']) ? date("M j, Y", strtotime($row['created_at'])) : 'Active'; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Category Cell -->
                                    <td>
                                        <span class="category-badge">
                                            📁 <?php echo htmlspecialchars($cat_name); ?>
                                        </span>
                                    </td>

                                    <!-- Price & Offers Cell -->
                                    <td>
                                        <div class="price-stack">
                                            <span class="current-price-val">₹<?php echo number_format($row['price']); ?></span>
                                            <?php if (!empty($row['original_price']) && $row['original_price'] > $row['price']): ?>
                                                <span class="original-price-val">₹<?php echo number_format($row['original_price']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($row['discount'])): ?>
                                                <span class="discount-chip"><?php echo htmlspecialchars($row['discount']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Real-time Market Performance -->
                                    <td>
                                        <div class="stats-badge-wrap">
                                            <span class="stat-sold">
                                                🛒 <?php echo $total_sold; ?> sold
                                            </span>
                                            <span class="stat-rating">
                                                ⭐ <?php echo $avg_rating; ?> (<?php echo intval($row['review_count']); ?>)
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Action Buttons -->
                                    <td style="text-align: right;">
                                        <div class="actions-cell" style="justify-content: flex-end;">
                                            <a href="product_detail.php?id=<?php echo $row['id']; ?>" target="_blank" class="action-icon-btn btn-view" title="Live Preview">
                                                👁️
                                            </a>
                                            <a href="admin_edit.php?id=<?php echo $row['id']; ?>" class="action-icon-btn btn-edit" title="Edit Parameters">
                                                ✏️ Edit
                                            </a>
                                            <a href="?delete_id=<?php echo $row['id']; ?>" 
                                               class="action-icon-btn btn-delete" 
                                               title="Delete Asset"
                                               onclick="return confirm('⚠️ Are you sure you want to permanently delete \'<?php echo addslashes($row['name']); ?>\' (ID: #<?php echo $row['id']; ?>)? This action cannot be undone.');">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state-box">
                                        <h3>No hardware products in inventory</h3>
                                        <p>Click "Add Product" above to publish your first gadget to the Volt storefront.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Dynamic No Search Results Found Row (Hidden by default) -->
            <div id="noSearchMatch" class="empty-state-box" style="display: none;">
                <h3>🔍 No matching products found</h3>
                <p>Try searching with another keyword or resetting the category filter.</p>
            </div>
        </section>

        <!-- ================= RECENT ORDERS & QUICK TOOLS ================= -->
        <div class="bottom-grid">
            <!-- Recent Transactions Table -->
            <div class="section-card" style="margin-bottom: 0;">
                <div class="section-header-row">
                    <div class="section-title-wrap">
                        <h2>🛒 Recent Customer Orders</h2>
                        <span>Latest 5 transaction records</span>
                    </div>
                    <a href="admin_users.php" class="nav-btn nav-btn-secondary" style="font-size: 12px; padding: 6px 12px;">
                        View All Orders →
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Order Date</th>
                                <th>Amount</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                                <?php while ($ro = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="id-badge">#<?php echo $ro['id']; ?></span>
                                        </td>
                                        <td>
                                            <strong style="color: #fff;"><?php echo htmlspecialchars($ro['username'] ?? 'Guest'); ?></strong>
                                            <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($ro['email'] ?? ''); ?></div>
                                        </td>
                                        <td style="color: var(--text-secondary); font-size: 12px;">
                                            <?php echo date("M j, Y • h:i A", strtotime($ro['order_date'])); ?>
                                        </td>
                                        <td>
                                            <strong style="color: #10b981;">₹<?php echo number_format($ro['total_amount'], 2); ?></strong>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="admin_users.php" class="action-icon-btn btn-view" style="font-size: 11px;">Details</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 25px;">
                                        No customer orders recorded yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Platform Shortcuts & Status Hub -->
            <div class="section-card" style="margin-bottom: 0;">
                <div class="section-header-row">
                    <div class="section-title-wrap">
                        <h2>⚡ System Shortcuts</h2>
                        <span>Quick command links</span>
                    </div>
                </div>

                <div class="quick-links-panel">
                    <a href="admin_add.php" class="shortcut-item">
                        <div class="shortcut-left">
                            <div class="shortcut-icon" style="color: var(--volt-red);">➕</div>
                            <div class="shortcut-text">
                                <h4>Add New Product</h4>
                                <p>Publish new gadget with image & pricing</p>
                            </div>
                        </div>
                        <span class="shortcut-arrow">→</span>
                    </a>

                    <a href="admin_users.php" class="shortcut-item">
                        <div class="shortcut-left">
                            <div class="shortcut-icon" style="color: var(--accent-blue);">👥</div>
                            <div class="shortcut-text">
                                <h4>User Accounts & Orders</h4>
                                <p>Inspect user database & order logs</p>
                            </div>
                        </div>
                        <span class="shortcut-arrow">→</span>
                    </a>

                    <a href="index.php" target="_blank" class="shortcut-item">
                        <div class="shortcut-left">
                            <div class="shortcut-icon" style="color: var(--accent-green);">🌐</div>
                            <div class="shortcut-text">
                                <h4>Live Storefront</h4>
                                <p>Open client-facing website in new tab</p>
                            </div>
                        </div>
                        <span class="shortcut-arrow">↗</span>
                    </a>

                    <div class="shortcut-item" style="cursor: default; opacity: 0.9;">
                        <div class="shortcut-left">
                            <div class="shortcut-icon" style="color: var(--accent-purple);">🛡️</div>
                            <div class="shortcut-text">
                                <h4>Database Status</h4>
                                <p>MySQL Connected • volt_shop</p>
                            </div>
                        </div>
                        <span style="font-size: 11px; color: #10b981; font-weight: 700;">ONLINE</span>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- ================= ADMIN FOOTER ================= -->
    <footer class="admin-footer">
        <p>&copy; <?php echo date("Y"); ?> VOLT Gear India &bull; Internal Hardware & Inventory Management System &bull; Version 2.5</p>
    </footer>

    <!-- ================= JAVASCRIPT REAL-TIME SEARCH & FILTER ================= -->
    <script>
        let currentCategory = 'ALL';

        function filterProductsTable() {
            const query = document.getElementById('productSearchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.product-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const cat = row.getAttribute('data-category') || '';
                const id = row.getAttribute('data-id') || '';

                const matchesQuery = query === '' || name.includes(query) || cat.toLowerCase().includes(query) || id.includes(query);
                const matchesCategory = currentCategory === 'ALL' || cat.toLowerCase() === currentCategory.toLowerCase();

                if (matchesQuery && matchesCategory) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const noMatchBox = document.getElementById('noSearchMatch');
            if (noMatchBox) {
                noMatchBox.style.display = (visibleCount === 0 && rows.length > 0) ? 'block' : 'none';
            }

            const counter = document.getElementById('productCounter');
            if (counter) {
                counter.textContent = `Displaying ${visibleCount} of ${rows.length} registered products`;
            }
        }

        function filterByCategory(categoryName, btnElement) {
            currentCategory = categoryName;

            // Update active pill button style
            document.querySelectorAll('.filter-pill').forEach(btn => {
                btn.classList.remove('active');
            });
            if (btnElement) {
                btnElement.classList.add('active');
            }

            filterProductsTable();
        }
    </script>
</body>
</html>