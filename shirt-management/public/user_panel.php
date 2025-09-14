<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login_user.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get shirt catalog for user to select from
$catalog_stmt = $pdo->query("SELECT * FROM shirt_catalog ORDER BY brand_name, size, sleeve_type");
$shirt_catalog = $catalog_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Present';
    
    // Process shirt entries
    if (isset($_POST['shirts']) && is_array($_POST['shirts'])) {
        foreach ($_POST['shirts'] as $shirt_data) {
            if (!empty($shirt_data['shirt_id']) && !empty($shirt_data['quantity'])) {
                $shirt_id = (int)$shirt_data['shirt_id'];
                $quantity = (int)$shirt_data['quantity'];
                $sleeve_type = $shirt_data['sleeve_type'];
                
                // Get the shirt details from catalog
                $shirt_details = null;
                foreach ($shirt_catalog as $shirt) {
                    if ($shirt['shirt_id'] == $shirt_id) {
                        $shirt_details = $shirt;
                        break;
                    }
                }
                
                if ($shirt_details) {
                    // Insert shirt record with all required fields including price
                    $stmt = $pdo->prepare("INSERT INTO shirts (user_id, shirt_id, brand_name, size, quantity, sleeve_type, price, inbound_time) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        $user_id, 
                        $shirt_id,
                        $shirt_details['brand_name'],
                        $shirt_details['size'],
                        $quantity, 
                        $sleeve_type,
                        $shirt_details['price'] // Added price field
                    ]);
                }
            }
        }
        $success = "Records saved successfully!";
        
        // Refresh today's records after saving
        $records_stmt = $pdo->prepare("SELECT s.*, sc.brand_name, sc.size, sc.price 
                                      FROM shirts s 
                                      LEFT JOIN shirt_catalog sc ON s.shirt_id = sc.shirt_id
                                      WHERE s.user_id = ? AND DATE(s.inbound_time) = ?");
        $records_stmt->execute([$user_id, $date]);
        $today_records = $records_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get today's records for the user
$today = date('Y-m-d');
if (!isset($today_records)) {
    $records_stmt = $pdo->prepare("SELECT s.*, sc.brand_name, sc.size, sc.price 
                                  FROM shirts s 
                                  LEFT JOIN shirt_catalog sc ON s.shirt_id = sc.shirt_id
                                  WHERE s.user_id = ? AND DATE(s.inbound_time) = ?");
    $records_stmt->execute([$user_id, $today]);
    $today_records = $records_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Panel - Shirt Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #7209b7, #4361ee);
            color: #333;
            min-height: 100vh;
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
            color: #7209b7;
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
            color: #7209b7;
        }
        
        .logout-btn {
            background: #7209b7;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }
        
        /* Main Content */
        .user-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-header h1 {
            color: #7209b7;
            margin-bottom: 0.5rem;
        }
        
        .user-info {
            color: #666;
        }
        
        .date-display {
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-align: center;
        }
        
        .date-display .time {
            font-size: 1.5rem;
            font-weight: bold;
            color: #7209b7;
        }
        
        .date-display .date {
            color: #666;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .dashboard-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-card h3 {
            color: #7209b7;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        /* Form Elements */
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
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #7209b7;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a08a0;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        /* Shirt Entry Table */
        .shirt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        
        .shirt-table th {
            background: #f8f9fa;
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .shirt-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .shirt-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Today's Records */
        .records-container {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .record-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .record-item:last-child {
            border-bottom: none;
        }
        
        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.8);
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
            
            .welcome-header {
                flex-direction: column;
            }
            
            .date-display {
                margin-top: 1rem;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
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
                <li><a href="#"><i class="fas fa-user"></i> User Panel</a></li>
                <li><a href="../api/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="user-container">
        <div class="welcome-header">
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <div class="user-info">User ID: <?php echo $user_id; ?></div>
            </div>
            <div class="date-display">
                <div class="time" id="current-time"></div>
                <div class="date" id="current-date"></div>
            </div>
        </div>
        
        <div class="dashboard-cards">
            <!-- Attendance Form -->
            <div class="dashboard-card">
                <h3>Attendance & Daily Records</h3>
                <form method="POST" id="attendance-form">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Present" selected>Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Leave">Leave</option>
                        </select>
                    </div>
                    
                    <h4 class="mt-4">Shirts Stitched Today</h4>
                    
                    <table class="shirt-table" id="shirts-table">
                        <thead>
                            <tr>
                                <th>Brand & Size</th>
                                <th>Sleeve</th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="shirts[0][shirt_id]" class="form-control form-control-sm" required>
                                        <option value="">Select Shirt</option>
                                        <?php foreach ($shirt_catalog as $shirt): ?>
                                            <option value="<?php echo $shirt['shirt_id']; ?>">
                                                <?php echo htmlspecialchars($shirt['brand_name']); ?> - Size: <?php echo $shirt['size']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="shirts[0][sleeve_type]" class="form-control form-control-sm" required>
                                        <option value="Full-hand">Full-hand</option>
                                        <option value="Half-hand">Half-hand</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="shirts[0][quantity]" min="1" value="1" class="form-control form-control-sm" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeShirtRow(this)">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-primary" onclick="addShirtRow()">Add Another Shirt</button>
                        <button type="submit" class="btn btn-success">Save Records</button>
                    </div>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Today's Records -->
            <div class="dashboard-card">
                <h3>Today's Production</h3>
                <div class="records-container">
                    <?php if (count($today_records) > 0): ?>
                        <?php 
                        $total_quantity = 0;
                        foreach ($today_records as $record): 
                            $total_quantity += $record['quantity'];
                        ?>
                            <div class="record-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($record['brand_name']); ?></strong>
                                    <div>Size: <?php echo $record['size']; ?> | Sleeve: <?php echo $record['sleeve_type']; ?></div>
                                </div>
                                <div>
                                    <span class="badge bg-primary">Qty: <?php echo $record['quantity']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="record-item" style="border-top: 2px solid #7209b7; font-weight: bold;">
                            <div>Total Shirts Today:</div>
                            <div><?php echo $total_quantity; ?></div>
                        </div>
                    <?php else: ?>
                        <p>No records found for today.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Shirt Management System. All rights reserved.</p>
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
        
        // Shirt row management
        let shirtRowCount = 1;
        
        function addShirtRow() {
            const tbody = document.querySelector('#shirts-table tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <select name="shirts[${shirtRowCount}][shirt_id]" class="form-control form-control-sm" required>
                        <option value="">Select Shirt</option>
                        <?php foreach ($shirt_catalog as $shirt): ?>
                            <option value="<?php echo $shirt['shirt_id']; ?>">
                                <?php echo htmlspecialchars($shirt['brand_name']); ?> - Size: <?php echo $shirt['size']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="shirts[${shirtRowCount}][sleeve_type]" class="form-control form-control-sm" required>
                        <option value="Full-hand">Full-hand</option>
                        <option value="Half-hand">Half-hand</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="shirts[${shirtRowCount}][quantity]" min="1" value="1" class="form-control form-control-sm" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeShirtRow(this)">Remove</button>
                </td>
            `;
            tbody.appendChild(newRow);
            shirtRowCount++;
        }
        
        function removeShirtRow(button) {
            const row = button.closest('tr');
            if (document.querySelectorAll('#shirts-table tbody tr').length > 1) {
                row.remove();
            }
        }
    </script>
</body>
</html>