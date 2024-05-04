<?php
include_once '../connection/connect.php';
include_once 'header.php';
include_once 'sidebar.php';

if(isset($_GET['delete_users']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $delete_query = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

?>

<div id="content">
    <div class="table-header">
        <div class="search-container">
            <input type="text" placeholder="Search...">
            <button id="search-btn">Search</button>
        </div>
    </div>

    <!-- Add Product Form -->
    <div id="add-product-form">
        <span id="close-form-btn">&times;</span>
        <h2>Add New Category</h2>
        <form action="#" method="post">
            <label for="categoryName">Category Name:</label>
            <input type="text" name="categoryName" id="categoryName">
            <input type="submit" value="Submit">
        </form>
    </div>

    <?php
    $get_users = "SELECT * FROM users WHERE userType <> 1";
    $result = mysqli_query($conn, $get_users);
    $row_count = mysqli_num_rows($result);

    if ($row_count == 0) {
      echo "<p>No users yet</p>";
    } else {
    ?>

    <!-- Assuming $result is available from elsewhere -->
    <h2 class="list">User List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
          <?php
          $number = 0;

          while ($row_data = mysqli_fetch_assoc($result)) {
            $user_id = $row_data['id'];
            $username = $row_data['username'];
            $user_email = $row_data['email'];
            $number++;

            echo "<tr>
              <td>$number</td>
              <td>$username</td>
              <td>$user_email</td>
              <td>
                <a href='user_list.php?delete_users=1&id=$user_id' class='delete-btn' onclick='return confirmDelete();'><i class='fas fa-trash-alt'></i></a>
              </td>
            </tr>";
          }
          ?>
        </tbody>
    </table>
    <?php
    }
    ?>
</div>

<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this item?");
    }
</script>

<?php
include_once 'footer.php';
?>
