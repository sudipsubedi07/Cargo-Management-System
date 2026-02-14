<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbhost = "localhost";
$dbname = "cargo_record"; // Make sure this matches your database name
$dbuser = "root"; 
$dbpassword = "";
$dbport = "3308";

$enteredUsername = $_POST['username'];
$enteredPassword = $_POST['password'];

$conn = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname, $dbport);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM users WHERE username = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $enteredUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($enteredPassword, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = ($user['user_type'] === 'admin');
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            if ($user['user_type'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php"); // Create this file for regular users
            }
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
} else {
    $error = "Database error: " . $conn->error;
}

if (isset($error)) {
    $_SESSION['login_error'] = $error;
    header("Location: login.php");
    exit();
}

$conn->close();
?>

