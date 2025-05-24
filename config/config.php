<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
require_once __DIR__ . '/database.php';

// Site configuration
define('SITE_NAME', 'Classic Motorcycle Parts PH');
define('SITE_URL', 'http://localhost/motorcycle-parts');
define('ADMIN_EMAIL', 'admin@motorcycleparts.ph');

// Currency
define('CURRENCY', '₱');

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Upload paths
define('UPLOAD_PATH', 'assets/images/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// Helper functions
function formatPrice($price) {
    return CURRENCY . number_format($price, 2);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function logAdminAction($admin_id, $action, $table_name, $record_id, $old_values = null, $new_values = null) {
    global $db;
    
    $query = "INSERT INTO admin_logs (admin_id, action, table_name, record_id, old_values, new_values) 
              VALUES (:admin_id, :action, :table_name, :record_id, :old_values, :new_values)";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':admin_id' => $admin_id,
        ':action' => $action,
        ':table_name' => $table_name,
        ':record_id' => $record_id,
        ':old_values' => $old_values ? json_encode($old_values) : null,
        ':new_values' => $new_values ? json_encode($new_values) : null
    ]);
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Set timezone
date_default_timezone_set('Asia/Manila');
?>
