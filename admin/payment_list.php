<?php
include_once '../connection/connect.php';
include_once 'header.php';
include_once 'sidebar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['userType'] != 1) {
    header("Location: login.php");
    exit();
}

// Pagination settings
$results_per_page = 5;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;

// Get search query
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Fetch payment data from the database with pagination and search
$offset = ($current_page - 1) * $results_per_page;
$query = "SELECT * FROM orders WHERE total_price LIKE '%$search_query%' OR payment_method LIKE '%$search_query%' OR order_date LIKE '%$search_query%' LIMIT $results_per_page OFFSET $offset";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching payments: " . mysqli_error($conn));
}

// Count total number of payments for pagination
$total_payments_query = "SELECT COUNT(*) AS total FROM orders WHERE total_price LIKE '%$search_query%' OR payment_method LIKE '%$search_query%' OR status LIKE '%$search_query%' OR order_date LIKE '%$search_query%'";
$total_payments_result = mysqli_query($conn, $total_payments_query);
$total_payments_data = mysqli_fetch_assoc($total_payments_result);
$total_payments = $total_payments_data['total'];

// Calculate total number of pages
$total_pages = ceil($total_payments / $results_per_page);

?>

<div id="content">
    <div class="table-header">
        <div class="search-container">
            <form method="get">
                <input type="text" name="search" placeholder="Search..." value="<?php echo $search_query; ?>">
                <button type="submit" id="search-btn">Search</button>
            </form>
        </div>
    </div>

    <h2 class="list">Payment List</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Amount</th>
                <th>Payment Mode</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr id="row-' . $row['order_id'] . '">';

                    echo '<td>' . $row['order_id'] . '</td>';
                    echo '<td>₱' . $row['total_price'] . '</td>';
                    echo '<td>' . $row['payment_method'] . '</td>';
                    echo '<td>' . $row['status'] . '</td>';
                    echo '<td>' . $row['order_date'] . '</td>';
                    echo '<td class="action-buttons">';
                    
                    echo '<a href="javascript:void(0);" class="delete" onclick="confirmDelete(' . $row['order_id'] . ')"><i class="fa-solid fa-trash delete-sign"></i>Delete</a>';

                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5">No payments found.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <!-- Pagination links -->
    <div class="pagination">
        <?php
        for ($page = 1; $page <= $total_pages; $page++) {
            echo '<a href="?page=' . $page . '&search=' . $search_query . '" ' . ($page == $current_page ? 'class="active"' : '') . '>' . $page . '</a>';
        }
        ?>
    </div>
</div>

<script>
    function confirmDelete(orderID) {
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
                // If user confirms, trigger AJAX to delete the payment
                deletePayment(orderID);
            }
        });
    }

    function deletePayment(orderID) {
    // AJAX request to delete the payment
    $.ajax({
        type: 'GET',
        url: 'delete_payment_list.php',
        data: { 'delete_payment': 1, 'delete-order-id': orderID },
        dataType: 'json', // Add this line to specify that the expected response is JSON
        success: function (response) {
            // Check the response and show SweetAlert accordingly
            if (response.status === 'success') {
                // Remove the row from the table
                $('#row-' + orderID).remove();

                Swal.fire('Deleted!', 'The payment has been deleted.', 'success');
            } else {
                Swal.fire('Error!', 'Failed to delete the payment.', 'error');
            }
        }
    });
}



</script>


<?php
include_once 'footer.php';
?>
