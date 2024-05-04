<?php
include_once 'header.php';
include_once 'user_links.php';
include_once 'connection/connect.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_query = "SELECT firstname, lastname, email, number, street, barangay, city FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);

if ($user_result) {
    $user_data = mysqli_fetch_assoc($user_result);
    $name = $user_data['firstname'] . ' ' . $user_data['lastname'];
    $contact = $user_data['number'];
    $email = $user_data['email'];
    $address = $user_data['street'] . ', ' . $user_data['barangay'] . ', ' . $user_data['city'];
} else {
    echo "Error: " . mysqli_error($conn);
    exit();
}
if (!isset($_SESSION['selected_address_id'])) {
    $delivery_name = $name;
    $delivery_email = $email;
    $delivery_address = $address; 
    $delivery_contact = $contact;
} else {
    $selected_address_id = $_SESSION['selected_address_id'];

    $address_query = "SELECT full_name, contact, email, street, barangay, city  FROM addresses WHERE id = '$selected_address_id' AND user_id = '$user_id'";
    $address_result = mysqli_query($conn, $address_query);

    if ($address_result) {
        $address_data = mysqli_fetch_assoc($address_result);
        $delivery_name = $address_data['full_name'];
        $delivery_email = $address_data['email'];
        $delivery_address = $address_data['street'] . ', ' . $address_data['barangay'] . ', ' . $address_data['city'];
        $delivery_contact = $address_data['contact'];
    } else {
        echo "Error: " . mysqli_error($conn);
        exit();
    }
}

$cart_query = "SELECT products.product_id, products.product_name, products.description, products.price, products.image, products.quantity AS stock, cart.quantity
               FROM cart
               JOIN products ON cart.product_id = products.product_id
               WHERE cart.user_id = $user_id";


$cart_result = mysqli_query($conn, $cart_query);
$cart_items = mysqli_fetch_all($cart_result, MYSQLI_ASSOC);

$total_items = 0;
$total_price = 0;

foreach ($cart_items as $cart_item) {
    $total_items += $cart_item['quantity'];
    $total_price += $cart_item['price'] * $cart_item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {

    function generateOrderID() {
        $timestamp = microtime(true);
        $random = mt_rand(1000, 9999);
        $orderID = 'ORD' .intval($timestamp . $random);
        return $orderID;
    }

    $orderID = generateOrderID();

    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : "Pick up";

    // Calculate total price based on payment method
    if ($payment_method === "Cash On Delivery") {
        $total_price += 50; // Add delivery fee
    }

    // Use prepared statements to prevent SQL injection
    $insert_cart_item_query = "INSERT INTO cart_items (user_id, total_items, total_price) VALUES (?, ?, ?)";
    $stmt1 = mysqli_prepare($conn, $insert_cart_item_query);
    mysqli_stmt_bind_param($stmt1, "iii", $user_id, $total_items, $total_price);

    if (!mysqli_stmt_execute($stmt1)) {
        echo "Error: " . mysqli_error($conn);
        exit();
    }

    $delete_cart_query = "DELETE FROM cart WHERE user_id = ?";
    $stmt3 = mysqli_prepare($conn, $delete_cart_query);
    mysqli_stmt_bind_param($stmt3, "i", $user_id);

    if (!mysqli_stmt_execute($stmt3)) {
        echo "Error: " . mysqli_error($conn);
        exit();
    }

    $shipping_fee = $payment_method === "Cash On Delivery" ? 50 : 0; // Update shipping fee based on payment method
    $status = "Preparing";


    foreach ($cart_items as $cart_item) {
        $product_name = $cart_item['product_name'];
        $quantity = $cart_item['quantity'];
        $productId = $cart_item['product_id']; // Fetch product ID from cart item
        
        // Check if quantity exceeds available stock
        if ($quantity > $cart_item['stock']) {
            echo "Error: Insufficient stock for product $product_name.";
            exit();
        }
        
        // Update the stock in the products table
        $updateStockQuery = "UPDATE products SET quantity = quantity - $quantity WHERE product_id = $productId";
        mysqli_query($conn, $updateStockQuery);
        
        // Check if the updated stock is zero or negative
        $checkStockQuery = "SELECT quantity FROM products WHERE product_id = $productId";
        $stockResult = mysqli_query($conn, $checkStockQuery);
        $newStock = mysqli_fetch_assoc($stockResult)['quantity'];
    
        if ($newStock <= 0) {
            // Remove the product from both products table and cart table
            $removeFromProductsQuery = "DELETE FROM products WHERE product_id = $productId";
            mysqli_query($conn, $removeFromProductsQuery);
            
        }
        
        // Use prepared statements to prevent SQL injection
        $insert_order_query = "INSERT INTO orders (user_id, transaction_id, product_name, quantity, total_items, total_price, name, contact, email, address, shipping_fee, payment_method, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $insert_order_query);
        mysqli_stmt_bind_param($stmt2, "ississsssssss", $user_id, $orderID, $product_name, $quantity, $total_items, $total_price, $name, $contact, $email, $address, $shipping_fee, $payment_method, $status);
    
        if (!mysqli_stmt_execute($stmt2)) {
            echo "Error: " . mysqli_error($conn);
            exit();
        }
    }
    
    echo '<script>
            Swal.fire({
                title: "Thank you for your purchase!",
                text: "Please wait a second...",
                icon: "success",
                timer: 2000,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
                willClose: () => {
                    window.location.href = "thank_you.php";
                }
            });
          </script>';
    exit();
}
?>

<section class="cart-container">
    <div class="cart-items">
        <?php foreach ($cart_items as $cart_item) : ?>
            <div class="cart-item" data-category="<?php echo $cart_item['product_id']; ?>">
                <img src="image/<?php echo $cart_item['image']; ?>" alt="<?php echo $cart_item['product_name']; ?>">
                <div class="item-details">
                    <h2><?php echo $cart_item['product_name']; ?></h2>
                    <p>Description: <?php echo $cart_item['description']; ?></p>
                    <p>Price: ₱<?php echo number_format($cart_item['price'], 2); ?></p>
                    <label for="quantity<?php echo $cart_item['product_id']; ?>">Quantity:</label>
                    <button class="decrement-btn" data-product-id="<?php echo $cart_item['product_id']; ?>">-</button>
                    <input type="number" class="quantity-input" id="quantity<?php echo $cart_item['product_id']; ?>" name="quantity<?php echo $cart_item['product_id']; ?>" value="<?php echo $cart_item['quantity']; ?>" min="1" data-stock="<?php echo $cart_item['stock']; ?>">
                    <button class="increment-btn" data-product-id="<?php echo $cart_item['product_id']; ?>">+</button>
                    <button class="remove-btn" data-product-id="<?php echo $cart_item['product_id']; ?>">Remove</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($cart_items)) : ?>
    <section class="cart-summary">
        <h2>Order Summary</h2>
        <div class="summary-details">
            <p>Total Items: <?php echo $total_items; ?></p>
            <!-- Removed hardcoded delivery fee -->
            <p>Total Price: ₱<?php echo number_format($total_price, 2); ?></p>
            
            <!-- Payment Method Selection Form -->
            <h2 class="top">Payment Method</h2>
            <div class="summary-details">
                <form action="cart.php" method="post">
                    <input type="radio" id="pickup" name="payment_method" value="Pick up" checked>
                    <label for="pickup">Pick up</label><br>
                    <input type="radio" id="cash_on_delivery" name="payment_method" value="Cash On Delivery">
                    <label for="cash_on_delivery">Cash On Delivery (+₱50 delivery fee)</label><br>

                    <!-- Delivery Address -->
                    <h2 class="top"><i class="fa-solid fa-location-dot"></i> Delivery Address</h2>
                    <div class="summary-details detail">
                        <a href="edit_address.php">
                            <p class="change">Change <i class="fa-solid fa-arrow-right"></i></p>
                            <p>Name: <span> <?php echo $delivery_name; ?></span></p>
                            <p>Contact: <span> <?php echo $delivery_contact; ?></span></p>
                            <p>Email: <span> <?php echo $delivery_email; ?></span></p>
                            <p>Address: <span> <?php echo $delivery_address; ?></span></p>
                        </a>
                    </div>
                    
                    <input type="hidden" name="total_items" value="<?php echo $total_items; ?>">
                    <!-- Passing total price as a hidden field -->
                    <input type="hidden" name="total_price" id="total_price" value="<?php echo $total_price; ?>">
                    <button type="submit" class="checkout-btn" name="checkout"><i class="fa-solid fa-cart-shopping"></i> Buy Now</button>
                </form>
            </div>
        </div>
    </section>
<?php else : ?>
    <p>Your cart is empty. <a href="index.php">Start shopping now!</a></p>
<?php endif; ?>

</section>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function() {
        $(".increment-btn, .decrement-btn").click(function() {
            var productId = $(this).data("product-id");
            var action = $(this).hasClass("increment-btn") ? "increment" : "decrement";
            updateCart(productId, action);
        });

        $(".remove-btn").click(function() {
            var productId = $(this).data("product-id");
            removeItem(productId);
        });

        function updateCart(productId, action) {
            var inputElement = $("#quantity" + productId);
            var currentQuantity = parseInt(inputElement.val());
            var stock = parseInt(inputElement.data("stock"));

            if (action === "increment" && currentQuantity >= stock) {
                alert("Out of stock.");
                return;
            }

            if (action === "decrement" && currentQuantity <= 1) {
                alert("Quantity cannot be less than 1.");
                return;
            }

            $.ajax({
                url: "update_cart.php",
                method: "POST",
                data: {
                    action: action,
                    product_id: productId
                },
                success: function(data) {
                    location.reload();
                }
            });
        }

        function removeItem(productId) {
            $.ajax({
                url: "update_cart.php",
                method: "POST",
                data: {
                    action: "remove",
                    product_id: productId
                },
                success: function(data) {
                    location.reload();
                }
            });
        }

        // Update total price when payment method changes
        $("input[name='payment_method']").change(function() {
            var total_price = parseFloat($("#total_price").val());

            if ($(this).val() === "Cash On Delivery") {
                // Add delivery fee
                total_price += 50;
            } else {
                // Remove delivery fee
                total_price -= 50;
            }

            // Update total price in the hidden field
            $("#total_price").val(total_price.toFixed(2));
            // Update display of total price
            $(".summary-details p:contains('Total Price')").text("Total Price: ₱" + total_price.toFixed(2));
        });
    });
</script>
