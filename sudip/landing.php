<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get system name from session or database
$system_name = $_SESSION['system_name'] ?? null;

// If not in session, fetch from database
if (!$system_name) {
    include 'config.php';
    $query = "SELECT system_name FROM system_settings LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $system_name = $row['system_name'];
        $_SESSION['system_name'] = $system_name;
    } else {
        // Default name if not found in database
        $system_name = 'CarGO';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarGO - Advanced Cargo Management Software</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>

        :root {
            --blue: #0066cc;
            --deep-blue: #004080;
            --accent-orange: #FF6600;
            --light-gray: #F3F4F6;
            --text-dark: #1F2937;
            --text-light: #6B7280;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #FFFFFF;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: var(--blue);
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 28px;
            font-weight: 700;
            color: #FFFFFF;
            text-decoration: none;
        }

        .logo-icon {
            font-size: 32px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #FFFFFF;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--accent-orange);
        }

        .try-demo-btn {
            background-color: var(--accent-orange);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .try-demo-btn:hover {
            background-color: #FF8533;
        }


        .hero {
            padding: 80px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 60px;
        }

        .hero-content {
            flex: 1;
            margin-top:-38px
        }

        .customizable {
            color: var(--accent-orange);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 700;
            color: var(--deep-blue);
            margin-bottom: 24px;
            line-height: 1.2;
        }

        .hero-description {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 32px;
            color: var(--text-light);
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 48px;
        }

        .get-started-btn {
            background-color: var(--deep-blue);
            color: white;
            padding: 14px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .get-started-btn:hover {
            background-color: var(--blue);
        }

        .features-btn {
            color: var(--deep-blue);
            text-decoration: none;
            font-weight: 600;
            padding: 12px 24px;
            border: 2px solid var(--deep-blue);
            border-radius: 50px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .features-btn:hover {
            background-color: var(--deep-blue);
            color: white;
        }

        .feature-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .feature-card {
            background: white;
            padding: 30px;
            margin-top:-20px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 36px;
            color: var(--accent-orange);
            margin-bottom: 20px;
        }

        .feature-title {
            color: var(--deep-blue);
            font-weight: 600;
            font-size: 20px;
            margin-bottom: 12px;
        }

        .feature-description {
            color: var(--text-light);
            font-size: 14px;
        }

        .hero-image {
            flex: 1;
            max-width: 500px;
        }

        .hero-image img {
            width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Footer */
        footer {
            background-color: var(--blue);
            color: #FFFFFF;
            padding: 60px 0;
            text-align: center;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        .footer-links {
            display: flex;
            gap: 30px;
        }

        .footer-links a {
            color: #FFFFFF;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--accent-orange);
        }

        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                padding: 60px 0;
            }

            .hero-title {
                font-size: 36px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .feature-cards {
                grid-template-columns: 1fr;
            }

            .hero-image {
                max-width: 100%;
            }

            .footer-links {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<script>
        // Check if system name is stored in localStorage (from settings page)
        document.addEventListener('DOMContentLoaded', function() {
            const storedSystemName = localStorage.getItem('systemName');
            if (storedSystemName) {
                // Update system name in the header
                const logoElements = document.querySelectorAll('.logo');
                logoElements.forEach(element => {
                    // Find text node within the logo element
                    for (let i = 0; i < element.childNodes.length; i++) {
                        if (element.childNodes[i].nodeType === Node.TEXT_NODE) {
                            element.childNodes[i].nodeValue = storedSystemName;
                            break;
                        }
                    }
                });
                
                // Update page title
                if (document.title.includes('CarGO') || document.title.includes('CargoPro')) {
                    document.title = document.title.replace(/CarGO|CargoPro/, storedSystemName);
                }
                
                // Update any elements with class 'system-name'
                document.querySelectorAll('.system-name').forEach(element => {
                    element.textContent = storedSystemName;
                });
            }
        });
        
        // Listen for system name changes from other frames/windows
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'systemNameChanged') {
                const newName = event.data.newName;
                localStorage.setItem('systemName', newName);
                
                // Update system name in the header
                const logoElements = document.querySelectorAll('.logo');
                logoElements.forEach(element => {
                    // Find text node within the logo element
                    for (let i = 0; i < element.childNodes.length; i++) {
                        if (element.childNodes[i].nodeType === Node.TEXT_NODE) {
                            element.childNodes[i].nodeValue = newName;
                            break;
                        }
                    }
                });
                
                // Update page title
                if (document.title.includes('CarGO') || document.title.includes('CargoPro')) {
                    document.title = document.title.replace(/CarGO|CargoPro/, newName);
                }
                
                // Update any elements with class 'system-name'
                document.querySelectorAll('.system-name').forEach(element => {
                    element.textContent = newName;
                });
            }
        });
        
    </script>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">
                <span class="logo-icon">🚚</span>
                <?php echo htmlspecialchars($system_name); ?>
            </a>
            <nav class="nav-links">
                <a href="#">Features</a>
                <a href="login.php" class="try-demo-btn">Get Started</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <section class="hero">
            <div class="hero-content">
                <div class="customizable">Logistics company</div>
                <h1 class="hero-title">Revolutionize Your Cargo Management</h1>
                <p class="hero-description">
                    Experience unparalleled efficiency and control with CarGO. Our cutting-edge software streamlines your logistics operations.
                </p>
                <div class="hero-buttons">
                    <a href="#" class="features-btn">Explore Features Cargo System</a>
                </div>
                <div class="feature-cards">
                    <div class="feature-card">
                        <div class="feature-icon">🔍</div>
                        <div class="feature-title">Real-time record</div>
                        <div class="feature-description">Monitor your  cargo-record  anytime, anywhere.</div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📦</div>
                        <div class="feature-title">Inventory Management</div>
                        <div class="feature-description">Optimize your stock levels with our smart inventory solutions.</div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <div class="feature-title">Advanced Analytics</div>
                        <div class="feature-description">Gain valuable insights with our comprehensive reporting tools.</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
          <img src="tracking-emblem-blue-location-icon-conveys-gps-navigation-white_904318-1870.avif">
            </div>
        </section>
    </div>
    <footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> <span class="system-name"><?php echo htmlspecialchars($system_name); ?></span>. All rights reserved by CMS.</p>
    </div>
</footer>

<style>
    footer {
        background-color: var(--blue);
        color: #FFFFFF;
        padding: 60px 0;
        text-align: center;
        margin-top: 50px;
    }
</style>

<script>
    // Check if system name is stored in localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const storedSystemName = localStorage.getItem('systemName');
        if (storedSystemName) {
            // Update system name in the footer
            document.querySelectorAll('.system-name').forEach(element => {
                element.textContent = storedSystemName;
            });
        }

    });


</script>
</body>
</html>