<?php
include_once '../connection/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_category']) && isset($_GET['delete-category-id'])) {
    $deleteCategoryID = mysqli_real_escape_string($conn, $_GET['delete-category-id']);

    // Validate and sanitize input if necessary

    // Delete category from the database
    $deleteQuery = "DELETE FROM categories WHERE category_id = '$deleteCategoryID'";
    $deleteResult = mysqli_query($conn, $deleteQuery);

    if ($deleteResult) {
        // Category deleted successfully
        $response = ['status' => 'success'];
        echo json_encode($response);
        exit();
    } else {
        // Error in deleting category
        $response = ['status' => 'error'];
        echo json_encode($response);
        exit();
    }
}
?>
