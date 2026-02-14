<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Ensure user_id is valid
$user_id = intval($_SESSION['user_id']);

// Fetch user data securely using prepared statements
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        die("User not found.");
    }
} else {
    die("Query preparation failed: " . mysqli_error($conn));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);

    // Validate name and email
    if (empty($name) || empty($email)) {
        $error_message = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Update user details
        $update_query = "UPDATE users SET name = ?, email = ?";

        // Include password update if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query .= ", password = ?";
        }

        $update_query .= " WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);

        if ($stmt) {
            if (!empty($new_password)) {
                mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $user_id);
            } else {
                mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $user_id);
            }

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Account updated successfully!";
            } else {
                $error_message = "Error updating account: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Failed to prepare update query: " . mysqli_error($conn);
        }
    }
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account - CargoPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5a623;
            --background-color: #f0f4f8;
            --text-color: #333;
            --card-background: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .sidebar {
            width: 280px;
            background: var(--primary-color);
            color: white;
            padding: 40px 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .sidebar .logo {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 40px;
        }

        .sidebar .menu-item {
            width: 100%;
            padding: 24px;
            margin: 10px 0;
            text-align: left;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
        }

        .sidebar .menu-item:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .sidebar .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: var(--primary-color);
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #3a7bc8;
        }

        .account-form {
            background-color: var(--card-background);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        

        <div class="logo">CargoGO</div>
        <a href="user_dashboard.php" class="menu-item"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#" class="menu-item"><i class="fas fa-box"></i> Cargo</a>
        <!-- <a href="#" class="menu-item"><i class="fas fa-truck"></i> Drivers</a> -->
        <a href="manage_account.php" class="menu-item"><i class="fas fa-user"></i> Manage Account</a>
        <a href="logout.php" class="menu-item"><i class="fas fa-door-open"></i> Sign Out</a>
    

    </div>


    <div class="main">
        <div class="header">
            <h1>Manage Account</h1>
        </div>
        <div class="account-form">
            <?php

            $success_message = $error_message = '';
            $user = [
                'name' => ' ',
                'email' => ' '
            ];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Process form submission
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                $new_password = $_POST['new_password'] ?? '';

                // Validate and update user information
                if (!empty($name) && !empty($email)) {
                    // Update user information in the database (not implemented in this example)
                    $user['name'] = $name;
                    $user['email'] = $email;

                    if (!empty($new_password)) {
                        // Update password (not implemented in this example)
                    }

                    $success_message = "Account information updated successfully!";
                } else {
                    $error_message = "Please fill in all required fields.";
                }
            }
            ?>

            <?php if ($success_message): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password (leave blank to keep current password)</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                <button type="submit" class="btn">Update Account</button>
            </form>
        </div>
    </div>
</body>
</html>


