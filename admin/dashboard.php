<?php
include_once 'header.php';
include_once 'sidebar.php';

// Include necessary files for database connection
include_once '../connection/connect.php';

// Fetch total users
$totalUsersQuery = "SELECT COUNT(id) as total_users FROM users";
$totalUsersResult = mysqli_query($conn, $totalUsersQuery);
$totalUsers = mysqli_fetch_assoc($totalUsersResult)['total_users'];

// Fetch total products
$totalProductsQuery = "SELECT COUNT(product_id) as total_products FROM products";
$totalProductsResult = mysqli_query($conn, $totalProductsQuery);
$totalProducts = mysqli_fetch_assoc($totalProductsResult)['total_products'];

// Fetch total price of all orders
$totalOrdersQuery = "SELECT SUM(total_price) as total_price FROM orders";
$totalOrdersResult = mysqli_query($conn, $totalOrdersQuery);
$totalPrice = mysqli_fetch_assoc($totalOrdersResult)['total_price'];

$totalStocksQuery = "SELECT SUM(quantity) as quantity FROM products";
$totalStocksResult = mysqli_query($conn, $totalStocksQuery);
$totalStocks = mysqli_fetch_assoc($totalStocksResult)['quantity'];

// Fetch monthly total orders
$monthlyOrdersQuery = "SELECT MONTH(order_date) as month, COUNT(order_id) as total_orders
                      FROM orders
                      GROUP BY MONTH(order_date)";
$monthlyOrdersResult = mysqli_query($conn, $monthlyOrdersQuery);
$months = [];
$totalOrders = [];

while ($row = mysqli_fetch_assoc($monthlyOrdersResult)) {
    $months[] = date("F", mktime(0, 0, 0, $row['month'], 1));
    $totalOrders[] = $row['total_orders'];
}

?>

<div id="main-content">
    <div class="row">
        <div><i class="fa fa-users"></i> Total Users: <?php echo $totalUsers; ?></div>
        <div><i class="fa fa-box"></i> Total Products: <?php echo $totalProducts; ?></div>
        <div><i class="fa fa-box"></i> Available Products: <?php echo $totalStocks; ?></div>
        <div><i class="fa fa-shopping-cart"></i> Total Sales: ₱<?php echo number_format($totalPrice, 2); ?></div>
    </div>


    <!-- Placeholder for a simple bar chart -->
    <div class="chart-container">
        <canvas id="myChart"></canvas>
    </div>

    <!-- Add more cards or charts as needed for additional statistics -->
</div>

<script>
    // Dynamic data for the chart
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Monthly Orders',
                data: <?php echo json_encode($totalOrders); ?>,
                backgroundColor: '#A5D8DD',
                borderColor: '#0091D5',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php
include_once 'footer.php';
?>