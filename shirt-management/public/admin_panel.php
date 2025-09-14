<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth_middleware.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

// Handle shirt catalog form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_shirt'])) {
    $brand_name = trim($_POST['brand_name']);
    $size = (int)$_POST['size'];
    $sleeve_type = $_POST['sleeve_type'];
    $price = (float)$_POST['price'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO shirt_catalog (brand_name, size, sleeve_type, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$brand_name, $size, $sleeve_type, $price]);
        $success = "Shirt added to catalog successfully!";
    } catch (PDOException $e) {
        $error = "Error adding shirt: " . $e->getMessage();
    }
}

// Get filter parameters
$date_filter = $_GET['date'] ?? date('Y-m-d');
$user_filter = $_GET['user'] ?? '';

// Query to get shirt stitching data
$query = "SELECT s.*, u.username, sc.brand_name, sc.size, sc.sleeve_type, sc.price
          FROM shirts s 
          JOIN users u ON s.user_id = u.user_id 
          LEFT JOIN shirt_catalog sc ON s.shirt_id = sc.shirt_id
          WHERE DATE(s.inbound_time) = :date_filter";
          
$params = [':date_filter' => $date_filter];

if (!empty($user_filter)) {
    $query .= " AND s.user_id = :user_id";
    $params[':user_id'] = $user_filter;
}

$query .= " ORDER BY s.inbound_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$shirts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total counts for today
$total_query = "SELECT 
                COUNT(*) as total_shirts,
                SUM(CASE WHEN sc.size BETWEEN 30 AND 34 THEN 1 ELSE 0 END) as small,
                SUM(CASE WHEN sc.size BETWEEN 35 AND 39 THEN 1 ELSE 0 END) as medium,
                SUM(CASE WHEN sc.size BETWEEN 40 AND 44 THEN 1 ELSE 0 END) as large,
                SUM(CASE WHEN sc.sleeve_type = 'Full-hand' THEN 1 ELSE 0 END) as full_sleeve,
                SUM(CASE WHEN sc.sleeve_type = 'Half-hand' THEN 1 ELSE 0 END) as half_sleeve,
                SUM(sc.price * s.quantity) as total_value
                FROM shirts s 
                LEFT JOIN shirt_catalog sc ON s.shirt_id = sc.shirt_id
                WHERE DATE(s.inbound_time) = :date_filter";
                
$total_stmt = $pdo->prepare($total_query);
$total_stmt->execute([':date_filter' => $date_filter]);
$totals = $total_stmt->fetch(PDO::FETCH_ASSOC);

// Get list of users for filter dropdown
$users_stmt = $pdo->query("SELECT user_id, username FROM users WHERE role = 'user' ORDER BY username");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get shirt catalog
$catalog_stmt = $pdo->query("SELECT * FROM shirt_catalog ORDER BY brand_name, size, sleeve_type");
$shirt_catalog = $catalog_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Shirt Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f7fa;
            color: #333;
            min-height: 100vh;
        }
        
        /* Navigation Bar */
        .navbar {
            background-color: #3a0ca3;
            padding: 1rem 2rem;
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .nav-container {
            max-width: 1400px;
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
            color: white;
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
            color: white;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        
        .nav-links a:hover {
            opacity: 0.8;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }
        
        /* Main Content */
        .admin-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .welcome-header h1 {
            color: #3a0ca3;
        }
        
        .date-display {
            font-size: 1.2rem;
            color: #666;
            background: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 2px solid #ddd;
        }
        
        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            background: #f0f0f0;
            border-radius: 5px 5px 0 0;
            margin-right: 0.5rem;
        }
        
        .tab.active {
            background: #3a0ca3;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .summary-card h3 {
            color: #3a0ca3;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            color: #4361ee;
        }
        
        .summary-small {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }
        
        /* Forms */
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .btn {
            background: #3a0ca3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn:hover {
            background: #4361ee;
        }
        
        /* Filters */
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #3a0ca3;
            color: white;
            padding: 1rem;
            text-align: left;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .size-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        
        .size-small { background: #e0f7fa; color: #006064; }
        .size-medium { background: #e8f5e9; color: #2e7d32; }
        .size-large { background: #fff3e0; color: #ef6c00; }
        
        .sleeve-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        
        .sleeve-full { background: #f3e5f5; color: #7b1fa2; }
        .sleeve-half { background: #e3f2fd; color: #1565c0; }
        
        /* Footer */
        footer {
            background: #3a0ca3;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
            }
            
            .nav-links {
                margin-top: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .nav-links li {
                margin: 0.5rem;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .welcome-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .date-display {
                margin-top: 1rem;
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
                <li><a href="index.php">Home</a></li>
                <li><a href="#"><i class="fas fa-user-shield"></i> Admin Panel</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="admin-container">
        <div class="welcome-header">
            <h1>Admin Dashboard</h1>
            <div class="date-display">
                <i class="fas fa-clock"></i> <span id="current-time"></span> | 
                <i class="fas fa-calendar-alt"></i> <span id="current-date"></span>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" onclick="switchTab('dashboard')">Dashboard</div>
            <div class="tab" onclick="switchTab('shirt-catalog')">Shirt Catalog</div>
            <div class="tab" onclick="switchTab('reports')">Reports</div>
        </div>
        
        <!-- Dashboard Tab -->
        <div id="dashboard-tab" class="tab-content active">
            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Shirts Today</h3>
                    <div class="summary-number"><?php echo $totals['total_shirts'] ?? 0; ?></div>
                    <div class="summary-small"><?php echo $date_filter; ?></div>
                </div>
                
                <div class="summary-card">
                    <h3>Full Sleeve</h3>
                    <div class="summary-number"><?php echo $totals['full_sleeve'] ?? 0; ?></div>
                    <div class="summary-small">Half: <?php echo $totals['half_sleeve'] ?? 0; ?></div>
                </div>
                
                <div class="summary-card">
                    <h3>Small (30-34)</h3>
                    <div class="summary-number"><?php echo $totals['small'] ?? 0; ?></div>
                    <div class="summary-small">Med: <?php echo $totals['medium'] ?? 0; ?> | Lg: <?php echo $totals['large'] ?? 0; ?></div>
                </div>
                
                <div class="summary-card">
                    <h3>Total Value</h3>
                    <div class="summary-number">₹<?php echo number_format($totals['total_value'] ?? 0, 2); ?></div>
                    <div class="summary-small">Today's production value</div>
                </div>
            </div>
            
            <!-- Filters -->
            <form method="GET" action="" class="filters">
                <div class="filter-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="<?php echo $date_filter; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="user">User</label>
                    <select id="user" name="user">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo ($user_filter == $user['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn">Apply Filters</button>
                </div>
            </form>
            
            <!-- Shirts Table -->
            <div class="table-container">
                <table class="shirts-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Brand</th>
                            <th>Size</th>
                            <th>Sleeve Type</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Inbound Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($shirts) > 0): ?>
                            <?php foreach ($shirts as $shirt): 
                                $size_class = '';
                                if ($shirt['size'] >= 30 && $shirt['size'] <= 34) $size_class = 'size-small';
                                elseif ($shirt['size'] >= 35 && $shirt['size'] <= 39) $size_class = 'size-medium';
                                else $size_class = 'size-large';
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($shirt['username']); ?></td>
                                    <td><?php echo htmlspecialchars($shirt['brand_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="size-badge <?php echo $size_class; ?>">
                                            <?php echo $shirt['size'] ?? 'N/A'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="sleeve-badge sleeve-<?php echo strtolower($shirt['sleeve_type'] ?? ''); ?>">
                                            <?php echo $shirt['sleeve_type'] ?? 'N/A'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $shirt['quantity']; ?></td>
                                    <td>₹<?php echo number_format($shirt['price'] ?? 0, 2); ?></td>
                                    <td>₹<?php echo number_format(($shirt['price'] ?? 0) * $shirt['quantity'], 2); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($shirt['inbound_time'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem;">
                                    No shirts found for the selected date.
                                </td>
                           </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Shirt Catalog Tab -->
        <div id="shirt-catalog-tab" class="tab-content">
            <div class="form-container">
                <h3>Add New Shirt to Catalog</h3>
                <?php if (isset($success)): ?>
                    <div style="color: green; margin-bottom: 1rem;"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div style="color: red; margin-bottom: 1rem;"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="brand_name">Brand Name</label>
                        <input type="text" id="brand_name" name="brand_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="size">Size (30-44)</label>
                        <select id="size" name="size" required>
                            <option value="">Select Size</option>
                            <?php for ($i = 30; $i <= 44; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sleeve_type">Sleeve Type</label>
                        <select id="sleeve_type" name="sleeve_type" required>
                            <option value="">Select Sleeve Type</option>
                            <option value="Full-hand">Full-hand</option>
                            <option value="Half-hand">Half-hand</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price per Shirt (₹)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <button type="submit" name="add_shirt" class="btn">Add to Catalog</button>
                </form>
            </div>
            
            <div class="table-container">
                <h3 style="padding: 1rem;">Current Shirt Catalog</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Size</th>
                            <th>Sleeve Type</th>
                            <th>Price</th>
                            <th>Added On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($shirt_catalog) > 0): ?>
                            <?php foreach ($shirt_catalog as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['brand_name']); ?></td>
                                    <td><?php echo $item['size']; ?></td>
                                    <td><?php echo $item['sleeve_type']; ?></td>
                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem;">
                                    No shirts in catalog yet.
                                </td>
                           </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Reports Tab -->
        <div id="reports-tab" class="tab-content">
            <div class="form-container">
                <h3>Generate Reports</h3>
                <p>Weekly and monthly reports will be available here.</p>
                <!-- Report generation form will be added here -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Shirt Management System. All rights reserved.</p>
    </footer>

    <script>
        // Live clock
        function updateClock() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString();
            document.getElementById('current-date').textContent = now.toLocaleDateString();
        }
        setInterval(updateClock, 1000);
        updateClock();
        
        // Tab switching
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Deactivate all tab buttons
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activate selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Activate clicked tab button
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>