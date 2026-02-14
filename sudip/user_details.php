<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Fetch user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT u.*, 
               COUNT(c.cargo_id) as total_cargo,
               SUM(c.total_price) as total_value
               FROM users u 
               LEFT JOIN cargo_items c ON u.user_id = c.user_id 
               WHERE u.user_id = ?
               GROUP BY u.user_id";

$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = !empty($_POST['password']) ? 
        password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    
    $update_query = "UPDATE users SET email = ?";
    $params = array("s", $new_email);
    
    if ($new_password) {
        $update_query .= ", password_hash = ?";
        $params[0] .= "s";
        $params[] = $new_password;
    }
    
    $update_query .= " WHERE user_id = ?";
    $params[0] .= "i";
    $params[] = $user_id;
    
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, ...$params);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Error updating profile: " . mysqli_error($conn);
    }
}

///paasword change 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $errors = [];

    // Verify current password
    $stmt = mysqli_prepare($conn, "SELECT password_hash FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!password_verify($current_password, $user['password_hash'])) {
        $errors[] = "Current password is incorrect";
    }

    if (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long";
    }

    if ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match";
    }

    if (empty($errors)) {
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = mysqli_prepare($conn, "UPDATE users SET password_hash = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($update_stmt, "si", $new_password_hash, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success_message = "Password updated successfully";
        } else {
            $errors[] = "Error updating password";
        }
    }
}


















?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings - CarGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
:root {
            --primary-color: #6495ED;
            --background-color: #F8F9FD;
            --text-color: #333;
            --card-background: #ffffff;
            --border-color: #ddd;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] {
            --primary-color: #7BA7FF;
            --background-color: #1a1a1a;
            --text-color: #ffffff;
            --card-background: #2d2d2d;
            --border-color: #404040;
            --shadow-color: rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .settings-card {
    background: var(--card-background);
    border-radius: 12px;
    box-shadow: 0 2px 8px var(--shadow-color);
    padding: 32px;
    margin-bottom: 24px;
}

        .stat-card {
    background: var(--card-background);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px var(--shadow-color);
    text-align: center;
}

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s, background-color 0.2s;
            z-index: 1000;
        }

        .back-button:hover {
            transform: translateX(-5px);
            background-color: #5a85d6;
        }

     

        .profile-header {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 32px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: bold;
        }

        .profile-info h1 {
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stat-card h3 {
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background-color: #5a85d6;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .theme-switch {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 24px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 24px;
    border-radius: 4px;
    background-color: var(--card-background);
    box-shadow: 0 2px 8px var(--shadow-color);
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}





.password-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 5px;
            z-index: 1;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .password-strength-meter {
            height: 4px;
            background: #eee;
            margin-top: 8px;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
            border-radius: 2px;
        }

        .strength-weak { background-color: #dc3545; width: 33%; }
        .strength-medium { background-color: #ffc107; width: 66%; }
        .strength-strong { background-color: #28a745; width: 100%; }

        .password-requirements {
            display: block;
            color: #666;
            font-size: 0.8em;
            margin-top: 4px;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.8em;
            margin-top: 4px;
        }





















    </style>
</head>
<body data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
    <a href="user_dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
    </a>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="settings-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user_data['username'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user_data['username']); ?></h1>
                    <p><?php echo htmlspecialchars($user_data['email']); ?></p>
                    <p>Member since: <?php echo date('F j, Y', strtotime($user_data['created_at'])); ?></p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Cargo Items</h3>
                    <p><?php echo $user_data['total_cargo'] ?? 0; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Value</h3>
                    <p>$<?php echo number_format($user_data['total_value'] ?? 0, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Account Type</h3>
                    <p><?php echo ucfirst($user_data['user_type']); ?></p>
                </div>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>





           <div class="container">
        <div class="settings-card">
            <h2>Change Password</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="passwordForm" onsubmit="return validatePasswordForm()">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="password-input-group">
                        <input type="password" id="current_password" name="current_password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-input-group">
                        <input type="password" id="new_password" name="new_password" required 
                               onkeyup="checkPasswordStrength(this.value)">
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength-meter">
                        <div class="strength-bar"></div>
                    </div>
                    <small class="password-requirements">
                        Password must be at least 8 characters long and include numbers and special characters
                    </small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-input-group">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="change_password" class="btn">Update Password</button>
            </form>
        </div>
    </div>


    



                <div class="theme-switch">
                    <span>Dark Mode</span>
                    <label class="switch">
                        <input type="checkbox" id="theme-toggle" <?php echo ($_SESSION['theme'] ?? 'light') === 'dark' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
<!-- 
                <button type="submit" name="update_profile" class="btn">Save Changes</button> -->
            </form>
        </div>
    </div>



    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            
            function setTheme(theme) {
                document.body.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
                fetch('save_theme.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `theme=${theme}`
                }).catch(error => console.log('Error saving theme:', error));
            }

            themeToggle.addEventListener('change', () => {
                setTheme(themeToggle.checked ? 'dark' : 'light');
            });

            document.querySelector('form').addEventListener('submit', function(e) {
                const passwordField = this.querySelector('#password');
                if (passwordField.value && passwordField.value.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long');
                }
            });
        });



        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength(password) {
            const strengthBar = document.querySelector('.strength-bar');
            let strength = 0;

            // Length check
            if (password.length >= 8) strength++;
            
            // Contains number
            if (password.match(/\d/)) strength++;
            
            // Contains special character
            if (password.match(/[^A-Za-z0-9]/)) strength++;
            
            // Contains uppercase and lowercase
            if (password.match(/[A-Z]/) && password.match(/[a-z]/)) strength++;

            strengthBar.className = 'strength-bar';
            if (strength >= 4) {
                strengthBar.classList.add('strength-strong');
            } else if (strength >= 2) {
                strengthBar.classList.add('strength-medium');
            } else if (strength >= 1) {
                strengthBar.classList.add('strength-weak');
            }
        }

        function validatePasswordForm() {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            let isValid = true;

            // Clear previous error messages
            document.querySelectorAll('.error-message').forEach(el => el.remove());

            // Validate new password
            if (newPassword.length < 8) {
                showError('new_password', 'Password must be at least 8 characters long');
                isValid = false;
            }

            // Check password match
            if (newPassword !== confirmPassword) {
                showError('confirm_password', 'Passwords do not match');
                isValid = false;
            }

            return isValid;
        }

        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            field.parentNode.parentNode.appendChild(errorDiv);
        }

        // Clear error messages on input
        document.querySelectorAll('input[type="password"]').forEach(input => {
            input.addEventListener('input', function() {
                const errorMessage = this.parentNode.parentNode.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.remove();
                }
            });
        });







    </script>
</body>
</html>

<?php 

mysqli_close($conn); 

?>