<?php
/**
 * Helpers - Utility Functions
 * 
 * A collection of helper functions for the Shirt Management System
 * Provides common functionality used across the application
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Securely sanitize input data
 * 
 * @param mixed $data The input data to sanitize
 * @param string $filter_type The type of sanitization (string, email, int, float)
 * @return mixed Sanitized data
 */
function clean_input($data, $filter_type = 'string') {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    
    $data = trim($data);
    
    switch ($filter_type) {
        case 'email':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            break;
        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            break;
        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            break;
        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            break;
        case 'string':
        default:
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            break;
    }
    
    return $data;
}

/**
 * Debug and die - pretty print variables and halt execution
 * 
 * @param mixed $data Variable to debug
 * @param bool $return Return output instead of printing
 * @return string|null Debug output if $return is true
 */
function dd($data, $return = false) {
    $output = "<pre style='background: #f4f4f4; border: 1px solid #ddd; padding: 10px; margin: 10px; border-radius: 4px;'>";
    
    if (is_array($data) || is_object($data)) {
        $output .= print_r($data, true);
    } else {
        $output .= var_export($data, true);
    }
    
    $output .= "</pre>";
    
    if ($return) {
        return $output;
    }
    
    echo $output;
    die();
}

/**
 * Debug without dying - pretty print variables
 * 
 * @param mixed $data Variable to debug
 * @param bool $return Return output instead of printing
 * @return string|null Debug output if $return is true
 */
function debug($data, $return = false) {
    $output = dd($data, true);
    
    if ($return) {
        return $output;
    }
    
    echo $output;
}

/**
 * Redirect to specified URL
 * 
 * @param string $url URL to redirect to
 * @param int $status_code HTTP status code for redirect
 */
function redirect($url, $status_code = 302) {
    if (!headers_sent()) {
        header("Location: $url", true, $status_code);
    } else {
        echo "<script>window.location.href='$url';</script>";
    }
    exit();
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged-in user is admin
 * 
 * @return bool True if user is admin
 */
function is_admin() {
    return (is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

/**
 * Check if logged-in user is regular user
 * 
 * @return bool True if user is regular user
 */
function is_user() {
    return (is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'user');
}

/**
 * Set flash message in session
 * 
 * @param string $key Message key/type (success, error, warning, info)
 * @param string $message Message content
 */
function set_flash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

/**
 * Get and remove flash message from session
 * 
 * @param string $key Message key/type
 * @return string|null Message content or null if not exists
 */
function get_flash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

/**
 * Display flash messages with styling
 * 
 * @param string $key Specific message key to display, or all if null
 */
function display_flash($key = null) {
    if ($key) {
        $message = get_flash($key);
        if ($message) {
            echo "<div class='alert alert-$key'>$message</div>";
        }
    } else {
        foreach (['success', 'error', 'warning', 'info'] as $type) {
            $message = get_flash($type);
            if ($message) {
                echo "<div class='alert alert-$type'>$message</div>";
            }
        }
    }
}

/**
 * Format datetime for display
 * 
 * @param string $datetime DateTime string
 * @param string $format PHP date format
 * @return string Formatted date
 */
function format_datetime($datetime, $format = 'M j, Y g:i A') {
    if (empty($datetime) || $datetime == '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($datetime));
}

/**
 * Format date only for display
 * 
 * @param string $date Date string
 * @param string $format PHP date format
 * @return string Formatted date
 */
function format_date($date, $format = 'M j, Y') {
    if (empty($date) || $date == '0000-00-00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validate_csrf_token($token) {
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * 
 * @return string|null Username or null if not logged in
 */
function get_username() {
    return $_SESSION['username'] ?? null;
}

/**
 * Get current user role
 * 
 * @return string|null User role or null if not logged in
 */
function get_user_role() {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if current user can access resource
 * 
 * @param int $resource_user_id User ID associated with the resource
 * @return bool True if user can access the resource
 */
function can_access_resource($resource_user_id) {
    // Admins can access all resources
    if (is_admin()) {
        return true;
    }
    
    // Users can only access their own resources
    if (is_user() && get_user_id() == $resource_user_id) {
        return true;
    }
    
    return false;
}

/**
 * Generate random string
 * 
 * @param int $length Length of random string
 * @return string Random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool True if valid email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 * 
 * @param string $url URL to validate
 * @return bool True if valid URL
 */
function is_valid_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Get client IP address
 * 
 * @return string Client IP address
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Log activity to database
 * 
 * @param string $activity Activity description
 * @param int|null $user_id User ID (optional, defaults to current user)
 * @return bool True if logged successfully
 */
function log_activity($activity, $user_id = null) {
    global $pdo;
    
    if ($user_id === null) {
        $user_id = get_user_id();
    }
    
    $role = get_user_role();
    $ip_address = get_client_ip();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, role, activity, ip_address, user_agent) 
                              VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $role, $activity, $ip_address, $user_agent]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get file extension from filename
 * 
 * @param string $filename Filename
 * @return string File extension
 */
function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file is an image
 * 
 * @param string $filename Filename
 * @return bool True if file is an image
 */
function is_image_file($filename) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    $extension = get_file_extension($filename);
    return in_array($extension, $allowed_extensions);
}

/**
 * Format file size in human readable format
 * 
 * @param int $bytes File size in bytes
 * @param int $precision Number of decimal places
 * @return string Formatted file size
 */
function format_file_size($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Generate pagination data
 * 
 * @param int $total_items Total number of items
 * @param int $current_page Current page number
 * @param int $items_per_page Number of items per page
 * @return array Pagination data
 */
function generate_pagination($total_items, $current_page = 1, $items_per_page = 10) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    
    $start_index = ($current_page - 1) * $items_per_page;
    $end_index = min($start_index + $items_per_page - 1, $total_items - 1);
    
    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'start_index' => $start_index,
        'end_index' => $end_index,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Escape string for use in JavaScript
 * 
 * @param string $string String to escape
 * @return string Escaped string
 */
function js_escape($string) {
    return addslashes(htmlspecialchars($string, ENT_QUOTES, 'UTF-8'));
}

/**
 * Get current URL
 * 
 * @param bool $with_query_string Include query string
 * @return string Current URL
 */
function get_current_url($with_query_string = true) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    
    $url = "{$protocol}://{$host}{$uri}";
    
    if (!$with_query_string) {
        $url = strtok($url, '?');
    }
    
    return $url;
}

/**
 * Truncate text with ellipsis
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $ellipsis Ellipsis character(s)
 * @return string Truncated text
 */
function truncate_text($text, $length = 100, $ellipsis = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $ellipsis;
}

// CSS and HTML helper functions
/**
 * Generate alert HTML
 * 
 * @param string $message Alert message
 * @param string $type Alert type (success, danger, warning, info)
 * @param bool $dismissible Make alert dismissible
 * @return string HTML alert
 */
function alert($message, $type = 'info', $dismissible = true) {
    $class = "alert alert-$type";
    if ($dismissible) {
        $class .= ' alert-dismissible fade show';
    }
    
    $html = "<div class='$class' role='alert'>";
    $html .= $message;
    if ($dismissible) {
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    }
    $html .= '</div>';
    
    return $html;
}

/**
 * Generate badge HTML
 * 
 * @param string $text Badge text
 * @param string $type Badge type (primary, secondary, success, danger, warning, info, light, dark)
 * @return string HTML badge
 */
function badge($text, $type = 'primary') {
    return "<span class='badge bg-$type'>$text</span>";
}

/**
 * Check if current page is active
 * 
 * @param string $page_name Page name to check
 * @return string 'active' if current page, empty string otherwise
 */
function is_active_page($page_name) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page === $page_name) ? 'active' : '';
}