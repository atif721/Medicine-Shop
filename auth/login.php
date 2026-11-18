<?php
session_start();
include "../config/db.php";

if (isset($_SESSION["admin_id"])) {
    header("location: ../index.php");
    exit();
}

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if (empty($username)) {
        $username_error = "* Input username";
    }

    if (empty($password)) {
        $password_error = "* Input password";
    }

    if (!isset($username_error) && !isset($password_error)) {
        $hashed_password = md5($password);
        $sql = "SELECT * FROM admins WHERE username='$username' AND password='$hashed_password'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION["admin_id"] = $row["id"];
            $_SESSION["admin_name"] = $row["full_name"];
            $_SESSION["admin_user"] = $row["username"];
            header("location: ../index.php");
            exit();
        } else {
            $login_error = "Invalid username or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Medicine Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page d-flex align-items-center justify-content-center py-5">

<div class="card shadow-sm login-card w-100" style="max-width: 420px;">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="login-icon mb-2"><i class="bi bi-capsule-pill fs-1 text-success"></i></div>
            <h1 class="h4 mb-1">Medicine Shop</h1>
            <p class="text-muted small mb-0">Admin Billing Panel</p>
        </div>

        <?php if (isset($login_error)) { ?>
            <div class="alert alert-danger py-2 small"><?php echo $login_error; ?></div>
        <?php } ?>

        <form method="post" action="">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" value="<?php echo $username ?? ''; ?>">
                <span style="color: red; font-size: 13px;"><?php echo $username_error ?? ''; ?></span>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password">
                <span style="color: red; font-size: 13px;"><?php echo $password_error ?? ''; ?></span>
            </div>

            <button type="submit" name="login" class="btn btn-success w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i>Login
            </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            Default login: <strong>admin</strong> / <strong>admin123</strong>
        </p>
    </div>
</div>

</body>
</html>