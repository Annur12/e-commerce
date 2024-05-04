<?php
include_once '../connection/connect.php';
include_once 'header.php';
include_once 'sidebar.php';

$search_query = '';

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    // Get the search query from the form
    $search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
}
// Check if a status filter is set
$status_filter = isset($_GET['status_filter']) ? mysqli_real_escape_string($conn, $_GET['status_filter']) : '';

// Modify the orders query based on the selected status filter
if (!empty($status_filter)) {
    $orders_query = "SELECT * FROM orders WHERE status = '$status_filter' ORDER BY order_id DESC";
} else {
    $orders_query = "SELECT * FROM orders ORDER BY order_id DESC";
}

// Execute the modified orders query
$orders_result = mysqli_query($conn, $orders_query);

// Check for errors in executing the modified query
if (!$orders_result) {
    die("Error fetching orders: " . mysqli_error($conn));
}


$cancel_orders_query = "SELECT * FROM cancel_orders ORDER BY transaction_id DESC";
$cancel_orders_result = mysqli_query($conn, $cancel_orders_query);

if (!$cancel_orders_result) {
    die("Error fetching cancelled orders: " . mysqli_error($conn));
}

// Function to group cancelled order items by transaction_id
function groupCancelledOrdersByTransactionId($cancel_orders_result)
{
    $grouped_cancelled_orders = [];
    while ($row = mysqli_fetch_assoc($cancel_orders_result)) {
        $transaction_id = $row['transaction_id'];
        if (!isset($grouped_cancelled_orders[$transaction_id])) {
            $grouped_cancelled_orders[$transaction_id] = [];
        }
        $grouped_cancelled_orders[$transaction_id][] = $row;
    }
    return $grouped_cancelled_orders;
}

// Group cancelled orders by transaction_id
$grouped_cancelled_orders = groupCancelledOrdersByTransactionId($cancel_orders_result);

// Function to group order items by transaction_id
function groupOrdersByTransactionId($orders_result)
{
    $grouped_orders = [];
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $transaction_id = $row['transaction_id'];
        if (!isset($grouped_orders[$transaction_id])) {
            $grouped_orders[$transaction_id] = [];
        }
        $grouped_orders[$transaction_id][] = $row;
    }
    return $grouped_orders;
}

// Group orders by transaction_id
$grouped_orders = groupOrdersByTransactionId($orders_result);

// Inside the updateStatus function after updating the status to 'Delivered'
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $orderId = mysqli_real_escape_string($conn, $_POST['order_id']);
    $newStatus = mysqli_real_escape_string($conn, $_POST['new_status']);

    // Update order status in the database
    $update_query = "UPDATE orders SET status = '$newStatus' WHERE order_id = '$orderId'";
    $update_result = mysqli_query($conn, $update_query);

    if (!$update_result) {
        die("Error updating order status: " . mysqli_error($conn));
    }

    // Inside the updateStatus function after updating the status to 'Delivered'
    if ($newStatus === 'Delivered') {
        // Insert the delivered order into the delivered_orders table
        $insert_query = "INSERT INTO delivered_orders (order_id, product_name, quantity, total_price, payment_method, address, order_date, status) VALUES ('{$orderId}', '{$order['product_name']}', '{$order['quantity']}', '{$order['total_price']}', '{$order['payment_method']}', '{$order['address']}', '{$order['order_date']}', 'Delivered')";
        
        // Execute the insert query
        $insert_result = mysqli_query($conn, $insert_query);

        if (!$insert_result) {
            die("Error inserting delivered order: " . mysqli_error($conn));
        }

        // Remove the delivered order from the orders table
        $delete_query = "DELETE FROM orders WHERE order_id = '$orderId'";
        $delete_result = mysqli_query($conn, $delete_query);

        if (!$delete_result) {
            die("Error deleting delivered order: " . mysqli_error($conn));
        }

        // Remove the delivered order from the current view
        unset($grouped_orders[$transaction_id][$index]);
    }

    // Send JSON response back
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

?>

<style>
    .status-in-progress {
        color: #17a2b8;
    }

    .status-ready {
        color: #FFA500;
    }

    .status-delivered {
        color: #32CD32;
    }

    .status-delivering {
        color: #00CED1;
    }

    .status-cancelled {
        color: #FF0000;
    }

    .status-select {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    form {
        display: block;
    }

    .table-container .status-filter-container{
        display: flex;
        justify-content: right;
    }

    
</style>

<div id="content">
    <div class="search-container">
        <form action="" method="get">
            <input type="text" name="search" placeholder="Search..." value="<?php echo $search_query; ?>">
            <button type="submit" id="search-btn">Search</button>
        </form>
    </div>

    <div class="table-container">
        <div class="status-filter-container">
            <form action="" method="get">
                <select class="filter-select" name="status_filter">
                    <option value="">All Status</option>
                    <option value="Delivered" <?php if ($status_filter === 'Delivered') echo 'selected'; ?>>Delivered</option>
                    <option value="Delivering" <?php if ($status_filter === 'Delivering') echo 'selected'; ?>>Delivering</option>
                    <option value="Ready for pickup" <?php if ($status_filter === 'Ready for pickup') echo 'selected'; ?>>Ready for pickup</option>
                    <option value="Preparing" <?php if ($status_filter === 'Preparing') echo 'selected'; ?>>Preparing</option>
                </select>
                <button type="submit" id="filter-btn">Apply Filter</button>
            </form>
        </div>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Address</th>
                <th>Date of order</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
$counter = 1;
foreach ($grouped_orders as $transaction_id => $orders) {
    foreach ($orders as $index => $order) {
        // Skip orders with status 'Delivered'
        if ($order['status'] === 'Delivered') {
            continue;
        }

        echo '<tr>';
        if ($index === 0) {
            echo '<td rowspan="' . count($orders) . '">' . $transaction_id . '</td>';
        }
        echo '<td>' . $order['product_name'] . '</td>';
        echo '<td>' . $order['quantity'] . '</td>';
        if ($index === 0) {
            echo '<td rowspan="' . count($orders) . '">₱' . $order['total_price'] . '</td>';
            echo '<td rowspan="' . count($orders) . '">' . $order['payment_method'] . '</td>';
            echo '<td rowspan="' . count($orders) . '">' . $order['address'] . '</td>';
            echo '<td rowspan="' . count($orders) . '">' . htmlspecialchars(date('M d, Y', strtotime($order['order_date']))) . '</td>';
            echo '<td rowspan="' . count($orders) . '" class="status-' . strtolower($order['status']) . '">' . $order['status'] . '</td>';
            echo '<td rowspan="' . count($orders) . '">';
            if ($order['status'] != 'Delivered') {
                echo '<select class="status-select" onchange="updateStatus(' . $order['order_id'] . ', this.value)">';
                echo '<option value="Preparing" ' . ($order['status'] == 'Preparing' ? 'selected' : '') . '>Preparing</option>';
                echo '<option value="Ready for pickup" ' . ($order['status'] == 'Ready for pickup' ? 'selected' : '') . '>Ready for pickup</option>';
                echo '<option value="Delivering" ' . ($order['status'] == 'Delivering' ? 'selected' : '') . '>Delivering</option>';
                echo '<option value="Delivered" ' . ($order['status'] == 'Delivered' ? 'selected' : '') . '>Delivered</option>';
                echo '</select>';
            } else {
                echo 'Food is already delivered';
            }
            echo '</td>';
        }
        echo '</tr>';
        $counter++;
    }
}
?>

        </table>
    </div>

    <div class="table-container">
    <h2>Delivered Orders</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Amount</th>
            <th>Payment</th>
            <th>Address</th>
            <th>Date of order</th>
            <th>Status</th>
        </tr>
        <?php
        $counter = 1;
        foreach ($grouped_orders as $transaction_id => $orders) {
            foreach ($orders as $index => $order) {
                if ($order['status'] === 'Delivered') {
                    echo '<tr>';
                    if ($index === 0) {
                        echo '<td rowspan="' . count($orders) . '">' . $transaction_id . '</td>';
                    }
                    echo '<td>' . $order['product_name'] . '</td>';
                    echo '<td>' . $order['quantity'] . '</td>';
                    if ($index === 0) {
                        echo '<td rowspan="' . count($orders) . '">₱' . $order['total_price'] . '</td>';
                        echo '<td rowspan="' . count($orders) . '">' . $order['payment_method'] . '</td>';
                        echo '<td rowspan="' . count($orders) . '">' . $order['address'] . '</td>';
                        echo '<td rowspan="' . count($orders) . '">' . htmlspecialchars(date('M d, Y', strtotime($order['order_date']))) . '</td>';
                        echo '<td rowspan="' . count($orders) . '" class="status-' . strtolower($order['status']) . '">' . $order['status'] . '</td>';
                    }
                    echo '</tr>';
                    $counter++;
                }
            }
        }
        ?>
    </table>
</div>


    <div class="table-container">
        <h2>Cancelled Orders</h2>
        <table>
            <tr>

                <th>Transaction ID</th>
                <th>Total Items</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Cancellation Date</th>
            </tr>
            <?php
            foreach ($grouped_cancelled_orders as $transaction_id => $cancelled_orders) {
                $first_cancelled_order = reset($cancelled_orders);
                echo '<tr>';
                echo '<td rowspan="' . count($cancelled_orders) . '">' . $transaction_id . '</td>';
                echo '<td rowspan="' . count($cancelled_orders) . '">' . $first_cancelled_order['total_items'] . '</td>';
                echo '<td rowspan="' . count($cancelled_orders) . '">₱' . $first_cancelled_order['total_price'] . '</td>';
                echo '<td rowspan="' . count($cancelled_orders) . '" class="status-cancelled">Cancelled</td>';
                echo '<td rowspan="' . count($cancelled_orders) . '">' . htmlspecialchars(date('M d, Y', strtotime($first_cancelled_order['order_date']))) . '</td>';
                echo '<td rowspan="' . count($cancelled_orders) . '">' . htmlspecialchars(date('M d, Y', strtotime($first_cancelled_order['cancellation_date']))) . '</td>';
                echo '</tr>';
                foreach ($cancelled_orders as $index => $cancelled_order) {
                    if ($index !== 0) {
                        echo '<tr>';
                    }

                    echo '</tr>';
                }
            }
            ?>
        </table>
    </div>
</div>

<script>
    function updateStatus(orderId, newStatus) {
        console.log('Sending data:', {
            order_id: orderId,
            new_status: newStatus
        });

        fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    new_status: newStatus,
                }),
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                // Handle success case here
                // Reload the page after successful update
                location.reload();
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);

                if (data.success) {
                    // Update the status in the UI dynamically
                    const statusElement = document.querySelector(`.status-${newStatus.toLowerCase()}`);
                    statusElement.textContent = newStatus;
                    statusElement.className = `status-${newStatus.toLowerCase()}`;

                    console.log(`Status updated successfully for Order ID ${orderId}`);

                    // Remove the delivered order row from the table
                    if (newStatus === 'Delivered') {
                        const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
                        if (orderRow) {
                            orderRow.remove();
                        }
                    }
                } else {
                    console.error('Failed to update status:', data.message);
                }
            })
            .catch(error => {
                // Handle errors here
            });
    }
</script>

<?php
include_once 'footer.php';
?>
