Shirt Management System
A comprehensive web-based application for managing shirt production, inventory, and user operations. This system provides separate interfaces for administrators and users to track shirt production, manage catalog items, and maintain records.

🌟 Features
Admin Features
Dashboard Overview: View daily production statistics with visual summaries

Shirt Catalog Management: Add, edit, and manage shirt brands, sizes, and pricing

User Management: Monitor user activities and production records

Reporting: Generate daily, weekly, and monthly production reports

Real-time Analytics: Track production metrics with visual charts and summaries

User Features
Daily Attendance: Mark presence/absence status

Production Tracking: Record shirts stitched with brand, size, and sleeve type details

Real-time Dashboard: View today's production summary

Historical Records: Access previous production data

Technical Features
Role-based Authentication: Separate login systems for admins and users

Responsive Design: Works seamlessly on desktop and mobile devices

Secure Sessions: Protected routes with proper authentication middleware

Database Integration: MySQL database with optimized queries

Real-time Clock: Live date and time display

🚀 Installation
Prerequisites
PHP 7.4 or higher

MySQL 5.7 or higher

Web server (Apache/Nginx)

Composer (for dependency management)

Setup Instructions
Clone the repository

bash
git clone https://github.com/raghul1815/shirt-management-system.git
cd shirt-management-system
Database Setup

bash
mysql -u root -p
CREATE DATABASE shirt_management;
USE shirt_management;
Import Database Schema

bash
mysql -u username -p shirt_management < database/schema.sql
Configure Database Connection
Edit includes/config.php with your database credentials:

php
define('DB_HOST', 'localhost');
define('DB_NAME', 'shirt_management');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
Set Up Web Server

Point your web server to the public/ directory

Ensure mod_rewrite is enabled for Apache

Set proper file permissions:

bash
chmod 755 -R ./
chmod 777 uploads/ # if you have file uploads
Install Dependencies

bash
composer install
📁 Project Structure
text
shirt-management/
├── public/                 # Publicly accessible files
│   ├── index.php          # Home page
│   ├── user_panel.php     # User dashboard
│   ├── admin_panel.php    # Admin dashboard
│   ├── register.php       # User registration
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript files
├── api/                   # API endpoints
│   ├── auth.php          # Authentication handling
│   ├── login.php         # Login processing
│   ├── login_user.php    # User login
│   ├── login_admin.php   # Admin login
│   ├── logout.php        # Logout handling
│   ├── shirts_crud.php   # Shirt catalog management
│   ├── save_records.php  # Production record saving
│   ├── weekly_report.php # Report generation
│   └── admin_stats.php   # Admin statistics
├── includes/              # Application core
│   ├── config.php        # Database configuration
│   ├── auth_middleware.php # Authentication middleware
│   └── helpers.php       # Helper functions
├── vendor/               # Composer dependencies
├── database/             # Database files
│   └── schema.sql       # Database schema
└── README.md            # This file
🗄️ Database Schema
Main Tables
Users Table
sql
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Shirt Catalog Table
sql
CREATE TABLE shirt_catalog (
    shirt_id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) NOT NULL,
    size INT NOT NULL CHECK (size BETWEEN 30 AND 44),
    sleeve_type ENUM('Full-hand', 'Half-hand') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY brand_size_sleeve (brand_name, size, sleeve_type)
);
Shirts Production Table
sql
CREATE TABLE shirts (
    shirt_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    brand_name VARCHAR(100) NOT NULL,
    size INT NOT NULL,
    quantity INT DEFAULT 1,
    sleeve_type ENUM('Full-hand', 'Half-hand') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    inbound_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
🔐 Authentication System
User Login
Path: /api/login_user.php

Validates user credentials

Sets session variables: user_id, username, role

Admin Login
Path: /api/login_admin.php

Validates admin credentials

Sets session variables: user_id, username, role

Registration
Path: /public/register.php

Creates new user accounts

Supports both user and admin registration

Password hashing using PHP's password_hash()

🎨 UI Components
Admin Panel Features
Navigation Bar: Quick access to all admin features

Summary Cards: Visual overview of daily production

Filter System: Date and user-based filtering

Data Tables: Tabular display of production records

Catalog Management: Add/edit shirt brands and pricing

User Panel Features
Production Form: Easy input for daily production

Real-time Clock: Current date and time display

Production Summary: Today's work overview

Dynamic Form: Add multiple shirt entries dynamically

⚙️ Configuration
Environment Setup
Edit includes/config.php with your environment settings:

php
<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'shirt_management';
$db_user = 'your_username';
$db_pass = 'your_password';

// Create PDO instance
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Session configuration
ini_set('session.cookie_lifetime', 86400); // 24 hours
session_start();
?>
Security Settings
Password hashing with PASSWORD_DEFAULT

SQL injection prevention using PDO prepared statements

XSS protection with htmlspecialchars()

Session timeout management

Role-based access control

🚀 Usage
For Administrators
Login at /api/login_admin.php

Access the dashboard to view production statistics

Manage shirt catalog in the "Shirt Catalog" tab

Generate reports in the "Reports" tab

Monitor user activities and production records

For Users
Login at /api/login_user.php

Mark attendance status (Present/Absent/Leave)

Add daily production records

View today's production summary

Track historical production data

🛠️ API Endpoints
Authentication
POST /api/login_user.php - User login

POST /api/login_admin.php - Admin login

GET /api/logout.php - Logout

Data Management
POST /api/shirts_crud.php - Shirt catalog CRUD operations

POST /api/save_records.php - Save production records

GET /api/weekly_report.php - Generate weekly reports

GET /api/admin_stats.php - Admin statistics

🐛 Troubleshooting
Common Issues
Database Connection Error

Check database credentials in includes/config.php

Verify MySQL server is running

Session Issues

Ensure cookies are enabled in browser

Check file permissions on server

Page Redirect Issues

Verify session variables are set correctly

Check authentication middleware

Form Submission Errors

Validate all required fields are filled

Check database field requirements

Debug Mode
Enable debug mode by editing includes/config.php:

php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
📝 License
This project is licensed under the MIT License - see the LICENSE file for details.

🤝 Contributing
Fork the repository

Create a feature branch (git checkout -b feature/amazing-feature)

Commit your changes (git commit -m 'Add amazing feature')

Push to the branch (git push origin feature/amazing-feature)

Open a Pull Request

📞 Support
For support and questions:

Create an issue on GitHub

Email: support@shirtmanagement.com

Documentation: Wiki Pages

🔄 Version History
v1.0.0 (2024-03-20)

Initial release

Basic user and admin functionality

Production tracking system

Reporting features

Note: This system is designed for internal use in shirt manufacturing units. Ensure proper security measures are implemented when deploying in production environments.

