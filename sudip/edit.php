<?php

require_once 'config.php';
require_once 'auth.php'; // Make sure this file properly handles user authentication

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Sanitize input
    $query = "SELECT * FROM cargo_items WHERE cargo_id = $id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $item = mysqli_fetch_assoc($result);
    } else {
        $error = "Item not found or database query failed.";
    }
}

// Handle form submission for updating the item
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id']; // Sanitize ID
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $category = (int)$_POST['category'];
    $customer = (int)$_POST['customer'];
    $pickup_location = (int)$_POST['pickup_location'];
    $dropoff_location = (int)$_POST['dropoff_location'];


    // Validate required fields
    if (!$name || $quantity <= 0 || $price < 0 || !$category || !$customer || !$pickup_location || !$dropoff_location) {
        $error = "All fields are required, and values must be valid.";
    } 
    else {
    
        // Update the record in the database
        $query = "UPDATE cargo_items 
                  SET name='$name', quantity=$quantity, price=$price, category_id=$category, customer_id=$customer, pickup_location_id=$pickup_location, dropoff_location_id=$dropoff_location
                  WHERE cargo_id=$id";

        if (mysqli_query($conn, $query)) {
            header("Location: user_dashboard.php"); // Redirect to index
            exit();
        } else {
            $error = "Error updating record: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cargo Item - CargoPro</title>
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
            background: transparent;
            width: 100%;
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.25);
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #3a7bc8;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">CargoGO</div>
        <a href="#" class="menu-item">
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
            <h1>Edit Cargo Item</h1>
            <a href="user_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
        </div>

        <div class="form-container">
            <?php if (isset($error)) : ?>
                <p class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (isset($item)) : ?>
                <form action="edit.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $item['cargo_id']; ?>">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="<?php echo $item['quantity']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo $item['price']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select name="category" id="category" required>
                            <?php
                            $category_query = "SELECT * FROM categories";
                            $category_result = mysqli_query($conn, $category_query);
                            while ($category = mysqli_fetch_assoc($category_result)) {
                                echo "<option value='{$category['category_id']}' " . ($category['category_id'] == $item['category_id'] ? 'selected' : '') . ">{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="customer">Customer:</label>
                        <select name="customer" id="customer" required>
                            <?php
                            $customer_query = "SELECT * FROM customers";
                            $customer_result = mysqli_query($conn, $customer_query);
                            while ($customer = mysqli_fetch_assoc($customer_result)) {
                                echo "<option value='{$customer['customer_id']}' " . ($customer['customer_id'] == $item['customer_id'] ? 'selected' : '') . ">{$customer['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pickup_location">Pickup Location:</label>
                        <select name="pickup_location" id="pickup_location" required>
                            <?php
                            $location_query = "SELECT * FROM locations";
                            $location_result = mysqli_query($conn, $location_query);
                            while ($location = mysqli_fetch_assoc($location_result)) {
                                echo "<option value='{$location['location_id']}' " . ($location['location_id'] == $item['pickup_location_id'] ? 'selected' : '') . ">{$location['location_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dropoff_location">Dropoff Location:</label>
                        <select name="dropoff_location" id="dropoff_location" required>
                            <?php
                            mysqli_data_seek($location_result, 0);
                            while ($location = mysqli_fetch_assoc($location_result)) {
                                echo "<option value='{$location['location_id']}' " . ($location['location_id'] == $item['dropoff_location_id'] ? 'selected' : '') . ">{$location['location_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn">Update Cargo</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>