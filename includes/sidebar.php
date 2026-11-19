<?php
$base = file_exists("config/db.php") ? "" : "../";
$current_page = $_SERVER["PHP_SELF"];
?>
<div class="app-shell d-flex">
    <aside class="app-sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php if ($base == "" && basename($current_page) == "index.php")
                    echo "active"; ?>" href="<?php echo $base; ?>index.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if (strpos($current_page, "/categories/") !== false)
                    echo "active"; ?>" href="<?php echo $base; ?>categories/index.php">
                    <i class="bi bi-tags me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if (strpos($current_page, "/products/") !== false)
                    echo "active"; ?>" href="<?php echo $base; ?>products/index.php">
                    <i class="bi bi-box-seam me-2"></i>Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if (strpos($current_page, "/billing/") !== false)
                    echo "active"; ?>" href="<?php echo $base; ?>billing/index.php">
                    <i class="bi bi-cart3 me-2"></i>Billing (POS)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if (strpos($current_page, "/sales/") !== false)
                    echo "active"; ?>" href="<?php echo $base; ?>sales/index.php">
                    <i class="bi bi-receipt me-2"></i>Sales History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if (strpos($current_page, "/reports/") !== false)
                    echo "active"; ?>" href="<?php echo $base; ?>reports/profit_loss.php">
                    <i class="bi bi-graph-up-arrow me-2"></i>Profit / Loss
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link <?php if (strpos($current_page, "/auth/") !== false)
                    echo "active"; ?>" href="<?php echo $base; ?>auth/change_password.php">
                    <i class="bi bi-key me-2"></i>Change Password
            <li class="nav-item">
                <a class="nav-link <?php if (strpos($current_page, "/users/") !== false)
                    echo "active"; ?>"
                    href="<?php echo $base; ?>users/index.php">
                    <i class="bi bi-people me-2"></i>Users
                </a>
            </li>
            </a>
            </li>
        </ul>
    </aside>

    <main class="app-main flex-grow-1">