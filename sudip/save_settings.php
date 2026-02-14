<?php
session_start();
include('config.php');

// Check if the request is POST and contains JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data) {
        // Sanitize and validate the data
        $systemName = mysqli_real_escape_string($conn, $data['systemName']);
        $timezone = mysqli_real_escape_string($conn, $data['timezone']);
        $maxUsers = intval($data['maxUsers']);
        $defaultWeightUnit = mysqli_real_escape_string($conn, $data['defaultWeightUnit']);
        $maxCargoWeight = intval($data['maxCargoWeight']);
        $emailNotifications = isset($data['emailNotifications']) ? 1 : 0;
        $smsNotifications = isset($data['smsNotifications']) ? 1 : 0;

        // Update or insert settings
        $query = "INSERT INTO system_settings 
                  (system_name, timezone, max_users, default_weight_unit, max_cargo_weight, 
                   email_notifications, sms_notifications) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE 
                  system_name = VALUES(system_name),
                  timezone = VALUES(timezone),
                  max_users = VALUES(max_users),
                  default_weight_unit = VALUES(default_weight_unit),
                  max_cargo_weight = VALUES(max_cargo_weight),
                  email_notifications = VALUES(email_notifications),
                  sms_notifications = VALUES(sms_notifications)";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssissii", 
            $systemName, $timezone, $maxUsers, $defaultWeightUnit, 
            $maxCargoWeight, $emailNotifications, $smsNotifications
        );

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Database error: ' . mysqli_error($conn)
            ]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid JSON data'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
}

mysqli_close($conn);
?>