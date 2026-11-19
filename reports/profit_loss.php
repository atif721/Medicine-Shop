<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

$from = $_GET["from"] ?? date("Y-m-01");
$to = $_GET["to"] ?? date("Y-m-d");

$where = "WHERE 1=1";
if (!empty($from)) {
    $where .= " AND DATE(created_at) >= '$from'";
}
if (!empty($to)) {
    $where .= " AND DATE(created_at) <= '$to'";
}

$summary_sql = "SELECT COUNT(*) AS bills, 
                       SUM(subtotal) AS subtotal, 
                       SUM(discount) AS discount, 
                       SUM(grand_total) AS revenue, 
                       SUM(total_cost) AS cost 
                FROM sales $where";
$summary_res = mysqli_query($conn, $summary_sql);
$summary = mysqli_fetch_assoc($summary_res);

$bills = $summary["bills"] ?? 0;
$subtotal = $summary["subtotal"] ?? 0;
$discount = $summary["discount"] ?? 0;
$revenue = $summary["revenue"] ?? 0;
$cost = $summary["cost"] ?? 0;
$profit = $revenue - $cost;

$sales = mysqli_query($conn, "SELECT * FROM sales $where ORDER BY created_at DESC");

$pageTitle = "Profit / Loss Report";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title h4 mb-1">Profit / Loss Report</h1>
        <p class="text-muted small mb-0">
            <?php echo !empty($from) ? date("d M Y", strtotime($from)) : "Beginning"; ?>
            &ndash;
            <?php echo !empty($to) ? date("d M Y", strtotime($to)) : "Today"; ?>
        </p>
    </div>
    <a href="../sales/index.php?from=<?php echo $from; ?>&to=<?php echo $to; ?>"
        class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-receipt me-1"></i>View Bills in Range
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
                    <i class="bi bi-funnel me-1"></i>Show Report
                </button>
                <a href="profit_loss.php" class="btn btn-outline-secondary">This Month</a>
                <a href="profit_loss.php?from=<?php echo date("Y-m-d"); ?>&to=<?php echo date("Y-m-d"); ?>"
                    class="btn btn-outline-success">Today</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Sales Revenue</p>
                <div class="stat-value">৳ <?php echo number_format($revenue, 2); ?></div>
                <div class="small text-muted"><?php echo $bills; ?> bill(s)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Discount Given</p>
                <div class="stat-value">৳ <?php echo number_format($discount, 2); ?></div>
                <div class="small text-muted">Subtotal ৳ <?php echo number_format($subtotal, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Cost of Goods Sold</p>
                <div class="stat-value">৳ <?php echo number_format($cost, 2); ?></div>
                <div class="small text-muted">Cost prices saved with each sale</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 border-<?php echo $profit < 0 ? 'danger' : 'success'; ?>">
            <div class="card-body">
                <p class="text-muted small mb-1"><?php echo $profit < 0 ? "Net Loss" : "Net Profit"; ?></p>
                <div class="stat-value <?php echo $profit < 0 ? 'text-danger' : 'text-success'; ?>">
                    ৳ <?php echo number_format(abs($profit), 2); ?>
                </div>
                <div class="small text-muted">Revenue &minus; cost of goods</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white fw-semibold">Bill-wise Breakdown</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Grand Total</th>
                    <th class="text-end">Cost</th>
                    <th class="text-end">Profit / Loss</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($sales) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($sales)) {
                        $row_profit = $row["grand_total"] - $row["total_cost"];
                        ?>
                        <tr>
                            <td class="fw-semibold"><?php echo $row["invoice_no"]; ?></td>
                            <td class="small"><?php echo date("d M Y", strtotime($row["created_at"])); ?></td>
                            <td class="text-end">৳ <?php echo number_format($row["subtotal"], 2); ?></td>
                            <td class="text-end">৳ <?php echo number_format($row["discount"], 2); ?></td>
                            <td class="text-end">৳ <?php echo number_format($row["grand_total"], 2); ?></td>
                            <td class="text-end">৳ <?php echo number_format($row["total_cost"], 2); ?></td>
                            <td class="text-end fw-semibold <?php echo $row_profit < 0 ? 'text-danger' : 'text-success'; ?>">
                                <?php echo $row_profit < 0 ? "-" : ""; ?>৳ <?php echo number_format(abs($row_profit), 2); ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No sales found in this date range.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="alert alert-light border mt-3 small text-muted">
    <i class="bi bi-info-circle me-1"></i>
    Discount is treated as a reduction of revenue: net profit = (subtotal &minus; discount) &minus; cost of goods sold.
</div>

<?php include "../includes/footer.php"; ?>