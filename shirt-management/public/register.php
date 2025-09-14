<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Redirect to appropriate panel if already logged in - FIXED
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../public/admin_panel.php");
    } else {
        header("Location: ../public/user_panel.php");
    }
    exit;
}

$error = '';
$success = '';

// Process registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($password) || empty($role) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers, and underscores.";
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Username already exists. Please choose a different one.";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashedPassword, $role]);
                
                $success = "Account created successfully! You can now <a href='login_user.php'>login</a>."; // FIXED LINK
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Shirt Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
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
            color: #4361ee;
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
            color: #4361ee;
        }
        
        /* Registration Container */
        .register-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .register-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        
        .register-header {
            margin-bottom: 2rem;
        }
        
        .register-header h1 {
            font-size: 2rem;
            color: #4361ee;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
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
        
        .input-group input,
        .input-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .input-group input:focus,
        .input-group select:focus {
            border-color: #4361ee;
            outline: none;
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #666;
        }
        
        .register-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4361ee, #4cc9f0);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        
        .alternate-option {
            margin-top: 1.5rem;
            color: #666;
        }
        
        .alternate-option a {
            color: #4361ee;
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
        }
        
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
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
            
            .register-card {
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
                <li><a href="../public/index.php">Home</a></li> <!-- FIXED LINK -->
                <li><a href="../public/index.php#about">About</a></li> <!-- FIXED LINK -->
                <li><a href="../public/index.php#contact">Contact</a></li> <!-- FIXED LINK -->
            </ul>
        </div>
    </nav>

    <!-- Registration Container -->
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Create Account</h1>
                <p>Register as a user or administrator</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>">
                </div>
                
                <div class="input-group">
                    <label for="role">Account Type</label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="user" <?php echo (isset($role) && $role === 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-strength">Must be at least 6 characters</div>
                </div>
                
                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="register-btn">Create Account</button>
            </form>
            
            <div class="alternate-option">
                <p>Already have an account? <a href="login_user.php">Login here</a></p> <!-- FIXED LINK -->
                <p><a href="../public/index.php">Back to home</a></p> <!-- FIXED LINK -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Shirt Management System. All rights reserved.</p> <!-- UPDATED YEAR -->
    </footer>

    <script>
        // Simple password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            form.addEventListener('submit', function(e) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    confirmPassword.focus();
                }
            });
        });
    </script>
</body>
</html>