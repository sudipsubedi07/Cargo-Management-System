<?php
session_start();
include('config.php');


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch user count
$user_count_query = "SELECT COUNT(*) as count FROM users";
$user_count_result = mysqli_query($conn, $user_count_query);
$user_count = mysqli_fetch_assoc($user_count_result)['count'];

// Fetch cargo stats
$cargo_stats_query = "SELECT COUNT(*) as cargo_count, SUM(total_price) as total_value FROM cargo_items";
$cargo_stats_result = mysqli_query($conn, $cargo_stats_query);
$cargo_stats = mysqli_fetch_assoc($cargo_stats_result);

// Fetch categories
$category_query = "SELECT c.category_id, c.category_name, COUNT(ci.cargo_id) as item_count 
                   FROM categories c
                   LEFT JOIN cargo_items ci ON c.category_id = ci.category_id
                   GROUP BY c.category_id";
$category_result = mysqli_query($conn, $category_query);

// Fetch recent user activities
$activity_query = "SELECT u.username, c.name as cargo_name, cat.category_name as category, c.created_at 
                   FROM cargo_items c 
                   JOIN users u ON c.user_id = u.user_id 
                   JOIN categories cat ON c.category_id = cat.category_id 
                   ORDER BY c.created_at DESC LIMIT 10";
$activity_result = mysqli_query($conn, $activity_query);

// Fetch all cargo items
$cargo_items_query = "SELECT ci.cargo_id, ci.name, c.category_name, ci.total_price 
                      FROM cargo_items ci
                      JOIN categories c ON ci.category_id = c.category_id
                      ORDER BY ci.cargo_id DESC";
$cargo_items_result = mysqli_query($conn, $cargo_items_query);

// Fetch all users for the user management section
$users_query = "SELECT user_id, username FROM users";
$users_result = mysqli_query($conn, $users_query);

$monthly_activity_query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as activity_count
    FROM cargo_items
    GROUP BY month
    ORDER BY month DESC
    LIMIT 6";
$monthly_activity_result = mysqli_query($conn, $monthly_activity_query);

$daily_revenue_query = "SELECT 
    DATE(created_at) as date,
    SUM(total_price) as daily_revenue
    FROM cargo_items
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY date
    ORDER BY date";
$daily_revenue_result = mysqli_query($conn, $daily_revenue_query);

?>

<?php
$users_query = "SELECT user_id, username, is_blocked FROM users";
$users_result = mysqli_query($conn, $users_query);
?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CargoPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="adminstyle.css">
    <style>

          .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .user-table th, .user-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .user-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .view-btn {
            background: linear-gradient(45deg, #4a90e2, #007bff);
            color: white;
            border: none;
            padding: 8px 12px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
     
        .view-btn:hover {
            background: linear-gradient(45deg, #007bff, #0056b3);
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 123, 255, 0.5);
        }
        .popup {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .popup-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
            padding: 20px;
        }
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chart-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
            font-weight: bold;
        }
        .chart-wrapper {
            position: relative;
            height: 300px;
        }




        .action-buttons {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-btn:hover {
    background: linear-gradient(45deg, #357abd, #2d6da3);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
}

.block-btn {
    background: linear-gradient(45deg, #ff4b2b, #ff0000);
    color: white;
    transition: all 0.3s ease;
}

.block-btn:hover {
    background: linear-gradient(45deg, #ff0000, #cc0000);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 0, 0, 0.3);
}

.btn i {
    font-size: 14px;
}

.btn span {
    font-weight: 500;
}




.block-btn.unblock-btn {
    background: linear-gradient(45deg, #28a745, #218838);
}

.block-btn.unblock-btn:hover {
    background: linear-gradient(45deg, #218838, #1e7e34);
}

/* Loading state */
.block-btn.loading {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Success animation */
@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.success-animation {
    animation: successPulse 0.5s ease-in-out;
}







.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 4px;
    color: white;
    font-weight: 500;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
}

.notification.success {
    background-color: #28a745;
}

.notification.error {
    background-color: #dc3545;
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

.block-btn {
    position: relative;
    overflow: hidden;
}

.block-btn.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.3);
    cursor: not-allowed;
}

.unblock-btn {
    background: linear-gradient(45deg, #28a745, #218838) !important;
}

.block-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}





















    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Admin Dashboard</div>
        <div class="menu-item active" data-target="dashboard">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </div>
        <div class="menu-item" data-target="manage-users">
            <i class="fas fa-users"></i> Manage Users
        </div>
        <div class="menu-item" data-target="manage-cargo">
            <i class="fas fa-box"></i> Manage Cargo
        </div>
        <div class="menu-item" data-target="categories">
            <i class="fas fa-chart-bar"></i> Categories
        </div>
        <div class="menu-item" data-target="settings">
            <i class="fas fa-cog"></i> Settings
        </div>
    </div>

    <div class="main">
        <div class="header">

        </div>
        <div id="dashboard" class="content-section">
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?php echo $user_count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Cargo Items</h3>
                    <p><?php echo $cargo_stats['cargo_count']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Cargo Value</h3>
                    <p>$<?php echo number_format($cargo_stats['total_value'], 2); ?></p>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-container">
                    <h3>Monthly Activity</h3>
                    <div class="chart-wrapper">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
                <div class="chart-container">
                    <h3>Category Distribution</h3>
                    <div class="chart-wrapper">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
                <div class="chart-container">
                    <h3>Daily Revenue</h3>
                    <div class="chart-wrapper">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <h2>Recent User Activities</h2><br><br>
            <table class="cargo-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Cargo Item</th>
                        <th>Category</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($activity = mysqli_fetch_assoc($activity_result)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($activity['username']); ?></td>
                            <td><?php echo htmlspecialchars($activity['cargo_name']); ?></td>
                            <td><?php echo htmlspecialchars($activity['category']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>


        



        <div id="manage-users" class="content-section hidden">
    <h2>Users</h2>
    <table class="user-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="user-list">
            <?php
            $users_query = "SELECT user_id, username, is_blocked FROM users";
            $users_result = mysqli_query($conn, $users_query);
            
            while ($user = mysqli_fetch_assoc($users_result)) : 
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td class="action-buttons">
                        <button class="btn view-btn" data-userid="<?php echo $user['user_id']; ?>">
                            <i class="fas fa-eye"></i>
                            <span>View</span>
                        </button>
                        <!-- <button class="btn block-btn <?php echo $user['is_blocked'] ? 'unblock-btn' : ''; ?>" 
                                data-userid="<?php echo $user['user_id']; ?>">
                            <i class="fas <?php echo $user['is_blocked'] ? 'fa-unlock' : 'fa-ban'; ?>"></i>
                            <span><?php echo $user['is_blocked'] ? 'Unblock' : 'Block'; ?></span>
                        </button> -->
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>














        <div id="manage-cargo" class="content-section hidden">
            <div class="cargo-form">
                <h3>Add New Cargo Item</h3>
                <form id="add-cargo-form">
                    <div class="form-group">
                        <label for="cargo-name">Cargo Name:</label>
                        <input type="text" id="cargo-name" name="cargo-name" required>
                    </div>
                    <div class="form-group">
                        <label for="cargo-category">Category:</label>
                        <select id="cargo-category" name="cargo-category" required>
                            <option value="">Select a category</option>
                            <?php
                            mysqli_data_seek($category_result, 0);
                            while ($category = mysqli_fetch_assoc($category_result)) : ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cargo-price">Price:</label>
                        <input type="number" id="cargo-price" name="cargo-price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <button type="submit">Add Cargo Item</button>
                    </div>
                </form>
            </div>

            <h3>Existing Cargo Items</h3>
            <table class="cargo-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="cargo-table-body">
                    <?php while ($item = mysqli_fetch_assoc($cargo_items_result)) : ?>
                        <tr>
                            <td><?php echo $item['cargo_id']; ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                            <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                            <td class="action-buttons">
                                <button class="edit-btn" data-id="<?php echo $item['cargo_id']; ?>">Edit</button>
                                <button class="delete-btn" data-id="<?php echo $item['cargo_id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="categories" class="content-section hidden">
            <h2>Categories and Item Counts</h2>
            <div class="category-list">
                <?php
                mysqli_data_seek($category_result, 0);
                while ($category = mysqli_fetch_assoc($category_result)) : ?>
                    <div class="category-item">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                        (<?php echo $category['item_count']; ?>)
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div id="settings" class="content-section hidden">
            <?php include 'admin_setting.php'; ?>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>

   <div id="user-popup" class="popup">
    <div class="popup-content">
        <span class="close">&times;</span>
        <div class="user-header">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-info">
                <h2 id="popup-username"></h2>
                <p id="popup-email"></p>
                <p id="popup-status"></p>
            </div>
        </div>
        <div class="user-details">
            <div class="detail-section">
                <h3>Account Details</h3>
                <p id="popup-joined"></p>
                <p id="popup-last-login"></p>
            </div>
            <div class="detail-section">
                <h3>Cargo Items</h3>
                <div class="cargo-list-wrapper">
                    <ul id="popup-cargo-list"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

        document.addEventListener("DOMContentLoaded", function() {
            const menuItems = document.querySelectorAll(".menu-item");
            const contentSections = document.querySelectorAll(".content-section");

            menuItems.forEach(item => {
                item.addEventListener("click", () => {
                    menuItems.forEach(i => i.classList.remove("active"));
                    item.classList.add("active");

                    const targetId = item.getAttribute("data-target");
                    contentSections.forEach(section => {
                        if (section.id === targetId) {
                            section.classList.remove("hidden");
                        } else {
                            section.classList.add("hidden");
                        }
                    });
                });
            });

            <?php
            // Prepare data for charts
            $months = [];
            $activity_counts = [];
            while ($row = mysqli_fetch_assoc($monthly_activity_result)) {
                $months[] = date('M Y', strtotime($row['month'] . '-01'));
                $activity_counts[] = $row['activity_count'];
            }

            mysqli_data_seek($category_result, 0);
            $categories = [];
            $category_counts = [];
            while ($row = mysqli_fetch_assoc($category_result)) {
                $categories[] = $row['category_name'];
                $category_counts[] = $row['item_count'];
            }

            $dates = [];
            $revenues = [];
            while ($row = mysqli_fetch_assoc($daily_revenue_result)) {
                $dates[] = date('M d', strtotime($row['date']));
                $revenues[] = $row['daily_revenue'];
            }
            ?>

            // Activity Chart
            new Chart(document.getElementById('activityChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_reverse($months)); ?>,
                    datasets: [{
                        label: 'Number of Activities',
                        data: <?php echo json_encode(array_reverse($activity_counts)); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Category Chart
            new Chart(document.getElementById('categoryChart'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($categories); ?>,
                    datasets: [{
                        data: <?php echo json_encode($category_counts); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Revenue Chart
            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [{
                        label: 'Daily Revenue',
                        data: <?php echo json_encode($revenues); ?>,
                        fill: false,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            const addCargoForm = document.getElementById("add-cargo-form");
            const cargoTableBody = document.getElementById("cargo-table-body");

            addCargoForm.addEventListener("submit", function(e) {
                e.preventDefault();
                const name = document.getElementById("cargo-name").value;
                const category = document.getElementById("cargo-category").value;
                const price = document.getElementById("cargo-price").value;
                
                const newRow = document.createElement("tr");
                newRow.innerHTML = `
                    <td>${cargoTableBody.children.length + 1}</td>
                    <td>${name}</td>
                    <td>${document.getElementById("cargo-category").options[document.getElementById("cargo-category").selectedIndex].text}</td>
                    <td>$${parseFloat(price).toFixed(2)}</td>
                    <td class="action-buttons">
                        <button class="edit-btn" data-id="${cargoTableBody.children.length + 1}">Edit</button>
                        <button class="delete-btn" data-id="${cargoTableBody.children.length + 1}">Delete</button>
                    </td>
                `;
                cargoTableBody.appendChild(newRow);
                addCargoForm.reset();
            });

            cargoTableBody.addEventListener("click", function(e) {
                if (e.target.classList.contains("edit-btn")) {
                    const row = e.target.closest("tr");
                    const cells = row.getElementsByTagName("td");
                    document.getElementById("cargo-name").value = cells[1].textContent;
                    document.getElementById("cargo-price").value = parseFloat(cells[3].textContent.slice(1));
                }
                if (e.target.classList.contains("delete-btn")) {
                    if (confirm("Are you sure you want to delete this item?")) {
                        e.target.closest("tr").remove();
                    }
                }
            });

            // User management
            const userList = document.getElementById("user-list");
            const userPopup = document.getElementById("user-popup");
            const closePopup = document.getElementsByClassName("close")[0];

            userList.addEventListener("click", function(e) {
                if (e.target.classList.contains("view-btn")) {
                    const userId = e.target.getAttribute("data-userid");
                    showUserDetails(userId);
                }
            });

            closePopup.onclick = function() {
                userPopup.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == userPopup) {
                    userPopup.style.display = "none";
                }
            }

            function showUserDetails(userId) {
                fetch(`get_user_details.php?user_id=${userId}`)
                    .then(response => response.json())
                    .then(user => {
                        document.getElementById("popup-username").textContent = user.username;
                        document.getElementById("popup-email").textContent = `Email: ${user.email}`;
                        const cargoList = document.getElementById("popup-cargo-list");
                        cargoList.innerHTML = '';
                        if (user.cargo_items && user.cargo_items.length > 0) {
                            user.cargo_items.forEach(item => {
                                const li = document.createElement('li');
                                li.textContent = `${item.name} (${item.category_name}) - $${item.total_price}`;
                                cargoList.appendChild(li);
                            });
                        } else {
                            cargoList.innerHTML = '<li>No cargo items found for this user.</li>';
                        }
                        userPopup.style.display = "block";
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load user details. Please try again.');
                    });
            }
        });





document.addEventListener('DOMContentLoaded', function() {
    const userList = document.getElementById('user-list');

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    userList.addEventListener('click', function(e) {
        const blockBtn = e.target.closest('.block-btn');
        if (!blockBtn) return;

        if (blockBtn.classList.contains('loading')) return;

        const userId = blockBtn.dataset.userid;
        blockBtn.classList.add('loading');

        const formData = new FormData();
        formData.append('user_id', userId);

        fetch('toggle_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const isBlocked = data.new_status === 1;
                
                // Update button appearance
                if (isBlocked) {
                    blockBtn.classList.add('unblock-btn');
                    blockBtn.innerHTML = '<i class="fas fa-unlock"></i><span>Unblock</span>';
                } else {
                    blockBtn.classList.remove('unblock-btn');
                    blockBtn.innerHTML = '<i class="fas fa-ban"></i><span>Block</span>';
                }

                showNotification('User status updated successfully', 'success');
            } else {
                throw new Error(data.message || 'Failed to update user status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message, 'error');
        })
        .finally(() => {
            blockBtn.classList.remove('loading');
        });
    });
});







    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>
