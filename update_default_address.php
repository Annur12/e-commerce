<?php
session_start();
include_once 'connection/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address_id'])) {
    $address_id = $_POST['address_id'];
    $user_id = $_SESSION['user_id'];

    // Update the default address for the user in the database
    $update_address_query = "UPDATE users SET selected_address_id = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_address_query);
    mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating address: ' . mysqli_error($conn)]);
    }
    
}
?>
