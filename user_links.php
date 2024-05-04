<?php
session_start();
include_once 'connection/connect.php';

function getCartCount($userId, $conn)
{
    $cartCountQuery = "SELECT SUM(quantity) AS total FROM cart WHERE user_id = $userId";
    $cartCountResult = mysqli_query($conn, $cartCountQuery);
    $cartCountRow = mysqli_fetch_assoc($cartCountResult);

    return $cartCountRow['total'];
}

$cartCount = 0;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $cartCount = getCartCount($userId, $conn);

    $userImageQuery = "SELECT profile_image FROM users WHERE id = $userId";
    $userImageResult = mysqli_query($conn, $userImageQuery);
    $userImageRow = mysqli_fetch_assoc($userImageResult);

    $userImage = $userImageRow['profile_image'];
}
?>

<header>
    <a class="logo-store" href="index.php"><img class="mc-logo" src="image/mc-logo.png"></a>
    <nav>
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </nav>
    <div class="user-actions">
        <div class="cart-icon" onclick="navigateToCart()">
            <i class="fas fa-shopping-cart"></i>
            <?php

            if ($cartCount > 0) {
                echo '<div class="cart-count" id="cartCount">' . $cartCount . '</div>';
            }
            ?>
        </div>

        <?php
        if (isset($_SESSION['user_id'])) {

            $userId = $_SESSION['user_id'];
            $username = $_SESSION['username'];

            echo '<div class="user-action">';
            echo '<a href="profile.php" class="username" title="Profile">';
            echo  $username;
            echo '</a>';
            echo '<a href="logout.php" class="logout">Logout</a>';
            echo '</div>';
        } else {

            echo '<div class="login-register">';
            echo '<a href="login.php ">Login</a>';
            echo '<a href="register.php">Register</a>';
            echo '</div>';
        }

        ?>
    </div>
</header>