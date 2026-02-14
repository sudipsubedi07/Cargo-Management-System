<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Get cargo ID from URL parameter
$cargo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch cargo details including locations
$cargo_query = "SELECT 
    c.cargo_id,
    c.name,
    c.quantity,
    c.weight_range,
    cat.category_name,
    cust.name AS customer_name,
    l1.location_name AS pickup_location,
    l2.location_name AS dropoff_location,
    c.distance,
    c.price_per_km,
    c.total_price,
    c.created_at
FROM cargo_items c
JOIN categories cat ON c.category_id = cat.category_id
JOIN customers cust ON c.customer_id = cust.customer_id
JOIN locations l1 ON c.pickup_location_id = l1.location_id
JOIN locations l2 ON c.dropoff_location_id = l2.location_id
WHERE c.cargo_id = ?";

$stmt = mysqli_prepare($conn, $cargo_query);
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $cargo_id);
if (!mysqli_stmt_execute($stmt)) {
    die("Error executing statement: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
$cargo = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargo Report - CargoGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #ff9800;
            --background-color: #f4f7fc;
            --text-color: #343a40;
            --border-color: #ced4da;
            --box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
        }

        .cargo-details {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
        }

        .cargo-details h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .detail-item label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .detail-item span {
            color: var(--text-color);
            font-size: 1rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: bold;
        }

        .report-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: var(--box-shadow);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;
            transition: var(--transition-speed);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-speed);
            border: none;
            outline: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-print {
            background: var(--secondary-color);
            color: white;
        }

        .btn-print:hover {
            background: #e68a00;
        }

        .attachment-area {
            border: 2px dashed var(--border-color);
            border-radius: 5px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition-speed);
            margin-bottom: 1.5rem;
        }

        .attachment-area:hover {
            border-color: var(--primary-color);
            background-color: rgba(0, 123, 255, 0.05);
        }

        .file-list {
            margin-top: 1rem;
        }

        .file-list ul {
            list-style: none;
            padding: 0;
        }

        .file-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }

        @media print {
            .no-print {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Cargo Report</h2>
            <button class="btn btn-print" onclick="printReport()">Print Report</button>
        </div>

        <?php if ($cargo): ?>
        <div class="cargo-details">
            <h3>Cargo Details</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <label>Cargo Name:</label>
                    <span><?php echo htmlspecialchars($cargo['name']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Quantity:</label>
                    <span><?php echo htmlspecialchars($cargo['quantity']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Weight Range:</label>
                    <span><?php echo htmlspecialchars($cargo['weight_range']); ?> kg</span>
                </div>
                <div class="detail-item">
                    <label>Category:</label>
                    <span><?php echo htmlspecialchars($cargo['category_name']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Customer:</label>
                    <span><?php echo htmlspecialchars($cargo['customer_name']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Pickup Location:</label>
                    <span><?php echo htmlspecialchars($cargo['pickup_location']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Dropoff Location:</label>
                    <span><?php echo htmlspecialchars($cargo['dropoff_location']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Distance:</label>
                    <span><?php echo htmlspecialchars($cargo['distance']); ?> km</span>
                </div>
                <div class="detail-item">
                    <label>Price per KM:</label>
                    <span>$<?php echo htmlspecialchars(number_format($cargo['price_per_km'], 2)); ?></span>
                </div>
                <div class="detail-item">
                    <label>Total Price:</label>
                    <span>$<?php echo htmlspecialchars(number_format($cargo['total_price'], 2)); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="report-form">
            <form id="reportForm" action="process_report.php" method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="cargo_id" value="<?php echo $cargo_id; ?>">
                
                <div class="form-group">
                    <label class="form-label">Report Title</label>
                    <input type="text" class="form-control" name="title" placeholder="Enter report title" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="date" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Enter report details" required></textarea>
                </div>

                <div class="attachment-area no-print" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click and Drag to Upload Files</p>
                    <input type="file" id="fileInput" name="attachment" multiple style="display: none" accept="image/*,.pdf,.doc,.docx">
                </div>

                <div class="file-list" id="fileList">
                    <ul id="uploadedFiles"></ul>
                </div>

                <div class="btn-group no-print">
                    <button type="submit" class="btn btn-primary">Save Report</button>
                    <button type="button" class="btn btn-secondary" onclick="clearForm()">Clear Form</button>
                    <button type="button" class="btn btn-secondary" onclick="goToDashboard()">Back to Dashboard</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            let title = document.forms["reportForm"]["title"].value;
            let date = document.forms["reportForm"]["date"].value;
            let description = document.forms["reportForm"]["description"].value;

            if (title == "" || date == "" || description == "") {
                alert("All fields must be filled out");
                return false;
            }
            return true;
        }

        function printReport() {
            window.print();
        }

        function clearForm() {
            document.getElementById("reportForm").reset();
            document.getElementById("uploadedFiles").innerHTML = "";
        }

        function goToDashboard() {
            window.location.href = 'user_dashboard.php';
        }

        document.getElementById('fileInput').addEventListener('change', function(event) {
            let files = event.target.files;
            let fileList = document.getElementById('uploadedFiles');
            fileList.innerHTML = '';

            for (let i = 0; i < files.length; i++) {
                let li = document.createElement('li');
                li.textContent = files[i].name;
                let removeIcon = document.createElement('i');
                removeIcon.className = 'fas fa-times';
                removeIcon.onclick = function() {
                    li.remove();
                };
                li.appendChild(removeIcon);
                fileList.appendChild(li);
            }
        });

        // Set default date to today
        document.addEventListener('DOMContentLoaded', function() {
            let today = new Date().toISOString().split('T')[0];
            document.querySelector('input[type="date"]').value = today;
        });
    </script>
</body>
</html>