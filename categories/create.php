<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

if (isset($_POST["submit"])) {
    $name = $_POST["name"];
    $description = $_POST["description"];

    if (empty($name)) {
        $name_error = "* Input category name";
    }

    if (!isset($name_error)) {
        $check = mysqli_query($conn, "SELECT * FROM categories WHERE name='$name'");
        if (mysqli_num_rows($check) > 0) {
            $name_error = "* This category already exists";
        } else {
            $sql = "INSERT INTO categories (name, description) VALUES ('$name', '$description')";
            if (mysqli_query($conn, $sql)) {
                $_SESSION["message"] = "Category added successfully!";
                header("location: index.php");
                exit();
            } else {
                die("Insertion failed: " . mysqli_error($conn));
            }
        }
    }
}

$pageTitle = "Add Category";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title h4 mb-0">Add Category</h1>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Categories
    </a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?php echo $name ?? ''; ?>">
                        <span style="color: red; font-size: 13px;"><?php echo $name_error ?? ''; ?></span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"
                            rows="3"><?php echo $description ?? ''; ?></textarea>
                    </div>

                    <button type="submit" name="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i>Save Category
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>