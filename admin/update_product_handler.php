<?php
include_once '../connection/connect.php';

if (isset($_POST['update_product'])) {
    $productId = mysqli_real_escape_string($conn, $_POST['product_id']);
    $newProductName = mysqli_real_escape_string($conn, $_POST['product_name']);
    // Add other fields as needed

    // Perform the update query
    $updateQuery = "UPDATE products SET product_name = '$newProductName' WHERE product_id = '$productId'";
    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        // Product updated successfully
        header("Location: add_product.php?update_success=true");
        exit();
    } else {
        // Error in updating product
        echo "Error: " . mysqli_error($conn);
        header("Location: add_product.php?update_error=true");
        exit();
    }
}
?>
