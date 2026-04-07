<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

if (!isset($noAuthCheck) || !$noAuthCheck) {
    requireLogin();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin';
$userRole = getUserRole();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME . ' Admin' : SITE_NAME . ' Admin Dashboard'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>assets/css/admin-style.css?v=1.1">
    <?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="logo-area">
                    <i class="fas fa-graduation-cap"></i>
                    <span>UARD Admin</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <?php if (hasPermission('view_blog')): ?>
                <div class="nav-group">
                    <div class="nav-group-header">
                        <i class="fas fa-blog"></i>
                        <span>Blog Management</span>
                        <i class="fas fa-chevron-down group-toggle"></i>
                    </div>
                    <div class="nav-group-items">
                        <a href="blog/index.php" class="nav-subitem <?php echo strpos($currentPage, 'blog') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i>
                            <span>All Posts</span>
                        </a>
                        <?php if (hasPermission('create_blog')): ?>
                        <a href="blog/create.php" class="nav-subitem">
                            <i class="fas fa-plus"></i>
                            <span>Add New Post</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (hasPermission('view_testimonials')): ?>
                <div class="nav-group">
                    <div class="nav-group-header">
                        <i class="fas fa-star"></i>
                        <span>Testimonials</span>
                        <i class="fas fa-chevron-down group-toggle"></i>
                    </div>
                    <div class="nav-group-items">
                        <a href="testimonials/index.php" class="nav-subitem <?php echo strpos($currentPage, 'testimonials') !== false ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i>
                            <span>All Testimonials</span>
                        </a>
                        <?php if (hasPermission('create_testimonials')): ?>
                        <a href="testimonials/create.php" class="nav-subitem">
                            <i class="fas fa-plus"></i>
                            <span>Add New</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <a href="profile.php" class="nav-item <?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
                
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search..." id="globalSearch">
                </div>
                <div class="header-user">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($fullName); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($userRole); ?></span>
                    </div>
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>
            
            <div class="admin-content">