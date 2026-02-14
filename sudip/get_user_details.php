<?php
session_start();
include('config.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    die(json_encode(['error' => 'Unauthorized access']));
}

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    die(json_encode(['error' => 'User ID not provided']));
}

$user_id = intval($_GET['user_id']);

// Fetch user details
$user_query = "SELECT username, email FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    die(json_encode(['error' => 'User not found']));
}

// Fetch user's cargo items
$cargo_query = "SELECT ci.name, ci.quantity, ci.price, c.category_name 
                FROM cargo_items ci
                JOIN categories c ON ci.category_id = c.category_id
                WHERE ci.user_id = ?";
$stmt = mysqli_prepare($conn, $cargo_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$cargo_result = mysqli_stmt_get_result($stmt);

$cargo_items = [];
while ($row = mysqli_fetch_assoc($cargo_result)) {
    $cargo_items[] = $row;
}

$user['cargo_items'] = $cargo_items;

// Return user details and cargo items as JSON
header('Content-Type: application/json');
echo json_encode($user);

mysqli_close($conn);
?>