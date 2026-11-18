<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$base = file_exists("config/db.php") ? "" : "../";

if (!isset($_SESSION["admin_id"])) {
    header("location: " . $base . "auth/login.php");
    exit();
}
