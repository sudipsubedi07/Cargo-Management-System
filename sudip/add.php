<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}



$user_id = (int)$_SESSION['user_id']; 
require_once 'config.php';



// Function to safely fetch data using prepared statements
function fetchData($conn, $query, $params = [], $types = '') {
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($conn));
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing statement: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Fetch user and associated customer information
$user_query = "SELECT u.username, c.name as customer_name, c.customer_id 
               FROM users u 
               LEFT JOIN customers c ON customer_id = c.customer_id 
               WHERE u.user_id = ?";
$user_result = fetchData($conn, $user_query, [$user_id], 'i');
$user = mysqli_fetch_assoc($user_result);

$errors = [];



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $name = trim($_POST['name'] ?? '');
    $quantity = filter_var($_POST['quantity'] ?? 0, FILTER_VALIDATE_INT);
    $weight_range = trim($_POST['weight_range'] ?? '');
    $category_id = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
    $customer_id = $user['customer_id']; // Get customer_id from user data
    $pickup_location = trim($_POST['pickup_location'] ?? '');
    $dropoff_location = trim($_POST['dropoff_location'] ?? '');




    // Validation
    if (empty($name)) $errors[] = "Item Name is required.";
    if ($quantity <= 0) $errors[] = "Quantity must be greater than 0.";
    if (empty($weight_range)) $errors[] = "Weight range is required.";
    if ($category_id <= 0) $errors[] = "Category is required.";
    if (empty($customer_id)) $errors[] = "No customer associated with this user.";
    if (empty($pickup_location)) $errors[] = "Pickup Location is required.";
    if (empty($dropoff_location)) $errors[] = "Drop-off Location is required.";







    // Process if no errors
    if (empty($errors)) {
        // Fetch location IDs using prepared statements
        $pickup_location_query = "SELECT location_id FROM locations WHERE location_name = ?";
        $pickup_result = fetchData($conn, $pickup_location_query, [$pickup_location], 's');
        $pickup_data = mysqli_fetch_assoc($pickup_result);

        $dropoff_location_query = "SELECT location_id FROM locations WHERE location_name = ?";
        $dropoff_result = fetchData($conn, $dropoff_location_query, [$dropoff_location], 's');
        $dropoff_data = mysqli_fetch_assoc($dropoff_result);

        if (!$pickup_data || !$dropoff_data) {
            $errors[] = "One or both location names are invalid.";
        } else {
            $pickup_location_id = $pickup_data['location_id'];
            $dropoff_location_id = $dropoff_data['location_id'];

            // Insert using prepared statement
            $insert_query = "INSERT INTO cargo_items (name, quantity, weight_range, category_id, customer_id, 
                           pickup_location_id, dropoff_location_id, user_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            try {
                fetchData($conn, $insert_query, [
                    $name, $quantity, $weight_range, $category_id, $customer_id,
                    $pickup_location_id, $dropoff_location_id, $user_id
                ], 'sisiiiii');
                
                header("Location: user_dashboard.php");
                exit();
            } catch (Exception $e) {
                $errors[] = "Database Error: " . $e->getMessage();
            }
        }
    }
}

// Fetch categories
$category_query = "SELECT * FROM categories ORDER BY category_name";
$categories = fetchData($conn, $category_query);

// Fetch locations
$location_query = "SELECT * FROM locations ORDER BY location_name";
$locations = fetchData($conn, $location_query);
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Cargo Item - CargoPro</title>
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
            width: 250px;
            background: #4a90e2;
            color: white;
            padding: 32px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .sidebar .logo {
            font-size: 24px;
            font-weight: bold;
            padding: 0 0 24px 0;
            margin-bottom: 8px;
        }

        .sidebar .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            margin: 4px 0;
            text-decoration: none;
            color: white;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .sidebar .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .menu-item i {
            margin-right: 12px;
            width: 20px;
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

        .form-container {
            background: var(--card-background);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--text-color);
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
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

        .btn-secondary {
            background-color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: #e09511;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 20px;
            }

            .main {
                padding: 20px;
            }
        }

        .customer-info {
    color: var(--primary-color);
    margin-top: 8px;
    font-size: 16px;
}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">CargoGO</div>
        <a href="user_dashboard.php" class="menu-item">
            <i class="fas fa-home"></i>
            Dashboard
        </a>
        <a href="price.php" class="menu-item">
            <i class="fas fa-dollar-sign"></i>
            Price
        </a>
        <a href="report.php" class="menu-item">
            <i class="fas fa-chart-bar"></i>
            Report
        </a>
        <a href="add.php" class="menu-item">
            <i class="fas fa-plus"></i>
            Add New Cargo
        </a>
        <a href="settings.php" class="menu-item">
            <i class="fas fa-cog"></i>
            Settings
        </a>
        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <h1>Add Cargo Item</h1>
                <?php if ($user['customer_name']): ?>
                    <p class="customer-info">Customer: <?php echo htmlspecialchars($user['customer_name']); ?></p>
                <?php endif; ?>
            </div>
            <a href="user_dashboard.php" class="btn btn-secondary">Back to List</a>
        </div>


        <div class="form-container">
            <?php if (!empty($errors)) : ?>
                <div class="error">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="add.php" method="post" id="cargoForm">
                <div class="form-section">
                    <h2>Cargo Details</h2>
                    <div class="form-group">
                        <label for="name">Item Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($_POST['quantity'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="weight_range">Item Weight Range:</label>
                        <select id="weight_range" name="weight_range" required>
                            <option value="">Select Weight Range</option>
                            <?php
                            $weight_ranges = ['1-5', '5-10', '10-15', '15-20', '20-25', '25-30'];
                            foreach ($weight_ranges as $range) {
                                $selected = ($range === ($_POST['weight_range'] ?? '')) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($range) . "' $selected>" . htmlspecialchars($range) . " kg</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php while ($category = mysqli_fetch_assoc($categories)) : ?>
                                <?php $selected = ($category['category_id'] == ($_POST['category_id'] ?? '')) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($category['category_id']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Shipping Details</h2>
                    <div class="form-group">
                        <label for="pickup_location">Pickup Location:</label>
                        <select id="pickup_location" name="pickup_location" required>
                            <option value="">Select Pickup Location</option>
                            <?php 
                            mysqli_data_seek($locations, 0);
                            while ($location = mysqli_fetch_assoc($locations)) : ?>
                                <?php $selected = ($location['location_name'] === ($_POST['pickup_location'] ?? '')) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($location['location_name']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($location['location_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="dropoff_location">Drop-off Location:</label>
                        <select id="dropoff_location" name="dropoff_location" required>
                            <option value="">Select Drop-off Location</option>
                            <?php 
                            mysqli_data_seek($locations, 0);
                            while ($location = mysqli_fetch_assoc($locations)) : ?>
                                <?php $selected = ($location['location_name'] === ($_POST['dropoff_location'] ?? '')) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($location['location_name']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($location['location_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn">Add Cargo Item</button>
            </form>
        </div>
    </div>
</body>
</html>