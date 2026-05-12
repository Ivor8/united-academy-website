<?php
require_once 'admin/includes/config.php';

// Get database connection
$pdo = getDB();

// Get course data
$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($courseId) && empty($slug)) {
    header('Location: online-courses.php');
    exit();
}

// Build query
if (!empty($slug)) {
    $query = "SELECT oc.*, u.first_name, u.last_name
              FROM online_courses oc 
              LEFT JOIN users u ON oc.created_by = u.id 
              WHERE oc.slug = ? AND oc.status = 'published'";
    $params = [$slug];
} else {
    $query = "SELECT oc.*, u.first_name, u.last_name
              FROM online_courses oc 
              LEFT JOIN users u ON oc.created_by = u.id 
              WHERE oc.id = ? AND oc.status = 'published'";
    $params = [$courseId];
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$course = $stmt->fetch();

if (!$course) {
    header('HTTP/1.0 404 Not Found');
    include '404.html';
    exit();
}

// Get related courses
$relatedStmt = $pdo->prepare("
    SELECT oc.id, oc.title, oc.slug, oc.cover_image, oc.category, oc.level, oc.duration, oc.price, oc.currency
    FROM online_courses oc 
    WHERE oc.status = 'published' AND oc.id != ? AND oc.category = ?
    ORDER BY oc.created_at DESC 
    LIMIT 3
");
$relatedStmt->execute([$course['id'], $course['category']]);
$relatedCourses = $relatedStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?php echo htmlspecialchars($course['title']); ?> | <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($course['short_description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($course['meta_keywords'] ?: $course['category'] . ', ' . $course['level']); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($course['instructor_name']); ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?php echo htmlspecialchars($course['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($course['short_description']); ?>">
    <meta property="og:image" content="<?php echo UPLOAD_URL . $course['cover_image']; ?>">
    <meta property="og:url" content="<?php echo SITE_URL; ?>course-single.php?slug=<?php echo $course['slug']; ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($course['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($course['short_description']); ?>">
    <meta name="twitter:image" content="<?php echo UPLOAD_URL . $course['cover_image']; ?>">
    <link rel="canonical" href="<?php echo SITE_URL; ?>course-single.php?slug=<?php echo $course['slug']; ?>">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300..700&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .course-single {
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .course-hero {
            position: relative;
            height: 400px;
            background: linear-gradient(135deg, rgba(30,100,200,0.9), rgba(46,125,50,0.9)),
                        url('<?php echo UPLOAD_URL . $course['cover_image']; ?>');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        
        .course-hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
        }
        
        .course-hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 2rem;
        }
        
        .course-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .course-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .meta-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .meta-badge.category {
            background: rgba(30,100,200,0.1);
            color: white;
        }
        
        .meta-badge.level {
            background: rgba(46,125,50,0.1);
            color: white;
        }
        
        .course-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1E64C8;
        }
        
        .course-content {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .course-content h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 1rem;
        }
        
        .course-content p, .course-content ul, .course-content ol {
            color: #475569;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }
        
        .course-content ul {
            padding-left: 1.5rem;
        }
        
        .course-content li {
            margin-bottom: 0.5rem;
        }
        
        .instructor-info {
            background: var(--light);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
        }
        
        .instructor-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .instructor-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .instructor-details h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .instructor-details p {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .application-form {
            background: linear-gradient(135deg, #1E64C8, #2E7D32);
            color: white;
            padding: 3rem;
            border-radius: 20px;
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .application-form h3 {
            font-size: 1.8rem;
            margin-bottom: 2rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: white;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: 2px solid rgba(255,255,255,0.3);
        }
        
        .btn-apply {
            background: white;
            color: #1E64C8;
            border: 2px solid white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-apply:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }
        
        .related-courses {
            margin-top: 4rem;
        }
        
        .related-courses h3 {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--dark);
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .related-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .related-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .related-content {
            padding: 1.5rem;
        }
        
        .related-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        
        .related-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #64748b;
        }
        
        .related-price {
            font-weight: 600;
            color: #1E64C8;
        }
        
        @media (max-width: 768px) {
            .course-hero-content {
                padding: 1.5rem;
            }
            
            .course-content {
                padding: 2rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
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

        <!-- ========== COURSE HERO ========== -->
        <section class="course-hero">
            <div class="course-hero-overlay"></div>
            <div class="course-hero-content">
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <div class="course-meta">
                    <span class="meta-badge category"><?php echo ucfirst($course['category']); ?></span>
                    <span class="meta-badge level"><?php echo ucfirst($course['level']); ?></span>
                    <?php if ($course['price'] > 0): ?>
                        <span class="course-price"><?php echo number_format($course['price'], 0, ',', ' '); ?> <?php echo htmlspecialchars($course['currency']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ========== COURSE CONTENT ========== -->
        <section class="course-content">
            <div class="container">
                <div class="course-content">
                    <?php if ($course['video_intro_url']): ?>
                        <div style="text-align: center; margin-bottom: 2rem;">
                            <video controls style="width: 100%; max-width: 800px; border-radius: 15px;">
                                <source src="<?php echo htmlspecialchars($course['video_intro_url']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    <?php endif; ?>
                    
                    <h2>Course Overview</h2>
                    <p><?php echo nl2br(htmlspecialchars($course['long_description'])); ?></p>
                    
                    <?php if (!empty($course['requirements'])): ?>
                        <h3>Requirements</h3>
                        <p><?php echo nl2br(htmlspecialchars($course['requirements'])); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($course['objectives'])): ?>
                        <h3>What You'll Learn</h3>
                        <p><?php echo nl2br(htmlspecialchars($course['objectives'])); ?></p>
                    <?php endif; ?>
                    
                    <h2>Curriculum</h2>
                    <div><?php echo $course['curriculum']; ?></div>
                </div>
            </div>
        </section>

        <!-- ========== INSTRUCTOR INFO ========== -->
        <section class="instructor-info">
            <div class="container">
                <div class="instructor-header">
                    <?php if ($course['instructor_image']): ?>
                        <img src="<?php echo UPLOAD_URL . $course['instructor_image']; ?>" alt="<?php echo htmlspecialchars($course['instructor_name']); ?>" class="instructor-image">
                    <?php else: ?>
                        <div class="instructor-image" style="background: var(--gray); display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas fa-user-tie" style="font-size: 2rem;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="instructor-details">
                        <h3><?php echo htmlspecialchars($course['instructor_name']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($course['instructor_bio'])); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== APPLICATION FORM ========== -->
        <section class="application-form">
            <div class="container">
                <h3>Apply for This Course</h3>
                <form method="POST" action="course-application.php" class="form-grid">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth">
                    </div>
                    
                    <div class="form-group">
                        <label for="nationality">Nationality</label>
                        <input type="text" id="nationality" name="nationality">
                    </div>
                    
                    <div class="form-group">
                        <label for="current_education">Current Education</label>
                        <input type="text" id="current_education" name="current_education">
                    </div>
                    
                    <div class="form-group">
                        <label for="work_experience">Work Experience</label>
                        <textarea id="work_experience" name="work_experience" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="motivation">Motivation for Applying</label>
                        <textarea id="motivation" name="motivation" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="how_hear_about">How did you hear about us?</label>
                        <select id="how_hear_about" name="how_hear_about">
                            <option value="">Select an option</option>
                            <option value="social_media">Social Media</option>
                            <option value="website">Website</option>
                            <option value="friend">Friend/Colleague</option>
                            <option value="advertisement">Advertisement</option>
                            <option value="search_engine">Search Engine</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="has_computer" value="1">
                            I have access to a computer and internet
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="needs_financial_aid" value="1">
                            I require financial assistance
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="additional_info">Additional Information</label>
                        <textarea id="additional_info" name="additional_info" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn-apply">
                        <i class="fas fa-paper-plane"></i> Submit Application
                    </button>
                </form>
            </div>
        </section>

        <!-- ========== RELATED COURSES ========== -->
        <?php if (count($relatedCourses) > 0): ?>
        <section class="related-courses">
            <div class="container">
                <h3>Related Courses</h3>
                <div class="related-grid">
                    <?php foreach ($relatedCourses as $related): ?>
                        <div class="related-card">
                            <a href="course-single.php?slug=<?php echo $related['slug']; ?>" style="text-decoration: none; color: inherit;">
                                <?php if ($related['cover_image']): ?>
                                    <img src="<?php echo UPLOAD_URL . $related['cover_image']; ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="related-image">
                                <?php else: ?>
                                    <div class="related-image" style="background: var(--gray); display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas fa-graduation-cap" style="font-size: 2rem;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="related-content">
                                    <h4 class="related-title"><?php echo htmlspecialchars($related['title']); ?></h4>
                                    <div class="related-meta">
                                        <span class="related-price"><?php echo number_format($related['price'], 0, ',', ' '); ?> <?php echo htmlspecialchars($related['currency']); ?></span>
                                        <span><?php echo htmlspecialchars($related['duration']); ?></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

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
                            <li><a href="blog.php" data-i18n="nav_news">Updates</a></li>
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
