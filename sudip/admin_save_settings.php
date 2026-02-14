<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'config.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $systemName = mysqli_real_escape_string($conn, $_POST['systemName']);
    $timezone = mysqli_real_escape_string($conn, $_POST['timezone']);
    $dateFormat = mysqli_real_escape_string($conn, $_POST['dateFormat']);
    $maxUsers = (int)$_POST['maxUsers'];
    $userTimeout = (int)$_POST['userTimeout'];
    $defaultWeightUnit = mysqli_real_escape_string($conn, $_POST['defaultWeightUnit']);
    $maxCargoWeight = (int)$_POST['maxCargoWeight'];
    $defaultCurrency = mysqli_real_escape_string($conn, $_POST['defaultCurrency']);

    // Validate inputs
    if (strlen($systemName) < 3) {
        echo json_encode(['success' => false, 'message' => 'System name must be at least 3 characters long']);
        exit;
    }

    if ($maxUsers < 1) {
        echo json_encode(['success' => false, 'message' => 'Maximum users must be at least 1']);
        exit;
    }

    if ($userTimeout < 5) {
        echo json_encode(['success' => false, 'message' => 'Session timeout must be at least 5 minutes']);
        exit;
    }

    if ($maxCargoWeight < 1) {
        echo json_encode(['success' => false, 'message' => 'Maximum cargo weight must be at least 1 kg']);
        exit;
    }

    // Check if settings already exist
    $check_query = "SELECT COUNT(*) as count FROM system_settings";
    $check_result = mysqli_query($conn, $check_query);
    $row = mysqli_fetch_assoc($check_result);
    $settings_exist = $row['count'] > 0;

    if ($settings_exist) {
        // Update existing settings
        $query = "UPDATE system_settings SET 
                  system_name = '$systemName',
                  timezone = '$timezone',
                  max_users = $maxUsers,
                  default_weight_unit = '$defaultWeightUnit',
                  max_cargo_weight = $maxCargoWeight,
                  updated_at = NOW()";
    } else {
        // Insert new settings
        $query = "INSERT INTO system_settings (
                  system_name, timezone, max_users, 
                  default_weight_unit, max_cargo_weight, created_at, updated_at
                ) VALUES (
                  '$systemName', '$timezone', $maxUsers,
                  '$defaultWeightUnit', $maxCargoWeight, NOW(), NOW()
                )";
    }

    // Execute query
    if (mysqli_query($conn, $query)) {
        // Set session variable for system name
        $_SESSION['system_name'] = $systemName;
        
        echo json_encode(['success' => true, 'message' => 'Settings saved successfully', 'systemName' => $systemName]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} 
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>