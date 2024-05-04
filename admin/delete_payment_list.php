<?php

include_once '../connection/connect.php';

if (isset($_GET['delete_payment']) && isset($_GET['delete-order-id'])) {
    $deleteOrderID = mysqli_real_escape_string($conn, $_GET['delete-order-id']);

    // Validate and sanitize input if necessary

    // Delete payment from the database
    $deleteQuery = "DELETE FROM orders WHERE order_id = '$deleteOrderID'";
    $deleteResult = mysqli_query($conn, $deleteQuery);

    if ($deleteResult) {
        // Payment deleted successfully
        $response = ['status' => 'success'];
        echo json_encode($response);
        exit();
    } else {
        // Error in deleting payment
        $response = ['status' => 'error'];
        echo json_encode($response);
        exit();
    }
}

?>