<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

$id = $_GET["id"];

$sql = "SELECT s.*, a.full_name AS cashier_name 
        FROM sales s 
        LEFT JOIN admins a ON s.sold_by = a.id 
        WHERE s.id='$id'";
$result = mysqli_query($conn, $sql);
$sale = mysqli_fetch_assoc($result);

if (!$sale) {
    header("location: index.php");
    exit();
}

$items = mysqli_query($conn, "SELECT * FROM sale_items WHERE sale_id='$id' ORDER BY id ASC");

$pageTitle = "Bill " . $sale["invoice_no"];
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title h4 mb-1">Bill Details</h1>
        <p class="text-muted small mb-0"><?php echo $sale["invoice_no"]; ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Sales
        </a>
        <a href="../invoices/print.php?id=<?php echo $sale["id"]; ?>" class="btn btn-success btn-sm">
            <i class="bi bi-printer me-1"></i>Print Invoice
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Sold Items</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Medicine</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sl = 1;
                        while ($item = mysqli_fetch_assoc($items)) { ?>
                            <tr>
                                <td class="text-muted"><?php echo $sl++; ?></td>
                                <td class="fw-semibold"><?php echo $item["product_name"]; ?></td>
                                <td class="text-center"><?php echo $item["quantity"]; ?></td>
                                <td class="text-end">৳ <?php echo number_format($item["unit_price"], 2); ?></td>
                                <td class="text-end">৳ <?php echo number_format($item["line_total"], 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Bill Summary</div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">Invoice</dt>
                    <dd class="col-7 text-end fw-semibold"><?php echo $sale["invoice_no"]; ?></dd>

                    <dt class="col-5 text-muted fw-normal">Date</dt>
                    <dd class="col-7 text-end"><?php echo date("d M Y", strtotime($sale["created_at"])); ?></dd>

                    <dt class="col-5 text-muted fw-normal">Time</dt>
                    <dd class="col-7 text-end"><?php echo date("h:i A", strtotime($sale["created_at"])); ?></dd>

                    <dt class="col-5 text-muted fw-normal">Cashier</dt>
                    <dd class="col-7 text-end"><?php echo $sale["cashier_name"] ?? "Admin"; ?></dd>

                    <dt class="col-5 text-muted fw-normal">Customer</dt>
                    <dd class="col-7 text-end">
                        <?php echo !empty($sale["customer_name"]) ? $sale["customer_name"] : "Walk-in"; ?>
                    </dd>

                    <?php if (!empty($sale["customer_phone"])) { ?>
                        <dt class="col-5 text-muted fw-normal">Phone</dt>
                        <dd class="col-7 text-end"><?php echo $sale["customer_phone"]; ?></dd>
                    <?php } ?>

                    <dt class="col-5 text-muted fw-normal">Payment</dt>
                    <dd class="col-7 text-end"><?php echo $sale["payment_method"]; ?></dd>
                </dl>

                <hr>

                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Subtotal</span>
                    <span>৳ <?php echo number_format($sale["subtotal"], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Discount</span>
                    <span>- ৳ <?php echo number_format($sale["discount"], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between fs-5 fw-bold border-top pt-2 mt-2">
                    <span>Grand Total</span>
                    <span>৳ <?php echo number_format($sale["grand_total"], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white fw-semibold">Profit on this Bill</div>
            <div class="card-body">
                <?php $bill_profit = $sale["grand_total"] - $sale["total_cost"]; ?>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Cost of Goods</span>
                    <span>৳ <?php echo number_format($sale["total_cost"], 2); ?></span>
                </div>
                <div
                    class="d-flex justify-content-between fw-semibold <?php echo $bill_profit < 0 ? 'text-danger' : 'text-success'; ?>">
                    <span><?php echo $bill_profit < 0 ? "Loss" : "Profit"; ?></span>
                    <span>৳ <?php echo number_format(abs($bill_profit), 2); ?></span>
                </div>
                <div class="form-text mt-2">Discount is deducted from revenue before profit is calculated.</div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>