<?php
include_once 'header.php';
include_once 'user_links.php';
include_once 'connection/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch default address from users table
$user_query = "SELECT firstname, lastname, email, number, street, barangay, city FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);

if ($user_result) {
    $user_data = mysqli_fetch_assoc($user_result);
    $name = $user_data['firstname'] . ' ' . $user_data['lastname'];
    $contact = $user_data['number'];
    $email = $user_data['email'];
    $default_address = $user_data['street'] . ', ' . $user_data['barangay'] . ', ' . $user_data['city'];
} else {
    echo "Error: " . mysqli_error($conn);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    // Retrieve form data
    $fullname = $_POST['fullname'];
    $number = $_POST['number'];
    $email = $_POST['email'];
    $street = $_POST['street'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];

    // Insert new address into the database
    $insert_query = "INSERT INTO addresses (user_id, full_name, contact, email, street, barangay, city, is_default) VALUES ('$user_id', '$fullname', '$number', '$email', '$street', '$barangay', '$city', 0)";

    mysqli_query($conn, $insert_query);

    // Redirect to the current page to avoid form resubmission
    header("Location: edit_address.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default_address'])) {
    $selected_address_id = $_POST['default_address_id'];

    $update_query = "UPDATE addresses SET is_default = 0 WHERE user_id = '$user_id'";
    mysqli_query($conn, $update_query);

    $update_query = "UPDATE addresses SET is_default = 1 WHERE id = '$selected_address_id' AND user_id = '$user_id'";
    mysqli_query($conn, $update_query);

    $_SESSION['selected_address_id'] = $selected_address_id;

    header("Location: cart.php");
    exit();
}

if (isset($_GET['edit'])) {
    $edit_address_id = $_GET['edit'];
    
    $edit_query = "SELECT * FROM addresses WHERE id = '$edit_address_id' AND user_id = '$user_id'";
    $edit_result = mysqli_query($conn, $edit_query);

    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_data = mysqli_fetch_assoc($edit_result);
        
        // Assign edit data to variables
        $edit_fullname = $edit_data['full_name'];
        $edit_contact = $edit_data['contact'];
        $edit_email = $edit_data['email'];
        $edit_street = $edit_data['street'];
        $edit_barangay = $edit_data['barangay'];
        $edit_city = $edit_data['city'];
    } else {
        echo "Address not found!";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $edit_address_id = $_POST['edit_address_id'];
    $fullname = $_POST['fullname'];
    $number = $_POST['number'];
    $email = $_POST['email'];
    $street = $_POST['street'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];

    $update_query = "UPDATE addresses SET full_name = '$fullname', contact = '$number', email = '$email', street = '$street', barangay = '$barangay', city = '$city' WHERE id = '$edit_address_id' AND user_id = '$user_id'";
    
    if (mysqli_query($conn, $update_query)) {
        header("Location: edit_address.php");
        exit();
    } else {
        echo "Error updating address: " . mysqli_error($conn);
        exit();
    }
}


$address_query = "SELECT * FROM addresses WHERE user_id = '$user_id'";
$address_result = mysqli_query($conn, $address_query);
$user_addresses = mysqli_fetch_all($address_result, MYSQLI_ASSOC);

?>
<p class="reminder">"Reminder: Once you have selected another address as your default,
         you cannot use your previous address; however, you can create a new one."</p>
<div class="new_add">
    
    <form action="edit_address.php" method="post">
        <nav>
            <li>
                <span class="def">Default</span>
                <strong><?php echo $name; ?></strong>
                <?php echo $default_address; ?>
                <?php echo $contact; ?>
                <?php echo $email; ?>
            </li>
            <?php foreach ($user_addresses as $address) : ?>
                <li>
                    <a class="edit-address" href="edit_address.php?edit=<?php echo $address['id']; ?>">Edit</a>
                    <input type="radio" name="default_address_id" value="<?php echo $address['id']; ?>" <?php echo $address['is_default'] == 1 ? 'checked' : ''; ?>>
                    <strong><?php echo $address['full_name']; ?></strong>
                     <p>Address: <span><?php echo $address['street'] . ', ' . $address['barangay'] . ', ' . $address['city']; ?></span></p>
                     <p>Contact: <span><?php echo $address['contact']; ?></span></p>
                     <p>Email: <span><?php echo $address['email']; ?></span></p>
                </li>
            <?php endforeach; ?>
        </nav>
            <button class="default-btn" type="submit" name="set_default_address"><i class="fa-solid fa-check"></i> Set As Default</button>

    </form>
    <button class="add-btn" id="openModalBtn"><i class="fa-solid fa-plus"></i> Add New Address</button>

</div>

<div id="addressModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New Address</h2>
        <form action="edit_address.php" method="post">
            <label for="fullname">Fullname:</label>
            <input type="text" name="fullname" required><br>

            <label for="number">Contact Number:</label>
            <input type="text" name="number" required><br>

            <label for="email">Email:</label>
            <input type="text" name="email" required><br>

            <label for="street">Street:</label>
            <input type="text" name="street" required><br>

            <label for="barangay">Barangay:</label>
            <input type="text" name="barangay" required><br>

            <label for="city">City:</label>
            <input type="text" name="city" required><br>

            <button type="submit" name="add_address">Submit</button>
        </form>
    </div>
</div>

<div id="editAddressModal" class="modal" <?php echo isset($_GET['edit']) ? 'style="display:block;"' : ''; ?>>
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Address</h2>
        <form action="edit_address.php" method="post">
            <input type="hidden" name="edit_address_id" value="<?php echo isset($edit_address_id) ? $edit_address_id : ''; ?>">
            
            <label for="fullname">Fullname:</label>
            <input type="text" name="fullname" value="<?php echo isset($edit_fullname) ? $edit_fullname : ''; ?>" required><br>

            <label for="number">Contact Number:</label>
            <input type="text" name="number" value="<?php echo isset($edit_contact) ? $edit_contact : ''; ?>" required><br>

            <label for="email">Email:</label>
            <input type="text" name="email" value="<?php echo isset($edit_email) ? $edit_email : ''; ?>" required><br>

            <label for="street">Street:</label>
            <input type="text" name="street" value="<?php echo isset($edit_street) ? $edit_street : ''; ?>" required><br>

            <label for="barangay">Barangay:</label>
            <input type="text" name="barangay" value="<?php echo isset($edit_barangay) ? $edit_barangay : ''; ?>" required><br>

            <label for="city">City:</label>
            <input type="text" name="city" value="<?php echo isset($edit_city) ? $edit_city : ''; ?>" required><br>

            <button type="submit" name="update_address">Update Address</button>
        </form>
    </div>
</div>


<script>
    // Get the modal and the button that opens it
    var modal = document.getElementById("addressModal");
    var btn = document.getElementById("openModalBtn");
    var closeBtn = document.getElementsByClassName("close")[0];

    // Open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // Close the modal when the close button is clicked
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }

    // Close the modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    var addModal = document.getElementById("addressModal");
    var editModal = document.getElementById("editAddressModal");
    var openModalBtn = document.getElementById("openModalBtn");
    var closeBtns = document.querySelectorAll(".close");
    
    // Open the modal
    openModalBtn.onclick = function() {
        addModal.style.display = "block";
    }

    closeBtns[0].onclick = function() {
        addModal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == addModal) {
            addModal.style.display = "none";
        }
    }

    // Open the edit modal
    <?php if (isset($_GET['edit'])) : ?>
    editModal.style.display = "block";
    <?php endif; ?>

    closeBtns[1].onclick = function() {
        editModal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == editModal) {
            editModal.style.display = "none";
        }
    }

    
</script>