<?php
// your_delete_user_handler.php

include_once '../connection/connect.php';

if (isset($_POST['delete_users']) ) {

    // Use prepared statement
    $delete_user = "DELETE FROM users WHERE id=?";
    $stmt = mysqli_prepare($conn, $delete_user);
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
