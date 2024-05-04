<?php
include_once '../connection/connect.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $raw_post_data = file_get_contents("php://input");
    
    // Decode the JSON data
    $post_data = json_decode($raw_post_data, true);

    // Get the order_id and new_status from the decoded JSON data
    $order_id = isset($post_data['order_id']) ? $post_data['order_id'] : null;
    $new_status = isset($post_data['new_status']) ? $post_data['new_status'] : null;

    // Validate the data (you might want to add more validation)
    if ($order_id === null || $new_status === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit();
    }

    // Fetch the transaction_id of the order
    $fetch_transaction_id_query = "SELECT transaction_id FROM orders WHERE order_id = '$order_id'";
    $transaction_id_result = mysqli_query($conn, $fetch_transaction_id_query);

    if (!$transaction_id_result || mysqli_num_rows($transaction_id_result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch transaction ID']);
        exit();
    }

    $row = mysqli_fetch_assoc($transaction_id_result);
    $transaction_id = $row['transaction_id'];

    // Update the status of all orders with the same transaction_id
    $update_query = "UPDATE orders SET status = '$new_status' WHERE transaction_id = '$transaction_id'";

    if (mysqli_query($conn, $update_query)) {
        // If the update is successful, send a success response
        echo json_encode(['success' => true]);
        exit();
    } else {
        // If there's an error with the update, send a failure response
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        exit();
    }
} else {
    // If the request is not a POST request, return an error response
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}
?>
