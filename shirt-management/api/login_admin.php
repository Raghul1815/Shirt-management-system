<?php
require_once '../includes/config.php';
require_once '../includes/auth_middleware.php';

// Check if admin is already logged in, redirect to admin panel
if (isset($_SESSION['admin_id'])) {
    header("Location: ../public/admin_panel.php");
    exit();
}

// Process admin login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize user input
  $username = trim(htmlspecialchars($_POST['username'] ?? ''));
$password = trim($_POST['password'] ?? '');

    // Query to check admin credentials
   require_once __DIR__ . '/../includes/config.php';

// Sanitize input
$username = trim(htmlspecialchars($_POST['username'] ?? ''));
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
    die("Please enter both username and password.");
}

// Query the users table for admins only
$stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = ? AND role = 'admin'");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = [
        'user_id' => (int)$user['user_id'],
        'username' => $user['username'],
        'role' => $user['role']
    ];
    header("Location: ../public/admin_panel.php");
    exit;
} else {
    echo "Invalid admin username or password!";
}


    if ($admin) {
        // Verify password (assuming passwords are hashed)
        if (password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = 'admin';
            
            // Redirect to admin dashboard
            header("Location: ../public/admin_panel.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "No admin found with that username";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Shirt Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #3a0ca3, #4361ee);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navigation Bar */
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            font-weight: bold;
            font-size: 1.5rem;
            color: #3a0ca3;
        }
        
        .logo i {
            margin-right: 10px;
            font-size: 1.8rem;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 2rem;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #3a0ca3;
        }
        
        /* Login Container */
        .login-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        
        .login-header {
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            font-size: 2rem;
            color: #3a0ca3;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #666;
        }
        
        .input-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .input-group input:focus {
            border-color: #3a0ca3;
            outline: none;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #3a0ca3, #4361ee);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(58, 12, 163, 0.4);
        }
        
        .alternate-option {
            margin-top: 1.5rem;
            color: #666;
        }
        
        .alternate-option a {
            color: #3a0ca3;
            text-decoration: none;
            font-weight: 500;
        }
        
        .alternate-option a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background: #ffebee;
            color: #d32f2f;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            display: <?php echo isset($error) ? 'block' : 'none'; ?>;
        }
        
        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            text-align: center;
            padding: 1.5rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
            }
            
            .nav-links {
                margin-top: 1rem;
            }
            
            .nav-links li {
                margin: 0 1rem;
            }
            
            .login-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-tshirt"></i>
                <span>Shirt Management System</span>
            </div>
            <ul class="nav-links">
                <li><a href="../public/index.php">Home</a></li>
                <li><a href="../public/index.php#about">About</a></li>
                <li><a href="../public/index.php#contact">Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Admin Login</h1>
                <p>Access the administration panel</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="input-group">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn">Login as Admin</button>
            </form>
            
            <div class="alternate-option">
                 <p>Don't have an account? <a href="../public/register.php">Register here</a></p>
                <p>Are you a user? <a href="login_user.php">User login</a></p>
                <p><a href="../public/index.php">Back to home</a></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Shirt Management System. All rights reserved.</p>
    </footer>
</body>
</html>