<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

$from = $_GET["from"] ?? "";
$to = $_GET["to"] ?? "";

$where = "WHERE 1=1";
if (!empty($from)) {
    $where .= " AND DATE(s.created_at) >= '$from'";
}
if (!empty($to)) {
    $where .= " AND DATE(s.created_at) <= '$to'";
}

$summary_sql = "SELECT COUNT(*) AS bills, 
                       SUM(s.discount) AS discount, 
                       SUM(s.grand_total) AS total 
                FROM sales s $where";
$summary_res = mysqli_query($conn, $summary_sql);
$summary = mysqli_fetch_assoc($summary_res);

$sales_sql = "SELECT s.*, a.full_name AS cashier_name 
              FROM sales s 
              LEFT JOIN admins a ON s.sold_by = a.id 
              $where 
              ORDER BY s.created_at DESC";
$sales = mysqli_query($conn, $sales_sql);

$pageTitle = "Sales History";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title h4 mb-1">Sales History</h1>
        <p class="text-muted small mb-0">Browse saved bills and filter them by date.</p>
    </div>
    <a href="../billing/index.php" class="btn btn-success">
        <i class="bi bi-cart-plus me-1"></i>New Sale
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="get" action="" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="form-label small text-muted mb-1">From Date</label>
                <input type="date" class="form-control" name="from" value="<?php echo $from; ?>">
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label small text-muted mb-1">To Date</label>
                <input type="date" class="form-control" name="to" value="<?php echo $to; ?>">
            </div>
            <div class="col-sm-4 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                <a href="index.php?from=<?php echo date("Y-m-d"); ?>&to=<?php echo date("Y-m-d"); ?>"
                    class="btn btn-outline-success">Today</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body">
                <p class="text-muted small mb-1">Bills</p>
                <div class="stat-value"><?php echo $summary["bills"] ?? 0; ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body">
                <p class="text-muted small mb-1">Discount Given</p>
                <div class="stat-value">৳ <?php echo number_format($summary["discount"] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card">
            <div class="card-body">
                <p class="text-muted small mb-1">Sales Total</p>
                <div class="stat-value">৳ <?php echo number_format($summary["total"] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Date &amp; Time</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th class="text-end">Grand Total</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($sales) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($sales)) { ?>
                        <tr>
                            <td class="fw-semibold"><?php echo $row["invoice_no"]; ?></td>
                            <td class="small"><?php echo date("d M Y, h:i A", strtotime($row["created_at"])); ?></td>
                            <td>
                                <?php echo !empty($row["customer_name"]) ? $row["customer_name"] : "Walk-in"; ?>
                                <?php if (!empty($row["customer_phone"])) { ?>
                                    <div class="small text-muted"><?php echo $row["customer_phone"]; ?></div>
                                <?php } ?>
                            </td>
                            <td><span class="badge text-bg-light border"><?php echo $row["payment_method"]; ?></span></td>
                            <td class="text-end fw-semibold">৳ <?php echo number_format($row["grand_total"], 2); ?></td>
                            <td class="text-end text-nowrap">
                                <a href="view.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-outline-secondary"
                                    title="View bill">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="../invoices/print.php?id=<?php echo $row["id"]; ?>"
                                    class="btn btn-sm btn-outline-primary" title="Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No sales found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>