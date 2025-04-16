<?php
session_start();  // Start session to track logged-in users

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account = $_POST['account'];  // User's email
    $password = $_POST['password'];  // User's password
    $nickname = $_POST['nickname'];  // User's nickname
    $roles = $_POST['roles'];  // User's role, like "學生"

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'sa-6');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT * FROM account WHERE `E-mail` = ?");
    $stmt->bind_param("s", $account);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "This email is already registered.";
    } else {
        // Insert the new user into the database with Nickname and Roles
        $stmt = $conn->prepare("INSERT INTO account (`E-mail`, `Password`, `Nickname`, `Roles`) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $account, $password, $nickname, $roles);  // Insert email, password, nickname, and roles
        $stmt->execute();

        // Optionally, you can log the user in after sign-up by starting a session
        $_SESSION['user'] = $account;

        echo "Sign-up successful! You can now log in.";
        // Redirect to the login page or wherever you want after successful sign-up
        // header("Location: login.php"); // Uncomment this to redirect
    }

    $stmt->close();
    $conn->close();
}
?>