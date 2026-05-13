<?php
require_once 'admin/includes/config.php';

// Get database connection
$pdo = getDB();

// Handle filters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT oc.*, 
           (SELECT COUNT(*) FROM course_applications WHERE course_id = oc.id AND status = 'pending') as pending_applications,
           (SELECT COUNT(*) FROM course_enrollments WHERE course_id = oc.id AND status = 'active') as active_enrollments
          FROM online_courses oc 
          WHERE 1=1";

$params = [];

if ($category !== 'all') {
    $query .= " AND oc.category = ?";
    $params[] = $category;
}

if ($search) {
    $query .= " AND (oc.title LIKE ? OR oc.short_description LIKE ? OR oc.long_description LIKE ?)";
    $searchPattern = "%$search%";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
}

$query .= " ORDER BY oc.featured DESC, oc.order_position ASC, oc.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM online_courses oc WHERE oc.status = 'published'";
$countParams = [];

if ($category !== 'all') {
    $countQuery .= " AND oc.category = ?";
    $countParams[] = $category;
}

if ($search) {
    $countQuery .= " AND (oc.title LIKE ? OR oc.short_description LIKE ? OR oc.long_description LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalCourses = $countStmt->fetch()['total'];
$totalPages = ceil($totalCourses / $limit);

// Get featured courses for hero section
$featuredStmt = $pdo->prepare("
    SELECT oc.* 
    FROM online_courses oc 
    WHERE oc.status = 'published' AND oc.featured = 1
    ORDER BY oc.created_at DESC 
    LIMIT 3
");
$featuredStmt->execute();
$featuredCourses = $featuredStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <!-- META TAGS (SEO) -->
    <title>Online Courses | UNITED ACADEMY-UARD – Professional Training Programs</title>
    <meta name="description" content="Explore our comprehensive online courses in healthcare, IT, business, and professional development. Learn at your own pace with expert instructors.">
    <meta name="keywords" content="online courses, vocational training, digital marketing, web development, healthcare training, professional development, UNITED ACADEMY-UARD">
    <meta name="author" content="UNITED ACADEMY-UARD">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Online Courses – UNITED ACADEMY-UARD">
    <meta property="og:description" content="Explore our comprehensive online courses in healthcare, IT, business, and professional development. Learn at your own pace with expert instructors.">
    <meta property="og:image" content="assets/images/logo.jpg">
    <meta property="og:url" content="<?php echo SITE_URL; ?>online-courses.php">
    <link rel="canonical" href="<?php echo SITE_URL; ?>online-courses.php">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300..700&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Online Courses Page Styles */
        .courses-hero {
            background: linear-gradient(135deg, rgba(30,100,200,0.9), rgba(46,125,50,0.9)),
                        url('assets/images/hero1.jpg');
            background-size: cover;
            background-position: center;
            padding: 6rem 2rem;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .courses-hero h1 {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .courses-hero .divider {
            background: rgba(255,255,255,0.2);
            height: 3px;
            width: 80px;
            margin: 0 auto 1.5rem;
        }
        
        .courses-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .courses-filter-section {
            padding: 3rem 0;
            background: var(--light);
        }
        
        .courses-filter-wrapper {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        
        .courses-search {
            flex: 1;
            position: relative;
            min-width: 300px;
        }
        
        .courses-search input {
            width: 100%;
            padding: 1rem 3rem 1rem 45px;
            border: 2px solid #e5e7eb;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .courses-search input:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 10px rgba(30,100,200,0.1);
        }
        
        .courses-categories {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .category-btn {
            padding: 0.75rem 1.5rem;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .category-btn:hover {
            background: var(--blue);
            color: white;
            transform: translateY(-2px);
        }
        
        .category-btn.active {
            background: var(--blue);
            color: white;
            border-color: var(--blue);
        }
        
        .featured-section {
            margin-bottom: 4rem;
        }
        
        .featured-courses {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .featured-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        
        .featured-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .featured-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .featured-content {
            padding: 2rem;
        }
        
        .featured-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, #1E64C8, #2E7D32);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .featured-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        
        .featured-description {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .featured-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        
        .featured-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--blue);
        }
        
        .featured-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--blue);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .featured-cta:hover {
            background: var(--dark);
            transform: translateX(5px);
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .course-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        
        .course-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: var(--light);
        }
        
        .course-content {
            padding: 2rem;
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .course-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
            line-height: 1.3;
        }
        
        .course-meta {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .meta-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .meta-badge.category-health {
            background: rgba(46,125,50,0.1);
            color: #2E7D32;
        }
        
        .meta-badge.category-it {
            background: rgba(30,100,200,0.1);
            color: #1E64C8;
        }
        
        .meta-badge.category-business {
            background: rgba(211,47,47,0.1);
            color: #D32F2F;
        }
        
        .meta-badge.level-beginner {
            background: rgba(46,125,50,0.1);
            color: #2E7D32;
        }
        
        .meta-badge.level-intermediate {
            background: rgba(245,158,11,0.1);
            color: #F59E0B;
        }
        
        .meta-badge.level-advanced {
            background: rgba(239,68,68,0.1);
            color: #DC2626;
        }
        
        .course-description {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .course-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .course-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--blue);
        }
        
        .course-students {
            font-size: 0.85rem;
            color: #64748b;
        }
        
        .course-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--blue), var(--green));
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .course-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30,100,200,0.2);
        }
        
        .whatsapp-float {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #25D366;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(37,211,102,0.3);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .whatsapp-float:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(37,211,102,0.4);
        }
        
        @media (max-width: 768px) {
            .courses-filter-wrapper {
                flex-direction: column;
                align-items: stretch;
            }
            
            .courses-search {
                min-width: auto;
                margin-bottom: 1rem;
            }
            
            .featured-courses {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .courses-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .course-card {
                border-radius: 15px;
            }
            
            .course-content {
                padding: 1.5rem;
            }
            
            .whatsapp-float {
                bottom: 1rem;
                right: 1rem;
                padding: 0.8rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">

        <!-- ========== HEADER ========== -->
        <header class="main-header">
            <div class="container header-flex">
                <div class="logo-area">
                    <img src="assets/images/logo.jpg" alt="UNITED ACADEMY-UARD Logo" class="logo-img" width="50" height="50">
                </div>
                <nav class="main-nav">
                    <ul class="nav-links" id="navLinks">
                        <li><a href="index.php" class="nav-link" data-i18n="nav_home">Home</a></li>
                        <li><a href="about.html" class="nav-link" data-i18n="nav_about">About</a></li>
                        <li><a href="programs.html" class="nav-link" data-i18n="nav_programs">Programs</a></li>
                        <li><a href="online-courses.php" class="nav-link active" data-i18n="nav_online_courses">Online Courses</a></li>
                        <li><a href="testimonials.php" class="nav-link" data-i18n="nav_testimonials">Testimonials</a></li>
                        <li><a href="blog.php" class="nav-link" data-i18n="nav_news">Updates</a></li>
                        <li><a href="contact.html" class="nav-link" data-i18n="nav_contact">Contact</a></li>
                    </ul>
                    <div class="lang-switcher">
                        <button id="lang-en" class="lang-btn">EN</button>
                        <span class="lang-divider">|</span>
                        <button id="lang-fr" class="lang-btn active-lang">FR</button>
                    </div>
                    <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
                </nav>
            </div>
        </header>

        <!-- ========== PAGE HERO ========== -->
        <section class="page-hero courses-hero">
            <div class="page-hero-overlay"></div>
            <div class="container page-hero-content">
                <h1>Online <span class="accent">Courses</span></h1>
                <div class="divider"></div>
                <p>Learn at your own pace with expert instructors and industry-relevant skills</p>
            </div>
        </section>

        <!-- ========== SEARCH & FILTER SECTION ========== -->
        <section class="section courses-filter-section">
            <div class="container">
                <form method="GET" class="courses-filter-wrapper">
                    <div class="courses-search">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="courses-categories">
                        <button type="submit" name="category" value="all" class="category-btn <?php echo $category === 'all' ? 'active' : ''; ?>">All Courses</button>
                        <button type="submit" name="category" value="health" class="category-btn <?php echo $category === 'health' ? 'active' : ''; ?>">Health</button>
                        <button type="submit" name="category" value="it" class="category-btn <?php echo $category === 'it' ? 'active' : ''; ?>">IT</button>
                        <button type="submit" name="category" value="business" class="category-btn <?php echo $category === 'business' ? 'active' : ''; ?>">Business</button>
                        <button type="submit" name="category" value="languages" class="category-btn <?php echo $category === 'languages' ? 'active' : ''; ?>">Languages</button>
                        <button type="submit" name="category" value="professional" class="category-btn <?php echo $category === 'professional' ? 'active' : ''; ?>">Professional</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- ========== FEATURED COURSES ========== -->
        <?php if (count($featuredCourses) > 0): ?>
        <section class="section featured-section">
            <div class="container">
                <div class="section-header fade-up visible">
                    <h2>Featured Courses</h2>
                    <div class="divider"></div>
                </div>
                
                <div class="featured-courses">
                    <?php foreach ($featuredCourses as $course): ?>
                        <div class="featured-card fade-up visible">
                            <?php if ($course['featured']): ?>
                                <div class="featured-badge">
                                    <i class="fas fa-star"></i> Featured
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($course['cover_image']): ?>
                                <img src="<?php echo htmlspecialchars(getUploadUrl($course['cover_image'])); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="featured-image">
                            <?php else: ?>
                                <div class="featured-image" style="display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                    <i class="fas fa-graduation-cap" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="featured-content">
                                <h3 class="featured-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <div class="featured-description">
                                    <?php echo htmlspecialchars($course['short_description']); ?>
                                </div>
                                
                                <div class="featured-meta">
                                    <div class="featured-price">
                                        <?php if ($course['price'] > 0): ?>
                                            <?php echo number_format($course['price'], 0, ',', ' '); ?> XAF
                                        <?php else: ?>
                                            Free
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="course-students">
                                        <i class="fas fa-users"></i> 
                                        <?php echo $course['max_students']; ?> Students
                                    </div>
                                </div>
                                
                                <div class="featured-cta">
                                    <a href="course-single.php?id=<?php echo $course['id']; ?>" class="featured-cta">
                                        View Course <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ========== COURSES GRID ========== -->
        <section class="section">
            <div class="container">
                <div class="section-header fade-up visible">
                    <h2>All Courses</h2>
                    <div class="divider"></div>
                </div>

                <div class="courses-grid">
                    <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card fade-up visible">
                                <?php if ($course['featured']): ?>
                                    <div class="featured-badge">
                                        <i class="fas fa-star"></i> Featured
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($course['cover_image']): ?>
                                    <img src="<?php echo htmlspecialchars(getUploadUrl($course['cover_image'])); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                                <?php else: ?>
                                    <div class="course-image" style="display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                        <i class="fas fa-graduation-cap" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="course-content">
                                    <div class="course-header">
                                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                                        <div class="course-meta">
                                            <span class="meta-badge category-<?php echo $course['category']; ?>"><?php echo ucfirst($course['category']); ?></span>
                                            <span class="meta-badge level-<?php echo $course['level']; ?>"><?php echo ucfirst($course['level']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="course-description">
                                        <?php echo htmlspecialchars($course['short_description']); ?>
                                    </div>
                                    
                                    <div class="course-footer">
                                        <div class="course-price">
                                            <?php if ($course['price'] > 0): ?>
                                                <?php echo number_format($course['price'], 0, ',', ' '); ?> XAF
                                            <?php else: ?>
                                                Free
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="course-students">
                                            <i class="fas fa-users"></i> 
                                            <?php echo $course['max_students']; ?> Max
                                        </div>
                                        
                                        <div class="course-cta">
                                            <a href="course-single.php?id=<?php echo $course['id']; ?>" class="course-cta">
                                                View Details <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-results" style="text-align: center; padding: 4rem 2rem;">
                            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <h3>No courses found</h3>
                            <p>Try adjusting your search or filter criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ========== PAGINATION ========== -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination-container fade-up visible">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn prev">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        
                        if ($start > 1) {
                            echo '<a href="?page=1&category=' . urlencode($category) . '&search=' . urlencode($search) . '" class="pagination-btn">1</a>';
                            if ($start > 2) echo '<span class="pagination-ellipsis">...</span>';
                        }
                        
                        for ($i = $start; $i <= $end; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<a href="?page=' . $i . '&category=' . urlencode($category) . '&search=' . urlencode($search) . '" class="pagination-btn ' . $active . '">' . $i . '</a>';
                        }
                        
                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1) echo '<span class="pagination-ellipsis">...</span>';
                            echo '<a href="?page=' . $totalPages . '&category=' . urlencode($category) . '&search=' . urlencode($search) . '" class="pagination-btn">' . $totalPages . '</a>';
                        }
                        ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>" class="pagination-btn next">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ========== WHATSAPP FLOAT BUTTON ========== -->
        <div class="whatsapp-float" onclick="window.open('https://wa.me/<?php echo str_replace([' ', ''], '+', getSetting('whatsapp_number')); ?>?text=Hello! I am interested in your online courses. Can you provide more information?', '_blank')">
            <i class="fab fa-whatsapp"></i>
            <span>Need Help?</span>
        </div>

        <!-- ========== FOOTER ========== -->
        <footer class="main-footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-col">
                        <img src="assets/images/logo.jpg" alt="UNITED ACADEMY-UARD" class="footer-logo" width="70">
                        <p data-i18n="footer_about">Vocational Training Institute UNITED ACADEMY-UARD – MINEFOP accredited. Excellence, employability, innovation.</p>
                    </div>
                    <div class="footer-col">
                        <h4 data-i18n="quick_links">Quick links</h4>
                        <ul>
                            <li><a href="index.php" data-i18n="nav_home">Home</a></li>
                            <li><a href="about.html" data-i18n="nav_about">About</a></li>
                            <li><a href="programs.html" data-i18n="nav_programs">Programs</a></li>
                            <li><a href="online-courses.php" data-i18n="nav_online_courses">Online Courses</a></li>
                            <li><a href="testimonials.php" data-i18n="nav_testimonials">Testimonials</a></li>
                            <li><a href="blog.php" data="i18n="nav_news">Updates</a></li>
                            <li><a href="contact.html" data-i18n="nav_contact">Contact</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4 data-i18n="footer_contact">Contact</h4>
                        <p><i class="fas fa-map-pin"></i> Yaoundé-Simbock, Montée Mechcam, Roseville Complex (Immeuble Bleu)</p>
                        <p><i class="fas fa-phone"></i> +237 683 05 93 55 / +237 658 72 62 37</p>
                        <p><i class="fas fa-envelope"></i> unitedacademyuard@gmail.com</p>
                    </div>
                    <div class="footer-col">
                        <h4 data-i18n="newsletter">Newsletter</h4>
                        <p data-i18n="newsletter_desc">Subscribe for updates and news.</p>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Your email" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                        <div class="footer-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-x-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2025 UNITED ACADEMY-UARD. <span data-i18n="footer_rights">All rights reserved.</span> | <span data-i18n="footer_approval">MINEFOP Agreement N° 00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC</span></p>
                    <p class="developer">Developed by <a href="https://www.miraedge.tech" target="_blank" rel="noopener">Mira Edge Technologies</a></p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
