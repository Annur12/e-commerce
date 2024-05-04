<?php
include_once 'header.php';
include_once 'user_links.php';
include_once 'connection/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve user input
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $number = $_POST['number'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $street = $_POST['street'];

    // Basic validation
    if (empty($username) || empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($confirmPassword) || empty($number) || empty($city) || empty($barangay) || empty($street)) {
        echo '<p>Please fill in all fields.</p>';
    } elseif ($password !== $confirmPassword) {
        echo '<p>Passwords do not match.</p>';
    } else {
        // Check if the username already exists
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->bind_result($usernameCount);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($usernameCount > 0) {
            echo '<p>Username already exists. Please choose a different username.</p>';
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Handle image upload
            $targetDirectory = "image/";
            $targetFile = $targetDirectory . basename($_FILES["userImage"]["name"]);
            
            // Move the uploaded file to the specified directory
            if (move_uploaded_file($_FILES["userImage"]["tmp_name"], $targetFile)) {
                // Insert user data into the database using prepared statements
                $insertStmt = $conn->prepare("INSERT INTO users (username, firstname, lastname, email, password, number, city, barangay, street, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if (!$insertStmt) {
                    // Error handling
                    die($conn->error); // Display the error message
                }
                
                // Extract the filename from the full path
                $filename = basename($_FILES["userImage"]["name"]);
                
                $insertStmt->bind_param("ssssssssss", $username, $firstname, $lastname, $email, $hashedPassword, $number, $city, $barangay, $street, $filename);
            
                if ($insertStmt->execute()) {
                    echo '<script>';
                    echo 'alert("Registration successful!");';
                    echo 'window.location.href = "login.php";'; // Redirect after the alert
                    echo '</script>';
                    exit(); // Ensure that no further code is executed after the redirection
                } else {
                    echo '<p>Error during registration.</p>';
                }
            
                // Close the statement
                $insertStmt->close();
            } else {
                echo '<p>Error uploading profile image.</p>';
            }
        }
    }
}

// Close the database connection
$conn->close();
?>

<div class="container">
    <div class="register-form">
        <h2>Register</h2>
        <form method="POST" action="" enctype="multipart/form-data">
  
            <input type="text" id="username" name="username" placeholder="Username" required>

            <input type="text" id="firstname" name="firstname" placeholder="Firstname" required>

            <input type="text" id="lastname" name="lastname" placeholder="Lastname" required>

            <input type="email" id="email" name="email" placeholder="Email" required>

            <input type="password" id="password" name="password" placeholder="Password" required>

            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>

            <input type="number" id="number" name="number" placeholder="Phone Number" required>

            <input type="text" id="city" name="city" placeholder="City" required>

            <input type="text" id="barangay" name="barangay" placeholder="Barangay" required>

            <input type="text" id="street" name="street" placeholder="Street" required>

            <label for="userImage">Profile Image:</label>
            <input type="file" id="userImage" name="userImage" accept="image/*">

            <button class="register" type="submit">Register</button>
        </form>
    </div>
</div>