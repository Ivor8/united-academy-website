<?php
require_once 'config.php';

// Login user
function loginUser($email, $password) {
    $pdo = getDB();
    
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role_id, first_name, last_name, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['is_active'] == 0) {
            return ['error' => 'Your account is disabled. Please contact administrator.'];
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['logged_in'] = true;
        
        // Update last login
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
        
        // Log login history
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $logStmt = $pdo->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
        $logStmt->execute([$user['id'], $ip, $userAgent]);
        
        return ['success' => true, 'user' => $user];
    }
    
    return ['error' => 'Invalid email or password'];
}

// Get user permissions
function getUserPermissions($roleId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.permission_slug 
        FROM permissions p
        JOIN role_permissions rp ON p.id = rp.permission_id
        WHERE rp.role_id = ?
    ");
    $stmt->execute([$roleId]);
    $permissions = $stmt->fetchAll();
    
    $userPermissions = [];
    foreach ($permissions as $perm) {
        $userPermissions[] = $perm['permission_slug'];
    }
    
    return $userPermissions;
}

// Check permission
function hasPermission($permissionSlug) {
    if (!isset($_SESSION['role_id'])) return false;
    
    if (!isset($_SESSION['permissions'])) {
        $_SESSION['permissions'] = getUserPermissions($_SESSION['role_id']);
    }
    
    return in_array($permissionSlug, $_SESSION['permissions']);
}

// Get user role name
function getUserRole() {
    if (!isset($_SESSION['role_id'])) return null;
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
    $stmt->execute([$_SESSION['role_id']]);
    $role = $stmt->fetch();
    
    return $role ? $role['role_name'] : null;
}
?>