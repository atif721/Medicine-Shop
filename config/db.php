<?php
include_once "config.php";

$servername = "localhost";
$username = "phpuser";
$password = "root123";
$dbname = "medicine_shop";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Server connection failed: " . mysqli_connect_error());
}
