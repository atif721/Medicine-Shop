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
    header("location: ../sales/index.php");
    exit();
}

$items = mysqli_query($conn, "SELECT * FROM sale_items WHERE sale_id='$id' ORDER BY id ASC");

$pageTitle = "Invoice " . $sale["invoice_no"];
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h1 class="page-title h4 mb-1">Invoice</h1>
        <p class="text-muted small mb-0"><?php echo $sale["invoice_no"]; ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="../sales/view.php?id=<?php echo $sale["id"]; ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-receipt me-1"></i>Bill Details
        </a>
        <a href="../sales/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Sales History
        </a>
        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Print Invoice
        </button>
    </div>
</div>

<div class="card invoice-box mx-auto">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex justify-content-between border-bottom pb-3 mb-4">
            <div>
                <h2 class="h4 mb-1 text-success"><?php echo SHOP_NAME; ?></h2>
                <div class="small text-muted"><?php echo SHOP_ADDRESS; ?></div>
                <div class="small text-muted">Phone: <?php echo SHOP_PHONE; ?></div>
            </div>
            <div class="text-end">
                <div class="h5 mb-1">INVOICE</div>
                <div class="small"><strong>No:</strong> <?php echo $sale["invoice_no"]; ?></div>
                <div class="small"><strong>Date:</strong>
                    <?php echo date("d M Y, h:i A", strtotime($sale["created_at"])); ?></div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-6">
                <div class="small text-muted mb-1">Billed To</div>
                <div class="fw-semibold">
                    <?php echo !empty($sale["customer_name"]) ? $sale["customer_name"] : "Walk-in Customer"; ?>
                </div>
                <?php if (!empty($sale["customer_phone"])) { ?>
                    <div class="small text-muted"><?php echo $sale["customer_phone"]; ?></div>
                <?php } ?>
            </div>
            <div class="col-sm-6 text-sm-end">
                <div class="small text-muted mb-1">Served By</div>
                <div><?php echo $sale["cashier_name"] ?? "Admin"; ?></div>
                <div class="small text-muted">Payment: <?php echo $sale["payment_method"]; ?></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <thead class="border-top border-bottom">
                    <tr>
                        <th>SL</th>
                        <th>Medicine</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sl = 1;
                    while ($item = mysqli_fetch_assoc($items)) { ?>
                        <tr class="border-bottom">
                            <td><?php echo $sl++; ?></td>
                            <td class="fw-semibold"><?php echo $item["product_name"]; ?></td>
                            <td class="text-center"><?php echo $item["quantity"]; ?></td>
                            <td class="text-end">৳ <?php echo number_format($item["unit_price"], 2); ?></td>
                            <td class="text-end">৳ <?php echo number_format($item["line_total"], 2); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end mt-3">
            <div class="col-sm-6 col-md-5">
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Subtotal</span>
                    <span>৳ <?php echo number_format($sale["subtotal"], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Discount</span>
                    <span>- ৳ <?php echo number_format($sale["discount"], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 mt-1 fs-5 fw-bold">
                    <span>Grand Total</span>
                    <span>৳ <?php echo number_format($sale["grand_total"], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="border-top mt-4 pt-3 text-center small text-muted">
            Thank you for your purchase. Medicines are not returnable after they leave the counter.
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>