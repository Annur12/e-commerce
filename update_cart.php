<?php
session_start();
include_once 'connection/connect.php';

if(isset($_POST["action"]) && isset($_POST["product_id"])) {
    $action = $_POST["action"];
    $productId = $_POST["product_id"];
    
    session_start();
    $userId = $_SESSION['user_id'];

    if ($action == "remove") {
        // Remove the item from the cart
        $removeQuery = "DELETE FROM cart WHERE user_id = $userId AND product_id = $productId";
        mysqli_query($conn, $removeQuery);
    } else {
        // Fetch current quantity from the cart
        $quantityQuery = "SELECT quantity FROM cart WHERE user_id = $userId AND product_id = $productId";
        $quantityResult = mysqli_query($conn, $quantityQuery);
        $currentQuantity = mysqli_fetch_assoc($quantityResult)['quantity'];

        // Update quantity based on action (increment or decrement)
        $newQuantity = ($action == "increment") ? ($currentQuantity + 1) : max(1, $currentQuantity - 1);

        // Update the cart
        $updateQuery = "UPDATE cart SET quantity = $newQuantity WHERE user_id = $userId AND product_id = $productId";
        mysqli_query($conn, $updateQuery);
    }

    // Return some response if needed
    echo "success";
}
?>
