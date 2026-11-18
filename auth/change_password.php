<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

$admin_id = $_SESSION["admin_id"];

if (isset($_POST["submit"])) {
    $current = $_POST["current_password"];
    $new = $_POST["new_password"];
    $confirm = $_POST["confirm_password"];

    if (empty($current)) {
        $current_error = "* Input current password";
    }
    if (empty($new)) {
        $new_error = "* Input new password";
    } elseif (strlen($new) < 6) {
        $new_error = "* Password must be at least 6 characters";
    }
    if (empty($confirm)) {
        $confirm_error = "* Confirm your new password";
    } elseif ($new != $confirm) {
        $confirm_error = "* Passwords do not match";
    }

    if (!isset($current_error) && !isset($new_error) && !isset($confirm_error)) {
        $hashed_current = md5($current);
        $sql = "SELECT * FROM admins WHERE id='$admin_id' AND password='$hashed_current'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $hashed_new = md5($new);
            $update = "UPDATE admins SET password='$hashed_new' WHERE id='$admin_id'";
            if (mysqli_query($conn, $update)) {
                $success_msg = "Password changed successfully!";
            } else {
                die("Update failed: " . mysqli_error($conn));
            }
        } else {
            $current_error = "* Current password is incorrect";
        }
    }
}

$pageTitle = "Change Password";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="mb-4">
  <h1 class="page-title h4 mb-1">Change Password</h1>
  <p class="text-muted small mb-0">Update the password used to sign in to the admin panel.</p>
</div>

<div class="row">
  <div class="col-lg-6">
    <?php if (isset($success_msg)) { ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php } ?>

    <div class="card">
      <div class="card-body">
        <form method="post" action="">
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" class="form-control" name="current_password">
            <span style="color: red; font-size: 13px;"><?php echo $current_error ?? ''; ?></span>
          </div>

          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" class="form-control" name="new_password">
            <span style="color: red; font-size: 13px;"><?php echo $new_error ?? ''; ?></span>
          </div>

          <div class="mb-4">
            <label class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" name="confirm_password">
            <span style="color: red; font-size: 13px;"><?php echo $confirm_error ?? ''; ?></span>
          </div>

          <button type="submit" name="submit" class="btn btn-success">
            <i class="bi bi-check2-circle me-1"></i>Update Password
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>