<?php
session_start();
require_once 'config.php'; // Ensure config.php contains a valid $conn for DB connection.

$error = '';
$success = '';

// Handle session error messages
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Login logic
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = $_POST['password'];

        $query = "SELECT user_id, username, password_hash, user_type FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $query);
        // $pass = $user['password_hash'];
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            // die $pass;

            if ($result && $user = mysqli_fetch_assoc($result)) {

                
                if (password_verify($password, $user['password_hash'])) {
                    // Successful login
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    // Redirect based on user type
                    if ($user['user_type'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: user_dashboard.php");
                    }
                    exit();
                } else {
                    $_SESSION['login_error'] = "Invalid username or password.";
                }
            } else {
                $_SESSION['login_error'] = "Invalid username or password.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Error preparing statement: " . mysqli_error($conn);
        }
    } 
    elseif (isset($_POST['register'])) {
        // Registration logic
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check for existing username or email
            $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
            $check_stmt = mysqli_prepare($conn, $check_query);

            if ($check_stmt) {
                mysqli_stmt_bind_param($check_stmt, 'ss', $username, $email);
                mysqli_stmt_execute($check_stmt);
                $check_result = mysqli_stmt_get_result($check_stmt);

                if ($check_result && mysqli_num_rows($check_result) > 0) {
                    $error = "Username or email already exists.";
                } else {
                    // Insert new user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_query = "INSERT INTO users (username, email, password_hash, user_type) VALUES (?, ?, ?, 'user')";
                    $insert_stmt = mysqli_prepare($conn, $insert_query);

                    if ($insert_stmt) {
                        mysqli_stmt_bind_param($insert_stmt, 'sss', $username, $email, $hashed_password);
                        if (mysqli_stmt_execute($insert_stmt)) {
                            $success = "Registration successful. You can now login.";
                        } else {
                            $error = "Registration failed: " . mysqli_error($conn);
                        }
                        mysqli_stmt_close($insert_stmt);
                    }
                }
                mysqli_stmt_close($check_stmt);
            } else {
                $error = "Error preparing statement: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login, Sign Up, and Admin Login - CargoPro</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: sans-serif;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f0f8ff; 
        color: #000;
    }

    .wrapper {
        position: relative;
        width: 750px;
        height: 450px;
        background: #ffffff; 
        border: 2px solid #1e90ff; 
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); 
        overflow: hidden;
    }

    .form-box {
        position: absolute;
        top: 0;
        width: 50%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 0 40px;
        transition: transform 0.7s ease;
    }

    .form-box.login {
        left: 0;
        padding: 0 60px 0 40px;
    }

    .form-box.register {
        right: 0;
        padding: 0 40px 0 60px;
        transform: translateX(100%);
    }

    .wrapper.active .form-box.login {
        transform: translateX(-100%);
    }

    .wrapper.active .form-box.register {
        transform: translateX(0);
    }

    .form-box h2 {
        font-size: 32px;
        color: #1e90ff; 
        text-align: center;
    }

    .input-box {
        position: relative;
        width: 100%;
        height: 50px;
        margin: 25px 0;
    }

    .input-box input {
        width: 100%;
        height: 100%;
        background: transparent;
        border: none;
        outline: none;
        border-bottom: 2px solid #1e90ff; 
        font-size: 16px;
        color: #000;
        padding: 0 35px 0 5px;
        transition: 0.5s;
    }

    .input-box input:focus,
    .input-box input:valid {
        border-bottom-color: #1e90ff;
    }

    .input-box label {
        position: absolute;
        top: 50%;
        left: 5px;
        transform: translateY(-50%);
        font-size: 16px;
        color: #000;
        pointer-events: none;
        transition: 0.5s;
    }

    .input-box input:focus ~ label,
    .input-box input:valid ~ label {
        top: -5px;
        color: #1e90ff;
    }

    .input-box .icon {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        color: #1e90ff;
        font-size: 1.2em;
    }

    .btn {
        position: relative;
        width: 100%;
        height: 45px;
        background: #1e90ff; 
        border: none;
        outline: none;
        border-radius: 40px;
        cursor: pointer;
        font-size: 16px;
        color: #fff;
        font-weight: 600;
        transition: background 0.3s ease, transform 0.2s ease;
    }

    .btn:hover {
        background: #4682b4; 
        transform: scale(1.05); 
    }

    .logreg-link {
        font-size: 14px;
        color: #000;
        text-align: center;
        margin: 20px 0 10px;
    }

    .logreg-link a {
        color: #1e90ff;
        text-decoration: none;
        font-weight: 600;
    }

    .logreg-link a:hover {
        text-decoration: underline;
    }

    .info-text {
        position: absolute;
        top: 0;
        width: 50%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background-color: #1e90ff;
        text-align: center;
    }

    .info-text h2 {
        font-size: 28px;
        margin: 0;
    }

    .info-text.login {
        right: 0;
    }

    .info-text.register {
        left: 0;
        transform: translateX(-100%);
        transition: transform 0.7s ease;
    }

    .wrapper.active .info-text.login {
        transform: translateX(100%);
    }

    .wrapper.active .info-text.register {
        transform: translateX(0);
    }

    .admin-login-btn {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        padding: 12px 25px;
        background: #1e90ff;
        color: #fff;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .admin-login-btn i {
        font-size: 18px;
    }

    .admin-login-btn:hover {
        background: #4682b4;
        transform: translateX(-50%) scale(1.05);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    .error {
        background-color: #ffcccc;
        color: #d70000;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        text-align: center;
    }

    .success {
        background-color: #ccffcc;
        color: #006400;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        text-align: center;
    }

    .password-toggle {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #1e90ff;
    }























    
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Login Form -->
        <div class="form-box login">
            <h2>Login</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <form id="login-form" method="POST" action="">
                <div class="input-box">
                    <input type="text" id="login-username" name="username" required minlength="4" placeholder=" " autocomplete="off">
                    <label>Username</label>
                    <span class="icon"><i class="fas fa-user"></i></span>
                </div>
                <div class="input-box">
                    <input type="password" id="login-password" name="password" required minlength="6" placeholder=" " autocomplete="off">
                    <label>Password</label>
                    <span class="password-toggle" onclick="togglePasswordVisibility('login-password', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
                <div class="logreg-link">
                    <p>Don't have an account? <a href="#" class="register-link">Sign up</a></p>
                </div>
            </form>
        </div>

        <!-- Register Form -->
        <div class="form-box register">
            <h2>Sign Up</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <form id="register-form" method="POST" action="">
                <div class="input-box">
                    <input type="text" id="signup-username" name="username" required minlength="4" placeholder=" " pattern="[a-zA-Z0-9_]+" autocomplete="off">
                    <label>Username</label>
                    <span class="icon"><i class="fas fa-user"></i></span>
                </div>
                <div class="input-box">
                    <input type="email" id="signup-email" name="email" required placeholder=" " autocomplete="off">
                    <label>Email</label>
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                </div>
                <div class="input-box">
                    <input type="password" id="signup-password" name="password" required minlength="6" placeholder=" " autocomplete="off">
                    <label>Password</label>
                    <span class="password-toggle" onclick="togglePasswordVisibility('signup-password', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="input-box">
                    <input type="password" id="signup-confirm-password" name="confirm_password" required minlength="6" placeholder=" " autocomplete="off">
                    <label>Confirm Password</label>
                    <span class="password-toggle" onclick="togglePasswordVisibility('signup-confirm-password', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <button type="submit" name="register" class="btn">Sign Up</button>
                <div class="logreg-link">
                    <p>Already have an account? <a href="#" class="login-link">Login</a></p>
                </div>
            </form>
        </div>

        <!-- Info Text -->
        <div class="info-text login">
            <h2>Welcome Back!</h2>
        </div>
        <div class="info-text register">
            <h2>Join Us!</h2>
        </div>
    </div>

    <button id="adminLoginBtn" class="admin-login-btn">
        <i class="fas fa-user-shield"></i> <a href="landing.php" style="color:white; text-decoration:none;">HELLO GAICH BACK TO HOME</a>
    </button>

    <script>
        const wrapper = document.querySelector('.wrapper');
        const loginLink = document.querySelector('.login-link');
        const registerLink = document.querySelector('.register-link');
        const adminLoginBtn = document.getElementById('adminLoginBtn');
        
        loginLink.addEventListener('click', () => {
            wrapper.classList.remove('active');
        });

        registerLink.addEventListener('click', () => {
            wrapper.classList.add('active');
        });

        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<i class="fas fa-eye"></i>';
            }
        }

        const registerForm = document.getElementById('register-form');
        const passwordInput = document.getElementById('signup-password');
        const confirmPasswordInput = document.getElementById('signup-confirm-password');

        registerForm.addEventListener('submit', function(event) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                event.preventDefault();
                alert('Passwords do not match. Please try again.');
            }
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>