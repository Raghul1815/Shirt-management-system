<?php
/**
 * Authentication Middleware
 * Provides functions to protect routes and check user authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

/**
 * Check if user has admin privileges
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['admin_id']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user has regular user privileges
 * @return bool True if user is regular user, false otherwise
 */
function isUser() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'user';
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getUserId() {
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    return null;
}

/**
 * Get current admin ID
 * @return int|null Admin ID or null if not admin
 */
function getAdminId() {
    if (isset($_SESSION['admin_id'])) {
        return $_SESSION['admin_id'];
    }
    return null;
}

/**
 * Get current username
 * @return string|null Username or null if not logged in
 */
function getUsername() {
    if (isset($_SESSION['username'])) {
        return $_SESSION['username'];
    }
    return null;
}

/**
 * Get current user role
 * @return string|null Role or null if not logged in
 */
function getRole() {
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    return null;
}

/**
 * Redirect to login page if not authenticated
 * @param string $role Required role (user or admin)
 */
function requireAuth($role = 'user') {
    if (!isLoggedIn()) {
        // Not logged in at all
        if ($role === 'admin') {
            header("Location: ../api/login_admin.php");
        } else {
            header("Location: ../api/login_user.php");
        }
        exit();
    }
    
    // Check if user has the required role
    if ($role === 'admin' && !isAdmin()) {
        // User doesn't have admin privileges
        header("Location: ../public/access_denied.php");
        exit();
    }
    
    if ($role === 'user' && !isUser()) {
        // Admin trying to access user area (might be allowed depending on your logic)
        // For now, redirect to appropriate panel
        if (isAdmin()) {
            header("Location: ../public/admin_panel.php");
        } else {
            header("Location: ../public/access_denied.php");
        }
        exit();
    }
}

/**
 * Redirect to appropriate panel if already logged in
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        if (isAdmin()) {
            header("Location: ../public/admin_panel.php");
        } else {
            header("Location: ../public/user_panel.php");
        }
        exit();
    }
}

/**
 * Logout function
 */
function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to home page
    header("Location: ../public/index.php");
    exit();
}

/**
 * CSRF token generation and validation
 */

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool True if valid, false otherwise
 */
function validateCsrfToken($token) {
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Require CSRF token for POST requests
 */
function requireCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            http_response_code(403);
            die('Invalid CSRF token');
        }
    }
}

/**
 * Check if user can access a specific resource
 * @param int $resourceUserId User ID associated with the resource
 * @return bool True if user can access, false otherwise
 */
function canAccessResource($resourceUserId) {
    // Admins can access all resources
    if (isAdmin()) {
        return true;
    }
    
    // Users can only access their own resources
    if (isUser() && getUserId() == $resourceUserId) {
        return true;
    }
    
    return false;
}

/**
 * Log activity
 * @param string $activity Description of the activity
 * @param int $userId User ID (optional)
 */
function logActivity($activity, $userId = null) {
    global $pdo;
    
    if ($userId === null) {
        $userId = getUserId() ?: getAdminId();
    }
    
    $role = getRole();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, role, activity, ip_address, user_agent) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $role,
            $activity,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch (PDOException $e) {
        // Silently fail logging to not disrupt user experience
        error_log("Failed to log activity: " . $e->getMessage());
    }
}