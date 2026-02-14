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

// Fetch all locations
$locations_query = "SELECT location_name FROM locations";
$locations_result = fetchData($conn, $locations_query);
$locations = [];
while ($row = mysqli_fetch_assoc($locations_result)) {
    $locations[] = $row['location_name'];
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT username FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarGO Pricing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    
        :root {
            --primary-color: #6495ED;
            --secondary-color: #f5a623;
            --background-color: #F8F9FD;
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
            background: var(--primary-color);
            color: white;
            padding: 30px 15px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar .logo {
            font-size: 24px;
            font-weight: bold;
            padding: 20px 10px;
            margin-bottom: 20px;
        }

        .sidebar .menu-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: white;
            border-radius: 6px;
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

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #FFA500;
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

        .price-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 20px;
            display: none;
        }

        .price-table th,
        .price-table td {
            padding: 15px;
            text-align: left;
        }

        .price-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
        }

        .price-table tr {
            background-color: var(--card-background);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .price-table tr:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .filters {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .filters select, .filters input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
            .price-table {
                font-size: 14px;
            }

            .filters {
                flex-direction: column;
            }
        }


    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">CargoGO</div>
        <a href="user_dashboard.php" class="menu-item"><i class="fas fa-home"></i> Dashboard</a>
        <a href="price.php" class="menu-item"><i class="fas fa-dollar-sign"></i> Price</a>
        <a href="report.php" class="menu-item"><i class="fas fa-chart-bar"></i> Report</a>
        <a href="add.php" class="menu-item"><i class="fas fa-plus"></i> Add New Cargo</a>
    </div>

    <div class="main">
        <div class="header">
            <h1>Cargo Pricing</h1>
            <div class="user-profile">
                <div class="user-avatar"><?= strtoupper(substr($user['username'], 0, 1)); ?></div>
                <span class="user-name"><?= htmlspecialchars($user['username']); ?></span>
            </div>
        </div>

        <div class="filters">
            <select id="pickupFilter">
                <option value="">Select Pickup Location</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= htmlspecialchars($location); ?>"><?= htmlspecialchars($location); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="dropoffFilter">
                <option value="">Select Dropoff Location</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= htmlspecialchars($location); ?>"><?= htmlspecialchars($location); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="weightFilter" placeholder="Weight (kg)">
        
        </div>

        <table class="price-table" id="priceTable">
            <thead>
                <tr>
                    <th>Weight Range</th>
                    <th>Pickup</th>
                    <th>Dropoff</th>
                    <th>Distance (km)</th>
                    <th>Price/km</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody id="priceTableBody">
                <?php
                $weight_ranges = ['1-5', '5-10', '10-15', '15-20', '20-25', '25-30'];
                $price_per_km = [50, 60, 70, 80, 90, 100];

                // Generate all combinations of pickup and dropoff locations
                foreach ($locations as $pickup) {
                    foreach ($locations as $dropoff) {
                        $distance = 0;
                        if ($pickup === $dropoff) {
                            $distance = 0; // Same location
                        } else {
                            // Define distances between locations
                            $distances = [
                                'Kathmandu' => ['Bhaktapur' => 12, 'Kavre' => 45],
                                'Bhaktapur' => ['Kathmandu' => 12, 'Kavre' => 30],
                                'Kavre' => ['Kathmandu' => 45, 'Bhaktapur' => 30,'Lalitpur'=>38],
                                'Lalitpur' => ['Kathmandu' => 7, 'Bhaktapur' => 14],
                        ];
                            $distance = $distances[$pickup][$dropoff] ?? 0;
                        }

                        for ($i = 0; $i < count($weight_ranges); $i++) {
                            $total_price = $distance * $price_per_km[$i];
                ?>
                            <tr>
                                <td><?= $weight_ranges[$i] . ' kg'; ?></td>
                                <td><?= htmlspecialchars($pickup); ?></td>
                                <td><?= htmlspecialchars($dropoff); ?></td>
                                <td><?= $distance; ?> km</td>
                                <td>$<?= number_format($price_per_km[$i], 2); ?></td>
                                <td>$<?= number_format($total_price, 2); ?></td>
                            </tr>
                <?php
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pickupFilter = document.getElementById('pickupFilter');
            const dropoffFilter = document.getElementById('dropoffFilter');
            const weightFilter = document.getElementById('weightFilter');
            const priceTable = document.getElementById('priceTable');
            const tableBody = document.getElementById('priceTableBody');
            const rows = tableBody.getElementsByTagName('tr');

            function filterTable() {
                const pickup = pickupFilter.value.toLowerCase();
                const dropoff = dropoffFilter.value.toLowerCase();
                const maxWeight = weightFilter.value ? parseFloat(weightFilter.value) : Infinity;

                let hasVisibleRows = false;

                for (let row of rows) {
                    const cells = row.getElementsByTagName('td');
                    const weightRange = cells[0].textContent;
                    const rowPickup = cells[1].textContent.toLowerCase();
                    const rowDropoff = cells[2].textContent.toLowerCase();
                    const maxRowWeight = parseFloat(weightRange.split('-')[1] || weightRange);

                    const pickupMatch = pickup === '' || rowPickup.includes(pickup);
                    const dropoffMatch = dropoff === '' || rowDropoff.includes(dropoff);
                    const weightMatch = maxRowWeight <= maxWeight;

                    if (pickupMatch && dropoffMatch && weightMatch) {
                        row.style.display = '';
                        hasVisibleRows = true;
                    } else {
                        row.style.display = 'none';
                    }
                }

                priceTable.style.display = hasVisibleRows ? 'table' : 'none';
            }

            pickupFilter.addEventListener('change', filterTable);
            dropoffFilter.addEventListener('change', filterTable);
            weightFilter.addEventListener('input', filterTable);

            // Initially hide the table
            priceTable.style.display = 'none';
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>