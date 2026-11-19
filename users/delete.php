<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

$id = $_GET["id"];

// Prevent deleting your own currently logged-in account
if ($id == $_SESSION["admin_id"]) {
  $_SESSION["error"] = "You cannot delete your own logged-in account.";
  header("location: index.php");
  exit();
}

$sql = "DELETE FROM admins WHERE id='$id'";
if (mysqli_query($conn, $sql)) {
  $_SESSION["message"] = "User deleted successfully!";
  header("location: index.php");
} else {
  die("Deletion failed: " . mysqli_error($conn));
}
