<?php
session_start();

// Assuming the user information is stored in the session
if (!isset($_SESSION['user_id']) || $_SESSION['userType'] != 1) {
    // Redirect non-admin users to the login page
    header("Location: login.php");
    exit();
}

?>

<div id="sidebar">
    <div class="mc-logo">
        <img src="../image/mc-logo.png" alt="">
    </div>
    
    <a href="dashboard.php"><i class="fa fa-tachometer-alt"></i> Dashboard</a>
    <button class="dropdown-btn"><i class="fa fa-box"></i> Product</button>
    <div class="dropdown-container">
        <a href="add_product.php">Add Product</a>
        <a href="add_category.php">Add Category</a>
    </div>
    
    <a href="order_list.php"><i class="fa fa-shopping-cart"></i> Orders</a>
    <a href="user_list.php"><i class="fa fa-users"></i> Users</a>
</div>

<div id="header">
    <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
    <a href="../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>