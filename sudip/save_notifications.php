<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $enabled = isset($_POST['enabled']) ? filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN) : false;

    // Validate notification type
    if (!in_array($type, ['email', 'sms'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid notification type']);
        exit;
    }

    // Update the notification preferences in the database
    $query = "UPDATE users SET {$type}_notifications = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $enabled, $user_id);
        $success = mysqli_stmt_execute($stmt);
        
        if ($success) {
            // Update session
            $_SESSION['notifications'][$type] = $enabled;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement']);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>