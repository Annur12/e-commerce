<?php
session_start();
// Include necessary files for database connection
include_once 'connection/connect.php';

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the POST request
    $productId = mysqli_real_escape_string($conn, $_POST['productId']);

    // Remove the item from the cart
    $removeQuery = "DELETE FROM cart WHERE product_id = '$productId'";
    $removeResult = mysqli_query($conn, $removeQuery);

    if ($removeResult) {
        // Fetch new total items and total price
        $newTotalItemsQuery = "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = $user_id";
        $newTotalItemsResult = mysqli_query($conn, $newTotalItemsQuery);
        $newTotalItems = mysqli_fetch_assoc($newTotalItemsResult)['total_items'];

        $newTotalPriceQuery = "SELECT SUM(products.price * cart.quantity) as total_price FROM cart JOIN products ON cart.product_id = products.product_id WHERE user_id = $user_id";
        $newTotalPriceResult = mysqli_query($conn, $newTotalPriceQuery);
        $newTotalPrice = mysqli_fetch_assoc($newTotalPriceResult)['total_price'];

        // Return success with new data
        echo json_encode(['success' => true, 'message' => 'Item removed successfully', 'newTotalItems' => $newTotalItems, 'newTotalPrice' => $newTotalPrice]);
        // Ensure that nothing else is being outputted here
        exit();
    } else {
        // Return an error message
        echo json_encode(['success' => false, 'message' => 'Error removing item']);
        // Ensure that nothing else is being outputted here
        exit();
    }
} else {
    // Handle non-POST requests, e.g., redirect or show an error
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    // Ensure that nothing else is being outputted here
    exit();
}
?>
