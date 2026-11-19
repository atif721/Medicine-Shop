<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

if (isset($_POST["submit"])) {
    $name = $_POST["name"];
    $generic_name = $_POST["generic_name"];
    $category_id = $_POST["category_id"];
    $unit = $_POST["unit"];
    $cost_price = $_POST["cost_price"];
    $selling_price = $_POST["selling_price"];
    $mrp = $_POST["mrp"];
    $stock = $_POST["stock"];
    $reorder_level = $_POST["reorder_level"];
    $expiry_date = $_POST["expiry_date"];

    if (empty($name)) {
        $name_error = "* Input product name";
    }
    if (empty($category_id)) {
        $category_error = "* Select a category";
    }
    if (empty($unit)) {
        $unit_error = "* Input unit";
    }
    if (empty($expiry_date)) {
        $expiry_error = "* Select expiry date";
    }
    if ($stock === "" || $stock < 0 || floor($stock) != $stock) {
        $stock_error = "* Stock must be a whole number";
    }
    if ($reorder_level === "" || $reorder_level < 0 || floor($reorder_level) != $reorder_level) {
        $reorder_error = "* Reorder level must be a whole number";
    }

    if (!isset($name_error) && !isset($category_error) && !isset($unit_error) && !isset($expiry_error) && !isset($stock_error) && !isset($reorder_error)) {
        $sql = "INSERT INTO products (name, generic_name, category_id, unit, cost_price, selling_price, mrp, stock, reorder_level, expiry_date) 
                VALUES ('$name', '$generic_name', '$category_id', '$unit', '$cost_price', '$selling_price', '$mrp', '$stock', '$reorder_level', '$expiry_date')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION["message"] = "Product added successfully!";
            if ($selling_price > $mrp) {
                $_SESSION["warning"] = "Warning: Selling price for '$name' is higher than printed MRP.";
            }
            header("location: index.php");
            exit();
        } else {
            die("Insertion failed: " . mysqli_error($conn));
        }
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

$pageTitle = "Add Product";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title h4 mb-0">Add Product</h1>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Products
    </a>
</div>

<form method="post" action="">
    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Medicine Information</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="<?php echo $name ?? ''; ?>">
                    <span style="color: red; font-size: 13px;"><?php echo $name_error ?? ''; ?></span>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Generic Name</label>
                    <input type="text" class="form-control" name="generic_name"
                        value="<?php echo $generic_name ?? ''; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" name="category_id">
                        <option value="">Select category</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                            <option value="<?php echo $cat["id"]; ?>" <?php if (($category_id ?? '') == $cat["id"])
                                   echo "selected"; ?>>
                                <?php echo $cat["name"]; ?>
                            </option>
                        <?php } ?>
                    </select>
                    <span style="color: red; font-size: 13px;"><?php echo $category_error ?? ''; ?></span>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="unit" value="<?php echo $unit ?? 'Piece'; ?>">
                    <span style="color: red; font-size: 13px;"><?php echo $unit_error ?? ''; ?></span>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="expiry_date"
                        value="<?php echo $expiry_date ?? ''; ?>">
                    <span style="color: red; font-size: 13px;"><?php echo $expiry_error ?? ''; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Pricing</div>
        <div class="card-body">
            <div class="alert alert-warning py-2 small d-none" id="mrp-warning">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Selling price is higher than the printed MRP!
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cost Price (৳)</label>
                    <input type="number" class="form-control" name="cost_price" min="0" step="0.01"
                        value="<?php echo $cost_price ?? '0'; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Selling Price (৳)</label>
                    <input type="number" class="form-control" id="selling_price" name="selling_price" min="0"
                        step="0.01" value="<?php echo $selling_price ?? '0'; ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Printed MRP (৳)</label>
                    <input type="number" class="form-control" id="mrp" name="mrp" min="0" step="0.01"
                        value="<?php echo $mrp ?? '0'; ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold">Stock</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="stock" min="0" step="1"
                        value="<?php echo $stock ?? '0'; ?>">
                    <span style="color: red; font-size: 13px;"><?php echo $stock_error ?? ''; ?></span>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Reorder Level <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="reorder_level" min="0" step="1"
                        value="<?php echo $reorder_level ?? '0'; ?>">
                    <span style="color: red; font-size: 13px;"><?php echo $reorder_error ?? ''; ?></span>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" name="submit" class="btn btn-success mb-4">
        <i class="bi bi-check2-circle me-1"></i>Save Product
    </button>
</form>

<script>
    var selling = document.getElementById("selling_price");
    var mrp = document.getElementById("mrp");
    var warning = document.getElementById("mrp-warning");

    function checkPrice() {
        if ((parseFloat(selling.value) || 0) > (parseFloat(mrp.value) || 0)) {
            warning.classList.remove("d-none");
        } else {
            warning.classList.add("d-none");
        }
    }
    selling.addEventListener("input", checkPrice);
    mrp.addEventListener("input", checkPrice);
</script>

<?php include "../includes/footer.php"; ?>