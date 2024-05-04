<?php
include_once 'connection/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $cartCountQuery = "SELECT COUNT(*) AS count FROM cart WHERE user_id = '$userId'";
    $cartCountResult = mysqli_query($conn, $cartCountQuery);
    $cartCountRow = mysqli_fetch_assoc($cartCountResult);
    echo $cartCountRow['count'];
} else {
    echo 0;
}
?>
