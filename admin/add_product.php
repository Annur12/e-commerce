<?php
include_once '../connection/connect.php';
include_once 'header.php';
include_once 'sidebar.php';

if(isset($_POST['insert_product'])){
    // Process the form submission
    $product_category = $_POST['product-category'];
    $product_name = $_POST['product-name'];
    $product_description = $_POST['product-description'];
    $product_price = $_POST['product-price'];
    $product_quantity = $_POST['product-quantity'];

    // Handle file upload
    $target_dir = "../image/";
    $target_file = $target_dir . basename($_FILES["product-image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["product-image"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["product-image"]["size"] > 1000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["product-image"]["tmp_name"], $target_file)) {
            // File uploaded successfully, now insert into database
            $sql = "INSERT INTO products (category_id, product_name, description, price, quantity, image, created_at, updated_at) VALUES ('$product_category', '$product_name', '$product_description', '$product_price', '$product_quantity', '$target_file', NOW(), NOW())";
            
            if(mysqli_query($conn, $sql)){
               
            } else{
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

if(isset($_POST['update_product'])){
    $product_id = $_POST['product_id'];
    $product_category_name = $_POST['update-product-category']; // Category name input
    $product_name = $_POST['update-product-name'];
    $product_description = $_POST['update-product-description'];
    $product_price = $_POST['update-product-price'];
    $product_quantity = $_POST['update-product-quantity'];

    // Check if the specified category exists
    $category_check_sql = "SELECT * FROM categories WHERE category_name = '$product_category_name'";
    $category_check_result = mysqli_query($conn, $category_check_sql);

    if(mysqli_num_rows($category_check_result) == 0) {
        echo "Error: The specified category does not exist.";
        exit; // Stop further execution
    }

    $category_row = mysqli_fetch_assoc($category_check_result);
    $product_category_id = $category_row['category_id'];

    // Handle file upload if a new image is selected
    if ($_FILES["update-product-image"]["size"] > 0) {
        // Process file upload and update image path in the database
        // Add your file upload and database update logic here
    }

    // Update the product information in the database
    $sql = "UPDATE products SET category_id = '$product_category_id', product_name = '$product_name', description = '$product_description', price = '$product_price', quantity = '$product_quantity', updated_at = NOW() WHERE product_id = '$product_id'";
    
    if(mysqli_query($conn, $sql)){
        echo "Product updated successfully.";
    } else{
        echo "Error updating product: " . mysqli_error($conn);
    }
}
if(isset($_GET['product_id'])){
    $product_id = $_GET['product_id'];

    // First, delete related records from the 'cart' table
    $delete_cart_sql = "DELETE FROM cart WHERE product_id = '$product_id'";
    if(mysqli_query($conn, $delete_cart_sql)){
        // Proceed with deleting the product if cart records were successfully deleted
        $sql = "DELETE FROM products WHERE product_id = '$product_id'";
        
        if(mysqli_query($conn, $sql)){
            header("Location: add_product.php"); // Redirect back to the products page after deletion
            exit;
        } else{
            echo "Error deleting product: " . mysqli_error($conn);
        }
    } else {
        // If there was an error deleting cart records, display an error message
        echo "Error deleting product: " . mysqli_error($conn);
    }
} else {
    echo "Product ID not provided.";
}


$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC";
$result = mysqli_query($conn, $sql);


$category_sql = "SELECT * FROM categories";
$category_result = mysqli_query($conn, $category_sql);

?>

<div id="content">
    <div class="table-header">
        <button id="add-product-btn">
            <i class="fa-solid fa-plus plus-sign"></i>
            Add Product
        </button>
        <div class="search-container">
            <form action="#" method="GET">
                <input type="text" name="search" placeholder="Search...">
                <button type="submit" id="search-btn">Submit</button>
            </form>
        </div>
    </div>

    <div id="add-product-form">
        <span id="close-form-btn">&times;</span>
        <h2>Add New Product</h2>
        <form action="#" method="POST" enctype="multipart/form-data">
            <label for="product-category">Category:</label>
            <select name="product-category" id="product-category">
                <option value="">Select Category</option>
                <?php
                // Populate the dropdown menu with category names
                while($row = mysqli_fetch_assoc($category_result)) {
                    echo "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
                }
                ?>
            </select>

            <label for="product-name">Product Name:</label>
            <input type="text" name="product-name" id="product-name">

            <label for="product-description">Description:</label>
            <textarea name="product-description" id="product-description" rows="3" required></textarea>

            <label for="product-price">Price:</label>
            <input type="text" name="product-price" id="product-price">

            <label for="product-quantity">Quantity:</label>
            <input type="number" name="product-quantity" id="product-quantity" value="1" min="1">

            <label for="product-image">Image:</label>
            <input type="file" name="product-image" id="product-image" accept="image/*">

            <input type="submit" value="Submit" name="insert_product">

        </form>
    </div>

    <div id="update-product-popup">
    <span id="close-update-form-btn" onclick="closeUpdateForm()">&times;</span>
    <form action="#" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" id="update-product-id">

        <label for="update-product-category">Category:</label>
        <input type="text" name="update-product-category" id="update-product-category">

        <label for="update-product-name">Product Name:</label>
        <input type="text" name="update-product-name" id="update-product-name">

        <label for="update-product-description">Description:</label>
        <textarea name="update-product-description" id="update-product-description" rows="3" required></textarea>

        <label for="update-product-price">Price:</label>
        <input type="text" name="update-product-price" id="update-product-price">

        <label for="update-product-quantity">Quantity:</label>
        <input type="number" name="update-product-quantity" id="update-product-quantity" min="1" value="1">

        <label for="update-product-image">Image:</label>
        <input type="file" name="update-product-image" id="update-product-image" accept="image/*">

        <input type="submit" value="Update" name="update_product">
    </form>
</div>


    <div id="update-product-popup">
    <span id="close-update-form-btn" onclick="closeUpdateForm()">&times;</span>
    <form action="#" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="product_id" id="update-product-id">

    <label for="update-product-category">Category:</label>
    <select name="update-product-category" id="update-product-category">
                <option value="">Select Category</option>
                <?php
                // Populate the dropdown menu with category names
                mysqli_data_seek($category_result, 0); // Reset pointer
                while($row = mysqli_fetch_assoc($category_result)) {
                    echo "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
                }
                ?>
            </select>

        <label for="update-product-name">Product Name:</label>
        <input type="text" name="update-product-name" id="update-product-name">

        <label for="update-product-description">Description:</label>
        <textarea name="update-product-description" id="update-product-description" rows="3" required></textarea>

        <label for="update-product-price">Price:</label>
        <input type="text" name="update-product-price" id="update-product-price">

        <label for="update-product-quantity">Quantity:</label>
        <input type="number" name="update-product-quantity" id="update-product-quantity" min="1" value="1">

        <label for="update-product-image">Image:</label>
        <input type="file" name="update-product-image" id="update-product-image" accept="image/*">

        <input type="submit" value="Update" name="update_product">
    </form>
    </div>

    <h2 class="list">Product List</h2>

    <table>
    <tr>
             <th>ID</th>
             <th>Category Name</th>
             <th>Product Name</th>
             <th>Description</th>
             <th>Price</th>
             <th>Stocks</th>
             <th>Image</th>
             <th>Action</th>
         </tr>

         <?php
        // Loop through each product and display it in a table row
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['product_id'] . "</td>";
            echo "<td>" . $row['category_name'] . "</td>";
            echo "<td>" . $row['product_name'] . "</td>";
            echo "<td>" . $row['description'] . "</td>";
            echo "<td>₱" . $row['price'] . "</td>";
            echo "<td>" . $row['quantity'] . "</td>";
            echo "<td><img src='../image/" . $row['image'] . "' alt='Product Image' class='product-img'></td>";
            echo "<td class='btns'><a href='#' class='update'><i class='fa-solid fa-pen-to-square edit-sign'></i>Edit</a> | <a href='add_product.php?product_id=" . $row['product_id'] . "' class='delete' onclick='return confirmDelete()'><i class='fa-solid fa-pen-to-square delete-sign'></i>Delete</a></td>";

            echo "</tr>";
        }
        ?>
    </table>

</div>

<?php
include_once 'footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateForm = document.getElementById('update-product-popup');
    const updateProductIdInput = document.getElementById('update-product-id');
    const updateProductNameInput = document.getElementById('update-product-name');
    const updateProductDescriptionInput = document.getElementById('update-product-description');
    const updateProductPriceInput = document.getElementById('update-product-price');
    const updateProductQuantityInput = document.getElementById('update-product-quantity');
    const updateProductCategoryInput = document.getElementById('update-product-category');

    const editButtons = document.querySelectorAll('.update');

    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const row = e.target.closest('tr');
            updateProductIdInput.value = row.querySelector('td:nth-child(1)').innerText;
            updateProductCategoryInput.value = row.querySelector('td:nth-child(2)').innerText;
            updateProductNameInput.value = row.querySelector('td:nth-child(3)').innerText;
            updateProductDescriptionInput.value = row.querySelector('td:nth-child(4)').innerText;
            updateProductPriceInput.value = row.querySelector('td:nth-child(5)').innerText;
            updateProductQuantityInput.value = row.querySelector('td:nth-child(6)').innerText;
            updateForm.style.display = 'block';
        });
    });

    document.getElementById('close-update-form-btn').addEventListener('click', function() {
        updateForm.style.display = 'none';
    });
});

function confirmDelete() {
    return confirm("Are you sure you want to delete this product?");
}

</script>
