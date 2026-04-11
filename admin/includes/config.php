<?php
// Database Configuration
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'united_academy');

// Site Configuration
define('SITE_NAME', 'UNITED ACADEMY-UARD');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/';
$baseDir = dirname($scriptPath);
$basePath = rtrim(preg_replace('#/admin(/.*)?$#', '', $baseDir), '/');
if ($basePath === '') {
    $basePath = '/';
}
define('SITE_URL', $protocol . $host . $basePath . (substr($basePath, -1) === '/' ? '' : '/'));
define('ADMIN_URL', SITE_URL . 'admin/');
$appRoot = realpath(dirname(__DIR__, 2));
if ($appRoot === false) {
    $appRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/';
} else {
    $appRoot = rtrim($appRoot, '/\\') . '/';
}
define('BASE_PATH', $appRoot);
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('UPLOAD_URL', SITE_URL . 'uploads/');

define('ERROR_LOG_PATH', BASE_PATH . 'php-error.log');

// Error Reporting (enable for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', ERROR_LOG_PATH);

// Database Connection
function getDB() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch(PDOException $e) {
        debug_log("Database connection FAILED", ['error' => $e->getMessage()]);
        die("Connection failed: " . $e->getMessage());
    }
}

// Debug function for troubleshooting
function debug_log($message, $data = null) {
    $debugFile = defined('ERROR_LOG_PATH') ? ERROR_LOG_PATH : BASE_PATH . 'debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    if ($data !== null) {
        $logMessage .= "\n" . print_r($data, true);
    }
    $logMessage .= "\n" . str_repeat('-', 80) . "\n";
    file_put_contents($debugFile, $logMessage, FILE_APPEND);
}

// Improved upload function with detailed debugging
function uploadFile($file, $type = 'blog') {
    debug_log("=== STARTING FILE UPLOAD ===");
    debug_log("Upload parameters", ['type' => $type, 'file_info' => [
        'name' => $file['name'] ?? 'not set',
        'error' => $file['error'] ?? 'not set',
        'size' => $file['size'] ?? 'not set',
        'tmp_name' => $file['tmp_name'] ?? 'not set'
    ]]);
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = "File upload error: ";
        if (!isset($file)) {
            $errorMsg .= "No file array provided";
        } else {
            switch($file['error']) {
                case UPLOAD_ERR_NO_FILE:
                    $errorMsg .= "No file was uploaded";
                    break;
                case UPLOAD_ERR_INI_SIZE:
                    $errorMsg .= "File exceeds upload_max_filesize directive in php.ini";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMsg .= "File exceeds MAX_FILE_SIZE directive in HTML form";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorMsg .= "File was only partially uploaded";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorMsg .= "Missing temporary folder";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorMsg .= "Failed to write file to disk";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorMsg .= "File upload stopped by extension";
                    break;
                default:
                    $errorMsg .= "Unknown error code: " . $file['error'];
            }
        }
        debug_log($errorMsg);
        return false;
    }
    
    // Create base upload directory
    $baseDir = BASE_PATH . 'uploads/';
    debug_log("Checking base directory", ['path' => $baseDir, 'exists' => file_exists($baseDir)]);
    
    if (!file_exists($baseDir)) {
        debug_log("Base directory does not exist, attempting to create");
        if (!mkdir($baseDir, 0777, true)) {
            debug_log("FAILED to create base directory. Check parent directory permissions.");
            return false;
        }
        debug_log("Base directory created successfully");
    }
    
    // Check if base directory is writable
    if (!is_writable($baseDir)) {
        debug_log("Base directory is not writable", ['permissions' => substr(sprintf('%o', fileperms($baseDir)), -4)]);
        return false;
    }
    
    $targetDir = $baseDir . $type . '/';
    debug_log("Checking type directory", ['path' => $targetDir, 'exists' => file_exists($targetDir)]);
    
    if (!file_exists($targetDir)) {
        debug_log("Type directory does not exist, attempting to create");
        if (!mkdir($targetDir, 0777, true)) {
            debug_log("FAILED to create type directory");
            return false;
        }
        debug_log("Type directory created successfully");
    }
    
    // Check if type directory is writable
    if (!is_writable($targetDir)) {
        debug_log("Type directory is not writable", ['permissions' => substr(sprintf('%o', fileperms($targetDir)), -4)]);
        return false;
    }
    
    // Generate unique filename
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = time() . '_' . uniqid() . '.' . $fileExt;
    $targetFile = $targetDir . $fileName;
    
    debug_log("File info", [
        'original_name' => $file['name'],
        'extension' => $fileExt,
        'new_name' => $fileName,
        'size_bytes' => $file['size'],
        'size_mb' => round($file['size'] / 1048576, 2),
        'tmp_name' => $file['tmp_name'],
        'target_path' => $targetFile
    ]);
    
    // Allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'webm', 'pdf'];
    
    if (!in_array($fileExt, $allowedTypes)) {
        $error = "File type '$fileExt' not allowed. Allowed: " . implode(', ', $allowedTypes);
        debug_log($error);
        return false;
    }
    
    // Check file size (max 20MB for images/PDF, 50MB for videos)
    $maxSize = in_array($fileExt, ['mp4', 'mov', 'avi', 'webm']) ? 52428800 : 20971520;
    if ($file['size'] > $maxSize) {
        $error = "File too large. Size: " . round($file['size']/1048576, 2) . "MB, Max: " . ($maxSize/1048576) . "MB";
        debug_log($error);
        return false;
    }
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $url = UPLOAD_URL . $type . '/' . $fileName;
        debug_log("SUCCESS: File uploaded", ['path' => $targetFile, 'url' => $url]);
        
        // Verify file was actually created
        if (file_exists($targetFile)) {
            debug_log("File verification passed", ['size' => filesize($targetFile)]);
        } else {
            debug_log("WARNING: File not found after move_uploaded_file claimed success");
        }
        
        return $url;
    }
    
    $error = "Failed to move uploaded file. Check directory permissions.";
    debug_log($error, [
        'source' => $file['tmp_name'],
        'destination' => $targetFile,
        'source_exists' => file_exists($file['tmp_name']),
        'destination_dir_writable' => is_writable($targetDir)
    ]);
    
    return false;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        debug_log("Unauthorized access attempt to " . $_SERVER['REQUEST_URI']);
        header('Location: ' . ADMIN_URL . 'login.php');
        exit();
    }
}

// Sanitize input
function sanitize($input) {
    if ($input === null) return '';
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Format date
function formatDate($date) {
    if (empty($date)) return 'N/A';
    return date('M d, Y', strtotime($date));
}

// Format datetime
function formatDateTime($date) {
    if (empty($date)) return 'N/A';
    return date('M d, Y H:i', strtotime($date));
}

// Truncate text
function truncate($text, $limit = 100) {
    if (empty($text)) return '';
    if (strlen($text) > $limit) {
        return substr($text, 0, $limit) . '...';
    }
    return $text;
}

// Normalize saved upload URLs and relative upload paths for current environment
function getUploadUrl($path) {
    if (empty($path)) {
        return '';
    }
    $path = trim($path);
    
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        $parsed = parse_url($path);
        if ($parsed && !empty($parsed['path'])) {
            $lowerPath = strtolower($parsed['path']);
            $uploadsPos = strpos($lowerPath, '/uploads/');
            if ($uploadsPos !== false) {
                $relative = substr($parsed['path'], $uploadsPos + strlen('/uploads/'));
                return rtrim(UPLOAD_URL, '/') . '/' . ltrim($relative, '/');
            }
        }
        return $path;
    }

    if (strpos($path, '/uploads/') === 0) {
        return rtrim(SITE_URL, '/') . $path;
    }

    if (strpos($path, 'uploads/') === 0) {
        return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
    }

    return rtrim(UPLOAD_URL, '/') . '/' . ltrim($path, '/');
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Log user activity
function logActivity($action, $tableName = null, $recordId = null, $oldData = null, $newData = null) {
    if (!isLoggedIn()) return;
    
    try {
        $pdo = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $tableName,
            $recordId,
            $oldData ? json_encode($oldData) : null,
            $newData ? json_encode($newData) : null,
            $ip,
            $userAgent
        ]);
        
        debug_log("Activity logged", ['action' => $action, 'table' => $tableName]);
    } catch (Exception $e) {
        debug_log("Failed to log activity", ['error' => $e->getMessage()]);
    }
}

// Create upload directories on script load
function initializeUploadDirectories() {
    $directories = [
        UPLOAD_PATH,
        UPLOAD_PATH . 'blog/',
        UPLOAD_PATH . 'testimonials/',
        UPLOAD_PATH . 'users/'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            debug_log("Creating directory", ['path' => $dir]);
            mkdir($dir, 0777, true);
        }
    }
}

// Initialize directories
initializeUploadDirectories();

// Debug initialization
debug_log("=== CONFIG.PHP INITIALIZED ===");
debug_log("Server info", [
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'upload_path' => UPLOAD_PATH,
    'upload_url' => UPLOAD_URL,
    'php_version' => PHP_VERSION,
    'session_id' => session_id()
]);
?>