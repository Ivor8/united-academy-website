<?php
$noAuthCheck = true;
require_once 'includes/config.php';
require_once 'includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $result = loginUser($email, $password);
    
    if (isset($result['success'])) {
        header('Location: index.php');
        exit();
    } else {
        $error = $result['error'];
    }
}

$pageTitle = 'Login';
$extraCss = '<link rel="stylesheet" href="assets/css/login.css">';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo SITE_NAME; ?> - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card animate-slide-up">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                    <span>UNITED ACADEMY-UARD</span>
                </div>
                <h2>Admin Login</h2>
                <p>Enter your credentials to access the dashboard</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error animate-shake">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required 
                           placeholder="admin@unitedacademy-uard.cm"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password">
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login to Dashboard
                </button>
            </form>
            
            <div class="login-footer">
                <p>Default credentials: admin@unitedacademy-uard.cm / Admin@123</p>
                <p class="demo-note">© 2025 UNITED ACADEMY-UARD. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/login.js"></script>
</body>
</html>