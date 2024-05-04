<?php
include_once 'header.php';
include_once 'user_links.php';
include_once 'connection/connect.php';

// Fetch data from the categories table
$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $categories_query);
$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);

$products_query = "SELECT products.*, categories.category_name, products.quantity 
                   FROM products
                   LEFT JOIN categories ON products.category_id = categories.category_id
                   WHERE products.quantity > 0"; // Add this condition to filter out products with zero stock



$products_result = mysqli_query($conn, $products_query);
$products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);

// Fetch cart count for the current user
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $cartCountQuery = "SELECT SUM(quantity) AS total FROM cart WHERE user_id = $userId";
    $cartCountResult = mysqli_query($conn, $cartCountQuery);
    $cartCountRow = mysqli_fetch_assoc($cartCountResult);
    $cartCount = $cartCountRow['total'];
}

// Check if the form is submitted for adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = 1; // You can customize the quantity if needed

    // Check if the item is already in the cart
    $checkQuery = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // Update quantity if the item is already in the cart
        $updateQuery = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id";
        mysqli_query($conn, $updateQuery);
    } else {
        // Insert a new record if the item is not in the cart
        $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
        mysqli_query($conn, $insertQuery);
    }

    // Redirect back to the product page or wherever you want the user to go
    header("Location: index.php");
    exit();
}

?>

<div class="container">

    <form class="search-container" action="#" method="POST">
        <div class="select-container">
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="all" <?php echo isset($_POST['category']) && $_POST['category'] == 'all' ? 'selected' : ''; ?>>Select Category</option>
                <?php
                foreach ($categories as $category) {
                    echo '<option value="' . $category['category_id'] . '" ' . (isset($_POST['category']) && $_POST['category'] == $category['category_id'] ? 'selected' : '') . '>'
                        . $category['category_name'] . '</option>';
                }
                ?>

            </select>
        </div>

        <div class="select-container">
            <input type="text" name="search" placeholder="Search..." value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>">
            <button type="submit" name="submit_search">Search</button>
        </div>

    </form>

    <div id="messageContainer" style="display: none;">No products available in this category.</div>

    <div class="products-container" id="productsContainer">
        <?php
        $selectedCategory = isset($_POST['category']) ? $_POST['category'] : 'all';
        $searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

        foreach ($products as $product) {
            if (
                ($selectedCategory == 'all' || $selectedCategory == $product['category_id']) &&
                (empty($searchTerm) || stripos($product['product_name'], $searchTerm) !== false ||
                    stripos($product['category_name'], $searchTerm) !== false)
            ) {
                echo '<div class="product" data-category="' . $product['category_id'] . '">';
                echo '<img src="image/' . $product['image'] . '" alt="' . $product['product_name'] . '">';
                echo '<h2>' . $product['product_name'] . '</h2>';
                echo '<p>' . $product['description'] . '</p>';
                echo '<p>Price: ₱' . number_format($product['price'], 2) . '</p>';
                echo '<p>Available: <span class="stocks">' . $product['quantity'] . '</span></p>'; // Display stock quantity
                echo '<form method="post" action="">';
                echo '<input type="hidden" name="product_id" value="' . $product['product_id'] . '">';
                echo '<button type="submit" name="add_to_cart"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</button>';
                echo '</form>';
                echo '</div>';
            }
        }

        if (
            $selectedCategory !== 'all' &&
            !anyProductsInSelectedCategory($products, $selectedCategory) &&
            empty($searchTerm)
        ) {
            echo '<div id="messageContainer">No products available in this category.</div>';
        } elseif (empty($products)) {
            echo '<div id="messageContainer">No products available.</div>';
        }
        ?>
    </div>
</div>

<?php
include_once 'footer.php';

// Helper function to check if there are any products in the selected category
function anyProductsInSelectedCategory($products, $selectedCategory)
{
    foreach ($products as $product) {
        if ($selectedCategory == $product['category_id']) {
            return true;
        }
    }
    return false;
}
?>