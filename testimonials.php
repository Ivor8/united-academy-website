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

// Get featured testimonial
$featuredTestimonial = null;
foreach ($testimonials as $testimonial) {
    if ($testimonial['featured']) {
        $featuredTestimonial = $testimonial;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- META TAGS (SEO) -->
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
    <!-- Testimonials CSS -->
    <link rel="stylesheet" href="assets/css/testimonials.css">
</head>
<body>
    <div class="page-wrapper">

        <!-- ========== HEADER ========== -->
        <header class="main-header">
            <div class="container header-flex">
                <div class="logo-area">
                    <img src="assets/images/logo.jpg" alt="UNITED ACADEMY-UARD Logo" class="logo-img" width="50" height="50">
                    <div class="logo-text-wrapper">
                        <span class="logo-text">UNITED ACADEMY-UARD</span>
                        <span class="logo-tagline">Vocational Training Institute</span>
                    </div>
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

        <!-- ========== PAGE HERO ========== -->
        <section class="page-hero testimonials-hero">
            <div class="page-hero-overlay"></div>
            <div class="container page-hero-content">
                <h1 data-i18n="testimonials_page_title">Student <span class="accent">Testimonials</span></h1>
                <div class="divider"></div>
                <p data-i18n="testimonials_page_sub">Real stories from our graduates</p>
            </div>
        </section>

        <!-- ========== VIDEO TESTIMONIALS SECTION ========== -->
        <?php if (count($videoTestimonials) > 0): ?>
        <section class="section video-testimonials-section">
            <div class="container">
                <div class="section-header fade-up">
                    <h2 data-i18n="video_testimonials_title">Video Testimonials</h2>
                    <div class="divider"></div>
                    <p data-i18n="video_testimonials_sub">Watch our students share their experiences</p>
                </div>
                <div class="video-testimonials-grid">
                    <?php foreach (array_slice($videoTestimonials, 0, 4) as $testimonial): ?>
                        <div class="video-card fade-up">
                            <div class="video-wrapper">
                                <video class="video-thumb" poster="<?php echo htmlspecialchars($testimonial['video_poster'] ?: 'assets/images/default-video-thumb.jpg'); ?>" preload="metadata">
                                    <source src="<?php echo htmlspecialchars($testimonial['media_url']); ?>" type="video/mp4">
                                    Your browser does not support video tag.
                                </video>
                                <div class="video-overlay">
                                    <button class="play-video-btn" 
                                            data-video-src="<?php echo htmlspecialchars($testimonial['media_url']); ?>" 
                                            data-video-poster="<?php echo htmlspecialchars($testimonial['video_poster'] ?: 'assets/images/default-video-thumb.jpg'); ?>" 
                                            data-video-title="<?php echo htmlspecialchars($testimonial['student_name']); ?> - <?php echo htmlspecialchars($testimonial['student_program'] ?: 'Graduate'); ?>" 
                                            data-video-desc="<?php echo htmlspecialchars(truncate($testimonial['testimonial_text'], 100)); ?>">
                                        <i class="fas fa-play-circle"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="video-info">
                                <h3><?php echo htmlspecialchars($testimonial['student_name']); ?> - <?php echo htmlspecialchars($testimonial['student_program'] ?: 'Graduate'); ?></h3>
                                <p>"<?php echo htmlspecialchars(truncate($testimonial['testimonial_text'], 80)); ?>"</p>
                                <span class="video-duration">
                                    <i class="fas fa-star"></i> 
                                    <?php echo str_repeat('★', $testimonial['rating']); ?><?php echo str_repeat('☆', 5 - $testimonial['rating']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ========== VIDEO MODAL ========== -->
        <div id="videoModal" class="video-modal">
            <div class="video-modal-content">
                <span class="close-modal">&times;</span>
                <div class="video-container">
                    <video id="modalVideoPlayer" width="100%" controls>
                        <source id="modalVideoSource" src="" type="video/mp4">
                        Your browser does not support video tag.
                    </video>
                </div>
                <div class="video-modal-info" id="videoModalInfo">
                    <h3 id="modalVideoTitle"></h3>
                    <p id="modalVideoDesc"></p>
                </div>
            </div>
        </div>

        <!-- ========== FEATURED TESTIMONIAL (QUOTE) ========== -->
        <?php if ($featuredTestimonial): ?>
        <section class="section featured-testimonial-section">
            <div class="container">
                <div class="featured-testimonial fade-up">
                    <div class="featured-quote">
                        <i class="fas fa-quote-left quote-icon"></i>
                        <p class="featured-text"><?php echo htmlspecialchars($featuredTestimonial['testimonial_text']); ?></p>
                        <div class="featured-author">
                            <?php if ($featuredTestimonial['student_avatar']): ?>
                                <img src="<?php echo htmlspecialchars($featuredTestimonial['student_avatar']); ?>" alt="Student" class="author-image">
                            <?php else: ?>
                                <div class="author-image" style="background: var(--light); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user" style="font-size: 2rem; color: var(--gray);"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p><strong><?php echo htmlspecialchars($featuredTestimonial['student_name']); ?></strong></p>
                                <p><?php echo htmlspecialchars($featuredTestimonial['student_program'] ?: 'Graduate'); ?><?php if ($featuredTestimonial['graduation_year']) echo ', Class of ' . $featuredTestimonial['graduation_year']; ?></p>
                                <?php if ($featuredTestimonial['current_position']): ?>
                                    <p><?php echo htmlspecialchars($featuredTestimonial['current_position']); ?><?php if ($featuredTestimonial['company_name']) echo ' at ' . htmlspecialchars($featuredTestimonial['company_name']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($featuredTestimonial['media_type'] === 'video' && $featuredTestimonial['media_url']): ?>
                        <div class="featured-video">
                            <div class="video-wrapper">
                                <video class="video-thumb" poster="<?php echo htmlspecialchars($featuredTestimonial['video_poster'] ?: 'assets/images/default-video-thumb.jpg'); ?>" preload="metadata">
                                    <source src="<?php echo htmlspecialchars($featuredTestimonial['media_url']); ?>" type="video/mp4">
                                    Your browser does not support video tag.
                                </video>
                                <div class="video-overlay">
                                    <button class="video-play-btn" 
                                            data-video-src="<?php echo htmlspecialchars($featuredTestimonial['media_url']); ?>" 
                                            data-video-poster="<?php echo htmlspecialchars($featuredTestimonial['video_poster'] ?: 'assets/images/default-video-thumb.jpg'); ?>" 
                                            data-video-title="Featured Testimonial" 
                                            data-video-desc="<?php echo htmlspecialchars($featuredTestimonial['student_name']); ?>'s full story">
                                        <i class="fas fa-play-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ========== STATS SECTION ========== -->
        <section class="section stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item fade-up">
                        <span class="stat-number">100%</span>
                        <span class="stat-label" data-i18n="stat_success">Success rate</span>
                    </div>
                    <div class="stat-item fade-up">
                        <span class="stat-number"><?php echo count($testimonials); ?>+</span>
                        <span class="stat-label" data-i18n="stat_graduates">Graduates</span>
                    </div>
                    <div class="stat-item fade-up">
                        <span class="stat-number">98%</span>
                        <span class="stat-label" data-i18n="stat_employment">Employment rate</span>
                    </div>
                    <div class="stat-item fade-up">
                        <span class="stat-number">15+</span>
                        <span class="stat-label" data-i18n="stat_partners">Partner clinics</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== ALL TESTIMONIALS SECTION ========== -->
        <?php if (count($imageTestimonials) > 0): ?>
        <section class="section testimonials-grid-section">
            <div class="container">
                <div class="section-header fade-up">
                    <h2>Student Success Stories</h2>
                    <div class="divider"></div>
                </div>
                <div class="testimonials-grid">
                    <?php foreach ($imageTestimonials as $testimonial): ?>
                        <div class="testimonial-card fade-up">
                            <div class="testimonial-header">
                                <?php if ($testimonial['student_avatar']): ?>
                                    <img src="<?php echo htmlspecialchars($testimonial['student_avatar']); ?>" alt="<?php echo htmlspecialchars($testimonial['student_name']); ?>" class="student-avatar">
                                <?php else: ?>
                                    <div class="student-avatar" style="background: var(--light); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user" style="color: var(--gray);"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="student-info">
                                    <h4><?php echo htmlspecialchars($testimonial['student_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($testimonial['student_program'] ?: 'Graduate'); ?><?php if ($testimonial['graduation_year']) echo ', Class of ' . $testimonial['graduation_year']; ?></p>
                                    <?php if ($testimonial['current_position']): ?>
                                        <p class="current-position"><?php echo htmlspecialchars($testimonial['current_position']); ?><?php if ($testimonial['company_name']) echo ' at ' . htmlspecialchars($testimonial['company_name']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="testimonial-content">
                                <i class="fas fa-quote-left quote-icon"></i>
                                <p><?php echo htmlspecialchars($testimonial['testimonial_text']); ?></p>
                            </div>
                            <div class="testimonial-footer">
                                <div class="rating">
                                    <?php echo str_repeat('★', $testimonial['rating']); ?><?php echo str_repeat('☆', 5 - $testimonial['rating']); ?>
                                </div>
                                <?php if ($testimonial['featured']): ?>
                                    <span class="featured-badge"><i class="fas fa-star"></i> Featured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ========== CTA SECTION ========== -->
        <section class="section cta-section">
            <div class="container cta-content">
                <h2 data-i18n="cta_testimonials">Ready to write your own success story?</h2>
                <p data-i18n="cta_testimonials_sub">Join UNITED ACADEMY-UARD and become part of our growing family.</p>
                <a href="contact.php" class="btn btn-primary" data-i18n="cta_contact">Enroll now</a>
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
    <script>
        // Video modal functionality
        const videoModal = document.getElementById('videoModal');
        const modalVideoPlayer = document.getElementById('modalVideoPlayer');
        const modalVideoSource = document.getElementById('modalVideoSource');
        const modalVideoTitle = document.getElementById('modalVideoTitle');
        const modalVideoDesc = document.getElementById('modalVideoDesc');

        // Play video buttons
        document.querySelectorAll('.play-video-btn, .video-play-btn').forEach(button => {
            button.addEventListener('click', function() {
                const videoSrc = this.dataset.videoSrc;
                const videoPoster = this.dataset.videoPoster;
                const videoTitle = this.dataset.videoTitle;
                const videoDesc = this.dataset.videoDesc;

                modalVideoPlayer.poster = videoPoster;
                modalVideoSource.src = videoSrc;
                modalVideoTitle.textContent = videoTitle;
                modalVideoDesc.textContent = videoDesc;
                videoModal.style.display = 'flex';
                modalVideoPlayer.load();
            });
        });

        // Close modal
        document.querySelector('.close-modal').addEventListener('click', function() {
            videoModal.style.display = 'none';
            modalVideoPlayer.pause();
        });

        // Close modal on outside click
        videoModal.addEventListener('click', function(e) {
            if (e.target === videoModal) {
                videoModal.style.display = 'none';
                modalVideoPlayer.pause();
            }
        });
    </script>
</body>
</html>
