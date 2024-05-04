<?php
// your_delete_product_handler.php

include_once '../connection/connect.php';

if (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
    $delete_id = $_POST['product_id'];

    // Use prepared statement
    $delete_product = "DELETE FROM products WHERE product_id=?";
    $stmt = mysqli_prepare($conn, $delete_product);
    mysqli_stmt_bind_param($stmt, "i", $delete_id);

    if (mysqli_stmt_execute($stmt)) {
        $response = array('status' => 'success');
    } else {
        $response = array('status' => 'error');
    }

    mysqli_stmt_close($stmt);

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

?>
