<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = isset($_POST['theme']) ? $_POST['theme'] : 'light';
    
    // Validate theme
    if (in_array($theme, ['light', 'dark', 'system'])) {
        $_SESSION['theme'] = $theme;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid theme']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>