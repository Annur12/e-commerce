<?php

include_once '../connection/connect.php';
include_once 'header.php';
include_once 'sidebar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['userType'] != 1) {
    header("Location: login.php");
    exit();
}

$results_per_page = 10;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;

// Get search query
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_category'])) {
        // Update category
        $updateCategoryID = mysqli_real_escape_string($conn, $_POST['update-category-id']);
        $updateCategoryName = mysqli_real_escape_string($conn, $_POST['update-category-name']);

        // Validate and sanitize input if necessary

        // Update category in the database
        $updateQuery = "UPDATE categories SET category_name = '$updateCategoryName' WHERE category_id = '$updateCategoryID'";
        $updateResult = mysqli_query($conn, $updateQuery);

        if ($updateResult) {
            // Category updated successfully
            header("Location: add_category.php?update_success=true");
            exit();
        } else {
            // Error in updating category
            header("Location: add_category.php?update_error=true");
            exit();
        }
    } 
}

if (isset($_POST['add_category'])) {
    // Add new category
    $categoryName = mysqli_real_escape_string($conn, $_POST['categoryName']);

    // Validate and sanitize input if necessary

    // Insert data into the database
    $query = "INSERT INTO categories (category_name) VALUES ('$categoryName')";
    $result = mysqli_query($conn, $query);

    // Check for errors
    if (!$result) {
        die("Error adding category: " . mysqli_error($conn));
    }

    if ($result) {
        // Category added successfully
        header("Location: add_category.php?success=true");
        exit();
    } else {
        // Error in adding category
        header("Location: add_category.php?error=true");
        exit();
    }
}

// Fetch category data from the database with pagination and search
$offset = ($current_page - 1) * $results_per_page;
$query = "SELECT * FROM categories WHERE category_name LIKE '%$search_query%' LIMIT $results_per_page OFFSET $offset";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching categories: " . mysqli_error($conn));
}

// Count total number of categories for pagination
$total_categories_query = "SELECT COUNT(*) AS total FROM categories WHERE category_name LIKE '%$search_query%'";
$total_categories_result = mysqli_query($conn, $total_categories_query);
$total_categories_data = mysqli_fetch_assoc($total_categories_result);
$total_categories = $total_categories_data['total'];

// Calculate total number of pages
$total_pages = ceil($total_categories / $results_per_page);


?>
<div id="content">
    <div class="table-header">
        <button id="add-product-btn"><i class="fa-solid fa-plus plus-sign"></i>Add Menu</button>
        <div class="search-container">
            <form method="get">
                <input type="text" name="search" placeholder="Search..." value="<?php echo $search_query; ?>">
                <button type="submit" id="search-btn">Search</button>
            </form>
        </div>
    </div>

    <!-- Add Product Form -->
    <div id="add-product-form">
        <span id="close-form-btn" onclick="hideUpdateForm()">&times;</span>
        <h2>Add New Category</h2>
        <form action="#" method="post">
            <label for="categoryName">Category Name:</label>
            <input type="text" name="categoryName" id="categoryName">
            <input type="submit" value="Submit" name="add_category">
        </form>
    </div>

    <!-- Update Category Form -->
    <div id="update-category-popup" style="<?php echo isset($_GET['edit_category']) ? 'display: block;' : 'display: none;'; ?>">
    <span id="close-update-form-btn" onclick="hideUpdateForm()">&times;</span>
    <h2>Update Category</h2>
    <?php
    if (isset($_GET['edit_category']) && isset($_GET['edit-category-id'])) {
        $editCategoryID = mysqli_real_escape_string($conn, $_GET['edit-category-id']);
        $editQuery = "SELECT * FROM categories WHERE category_id = '$editCategoryID'";
        $editResult = mysqli_query($conn, $editQuery);

        if ($editResult && mysqli_num_rows($editResult) > 0) {
            $editData = mysqli_fetch_assoc($editResult);
            ?>
            <form action="#" method="post">
                <input type="hidden" name="update-category-id" value="<?php echo $editData['category_id']; ?>">
                <label for="update-category-name">Category Name:</label>
                <input type="text" name="update-category-name" value="<?php echo $editData['category_name']; ?>" required>
                <input type="submit" value="Update" name="update_category">
            </form>
            <?php
        } else {
            echo '<p>Error fetching category data for editing.</p>';
        }
    } else {
        echo '<p>No category selected for editing.</p>';
    }
    ?>
</div>


    <?php
    $query = "SELECT * FROM categories";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<h2 class="list">Category List</h2>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Category Name</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr id="row-' . $row['category_id'] . '">';
            echo '<td>' . $row['category_id'] . '</td>';
            echo '<td>' . $row['category_name'] . '</td>';
            echo '<td class="btns">';
            echo '<a class="update" href="add_category.php?edit_category=1&edit-category-id=' . $row['category_id'] . '"><i class="fa-solid fa-pen-to-square edit-sign"></i>Edit</a>';

            echo '<a href="javascript:void(0);" class="delete" onclick="confirmDelete(' . $row['category_id'] . ')"><i class="fa-solid fa-trash delete-sign"></i>Delete</a>';

            echo '</td>'; // Add appropriate action buttons
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '<div class="pagination">';
        for ($page = 1; $page <= $total_pages; $page++) {
            echo '<a href="?page=' . $page . '&search=' . $search_query . '" ' . ($page == $current_page ? 'class="active"' : '') . '>' . $page . '</a>';
        }
        echo '</div>';
    } else {
        echo '<p>No categories found.</p>';
    }
    ?>
</div>



<script>

function confirmDelete(categoryID) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirms, trigger AJAX to delete the category
                deleteCategory(categoryID);
            }
        });
    }

    function deleteCategory(categoryID) {
        // AJAX request to delete the category
        $.ajax({
            type: 'GET',
            url: 'delete_category.php',
            data: { 'delete_category': 1, 'delete-category-id': categoryID },
            dataType: 'json', // Add this line to specify that the expected response is JSON
            success: function (response) {
                // Check the response and show SweetAlert accordingly
                if (response.status === 'success') {
                    // Remove the row from the table
                    $('#row-' + categoryID).remove();

                    Swal.fire('Deleted!', 'The category has been deleted.', 'success');
                } else {
                    Swal.fire('Error!', 'Failed to delete the category.', 'error');
                }
            }
        });
    }

    function showAlert(title, text, icon) {
      Swal.fire({
         title: title,
         text: text,
         icon: icon,
         toast: true,
         position: 'top-end',
         showConfirmButton: false,
         timer: 3000
      });
   }

   function hideUpdateForm() {
        document.getElementById('update-category-popup').style.display = 'none';
    }

   <?php
   // Check for update success
   if (isset($_GET['update_success']) && $_GET['update_success'] == 'true') {
      echo "showAlert('Success!', 'Category updated successfully.', 'success');";
   }

   // Check for update error
   if (isset($_GET['update_error']) && $_GET['update_error'] == 'true') {
      echo "showAlert('Error!', 'Failed to update category.', 'error');";
   }

   // Check for add success
   if (isset($_GET['success']) && $_GET['success'] == 'true') {
      echo "showAlert('Success!', 'Category added successfully.', 'success');";
   }

   // Check for add error
   if (isset($_GET['error']) && $_GET['error'] == 'true') {
      echo "showAlert('Error!', 'Failed to add category.', 'error');";
   }

   ?>
</script>

<?php
include_once 'footer.php';
?>