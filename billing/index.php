<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$today = date("Y-m-d");

// 1. Add product to cart
if (isset($_POST["add_to_cart"])) {
    $product_id = $_POST["product_id"];
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id'");
    $product = mysqli_fetch_assoc($res);

    if ($product) {
        if ($product["expiry_date"] < $today) {
            $_SESSION["error"] = "Expired medicines cannot be sold.";
        } elseif ($product["stock"] <= 0) {
            $_SESSION["error"] = "This product is out of stock.";
        } else {
            $current_qty = $_SESSION["cart"][$product_id] ?? 0;
            if ($current_qty + 1 > $product["stock"]) {
                $_SESSION["error"] = "Only " . $product["stock"] . " in stock.";
            } else {
                $_SESSION["cart"][$product_id] = $current_qty + 1;
                $_SESSION["message"] = $product["name"] . " added to cart.";
            }
        }
    }
    header("location: index.php");
    exit();
}

// 2. Update cart quantity
if (isset($_POST["update_qty"])) {
    $product_id = $_POST["product_id"];
    $new_qty = (int) $_POST["qty"];

    if ($new_qty <= 0) {
        unset($_SESSION["cart"][$product_id]);
    } else {
        $res = mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id'");
        $product = mysqli_fetch_assoc($res);

        if ($new_qty > $product["stock"]) {
            $_SESSION["cart"][$product_id] = $product["stock"];
            $_SESSION["error"] = "Only " . $product["stock"] . " available.";
        } else {
            $_SESSION["cart"][$product_id] = $new_qty;
            $_SESSION["message"] = "Cart updated.";
        }
    }
    header("location: index.php");
    exit();
}

// 3. Remove single item
if (isset($_GET["remove"])) {
    $remove_id = $_GET["remove"];
    unset($_SESSION["cart"][$remove_id]);
    header("location: index.php");
    exit();
}

// 4. Clear full cart
if (isset($_GET["clear"])) {
    $_SESSION["cart"] = [];
    header("location: index.php");
    exit();
}

// 5. Checkout & Save Sale
if (isset($_POST["checkout"])) {
    $customer_name = $_POST["customer_name"];
    $customer_phone = $_POST["customer_phone"];
    $discount = (float) $_POST["discount"];
    $payment_method = $_POST["payment_method"];
    $admin_id = $_SESSION["admin_id"];

    if (empty($_SESSION["cart"])) {
        $checkout_error = "Cart is empty.";
    } else {
        $subtotal = 0;
        $total_cost = 0;
        $cart_items = [];

        foreach ($_SESSION["cart"] as $pid => $qty) {
            $res = mysqli_query($conn, "SELECT * FROM products WHERE id='$pid'");
            $row = mysqli_fetch_assoc($res);

            if ($row["expiry_date"] < $today) {
                $checkout_error = $row["name"] . " has expired and cannot be sold.";
                break;
            }
            if ($qty > $row["stock"]) {
                $checkout_error = "Insufficient stock for " . $row["name"];
                break;
            }

            $line_total = $row["selling_price"] * $qty;
            $subtotal += $line_total;
            $total_cost += $row["cost_price"] * $qty;

            $row["cart_qty"] = $qty;
            $row["line_total"] = $line_total;
            $cart_items[] = $row;
        }

        if (!isset($checkout_error)) {
            if ($discount < 0 || $discount > $subtotal) {
                $checkout_error = "Invalid discount amount.";
            } else {
                $grand_total = $subtotal - $discount;
                $invoice_no = "INV-" . date("Ymd-His");

                $sql = "INSERT INTO sales (invoice_no, customer_name, customer_phone, subtotal, discount, grand_total, payment_method, total_cost, sold_by) 
                        VALUES ('$invoice_no', '$customer_name', '$customer_phone', '$subtotal', '$discount', '$grand_total', '$payment_method', '$total_cost', '$admin_id')";

                if (mysqli_query($conn, $sql)) {
                    $sale_id = mysqli_insert_id($conn);

                    foreach ($cart_items as $item) {
                        $pid = $item["id"];
                        $pname = $item["name"];
                        $qty = $item["cart_qty"];
                        $price = $item["selling_price"];
                        $cost = $item["cost_price"];
                        $ltot = $item["line_total"];

                        $item_sql = "INSERT INTO sale_items (sale_id, product_id, product_name, quantity, unit_price, cost_price, line_total) 
                                     VALUES ('$sale_id', '$pid', '$pname', '$qty', '$price', '$cost', '$ltot')";
                        mysqli_query($conn, $item_sql);

                        $stock_sql = "UPDATE products SET stock = stock - $qty WHERE id='$pid'";
                        mysqli_query($conn, $stock_sql);
                    }

                    $_SESSION["cart"] = [];
                    header("location: ../invoices/print.php?id=$sale_id");
                    exit();
                } else {
                    die("Sale failed: " . mysqli_error($conn));
                }
            }
        }
    }
}

// Fetch cart items for display
$display_cart = [];
$cart_total = 0;
$cart_count = 0;

foreach ($_SESSION["cart"] as $pid => $qty) {
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id='$pid'");
    if ($row = mysqli_fetch_assoc($res)) {
        $row["cart_qty"] = $qty;
        $row["line_total"] = $row["selling_price"] * $qty;
        $cart_total += $row["line_total"];
        $cart_count += $qty;
        $display_cart[] = $row;
    }
}

// Product Search & Filter
$q = $_GET["q"] ?? "";
$cat_id = $_GET["category_id"] ?? "";

$search_sql = "SELECT p.*, c.name AS category_name 
               FROM products p 
               JOIN categories c ON p.category_id = c.id 
               WHERE 1=1";

if (!empty($q)) {
    $search_sql .= " AND (p.name LIKE '%$q%' OR p.generic_name LIKE '%$q%')";
}
if (!empty($cat_id)) {
    $search_sql .= " AND p.category_id = '$cat_id'";
}
$search_sql .= " ORDER BY p.name ASC LIMIT 20";

$search_results = mysqli_query($conn, $search_sql);
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

$pageTitle = "Billing (POS)";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title h4 mb-1">Billing (POS)</h1>
        <p class="text-muted small mb-0">Search medicines, build the cart and complete the sale.</p>
    </div>
    <span class="badge text-bg-success fs-6"><?php echo $cart_count; ?> item(s) in cart</span>
</div>

<?php if (isset($_SESSION["message"])) { ?>
    <div class="alert alert-success py-2"><?php echo $_SESSION["message"];
    unset($_SESSION["message"]); ?></div>
<?php } ?>
<?php if (isset($_SESSION["error"])) { ?>
    <div class="alert alert-danger py-2"><?php echo $_SESSION["error"];
    unset($_SESSION["error"]); ?></div>
<?php } ?>
<?php if (isset($checkout_error)) { ?>
    <div class="alert alert-danger py-2"><?php echo $checkout_error; ?></div>
<?php } ?>

<div class="row g-3">
    <!-- Left: Product Search -->
    <div class="col-xl-7">
        <div class="card mb-3">
            <div class="card-body pb-3">
                <form method="get" action="" class="row g-2 align-items-end">
                    <div class="col-sm-5">
                        <label class="form-label small text-muted mb-1">Search product</label>
                        <input type="text" class="form-control" name="q" value="<?php echo $q; ?>"
                            placeholder="Name or generic name">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label small text-muted mb-1">Category</label>
                        <select class="form-select" name="category_id">
                            <option value="">All categories</option>
                            <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                                <option value="<?php echo $cat["id"]; ?>" <?php if ($cat_id == $cat["id"])
                                       echo "selected"; ?>>
                                    <?php echo $cat["name"]; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-3 d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-grow-1"><i class="bi bi-search"></i></button>
                        <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white fw-semibold">Products</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Medicine</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Stock</th>
                            <th>Expiry</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($search_results) > 0) { ?>
                            <?php while ($row = mysqli_fetch_assoc($search_results)) {
                                $is_expired = ($row["expiry_date"] < $today);
                                $in_stock = ($row["stock"] > 0);
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo $row["name"]; ?></div>
                                        <div class="small text-muted"><?php echo $row["category_name"]; ?> &middot;
                                            <?php echo $row["generic_name"]; ?>
                                        </div>
                                    </td>
                                    <td class="text-end">৳ <?php echo number_format($row["selling_price"], 2); ?></td>
                                    <td class="text-center">
                                        <span
                                            class="badge <?php echo $in_stock ? 'text-bg-light border' : 'text-bg-danger'; ?>">
                                            <?php echo $row["stock"]; ?>
                                        </span>
                                    </td>
                                    <td class="small">
                                        <?php echo $row["expiry_date"]; ?>
                                        <?php if ($is_expired) { ?>
                                            <div><span class="badge text-bg-danger">Expired</span></div>
                                        <?php } ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if (!$is_expired && $in_stock) { ?>
                                            <form method="post" action="" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?php echo $row["id"]; ?>">
                                                <button type="submit" name="add_to_cart" class="btn btn-sm btn-success">
                                                    <i class="bi bi-cart-plus me-1"></i>Add
                                                </button>
                                            </form>
                                        <?php } else { ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                <?php echo $is_expired ? "Expired" : "Out of stock"; ?>
                                            </button>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No products found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Cart -->
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-cart3 me-1"></i>Cart</span>
                <?php if (!empty($display_cart)) { ?>
                    <a href="index.php?clear=1" class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Clear cart?')">Clear</a>
                <?php } ?>
            </div>

            <?php if (!empty($display_cart)) { ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th style="width: 130px;">Qty</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($display_cart as $item) { ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold small"><?php echo $item["name"]; ?></div>
                                        <div class="text-muted small">৳ <?php echo number_format($item["selling_price"], 2); ?>
                                            each</div>
                                    </td>
                                    <td>
                                        <form method="post" action="" class="d-flex gap-1">
                                            <input type="hidden" name="product_id" value="<?php echo $item["id"]; ?>">
                                            <input type="number" class="form-control form-control-sm" name="qty"
                                                value="<?php echo $item["cart_qty"]; ?>" min="0"
                                                max="<?php echo $item["stock"]; ?>">
                                            <button type="submit" name="update_qty" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end small">৳ <?php echo number_format($item["line_total"], 2); ?></td>
                                    <td class="text-end">
                                        <a href="index.php?remove=<?php echo $item["id"]; ?>"
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-body border-top">
                    <form method="post" action="">
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small mb-1">Customer Name</label>
                                <input type="text" class="form-control form-control-sm" name="customer_name"
                                    placeholder="Optional">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small mb-1">Customer Phone</label>
                                <input type="text" class="form-control form-control-sm" name="customer_phone"
                                    placeholder="Optional">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small mb-1">Discount (৳)</label>
                                <input type="number" class="form-control form-control-sm" id="discount" name="discount"
                                    min="0" max="<?php echo $cart_total; ?>" step="0.01" value="0">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small mb-1">Payment Method</label>
                                <select class="form-select form-select-sm" name="payment_method">
                                    <option value="Cash">Cash</option>
                                    <option value="Card">Card</option>
                                    <option value="Mobile Banking">Mobile Banking</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Subtotal</span>
                            <span>৳ <?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Discount</span>
                            <span id="discount-display">৳ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between fs-5 fw-bold border-top pt-2 mb-3">
                            <span>Grand Total</span>
                            <span id="grand-total-display">৳ <?php echo number_format($cart_total, 2); ?></span>
                        </div>

                        <button type="submit" name="checkout" class="btn btn-success w-100">
                            <i class="bi bi-check2-circle me-1"></i>Complete Sale
                        </button>
                    </form>
                </div>
            <?php } else { ?>
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                    The cart is empty. Search for a medicine and add it.
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
    var subtotal = <?php echo $cart_total; ?>;
    var discountInput = document.getElementById("discount");
    if (discountInput) {
        discountInput.addEventListener("input", function () {
            var val = parseFloat(discountInput.value) || 0;
            if (val < 0) val = 0;
            if (val > subtotal) val = subtotal;
            document.getElementById("discount-display").textContent = "৳ " + val.toFixed(2);
            document.getElementById("grand-total-display").textContent = "৳ " + (subtotal - val).toFixed(2);
        });
    }
</script>

<?php include "../includes/footer.php"; ?>