<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shirt Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #ff6200;
            --primary-light: #ff9e62;
            --secondary: #2d3748;
            --accent: #f56565;
            --light: #f8f9fa;
            --dark: #1a202c;
            --gray: #718096;
            --transition: all 0.3s ease;
        }
        
        body {
             background: linear-gradient(135deg, #6a11cb, #2575fc);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: var(--dark);
            min-height: 100vh;
            padding-top: 80px;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Navigation Bar */
        .navbar {
            background-color: rgba(255, 255, 255, 0.98);
            padding: 0.8rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--primary);
            text-decoration: none;
        }
        
        .logo i {
            margin-right: 10px;
            font-size: 2rem;
            transition: var(--transition);
        }
        
        .logo:hover i {
            transform: rotate(15deg);
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 2rem;
            position: relative;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--secondary);
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            padding: 0.8rem 0;
        }
        
        .nav-links a i {
            margin-right: 8px;
            font-size: 1.1rem;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background-color: var(--primary);
            transition: var(--transition);
            border-radius: 3px;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .page {
            display: none;
            background: #FFF600;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease;
            min-height: 600px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        #home-page {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .hero {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }
        
        .hero h1 {
            font-size: 3rem;
            color: white;
            margin-bottom: 1rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero p {
            font-size: 1.2rem;
            color: white;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .feature-card {
            flex: 1;
            min-width: 280px;
            max-width: 350px;
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 0;
            background: linear-gradient(to bottom, #8A2BE2, #00BFFF);
            transition: var(--transition);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .feature-card:hover::before {
            height: 100%;
        }
        
        .feature-icon {
            font-size: 3rem;
            color: #8A2BE2;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1);
            color: #00BFFF;
        }
        
        .feature-card h3 {
            margin-bottom: 1rem;
            color: var(--secondary);
        }
        
        .feature-card p {
            color: var(--gray);
            line-height: 1.6;
        }
        
        .role-selection {
            text-align: center;
            margin-top: 4rem;
            padding: 2rem 0;
        }
        
        .role-selection h2 {
            margin-bottom: 2.5rem;
            color: white;
            font-size: 2.2rem;
            position: relative;
            display: inline-block;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .role-selection h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: white;
            border-radius: 2px;
        }
        
        .role-buttons {
            display: flex;
            justify-content: center;
            gap: 3rem;
            flex-wrap: wrap;
        }
        
        .role-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 220px;
            height: 220px;
            background: linear-gradient(135deg, #8A2BE2, #00BFFF);
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .role-btn::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(rgba(255,255,255,0.2), rgba(255,255,255,0));
            transform: rotate(45deg);
            transition: var(--transition);
        }
        
        .role-btn:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        
        .role-btn:hover::before {
            transform: rotate(45deg) translate(20px, 20px);
        }
        
        .role-btn i {
            font-size: 4rem;
            margin-bottom: 1.2rem;
            transition: var(--transition);
        }
        
        .role-btn:hover i {
            transform: scale(1.1);
        }
        
        .role-btn span {
            font-size: 1.3rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        /* About Page */
        .about-content {
            line-height: 1.8;
        }
        
        .about-content h2 {
            color: #8A2BE2;
            margin-bottom: 1.5rem;
            font-size: 2.2rem;
            display: flex;
            align-items: center;
        }
        
        .about-content h2 i {
            margin-right: 12px;
        }
        
        .about-content p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: var(--secondary);
        }
        
        .about-content ul {
            margin-left: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .about-content li {
            margin-bottom: 0.8rem;
            color: var(--secondary);
            display: flex;
            align-items: flex-start;
        }
        
        .about-content li i {
            color: #8A2BE2;
            margin-right: 10px;
            margin-top: 5px;
        }
        
        /* Contact Page */
        .contact-content {
            line-height: 1.8;
        }
        
        .contact-content h2 {
            color: #8A2BE2;
            margin-bottom: 1.5rem;
            font-size: 2.2rem;
            display: flex;
            align-items: center;
        }
        
        .contact-content h2 i {
            margin-right: 12px;
        }
        
        .contact-content p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: var(--secondary);
        }
        
        .portfolio-link {
            display: inline-flex;
            align-items: center;
            margin-top: 1.5rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #8A2BE2, #00BFFF);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: var(--transition);
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .portfolio-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .portfolio-link i {
            margin-right: 10px;
        }
        
        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            text-align: center;
            padding: 2.5rem 1rem;
            margin-top: 4rem;
            position: relative;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
            color: white;
        }
        
        .footer-logo i {
            margin-right: 10px;
            color: #8A2BE2;
            font-size: 2rem;
        }
        
        .footer-links {
            display: flex;
            list-style: none;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .footer-links li {
            margin: 0 1.5rem;
        }
        
        .footer-links a {
            text-decoration: none;
            color: var(--light);
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        .footer-links a i {
            margin-right: 8px;
        }
        
        .footer-links a:hover {
            color: #00BFFF;
        }
        
        .copyright {
            margin-top: 1.5rem;
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .copyright i {
            color: var(--accent);
            margin: 0 5px;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .feature-card {
                min-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }
            
            .navbar {
                padding: 0.7rem 1.5rem;
            }
            
            .nav-container {
                flex-direction: column;
            }
            
            .logo {
                margin-bottom: 1rem;
            }
            
            .nav-links {
                width: 100%;
                justify-content: center;
                margin-top: 0.5rem;
            }
            
            .nav-links li {
                margin: 0 1rem;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .page {
                padding: 1.5rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .role-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .role-btn {
                width: 100%;
                max-width: 280px;
            }
            
            .footer-links {
                flex-direction: column;
                align-items: center;
            }
            
            .footer-links li {
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo" onclick="showPage('home-page'); return false;">
                <i class="fas fa-tshirt"></i>
                <span>Shirt Management System</span>
            </a>
            <ul class="nav-links">
                <li><a href="#" onclick="showPage('home-page'); return false;"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#" onclick="showPage('about-page'); return false;"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="#" onclick="showPage('contact-page'); return false;"><i class="fas fa-envelope"></i> Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Home Page -->
        <div id="home-page" class="page">
            <div class="hero">
                <h1>Welcome to Shirt Management System</h1>
                <p>Streamline your shirt inventory, orders, and customer management with our comprehensive solution</p>
            </div>
            
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3>Inventory Management</h3>
                    <p>Efficiently track and manage your shirt inventory across multiple locations</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Order Processing</h3>
                    <p>Process orders quickly and accurately with our intuitive order management system</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Analytics & Reporting</h3>
                    <p>Gain insights into your business with comprehensive analytics and reports</p>
                </div>
            </div>
            
            <div class="role-selection">
                <h2>Select Your Role to Continue</h2>
                <div class="role-buttons">
                    <a href="/api/login_user.php" class="role-btn">
                        <i class="fas fa-user"></i>
                        <span>User Login</span>
                    </a>
                    <a href="/api/login_admin.php" class="role-btn">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin Login</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- About Page -->
        <div id="about-page" class="page">
            <div class="about-content">
                <h2><i class="fas fa-info-circle"></i> About This Project</h2>
                <p>The Shirt Management System is designed to simplify the process of managing a shirt business, whether you're a small boutique or a large retailer. Our goal is to provide a comprehensive solution that handles inventory, orders, and customer relationships in one place.</p>
                
                <p>This project was created to address the common challenges faced by shirt retailers, including:</p>
                
                <ul>
                    <li><i class="fas fa-check-circle"></i> Tracking inventory across multiple locations</li>
                    <li><i class="fas fa-check-circle"></i> Managing customer orders and preferences</li>
                    <li><i class="fas fa-check-circle"></i> Analyzing sales data to make informed business decisions</li>
                    <li><i class="fas fa-check-circle"></i> Streamlining the order fulfillment process</li>
                </ul>
                
                <p>With a user-friendly interface and powerful features, our system helps shirt businesses focus on what they do best - creating and selling great products.</p>
            </div>
        </div>
        
        <!-- Contact Page -->
        <div id="contact-page" class="page">
            <div class="contact-content">
                <h2><i class="fas fa-envelope"></i> Contact Us</h2>
                <p>If you have any questions about the Shirt Management System or would like to learn more about how it can benefit your business, please don't hesitate to reach out.</p>
                
                <p>For more information about the developer behind this project, visit my portfolio:</p>
                
                <a href="https://iamraghul.netlify.app/" target="_blank" class="portfolio-link">
                    <i class="fas fa-external-link-alt"></i> View Portfolio
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <i class="fas fa-tshirt"></i>
                <span>Shirt Management System</span>
            </div>
            <ul class="footer-links">
                <li><a href="#" onclick="showPage('home-page'); return false;"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#" onclick="showPage('about-page'); return false;"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="#" onclick="showPage('contact-page'); return false;"><i class="fas fa-envelope"></i> Contact</a></li>
            </ul>
            <div class="copyright">
                <p><i class="far fa-copyright"></i> 2025 Shirt Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function showPage(pageId) {
            // Hide all pages
            const pages = document.querySelectorAll('.page');
            pages.forEach(page => {
                page.style.display = 'none';
            });
            
            // Show the selected page
            document.getElementById(pageId).style.display = 'block';
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>