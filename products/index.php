<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

$q = $_GET["q"] ?? "";
$category_id = $_GET["category_id"] ?? "";

$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

if (!empty($q)) {
    $sql .= " AND (p.name LIKE '%$q%' OR p.generic_name LIKE '%$q%')";
}
if (!empty($category_id)) {
    $sql .= " AND p.category_id = '$category_id'";
}
$sql .= " ORDER BY p.name ASC";

$products = mysqli_query($conn, $sql);
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

$today = date("Y-m-d");
$next_60 = date("Y-m-d", strtotime("+60 days"));

$pageTitle = "Products";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title h4 mb-1">Products</h1>
        <p class="text-muted small mb-0">Manage medicines, prices, stock and expiry dates.</p>
    </div>
    <a href="create.php" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Add Product
    </a>
</div>

<?php if (isset($_SESSION["message"])) { ?>
    <div class="alert alert-success py-2"><?php echo $_SESSION["message"];
    unset($_SESSION["message"]); ?></div>
<?php } ?>
<?php if (isset($_SESSION["warning"])) { ?>
    <div class="alert alert-warning py-2"><?php echo $_SESSION["warning"];
    unset($_SESSION["warning"]); ?></div>
<?php } ?>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="get" action="" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Search by name or generic name</label>
                <input type="text" class="form-control" name="q" value="<?php echo $q; ?>"
                    placeholder="e.g. Paracetamol">
            </div>

            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Category</label>
                <select class="form-select" name="category_id">
                    <option value="">All categories</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                        <option value="<?php echo $cat["id"]; ?>" <?php if ($category_id == $cat["id"])
                               echo "selected"; ?>>
                            <?php echo $cat["name"]; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success flex-grow-1">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="index.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th class="text-end">Cost</th>
                    <th class="text-end">Selling</th>
                    <th class="text-end">MRP</th>
                    <th class="text-center">Stock</th>
                    <th>Expiry</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($products) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($products)) {
                        $is_expired = ($row["expiry_date"] < $today);
                        $is_expiring = (!$is_expired && $row["expiry_date"] <= $next_60);
                        $low_stock = ($row["stock"] <= $row["reorder_level"]);
                        $mrp_warning = ($row["selling_price"] > $row["mrp"]);
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo $row["name"]; ?></div>
                                <div class="small text-muted"><?php echo $row["generic_name"] ?: "—"; ?></div>
                            </td>
                            <td><?php echo $row["category_name"]; ?></td>
                            <td class="text-end">৳ <?php echo number_format($row["cost_price"], 2); ?></td>
                            <td class="text-end">
                                ৳ <?php echo number_format($row["selling_price"], 2); ?>
                                <?php if ($mrp_warning) { ?>
                                    <i class="bi bi-exclamation-triangle text-warning" title="Selling price is higher than MRP"></i>
                                <?php } ?>
                            </td>
                            <td class="text-end">৳ <?php echo number_format($row["mrp"], 2); ?></td>
                            <td class="text-center">
                                <span
                                    class="badge <?php echo $row["stock"] == 0 ? 'text-bg-danger' : ($low_stock ? 'text-bg-warning' : 'text-bg-light border'); ?>">
                                    <?php echo $row["stock"]; ?>         <?php echo $row["unit"]; ?>
                                </span>
                                <div class="small text-muted">Reorder: <?php echo $row["reorder_level"]; ?></div>
                            </td>
                            <td>
                                <?php echo date("d M Y", strtotime($row["expiry_date"])); ?>
                                <?php if ($is_expired) { ?>
                                    <div><span class="badge text-bg-danger">Expired</span></div>
                                <?php } elseif ($is_expiring) { ?>
                                    <div><span class="badge text-bg-warning">Expiring soon</span></div>
                                <?php } ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Delete this product?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No products found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>