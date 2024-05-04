<?php

include_once 'header.php'; 
include_once 'user_links.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<div class="thank-you-container">
    <h2>Thank You for Your Order!</h2>
    <p>Your order has been successfully placed. We appreciate your business. <i class="fa-solid fa-van-shuttle"></i></p>
    <a href="profile.php" class="button">View your orders <i class="fa-solid fa-basket-shopping"></i></a>
</div>
