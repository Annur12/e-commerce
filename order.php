<?php
include_once 'header.php';
include_once 'user_links.php';
include_once 'connection/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

// Fetch the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$user_query = "SELECT firstname, lastname, email, number, street, barangay, city FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);

if ($user_result) {
    $user_data = mysqli_fetch_assoc($user_result);
} else {
    echo "Error: " . mysqli_error($conn);
    exit();
}

// Retrieve order details from the form
$total_items = isset($_POST['total_items']) ? $_POST['total_items'] : 0;

$default_shipping_fee = 50.00;
$total_price = isset($_POST['total_price']) ? $_POST['total_price'] : 0;

$total_price += $default_shipping_fee;
?>



<form class="order-container" method="post" action="#">
    <!-- Display user details from the database -->
    <div class="order-section">
        <h3>Address</h3>
        <div class="input-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $user_data['firstname'] . ' ' . $user_data['lastname']; ?>" required>
        </div>

        <div class="input-group">
            <label for="contact">Contact Number:</label>
            <input type="text" id="contact" name="contact" value="<?php echo $user_data['number']; ?>" required>
        </div>


        <div class="input-group">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" value="<?php echo $user_data['email']; ?>" required>
        </div>

        <div class="input-group">
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo $user_data['street'] . ', ' . $user_data['barangay'] . ', ' . $user_data['city']; ?>" required>
        </div>



    </div>

    <div class="order-section">
        <div class="payment-methods">
            <h3>Available Payment Methods</h3>
            <label for="payment-method">Payment Method:</label>
            <select id="payment-method" name="payment-method" required>
                <option value="cash-on-delivery">Cash On Delivery</option>
            </select>
        </div>

        <div class="order-summary">
            <h3>Payment Details</h3>
            <div class="shipping-fee">
                Shipping Fee: <span><?php echo $default_shipping_fee; ?></span>
            </div>

            <div class="total-items">
                Total Items: <span><?php echo $total_items; ?></span>
            </div>

            <div class="total-amount">
                Total Amount: <span>₱<?php echo number_format($total_price, 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Include any necessary hidden fields for order details -->
    <div class="input-group">
        <input type="hidden" name="total_items" value="<?php echo $total_items; ?>">
        <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">

        <button type="submit" class="order-btn" name="placeorder">Place Order</button>
    </div>

</form>


<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['placeorder'])) {
    // Retrieve the rest of the form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Check if a new address is provided
    if (isset($_POST['new_address']) && !empty($_POST['new_address'])) {
        $address = mysqli_real_escape_string($conn, $_POST['new_address']);
    }

    $default_shipping_fee = 5.00; // Adjust this value as needed
    $shipping_fee = mysqli_real_escape_string($conn, $default_shipping_fee);

    $total_items = isset($_POST['total_items']) ? $_POST['total_items'] : 0;
    $total_price = isset($_POST['total_price']) ? $_POST['total_price'] : 0;

    // Insert the order and additional details into the database
    $insert_order_query = "INSERT INTO orders (user_id, total_items, total_price, order_date, name, contact, email, address, shipping_fee, payment_method, status)
                       VALUES ('$user_id', '$total_items', '$total_price', NOW(), '$name', '$contact', '$email', '$address', '$shipping_fee', 'Cash On Delivery', 'In Progress')";

    if (mysqli_query($conn, $insert_order_query)) {
        // Order successfully placed, you may want to clear the user's cart
        $clear_cart_query = "DELETE FROM cart WHERE user_id = $user_id";
        mysqli_query($conn, $clear_cart_query);

        // Display a popup message
        echo '<script>alert("Order placed successfully! Thank you for shopping.");</script>';

        // Redirect to a success page
        echo '<script>setTimeout(function(){ window.location.href = "thank_you.php"; }, 1000);</script>';
        exit();
    } else {
        // Error occurred while inserting the order
        echo "Error: " . mysqli_error($conn);
    }
}

?>