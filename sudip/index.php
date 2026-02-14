
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
?>



<?php
require_once 'config.php';
$query = "SELECT * FROM cargo_items";
$result = mysqli_query($conn, $query);
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarGO_Dashboard</title>
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
        .cargo-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 20px;
        }
        .cargo-table th,
        .cargo-table td {
            padding: 15px;
            text-align: left;
        }
        .cargo-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
        }
        .cargo-table tr {
            background-color: var(--card-background);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }
        .cargo-table tr:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .cargo-table td:first-child,
        .cargo-table th:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        .cargo-table td:last-child,
        .cargo-table th:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .btn-edit,
        .btn-delete {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-edit {
            background-color: #28a745;
        }
        .btn-edit:hover {
            background-color: #218838;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .settings-dropdown {
            position: relative;
        }
        .settings-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dropdown-icon {
            transition: transform 0.3s ease;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
            left: 0;
            top: 100%;
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
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
            .cargo-table {
                font-size: 14px;
            }
            .btn-edit,
            .btn-delete {
                padding: 4px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">CargoPro</div>
        <div class="menu-item"><i class="fas fa-home"></i> Dashboard</div>
        <div class="menu-item"><i class="fas fa-box"></i> Cargo</div>
        <div class="menu-item"><i class="fas fa-truck"></i> Drivers</div>
        <div class="menu-item settings-dropdown">
            <div class="settings-title">
                <span><i class="fas fa-cog"></i> Settings</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </div>
            <div class="dropdown-content">
                <a href="manage_account.php"><i class="fas fa-user"></i> Manage Account</a>
                <a href="logout.php"><i class="fas fa-door-open"></i> Sign Out</a>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Cargo Dashboard</h1>
            <div>
                <a href="add.php" class="btn">Add New Cargo</a>
            </div>
        </div>
        <table class="cargo-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Customer</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['cargo_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($row['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($row['category_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
                        <td>

            <a href="edit.php?id=<?php echo $row['cargo_id']; ?>" class="btn btn-edit"> Edit </a>
            <a href="delete.php?id=<?php echo $row['cargo_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>

                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const settingsDropdown = document.querySelector('.settings-dropdown');
            const dropdownContent = settingsDropdown.querySelector('.dropdown-content');
            const dropdownIcon = settingsDropdown.querySelector('.dropdown-icon');

            settingsDropdown.addEventListener('click', () => {
                dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
                dropdownIcon.style.transform = dropdownContent.style.display === 'block' ? 'rotate(180deg)' : 'rotate(0deg)';
            });

            // Close the dropdown when clicking outside
            document.addEventListener('click', (event) => {
                if (!settingsDropdown.contains(event.target)) {
                    dropdownContent.style.display = 'none';
                    dropdownIcon.style.transform = 'rotate(0deg)';
                }
            });
        });
    </script>
</body>
</html>
<?php
mysqli_close($conn);
?>