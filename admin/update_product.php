<?php
include_once '../connection/connect.php';

if (isset($_POST['update_product'])) {
    $productId = mysqli_real_escape_string($conn, $_POST['product_id']);

    // Fetch the existing product details from the database
    $selectQuery = "SELECT * FROM products WHERE product_id = '$productId'";
    $selectResult = mysqli_query($conn, $selectQuery);

    if ($selectResult && mysqli_num_rows($selectResult) > 0) {
        $productDetails = mysqli_fetch_assoc($selectResult);
        // Now, you can use $productDetails to pre-fill a form or display in a modal for updating
        // You can redirect to a new page or display the form directly here
        // For simplicity, let's just echo the details for now
        echo "<h2>Update Product</h2>";
        echo "<form action='update_product_handler.php' method='post'>";
        echo "<input type='hidden' name='product_id' value='" . $productDetails['product_id'] . "'>";
        echo "Product Name: <input type='text' name='product_name' value='" . $productDetails['product_name'] . "'><br>";
        // Add other input fields for other details
        echo "<input type='submit' value='Update' name='update_product'>";
        echo "</form>";
    } else {
        echo "Error: Product not found";
    }
}
?>
