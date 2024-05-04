<?php
include_once 'connection/connect.php';
include_once 'user_links.php';
include_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_query = "SELECT username, password, email, number, profile_image FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);

if (!$user_result) {
    die("Error fetching user information: " . mysqli_error($conn));
}

$user_data = mysqli_fetch_assoc($user_result);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_changes"])) {
    $new_username = mysqli_real_escape_string($conn, $_POST["new_username"]);
    $new_email = mysqli_real_escape_string($conn, $_POST["new_email"]);
    $new_password = mysqli_real_escape_string($conn, $_POST["new_password"]);
    $new_contact = mysqli_real_escape_string($conn, $_POST["new_contact"]);

    $upload_dir = "image/";

    $new_profile_image = "";
    if (!empty($_FILES["new_profile_image"]["name"])) {
        $new_profile_image = $_FILES["new_profile_image"]["name"];
        $temp_image = $_FILES["new_profile_image"]["tmp_name"];
        move_uploaded_file($temp_image, $upload_dir . $new_profile_image);
    } else {
        $new_profile_image = $user_data['profile_image'];
    }

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    } else {
        $hashed_password = $user_data['password'];
    }

    $update_user_query = "UPDATE users SET username = '$new_username', email = '$new_email', number ='$new_contact', profile_image = '$new_profile_image', password = '$hashed_password' WHERE id = '$user_id'";
    $update_user_result = mysqli_query($conn, $update_user_query);

    if ($update_user_result) {
        $user_query = "SELECT username, email, number, profile_image FROM users WHERE id = '$user_id'";
        $user_result = mysqli_query($conn, $user_query);

        if (!$user_result) {
            die("Error fetching updated user information: " . mysqli_error($conn));
        }

        $user_data = mysqli_fetch_assoc($user_result);
    } else {
        echo "Error updating user information: " . mysqli_error($conn);
    }
}

$results_per_page = 5;

if (!isset($_GET['page'])) {
    $page = 1;
} else {
    $page = $_GET['page'];
}

$offset = ($page - 1) * $results_per_page;

$pending_orders_query = "SELECT order_id, total_items, transaction_id, product_name, quantity, total_price, status, order_date FROM orders WHERE user_id = '$user_id' AND status != 'Delivered' ORDER BY order_date DESC LIMIT $results_per_page OFFSET $offset";
$pending_orders_result = mysqli_query($conn, $pending_orders_query);

$delivered_orders_query = "SELECT order_id, total_items, transaction_id, product_name, quantity, total_price, status, order_date FROM orders WHERE user_id = '$user_id' AND status = 'Delivered' ORDER BY order_date DESC";
$delivered_orders_result = mysqli_query($conn, $delivered_orders_query);

?>

<div class="profile-container">
    <div class="wrap-container">
        <div class="profile edit-users">
            <button class="edit-button" onclick="openEditForm()">Edit</button>
            <img src="image/<?php echo htmlspecialchars($user_data['profile_image']); ?>" alt="Profile Image">
            <p>Username: <?php echo htmlspecialchars($user_data['username']); ?></p>
            <p>Email: <?php echo htmlspecialchars($user_data['email']); ?></p>
            <p>Contact: <?php echo htmlspecialchars($user_data['number']); ?></p>
        </div>

        <div class="profile table">
            <h2>Pending Orders</h2>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Total Items</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th class="cancel-head"></th>
                </tr>
                <?php
                while ($order = mysqli_fetch_assoc($pending_orders_result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($order['transaction_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['total_items']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['product_name']) . "</td>";
                    echo "<td>₱" . htmlspecialchars($order['total_price']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                    echo "<td>" . htmlspecialchars(date('F d, Y', strtotime($order['order_date']))) . "</td>";
                    echo "<td>";
                    // Check if status is not "Ready for pickup" or "Delivering"
                    if ($order['status'] != 'Ready for pickup' && $order['status'] != 'Delivering') {
                        echo '<form method="POST" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" onsubmit="return confirmCancel();">';
                        echo '<input type="hidden" name="order_id" value="' . $order['order_id'] . '">';
                        echo '<button class="cancel-orders" type="submit" name="cancel_order">Cancel Order</button>';
                        echo '</form>';
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            
        </div>
            
        </div>

        <div class="profile table">
            <h2>Delivered Orders</h2>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Total Items</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Order Date</th>
                </tr>
                <?php
                while ($order = mysqli_fetch_assoc($delivered_orders_result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($order['transaction_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['total_items']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['product_name']) . "</td>";
                    echo "<td>₱" . htmlspecialchars($order['total_price']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                    echo "<td>" . htmlspecialchars(date('F d, Y', strtotime($order['order_date']))) . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
    </div>

    <div class="edit-form" id="editForm">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <label for="">Username: </label>
            <input type="text" name="new_username" value="<?php echo $user_data['username']; ?>">

            <label for="">Password: </label>
            <input type="password" name="new_password" value="******">
            <input type="hidden" name="original_password" value="<?php echo $user_data['password']; ?>">

            <label for="">Email: </label>
            <input type="text" name="new_email" value="<?php echo $user_data['email']; ?>">

            <label for="">Contact: </label>
            <input type="text" name="new_contact" value="<?php echo $user_data['number']; ?>">

            <label for="">Image: </label>
            <input type="file" name="new_profile_image" value="<?php echo $user_data['profile_image']; ?>">
            <input type="hidden" name="current_profile_image" value="<?php echo htmlspecialchars($user_data['profile_image']); ?>">

            <button type="submit" class="save" name="save_changes">Save</button>
            <button type="button" class="cancel" onclick="closeEditForm()">Cancel</button>
        </form>
    </div>
</div>

<script src="javascript/profile.js"></script>

<script>
    function confirmCancel() {
        return confirm("Are you sure you want to cancel this order?");
    }
</script>