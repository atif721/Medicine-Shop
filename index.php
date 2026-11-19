<?php
session_start();
include "config/db.php";
include "includes/auth.php";

$today = date("Y-m-d");
$next_60 = date("Y-m-d", strtotime("+60 days"));

// 1. Today's sales and profit
$today_sql = "SELECT COUNT(*) AS bills, 
                     SUM(grand_total) AS revenue, 
                     SUM(total_cost) AS cost 
              FROM sales 
              WHERE DATE(created_at) = '$today'";
$today_res = mysqli_query($conn, $today_sql);
$today_row = mysqli_fetch_assoc($today_res);

$today_bills = $today_row["bills"] ?? 0;
$today_revenue = $today_row["revenue"] ?? 0;
$today_cost = $today_row["cost"] ?? 0;
$today_profit = $today_revenue - $today_cost;

// 2. Dashboard counters
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
$low_stock_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products WHERE stock <= reorder_level"));
$expired_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products WHERE expiry_date < '$today'"));
$expiring_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products WHERE expiry_date >= '$today' AND expiry_date <= '$next_60'"));

// 3. Low stock list
$low_stock = mysqli_query($conn, "SELECT p.*, c.name AS category_name 
                                  FROM products p 
                                  JOIN categories c ON p.category_id = c.id 
                                  WHERE p.stock <= p.reorder_level 
                                  ORDER BY p.stock ASC LIMIT 8");

// 4. Expired and expiring within 60 days list
$expiring = mysqli_query($conn, "SELECT p.*, c.name AS category_name 
                                 FROM products p 
                                 JOIN categories c ON p.category_id = c.id 
                                 WHERE p.expiry_date <= '$next_60' 
                                 ORDER BY p.expiry_date ASC LIMIT 8");

// 5. Product count per category
$category_counts = mysqli_query($conn, "SELECT c.name, COUNT(p.id) AS product_count 
                                        FROM categories c 
                                        LEFT JOIN products p ON p.category_id = c.id 
                                        GROUP BY c.id 
                                        ORDER BY c.name ASC");

// 6. Recent sales
$recent_sales = mysqli_query($conn, "SELECT * FROM sales ORDER BY created_at DESC LIMIT 6");

$pageTitle = "Dashboard";
include "includes/header.php";
include "includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title h4 mb-1">Dashboard</h1>
        <p class="text-muted small mb-0"><?php echo date("l, d F Y"); ?></p>
    </div>
    <a href="billing/index.php" class="btn btn-success">
        <i class="bi bi-cart-plus me-1"></i>New Sale
    </a>
</div>

<!-- Top Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Today's Sales</p>
                <div class="stat-value">৳ <?php echo number_format($today_revenue, 2); ?></div>
                <div class="small text-muted"><?php echo $today_bills; ?> bill(s) today</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Today's Profit</p>
                <div class="stat-value <?php echo $today_profit < 0 ? 'text-danger' : 'text-success'; ?>">
                    ৳ <?php echo number_format(abs($today_profit), 2); ?>
                </div>
                <div class="small text-muted"><?php echo $today_profit < 0 ? "Loss today" : "Profit today"; ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Low Stock Products</p>
                <div class="stat-value"><?php echo $low_stock_count; ?></div>
                <div class="small text-muted">Of <?php echo $total_products; ?> product(s) in total</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Expired Medicines</p>
                <div class="stat-value <?php echo $expired_count > 0 ? 'text-danger' : ''; ?>">
                    <?php echo $expired_count; ?>
                </div>
                <div class="small text-muted"><?php echo $expiring_count; ?> expiring within 60 days</div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock & Expiry Watch -->
<div class="row g-3 mb-3">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-exclamation-triangle me-1 text-warning"></i>Low Stock
                    Products</span>
                <a href="products/index.php" class="small text-decoration-none">All products</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Medicine</th>
                            <th>Category</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Reorder</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($low_stock) > 0) { ?>
                            <?php while ($row = mysqli_fetch_assoc($low_stock)) { ?>
                                <tr>
                                    <td>
                                        <a href="products/edit.php?id=<?php echo $row["id"]; ?>"
                                            class="fw-semibold text-decoration-none">
                                            <?php echo $row["name"]; ?>
                                        </a>
                                    </td>
                                    <td class="small text-muted"><?php echo $row["category_name"]; ?></td>
                                    <td class="text-center">
                                        <span
                                            class="badge <?php echo $row["stock"] == 0 ? 'text-bg-danger' : 'text-bg-warning'; ?>">
                                            <?php echo $row["stock"]; ?>
                                        </span>
                                    </td>
                                    <td class="text-center small text-muted"><?php echo $row["reorder_level"]; ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No low-stock products.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calendar-x me-1 text-danger"></i>Expiry Watch
            </div>
            <div class="list-group list-group-flush">
                <?php if (mysqli_num_rows($expiring) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($expiring)) {
                        $is_expired = ($row["expiry_date"] < $today);
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-semibold small"><?php echo $row["name"]; ?></div>
                                <div class="text-muted small">
                                    <?php echo $row["category_name"]; ?> &middot;
                                    <?php echo date("d M Y", strtotime($row["expiry_date"])); ?>
                                </div>
                            </div>
                            <span class="badge <?php echo $is_expired ? 'text-bg-danger' : 'text-bg-warning'; ?>">
                                <?php echo $is_expired ? "Expired" : "Soon"; ?>
                            </span>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="list-group-item text-center text-muted py-4">
                        No expired or soon-to-expire medicines.
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Products per Category & Recent Sales -->
<div class="row g-3">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-tags me-1"></i>Products per Category
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($category_counts) > 0) { ?>
                    <ul class="list-group list-group-flush">
                        <?php while ($row = mysqli_fetch_assoc($category_counts)) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><?php echo $row["name"]; ?></span>
                                <span class="badge text-bg-success rounded-pill"><?php echo $row["product_count"]; ?></span>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p class="text-muted mb-0">No categories yet.</p>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-clock-history me-1"></i>Recent Sales</span>
                <a href="sales/index.php" class="small text-decoration-none">Sales history</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Payment</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($recent_sales) > 0) { ?>
                            <?php while ($row = mysqli_fetch_assoc($recent_sales)) { ?>
                                <tr>
                                    <td>
                                        <a href="sales/view.php?id=<?php echo $row["id"]; ?>"
                                            class="text-decoration-none fw-semibold">
                                            <?php echo $row["invoice_no"]; ?>
                                        </a>
                                        <div class="text-muted small">
                                            <?php echo date("d M, h:i A", strtotime($row["created_at"])); ?>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <?php echo !empty($row["customer_name"]) ? $row["customer_name"] : "Walk-in"; ?>
                                    </td>
                                    <td class="small"><span
                                            class="badge text-bg-light border"><?php echo $row["payment_method"]; ?></span></td>
                                    <td class="text-end fw-semibold">৳ <?php echo number_format($row["grand_total"], 2); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No sales recorded yet.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>