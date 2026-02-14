<?php
require_once 'config.php';
require_once 'auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    // Corrected query using 'cargo_id' instead of 'id'
    $query = "DELETE FROM cargo_items WHERE cargo_id = $id";
    
    if (mysqli_query($conn, $query)) {
        header("Location: user_dashboard.php");
        exit();
    } else {
        $error = "Error deleting record: " . mysqli_error($conn);
    }
}

// Fetch item details
$query = "SELECT name FROM cargo_items WHERE cargo_id = $id";
$result = mysqli_query($conn, $query);
$item = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Cargo Item - CargoPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
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
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            color: var(--dark-color);
        }

        .sidebar {
            width: 250px;
            background: var(--primary-color);
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .sidebar .menu-item {
            width: 100%;
            padding: 1rem;
            margin: 0.5rem 0;
            text-align: left;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sidebar .menu-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .sidebar .menu-item i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        .main {
            flex-grow: 1;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--danger-color);
        }

        .modal h2 {
            color: var(--danger-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0.5rem;
        }

        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #f5c6cb;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 1rem;
            }

            .main {
                padding: 1rem;
            }

            .modal {
                width: 95%;
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
        <div class="menu-item"><i class="fas fa-cog"></i> Settings</div>
    </div>

    <div class="main">
        <div class="modal">
            <h2><i class="fas fa-exclamation-triangle"></i> Delete Cargo Item</h2>
            <?php if (isset($error)) : ?>
                <p class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
            <?php elseif (isset($item)) : ?>
                <p>Are you sure you want to delete the item: <br><strong><?php echo htmlspecialchars($item['name']); ?></strong>?</p>
                <form action="delete.php?id=<?php echo $id; ?>" method="post">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="btn btn-delete"><i class="fas fa-trash"></i> Delete</button>
                    <a href="user_dashboard.php" class="btn btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </form>
            <?php else : ?>
                <p class="error"><i class="fas fa-exclamation-circle"></i> Item not found.</p>
                <a href="user_dashboard.php" class="btn btn-cancel"><i class="fas fa-times"></i> Back to List</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>