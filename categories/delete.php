<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

$id = $_GET["id"];

$check = mysqli_query($conn, "SELECT * FROM products WHERE category_id='$id'");
if (mysqli_num_rows($check) > 0) {
    $_SESSION["error"] = "Cannot delete category because products exist in it.";
    header("location: index.php");
    exit();
}

$sql = "DELETE FROM categories WHERE id='$id'";
$result = mysqli_query($conn, $sql);

if ($result) {
    $_SESSION["message"] = "Category deleted successfully!";
    header("location: index.php");
} else {
    die("Deletion failed: " . mysqli_error($conn));
}
?>