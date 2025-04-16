<?php
session_start();  // Start session to track logged-in users

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account = $_POST['account'];
    $password = $_POST['password'];

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'sa-6');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check the user's credentials
    $stmt = $conn->prepare("SELECT * FROM account WHERE `E-mail` = ?");

    $stmt->bind_param("s", $account);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if ($password === $user['Password']) {
            // Login successful, create session
            $_SESSION['user'] = $user['E-mail'];
        
            // Redirect to dashboard or home page
            header("Location: index.php");
            exit();
        } else {
            echo "Incorrect password.";
        }
    }        

    $stmt->close();
    $conn->close();
}
?>