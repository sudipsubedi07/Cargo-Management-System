<?php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    
    // Get current status
    $status_query = "SELECT is_blocked FROM users WHERE user_id = '$user_id'";
    $status_result = mysqli_query($conn, $status_query);
    $user = mysqli_fetch_assoc($status_result);
    
    // Toggle status
    $new_status = $user['is_blocked'] ? 0 : 1;
    
    $update_query = "UPDATE users SET is_blocked = $new_status WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $update_query)) {
        echo json_encode([
            'success' => true,
            'new_status' => $new_status,
            'message' => 'User status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update user status'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}

mysqli_close($conn);
?>