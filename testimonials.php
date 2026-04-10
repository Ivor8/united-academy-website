<?php
require_once 'admin/includes/config.php';

// Get database connection
$pdo = getDB();

// Get approved testimonials from database view
$stmt = $pdo->prepare("SELECT * FROM vw_approved_testimonials ORDER BY featured DESC, created_at DESC");
$stmt->execute();
$testimonials = $stmt->fetchAll();

// Separate video and image testimonials
$videoTestimonials = array_filter($testimonials, function($t) {
    return $t['media_type'] === 'video' && $t['media_url'];
});

$imageTestimonials = array_filter($testimonials, function($t) {
    return $t['media_type'] === 'image' || !$t['media_url'];
});

// REMOVED: truncate() function is already defined in config.php
// Just use it directly - no need to redeclare
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials | UNITED ACADEMY-UARD – Student Success Stories</title>
    <meta name="description" content="Read inspiring success stories from UNITED ACADEMY-UARD graduates. Real experiences from students in health, IT, and management programs. Watch video testimonials.">
    <meta name="keywords" content="UNITED ACADEMY-UARD testimonials, student reviews, success stories, vocational training graduates Cameroon, video testimonials">
    <meta name="author" content="UNITED ACADEMY-UARD">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Student Testimonials – UNITED ACADEMY-UARD">
    <meta property="og:description" content="Hear from our graduates about their journey and success at UNITED ACADEMY-UARD.">
    <meta property="og:image" content="assets/images/logo.jpg">
    <meta property="og:url" content="https://www.unitedacademy-uard.cm/testimonials">
    <link rel="canonical" href="https://www.unitedacademy-uard.cm/testimonials">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300..700&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Testimonials Redesign CSS -->
    <link rel="stylesheet" href="assets/css/testimonials-redesign.css">
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
                        <li><a href="about.php" class="nav-link" data-i18n="nav_about">About</a></li>
                        <li><a href="programs.php" class="nav-link" data-i18n="nav_programs">Programs</a></li>
                        <li><a href="testimonials.php" class="nav-link active" data-i18n="nav_testimonials">Testimonials</a></li>
                        <li><a href="blog.php" class="nav-link" data-i18n="nav_news">Updates</a></li>
                        <li><a href="contact.php" class="nav-link" data-i18n="nav_contact">Contact</a></li>
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

        <!-- ========== HERO SECTION ========== -->
        <section class="hero-section">
            <div class="container hero-content">
                <h1>Student <span class="highlight">Testimonials</span></h1>
                <div class="hero-divider"></div>
                <p class="hero-subtitle">Real stories from our graduates</p>
            </div>
        </section>

        <!-- ========== VIDEO TESTIMONIALS SECTION ========== -->
        <?php if (count($videoTestimonials) > 0): ?>
        <section class="testimonials-section">
            <div class="container">
                <div class="section-header">
                    <h2>Video <span class="highlight">Testimonials</span></h2>
                    <div class="section-divider"></div>
                    <p>Watch our students share their experiences</p>
                </div>
                <div class="testimonials-grid">
                    <?php foreach ($videoTestimonials as $testimonial): ?>
                        <div class="testimonial-card video-card-item">
                            <div class="testimonial-card-inner">
                                <div class="quote-icon">
                                    <i class="fas fa-quote-left"></i>
                                </div>
                                <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['testimonial_text']); ?>"</p>
                                <div class="testimonial-author">
                                    <div class="author-avatar">
                                        <?php if ($testimonial['student_avatar']): ?>
                                            <img src="<?php echo htmlspecialchars($testimonial['student_avatar']); ?>" alt="<?php echo htmlspecialchars($testimonial['student_name']); ?>">
                                        <?php else: ?>
                                            <i class="fas fa-user-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="author-details">
                                        <h4><?php echo htmlspecialchars($testimonial['student_name']); ?></h4>
                                        <p class="program"><?php echo htmlspecialchars($testimonial['student_program'] ?: 'Graduate'); ?></p>
                                        <?php if ($testimonial['graduation_year']): ?>
                                            <p class="graduation-year">Class of <?php echo $testimonial['graduation_year']; ?></p>
                                        <?php endif; ?>
                                        <?php if ($testimonial['current_position']): ?>
                                            <p class="position"><?php echo htmlspecialchars($testimonial['current_position']); ?><?php if ($testimonial['company_name']): ?> at <?php echo htmlspecialchars($testimonial['company_name']); endif; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="testimonial-footer">
                                    <div class="rating">
                                        <?php 
                                        $rating = (int)$testimonial['rating'];
                                        for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($testimonial['media_url']); ?>" class="video-play-link" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-play-circle"></i> Watch Video
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ========== STATS SECTION ========== -->
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($testimonials); ?>+</div>
                        <div class="stat-label">Happy Graduates</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Employment Rate</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Partner Clinics</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== WRITTEN TESTIMONIALS SECTION ========== -->
        <?php if (count($imageTestimonials) > 0): ?>
        <section class="testimonials-section written-section">
            <div class="container">
                <div class="section-header">
                    <h2>Success <span class="highlight">Stories</span></h2>
                    <div class="section-divider"></div>
                    <p>What our graduates say about us</p>
                </div>
                <div class="testimonials-grid">
                    <?php foreach ($imageTestimonials as $testimonial): ?>
                        <div class="testimonial-card">
                            <div class="testimonial-card-inner">
                                <div class="quote-icon">
                                    <i class="fas fa-quote-left"></i>
                                </div>
                                <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['testimonial_text']); ?>"</p>
                                <div class="testimonial-author">
                                    <div class="author-avatar">
                                        <?php if ($testimonial['student_avatar']): ?>
                                            <img src="<?php echo htmlspecialchars($testimonial['student_avatar']); ?>" alt="<?php echo htmlspecialchars($testimonial['student_name']); ?>">
                                        <?php else: ?>
                                            <i class="fas fa-user-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="author-details">
                                        <h4><?php echo htmlspecialchars($testimonial['student_name']); ?></h4>
                                        <p class="program"><?php echo htmlspecialchars($testimonial['student_program'] ?: 'Graduate'); ?></p>
                                        <?php if ($testimonial['graduation_year']): ?>
                                            <p class="graduation-year">Class of <?php echo $testimonial['graduation_year']; ?></p>
                                        <?php endif; ?>
                                        <?php if ($testimonial['current_position']): ?>
                                            <p class="position"><?php echo htmlspecialchars($testimonial['current_position']); ?><?php if ($testimonial['company_name']): ?> at <?php echo htmlspecialchars($testimonial['company_name']); endif; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="testimonial-footer">
                                    <div class="rating">
                                        <?php 
                                        $rating = (int)$testimonial['rating'];
                                        for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if ($testimonial['featured']): ?>
                                        <span class="featured-badge"><i class="fas fa-trophy"></i> Featured</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ========== CTA SECTION ========== -->
        <section class="cta-section">
            <div class="container cta-content">
                <h2>Ready to write your own <span class="highlight">success story?</span></h2>
                <p>Join UNITED ACADEMY-UARD and become part of our growing family</p>
                <a href="contact.php" class="cta-button">Enroll Now <i class="fas fa-arrow-right"></i></a>
            </div>
        </section>

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
                            <li><a href="about.php" data-i18n="nav_about">About</a></li>
                            <li><a href="programs.php" data-i18n="nav_programs">Programs</a></li>
                            <li><a href="testimonials.php" data-i18n="nav_testimonials">Testimonials</a></li>
                            <li><a href="contact.php" data-i18n="nav_contact">Contact</a></li>
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
    <!-- Testimonials JS -->
    <script src="assets/js/testimonials-redesign.js"></script>
</body>
</html>