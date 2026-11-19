<?php
session_start();
include "../config/db.php";
include "../includes/auth.php";

if (isset($_POST["submit"])) {
  $full_name = $_POST["full_name"];
  $username = $_POST["username"];
  $password = $_POST["password"];

  if (empty($full_name)) {
    $name_error = "* Input full name";
  }
  if (empty($username)) {
    $username_error = "* Input username";
  }
  if (empty($password)) {
    $password_error = "* Input password";
  } elseif (strlen($password) < 6) {
    $password_error = "* Minimum 6 characters";
  }

  if (!isset($name_error) && !isset($username_error) && !isset($password_error)) {
    $check = mysqli_query($conn, "SELECT * FROM admins WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
      $username_error = "* This username already exists";
    } else {
      $hashed_password = md5($password);
      $sql = "INSERT INTO admins (full_name, username, password) VALUES ('$full_name', '$username', '$hashed_password')";
      if (mysqli_query($conn, $sql)) {
        $_SESSION["message"] = "User added successfully!";
        header("location: index.php");
        exit();
      } else {
        die("Insertion failed: " . mysqli_error($conn));
      }
    }
  }
}

$users = mysqli_query($conn, "SELECT * FROM admins ORDER BY id ASC");

$pageTitle = "Manage Users";
include "../includes/header.php";
include "../includes/sidebar.php";
?>

<div class="mb-4">
  <h1 class="page-title h4 mb-1">Manage Users</h1>
  <p class="text-muted small mb-0">Add or remove admin and cashier accounts.</p>
</div>

<?php if (isset($_SESSION["message"])) { ?>
  <div class="alert alert-success py-2"><?php echo $_SESSION["message"];
  unset($_SESSION["message"]); ?></div>
<?php } ?>
<?php if (isset($_SESSION["error"])) { ?>
  <div class="alert alert-danger py-2"><?php echo $_SESSION["error"];
  unset($_SESSION["error"]); ?></div>
<?php } ?>

<div class="row g-3">
  <!-- Add User Form -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header bg-white fw-semibold">Add New User</div>
      <div class="card-body">
        <form method="post" action="">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="full_name" value="<?php echo $full_name ?? ''; ?>">
            <span style="color: red; font-size: 13px;"><?php echo $name_error ?? ''; ?></span>
          </div>

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

          <button type="submit" name="submit" class="btn btn-success w-100">
            <i class="bi bi-person-plus me-1"></i>Add User
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- User List Table -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header bg-white fw-semibold">Existing Users</div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Full Name</th>
              <th>Username</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1;
            while ($row = mysqli_fetch_assoc($users)) { ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td class="fw-semibold"><?php echo $row["full_name"]; ?></td>
                <td><?php echo $row["username"]; ?></td>
                <td class="text-end">
                  <?php if ($row["id"] != $_SESSION["admin_id"]) { ?>
                    <a href="delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-sm btn-outline-danger"
                      onclick="return confirm('Delete this user?');">
                      <i class="bi bi-trash"></i>
                    </a>
                  <?php } else { ?>
                    <span class="badge text-bg-light border">Current</span>
                  <?php } ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>