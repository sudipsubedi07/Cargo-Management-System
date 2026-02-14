<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

function fetchData($conn, $query) {
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        die("Error executing query: " . mysqli_error($conn));
    }
    return $result;
}



$cargo_query = "SELECT c.cargo_id, c.name, c.quantity, c.weight_range, c.price, 
                cat.category_name, cu.name AS customer_name
                FROM cargo_items c
                JOIN categories cat ON c.category_id = cat.category_id
                JOIN customers cu ON c.customer_id = cu.customer_id";


$cargo_result = fetchData($conn, $cargo_query);



$user_id = $_SESSION['user_id'];
$user_query = "SELECT username FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $user_query);
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
if (!mysqli_stmt_execute($stmt)) {
    die("Error executing statement: " . mysqli_stmt_error($stmt));
}

$user_result = mysqli_stmt_get_result($stmt);
if ($user_result === false) {
    die("Error getting result: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($user_result);
if ($user === null) {
    die("User not found");
}

mysqli_stmt_close($stmt);

// Fetch data for the chart
$chart_data = array();
while ($row = mysqli_fetch_assoc($cargo_result)) {
    $chart_data[] = array(
        'name' => $row['name'],
        'quantity' => $row['quantity']
    );
}
mysqli_data_seek($cargo_result, 0);

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new cargo item
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $quantity = intval($_POST['quantity']);
                $price = floatval($_POST['price']);
                $category_id = intval($_POST['category_id']);
                $customer_id = intval($_POST['customer_id']);
                
                $insert_query = "INSERT INTO cargo_items (name, quantity, price, category_id, customer_id) VALUES ('$name', $quantity, $price, $category_id, $customer_id)";
                mysqli_query($conn, $insert_query);
                break;
            
            case 'edit':
                // Edit existing cargo item
                $cargo_id = intval($_POST['cargo_id']);
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $quantity = intval($_POST['quantity']);
                $price = floatval($_POST['price']);
                
                $update_query = "UPDATE cargo_items SET name='$name', quantity=$quantity, price=$price WHERE cargo_id=$cargo_id";
                mysqli_query($conn, $update_query);
                break;
            
            case 'delete':
                // Delete cargo item
                $cargo_id = intval($_POST['cargo_id']);
                $delete_query = "DELETE FROM cargo_items WHERE cargo_id=$cargo_id";
                mysqli_query($conn, $delete_query);
                break;
        }
        
        // Refresh cargo data after CRUD operation
        $cargo_result = fetchData($conn, $cargo_query);
        $chart_data = array();
        while ($row = mysqli_fetch_assoc($cargo_result)) {
            $chart_data[] = array(
                'name' => $row['name'],
                'quantity' => $row['quantity']
            );
        }
        mysqli_data_seek($cargo_result, 0);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarGO Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #6495ED;
            --background-color: #F8F9FD;
            --text-color: #333;
            --white: #ffffff;
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
            background: var(--primary-color);
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
            flex: 1;
            padding: 32px 48px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding: 24px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .header h1 {
            font-size: 28px;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background-color: #FFA500;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .user-name {
            font-weight: 500;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background-color: #5a85d6;
        }

        .cargo-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 32px;
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .cargo-table th {
            background-color: var(--primary-color);
            color: white;
            text-align: left;
            padding: 16px 24px;
            font-weight: 500;
        }

        .cargo-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #eee;
        }

        .cargo-table tr:last-child td {
            border-bottom: none;
        }

        .cargo-table th:first-child {
            border-top-left-radius: 12px;
        }

        .cargo-table th:last-child {
            border-top-right-radius: 12px;
        }

        .btn-edit, .btn-delete,.btn-report{
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            color: white;
            transition: background-color 0.2s;
        }

        .btn-edit {
            background-color: #4CAF50;
            margin-right: 12px;
        }

        .btn-edit:hover {
            background-color: #45a049;
        }

        .btn-delete {
            background-color: #DC3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .btn-report{
            background-color:rgb(67, 220, 53);
        }

        .btn-report:hover{
            background-color:rgb(67, 220, 53);
        }


        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
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

        .dropdown:hover .dropdown-content {
            display: block;
        }

        #cargoChart {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                padding: 24px 16px;
            }
            .main {
                padding: 24px;
            }
            .cargo-table {
                font-size: 14px;
            }
            .btn-edit, .btn-delete {
                padding: 6px 12px;
                font-size: 12px;
            }
        }



        .search-container {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-left: auto;
        }

        .search-container input {
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .search-container input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-container .search-icon {
            position: relative;
            left: -40px;
            color: #666;
        }

        tr.hidden {
            display: none;
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


        <a href="add.php" class="menu-item">
            <i class="fas fa-plus"></i>
            Add New Cargo
        </a>
        <div class="dropdown">
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                Settings
                <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
            </a>
            <div class="dropdown-content">
                <a href="user_details.php"><i class="fas fa-user"></i> User Details</a>
                
            </div>
        </div>
        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>

    <div class="main">
        <div class="header">
            <h1>Cargo Dashboard</h1>
            <div class="user-profile">
                <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
        </div>



        <div class="search-container">
            <input type="text" id="cargoSearch" placeholder="Search Cargo">
            <i class="fas fa-search search-icon"></i>
        </div>




        <canvas id="cargoChart"></canvas>

        <table class="cargo-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Weight</th>
                    <th>Category</th>
                    <th>Customer</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($cargo_result)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['cargo_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['weight_range']); ?> kg</td>
                
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        
                        <td>
                            <a href="edit.php?id=<?php echo $row['cargo_id']; ?>" class="btn-edit">Edit</a>
                            <a href="delete.php?id=<?php echo $row['cargo_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                        
                            <a href="report.php?id=<?php echo $row['cargo_id']; ?>" class="btn-report">Report</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdowns = document.querySelectorAll('.dropdown');

            dropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.menu-item');
                const content = dropdown.querySelector('.dropdown-content');

                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    content.style.display = content.style.display === 'block' ? 'none' : 'block';
                });

                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target)) {
                        content.style.display = 'none';
                    }
                });
            });






            const searchInput = document.getElementById('cargoSearch');
            const tableRows = document.querySelectorAll('.cargo-table tbody tr');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                tableRows.forEach(row => {
                    const name = row.children[1].textContent.toLowerCase();
                    const category = row.children[4].textContent.toLowerCase();
                    const customer = row.children[5].textContent.toLowerCase();

                    if (name.includes(searchTerm) || 
                        category.includes(searchTerm) || 
                        customer.includes(searchTerm)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });

                // Update chart to show only visible items
                updateChartWithFilteredData();
            });

            function updateChartWithFilteredData() {
                const visibleRows = document.querySelectorAll('.cargo-table tbody tr:not(.hidden)');
                const filteredData = Array.from(visibleRows).map(row => ({
                    name: row.children[1].textContent,
                    quantity: parseInt(row.children[2].textContent)
                }));

                cargoChart.data.labels = filteredData.map(item => item.name);
                cargoChart.data.datasets[0].data = filteredData.map(item => item.quantity);
                cargoChart.update();
            }














            // Chart initialization
            var ctx = document.getElementById('cargoChart').getContext('2d');
            var chartData = <?php echo json_encode($chart_data); ?>;
            
            var cargoChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.map(item => item.name),
                    datasets: [{
                        label: 'Cargo Quantity',
                        data: chartData.map(item => item.quantity),
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Cargo Quantity Chart'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php
mysqli_close($conn);
?>