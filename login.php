<?php

include_once 'connection/connect.php';
include_once 'header.php';
include_once 'user_links.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usernameOrEmail = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        
        if (password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['userType'] = $user['userType'];

            if ($user['userType'] == 1) {
                header("Location: admin/dashboard.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
        } else {
            echo '<p>Password is incorrect.</p>';
        }
    } else {
        echo '<p>User not found.</p>';
    }

    $stmt->close();

    $conn->close();
}

?>



<div class="container">
    <div class="login-form">
        <h2>Login</h2>
        <form method="POST" action="#">
            <label for="username">Email:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="login">Login</button>
        </form>

        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    </div>
</div>
