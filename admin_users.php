<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Force Admin Authorization
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

include('config/db.php');

// Fetch All Users & Associated Purchase Orders
$sql = "SELECT u.id as user_id, u.username, u.email, u.created_at, 
        o.id as order_id, o.total_amount, o.order_date 
        FROM users u 
        LEFT JOIN orders o ON u.id = o.user_id 
        ORDER BY u.created_at DESC, o.order_date DESC";
        
$result = $conn->query($sql);
$users_data = [];

$total_orders_count = 0;
$total_revenue_sum = 0;
$active_buyers_count = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $uid = $row['user_id'];
        if (!isset($users_data[$uid])) {
            $users_data[$uid] = [
                'name' => $row['username'],
                'email' => $row['email'],
                'joined' => $row['created_at'],
                'total_spent' => 0,
                'orders' => []
            ];
        }
        if ($row['order_id']) {
            $oid = $row['order_id'];
            $items_query = $conn->query("SELECT oi.quantity, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $oid");
            $items = [];
            if ($items_query) {
                while($i_row = $items_query->fetch_assoc()){
                    $items[] = $i_row['quantity'] . "x " . $i_row['name'];
                }
            }
            
            $order_amount = floatval($row['total_amount']);
            $users_data[$uid]['total_spent'] += $order_amount;
            $total_revenue_sum += $order_amount;
            $total_orders_count++;

            $users_data[$uid]['orders'][] = [
                'id' => $oid,
                'amount' => $order_amount,
                'date' => $row['order_date'],
                'items' => implode(', ', $items)
            ];
        }
    }
}

foreach ($users_data as $u) {
    if (!empty($u['orders'])) {
        $active_buyers_count++;
    }
}

$total_users_count = count($users_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volt | Customer Accounts & Orders Matrix</title>
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

        /* ================= TOP ADMIN NAVBAR ================= */
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

        /* ================= METRICS & KPIS ================= */
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

        .metric-card.amber::before { background: var(--accent-amber); }
        .metric-card.green::before { background: var(--accent-green); }
        .metric-card.blue::before { background: var(--accent-blue); }
        .metric-card.purple::before { background: var(--accent-purple); }

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

        .metric-card.amber .metric-icon { background: var(--accent-amber-bg); color: var(--accent-amber); }
        .metric-card.green .metric-icon { background: var(--accent-green-bg); color: var(--accent-green); }
        .metric-card.blue .metric-icon { background: var(--accent-blue-bg); color: var(--accent-blue); }
        .metric-card.purple .metric-icon { background: var(--accent-purple-bg); color: var(--accent-purple); }

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

        /* ================= FILTER & SEARCH TOOLBAR ================= */
        .filter-toolbar-box {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .search-input-wrap {
            position: relative;
            flex: 1;
            min-width: 280px;
            max-width: 480px;
        }

        .search-input-wrap input {
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 8px;
            padding: 11px 16px 11px 40px;
            color: #ffffff;
            font-size: 13.5px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-input-wrap input:focus {
            border-color: var(--volt-red);
            box-shadow: 0 0 0 3px rgba(255, 71, 71, 0.15);
        }

        .search-input-wrap .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 15px;
            pointer-events: none;
        }

        .status-filter-pills {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-pill {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            color: var(--text-secondary);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12.5px;
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

        /* ================= USER CARDS CONTAINER ================= */
        .users-list {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .user-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .user-card:hover {
            border-color: rgba(255, 255, 255, 0.16);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35);
        }

        /* User Header Strip */
        .user-card-header {
            padding: 22px 24px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02) 0%, rgba(255, 255, 255, 0) 100%);
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .user-profile-meta {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-avatar {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #252836 0%, #171822 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--volt-red);
            font-size: 22px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.4);
            flex-shrink: 0;
        }

        .user-titles h3 {
            font-size: 17px;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-titles .user-email {
            font-size: 13.5px;
            color: var(--text-secondary);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .user-badge-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--border-subtle);
            background: var(--bg-card);
            color: #e2e8f0;
        }

        .tag-pill.tag-orders {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.25);
            color: #60a5fa;
        }

        .tag-pill.tag-spent {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.25);
            color: #34d399;
        }

        .tag-pill.tag-inactive {
            background: rgba(100, 116, 139, 0.1);
            border-color: rgba(100, 116, 139, 0.2);
            color: #94a3b8;
        }

        .id-badge {
            background: rgba(255, 255, 255, 0.08);
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }

        /* User Orders Section */
        .user-card-body {
            padding: 22px 24px;
        }

        .purchase-heading-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .purchase-heading-row h4 {
            font-size: 14px;
            font-weight: 700;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--border-subtle);
            background: var(--bg-card);
        }

        table.user-orders-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }

        table.user-orders-table th {
            background: #151620;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.8px;
            padding: 12px 18px;
            border-bottom: 1px solid var(--border-subtle);
            white-space: nowrap;
        }

        table.user-orders-table td {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            vertical-align: middle;
        }

        table.user-orders-table tbody tr {
            transition: background-color 0.15s ease;
        }

        table.user-orders-table tbody tr:hover {
            background-color: var(--bg-card-hover);
        }

        table.user-orders-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .item-chip {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            color: #cbd5e1;
        }

        .status-badge-done {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(16, 185, 129, 0.12);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.25);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .empty-history-box {
            padding: 24px;
            text-align: center;
            background: var(--bg-card);
            border-radius: 10px;
            border: 1px dashed var(--border-subtle);
            color: var(--text-muted);
            font-size: 13.5px;
        }

        .empty-state-card {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            color: var(--text-secondary);
        }

        .empty-state-card h3 {
            color: #ffffff;
            font-size: 18px;
            margin-bottom: 6px;
        }

        /* ================= ADMIN FOOTER ================= */
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
    </style>
</head>
<body>

    <!-- ================= TOP ADMIN NAVBAR ================= -->
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
                <a href="admin_dashboard.php" class="nav-btn nav-btn-secondary" title="Return to Inventory Dashboard">
                    ⚡ <span>Dashboard</span>
                </a>
                <a href="index.php" target="_blank" class="nav-btn nav-btn-secondary" title="View Customer Storefront">
                    🌐 <span>Storefront</span>
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

    <!-- ================= MAIN DASHBOARD CONTAINER ================= -->
    <main class="dashboard-container">

        <!-- ================= PAGE HEADER ================= -->
        <div class="dashboard-header">
            <div class="header-title-box">
                <h1>👥 User Accounts & Orders Matrix</h1>
                <p>Comprehensive overview of registered customers, account status, lifetime spending & purchases.</p>
            </div>
            <div class="quick-action-strip">
                <a href="admin_dashboard.php" class="nav-btn nav-btn-secondary">⚡ Back to Dashboard</a>
                <a href="admin_add.php" class="nav-btn nav-btn-primary">➕ New Hardware Asset</a>
            </div>
        </div>

        <!-- ================= USER & ORDER KPIS ================= -->
        <section class="metrics-grid">
            <!-- 1. Registered Customers -->
            <div class="metric-card amber">
                <div class="metric-icon">👥</div>
                <div class="metric-info">
                    <span class="metric-label">Registered Users</span>
                    <span class="metric-value"><?php echo $total_users_count; ?></span>
                    <span class="metric-sub">Verified customer accounts</span>
                </div>
            </div>

            <!-- 2. Active Buyers -->
            <div class="metric-card blue">
                <div class="metric-icon">🛍️</div>
                <div class="metric-info">
                    <span class="metric-label">Active Buyers</span>
                    <span class="metric-value"><?php echo $active_buyers_count; ?></span>
                    <span class="metric-sub">Accounts with >= 1 order</span>
                </div>
            </div>

            <!-- 3. Total Orders Placed -->
            <div class="metric-card purple">
                <div class="metric-icon">🛒</div>
                <div class="metric-info">
                    <span class="metric-label">Completed Orders</span>
                    <span class="metric-value"><?php echo $total_orders_count; ?></span>
                    <span class="metric-sub">Processed transactions</span>
                </div>
            </div>

            <!-- 4. Gross Customer Revenue -->
            <div class="metric-card green">
                <div class="metric-icon">💰</div>
                <div class="metric-info">
                    <span class="metric-label">Gross Billing</span>
                    <span class="metric-value">₹<?php echo number_format($total_revenue_sum, 2); ?></span>
                    <span class="metric-sub">Lifetime revenue from users</span>
                </div>
            </div>
        </section>

        <!-- ================= INTERACTIVE SEARCH & FILTER TOOLBAR ================= -->
        <div class="filter-toolbar-box">
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="userSearchInput" placeholder="Search by customer name, email, order ID, or product..." onkeyup="filterUsers()">
            </div>

            <div class="status-filter-pills">
                <button class="filter-pill active" data-filter="ALL" onclick="filterByStatus('ALL', this)">
                    All Users (<?php echo $total_users_count; ?>)
                </button>
                <button class="filter-pill" data-filter="BUYERS" onclick="filterByStatus('BUYERS', this)">
                    Active Buyers (<?php echo $active_buyers_count; ?>)
                </button>
                <button class="filter-pill" data-filter="NEW" onclick="filterByStatus('NEW', this)">
                    No Orders Yet (<?php echo ($total_users_count - $active_buyers_count); ?>)
                </button>
            </div>
        </div>

        <!-- ================= USERS LIST ================= -->
        <?php if (empty($users_data)): ?>
            <div class="empty-state-card">
                <h3>👥 No user accounts found</h3>
                <p>Customer accounts created on the storefront will automatically appear in this registry.</p>
            </div>
        <?php else: ?>
            <div class="users-list" id="usersListContainer">
                <?php foreach ($users_data as $uid => $user): ?>
                    <?php 
                        $has_orders = !empty($user['orders']);
                        $orders_count = count($user['orders']);
                        $initial = strtoupper(substr($user['name'] ?? 'U', 0, 1));
                        
                        // Collect all purchased items string for live search
                        $all_items_str = '';
                        foreach ($user['orders'] as $ord) {
                            $all_items_str .= ' ' . $ord['items'] . ' #' . $ord['id'];
                        }
                    ?>
                    <article class="user-card" 
                             data-name="<?php echo strtolower(htmlspecialchars($user['name'])); ?>" 
                             data-email="<?php echo strtolower(htmlspecialchars($user['email'])); ?>" 
                             data-id="<?php echo $uid; ?>"
                             data-has-orders="<?php echo $has_orders ? 'yes' : 'no'; ?>"
                             data-items="<?php echo strtolower(htmlspecialchars($all_items_str)); ?>">
                        
                        <!-- User Header Strip -->
                        <div class="user-card-header">
                            <div class="user-profile-meta">
                                <div class="user-avatar"><?php echo $initial; ?></div>
                                <div class="user-titles">
                                    <h3>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                        <span class="id-badge">ID: #<?php echo $uid; ?></span>
                                    </h3>
                                    <div class="user-email">
                                        📧 <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="user-badge-strip">
                                <span class="tag-pill">
                                    📅 Joined <?php echo date("M j, Y", strtotime($user['joined'])); ?>
                                </span>
                                
                                <?php if ($has_orders): ?>
                                    <span class="tag-pill tag-orders">
                                        🛒 <?php echo $orders_count; ?> <?php echo ($orders_count === 1) ? 'Order' : 'Orders'; ?>
                                    </span>
                                    <span class="tag-pill tag-spent">
                                        💰 Spent ₹<?php echo number_format($user['total_spent'], 2); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="tag-pill tag-inactive">
                                        💤 No Orders Yet
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- User Purchase Orders Table -->
                        <div class="user-card-body">
                            <div class="purchase-heading-row">
                                <h4>🛒 Purchase History & Order Logs</h4>
                                <span style="font-size: 12px; color: var(--text-muted);">
                                    <?php echo $orders_count; ?> recorded transaction<?php echo ($orders_count === 1) ? '' : 's'; ?>
                                </span>
                            </div>

                            <?php if (empty($user['orders'])): ?>
                                <div class="empty-history-box">
                                    This customer has registered an account but has not placed any orders yet.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="user-orders-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 14%;">Order ID</th>
                                                <th style="width: 22%;">Transaction Date</th>
                                                <th style="width: 42%;">Purchased Hardware Items</th>
                                                <th style="width: 12%;">Total Amount</th>
                                                <th style="width: 10%; text-align: right;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($user['orders'] as $order): ?>
                                                <tr>
                                                    <td>
                                                        <span class="id-badge">#<?php echo $order['id']; ?></span>
                                                    </td>
                                                    <td style="color: var(--text-secondary); font-size: 13px;">
                                                        <?php echo date("M j, Y • h:i A", strtotime($order['date'])); ?>
                                                    </td>
                                                    <td>
                                                        <div class="items-chip-list">
                                                            <?php 
                                                                $items_arr = explode(', ', $order['items']);
                                                                foreach ($items_arr as $item_text):
                                                                    if (empty(trim($item_text))) continue;
                                                            ?>
                                                                <span class="item-chip">
                                                                    📦 <?php echo htmlspecialchars(trim($item_text)); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong style="color: #10b981; font-size: 14px;">
                                                            ₹<?php echo number_format($order['amount'], 2); ?>
                                                        </strong>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <span class="status-badge-done">
                                                            ✓ Completed
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Dynamic Search No-Results Box -->
            <div id="noSearchUsers" class="empty-state-card" style="display: none; margin-top: 20px;">
                <h3>🔍 No matching customer accounts found</h3>
                <p>Try searching with another keyword or resetting the filter pills.</p>
            </div>
        <?php endif; ?>

    </main>

    <!-- ================= ADMIN FOOTER ================= -->
    <footer class="admin-footer">
        <p>&copy; <?php echo date("Y"); ?> VOLT Gear India &bull; Internal Hardware & Inventory Management System &bull; Version 2.5</p>
    </footer>

    <!-- ================= JAVASCRIPT REAL-TIME SEARCH & FILTER ================= -->
    <script>
        let currentStatusFilter = 'ALL';

        function filterUsers() {
            const query = document.getElementById('userSearchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.user-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const email = card.getAttribute('data-email') || '';
                const id = card.getAttribute('data-id') || '';
                const items = card.getAttribute('data-items') || '';
                const hasOrders = card.getAttribute('data-has-orders') || 'no';

                const matchesQuery = query === '' || 
                                     name.includes(query) || 
                                     email.includes(query) || 
                                     id.includes(query) || 
                                     items.includes(query);

                let matchesFilter = true;
                if (currentStatusFilter === 'BUYERS') {
                    matchesFilter = (hasOrders === 'yes');
                } else if (currentStatusFilter === 'NEW') {
                    matchesFilter = (hasOrders === 'no');
                }

                if (matchesQuery && matchesFilter) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const noMatchBox = document.getElementById('noSearchUsers');
            if (noMatchBox) {
                noMatchBox.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
            }
        }

        function filterByStatus(status, btnElement) {
            currentStatusFilter = status;

            document.querySelectorAll('.filter-pill').forEach(btn => {
                btn.classList.remove('active');
            });
            if (btnElement) {
                btnElement.classList.add('active');
            }

            filterUsers();
        }
    </script>
</body>
</html>
