<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

$sql = "SELECT c.*, COUNT(p.id) AS product_count 
        FROM categories c 
        LEFT JOIN products p ON p.category_id = c.id 
        GROUP BY c.id 
        ORDER BY c.name ASC";
$result = mysqli_query($conn, $sql);

$pageTitle = "Categories";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title h4 mb-1">Categories</h1>
        <p class="text-muted small mb-0">Manage medicine categories used by products.</p>
    </div>
    <a href="create.php" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Add Category
    </a>
</div>

<?php if (isset($_SESSION["message"])) { ?>
    <div class="alert alert-success py-2"><?php echo $_SESSION["message"];
    unset($_SESSION["message"]); ?></div>
<?php } ?>
<?php if (isset($_SESSION["error"])) { ?>
    <div class="alert alert-danger py-2"><?php echo $_SESSION["error"];
    unset($_SESSION["error"]); ?></div>
<?php } ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 70px;">#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="text-center">Products</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <?php $i = 1;
                    while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td class="text-muted"><?php echo $i++; ?></td>
                            <td class="fw-semibold"><?php echo $row["name"]; ?></td>
                            <td class="text-muted"><?php echo $row["description"] ?: "—"; ?></td>
                            <td class="text-center">
                                <span class="badge text-bg-light border"><?php echo $row["product_count"]; ?></span>
                            </td>
                            <td class="text-muted small"><?php echo date("d M Y", strtotime($row["created_at"])); ?></td>
                            <td class="text-end">
                                <a href="edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($row["product_count"] == 0) { ?>
                                    <a href="delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete this category?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php } else { ?>
                                    <button class="btn btn-sm btn-outline-danger" disabled
                                        title="Cannot delete category with products">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No categories found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>