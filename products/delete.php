<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

$id = $_GET["id"];

$sql = "DELETE FROM products WHERE id='$id'";
$result = mysqli_query($conn, $sql);

if ($result) {
    $_SESSION["message"] = "Product deleted successfully!";
    header("location: index.php");
} else {
    die("Deletion failed: " . mysqli_error($conn));
}
?>