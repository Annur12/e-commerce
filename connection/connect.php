 <?php

 $conn = new mysqli('localhost', 'root', '', 'mcstore');

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
?>