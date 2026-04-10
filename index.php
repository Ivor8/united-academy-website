<?php
require_once 'admin/includes/config.php';

// Initialize database connection
$pdo = getDB();

// Get latest blog posts for homepage (limit to 3)
$blogStmt = $pdo->prepare("
    SELECT bp.*, u.first_name, u.last_name, GROUP_CONCAT(t.name) as tags
    FROM blog_posts bp 
    LEFT JOIN users u ON bp.author_id = u.id 
    LEFT JOIN blog_post_tags bpt ON bp.id = bpt.blog_post_id
    LEFT JOIN tags t ON bpt.tag_id = t.id
    WHERE bp.status = 'published'
    GROUP BY bp.id 
    ORDER BY bp.published_at DESC 
    LIMIT 3
");
$blogStmt->execute();
$blogPosts = $blogStmt->fetchAll();

// Get featured testimonials for homepage (limit to 3)
$testimonialStmt = $pdo->prepare("
    SELECT * FROM vw_approved_testimonials 
    WHERE featured = 1 OR rating >= 4
    ORDER BY featured DESC, rating DESC, created_at DESC 
    LIMIT 3
");
$testimonialStmt->execute();
$testimonials = $testimonialStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- PRIMARY META TAGS (SEO) -->
    <title>UNITED ACADEMY-UARD | Vocational Training Institute - MINEFOP Accredited</title>
    <meta name="description" content="Official website of Vocational Training Institute UNITED ACADEMY-UARD - MINEFOP accredited center in Yaoundé. Professional certifications in health, paramedical, IT, management. 100% success rate.">
    <meta name="keywords" content="vocational training Cameroon, MINEFOP, UNITED ACADEMY-UARD, United Academy, health training Yaoundé, pharmacy salesperson, community management, infographics, social care assistant, professional certification">
    <meta name="author" content="UNITED ACADEMY-UARD">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="UNITED ACADEMY-UARD - School of Multiple Talents">
    <meta property="og:description" content="Accredited vocational training center in Yaoundé. Prepare for DQP/CQP with 100% success. Health, IT, management programs.">
    <meta property="og:image" content="assets/images/logo.jpg">
    <meta property="og:url" content="https://www.unitedacademy-uard.cm">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="https://www.unitedacademy-uard.cm">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter + Playfair Display (elegant) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300..700&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- ========== PRELOADER ========== -->
    <div id="preloader">
        <div class="preloader-animation">
            <div class="book">
                <div class="book-cover"></div>
                <div class="book-pages"></div>
            </div>
            <div class="pen"></div>
            <div class="preloader-text">Loading UNITED ACADEMY-UARD...</div>
        </div>
    </div>

    <div class="page-wrapper">

        <!-- ========== HEADER ========== -->
        <header class="main-header">
            <div class="container header-flex">
                <div class="logo-area">
                    <img src="assets/images/logo.jpg" alt="UNITED ACADEMY-UARD Logo" class="logo-img" width="50" height="50">
                </div>
                <!-- navigation + language switcher -->
                <nav class="main-nav">
                    <ul class="nav-links" id="navLinks">
                        <li><a href="index.php" class="nav-link active" data-i18n="nav_home">Home</a></li>
                        <li><a href="about.html" class="nav-link" data-i18n="nav_about">About</a></li>
                        <li><a href="programs.html" class="nav-link" data-i18n="nav_programs">Programs</a></li>
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

        <!-- ========== HERO SECTION (thicker overlay) ========== -->
        <section id="home" class="hero">
            <div class="hero-slider">
                <div class="slide active" style="background-image: url('assets/images/hero1.jpg');"></div>
                <div class="slide" style="background-image: url('assets/images/hero2.jpg');"></div>
            </div>
            <div class="thick-overlay"></div>
            <div class="container hero-content">
                <div class="hero-text-box fade-up">
                    <h1 class="hero-title">Welcome to <span class="accent">UNITED ACADEMY-UARD</span></h1>
                    <p class="hero-sub">Your gateway to professional excellence in Cameroon</p>
                    <div class="hero-buttons">
                        <a href="programs.html" class="btn btn-primary" data-i18n="explore_programs">Explore Programs</a>
                        <a href="contact.html" class="btn btn-outline" data-i18n="contact_us">Contact Us</a>
                    </div>
                </div>
            </div>
            <div class="slider-indicators">
                <span class="dot active" data-slide="0"></span>
                <span class="dot" data-slide="1"></span>
            </div>
        </section>

        <!-- ========== ABOUT SECTION ========== -->
        <section id="about" class="section about-section">
            <div class="container">
                <div class="section-header fade-up">
                    <h2 data-i18n="about_title">About UNITED ACADEMY-UARD</h2>
                    <div class="divider"></div>
                </div>
                <div class="about-grid">
                    <div class="about-text fade-up">
                        <p><strong>Vocational Training Institute UNITED ACADEMY-UARD</strong> is a MINEFOP-accredited center (Agreement N° <strong>00300/MINEFOP/SG/DFOP/SDGSF/CSACD/CBAC</strong>) located in Yaoundé, Simbock. Since 2024, we blend academic excellence with hands-on skills to prepare the next generation of health, IT, and management professionals.</p>
                        <p>Our mission: deliver certified qualifications (DQP/CQP) with a 100% success rate, thanks to expert trainers and modern facilities. We are <em>the school of multiple talents</em> - rooted in Cameroonian values and global standards.</p>
                        <div class="about-stats">
                            <div class="stat"><span class="stat-num">100%</span> <span data-i18n="about_success">success rate</span></div>
                            <div class="stat"><span class="stat-num">8+</span> <span data-i18n="about_programs">specialized programs</span></div>
                            <div class="stat"><span class="stat-num">15+</span> <span data-i18n="about_partners">clinic partners</span></div>
                            <div class="stat"><span class="stat-num">98%</span> <span data-i18n="about_employ">employment rate</span></div>
                        </div>
                    </div>
                    <div class="about-image fade-up">
                        <img src="assets/images/about top.jpg" alt="Students at UNITED ACADEMY-UARD lab" loading="lazy">
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== PROGRAMS SECTION (updated with flyer programs) ========== -->
        <section id="programs" class="section programs-section bg-light">
            <div class="container">
                <div class="section-header fade-up">
                    <h2 data-i18n="programs_title">Our professional programs</h2>
                    <div class="divider"></div>
                </div>
                <div class="programs-grid">
                    <!-- Health Programs -->
                    <div class="program-card fade-up">
                        <div class="card-icon" style="background: rgba(30, 100, 200, 0.1);"><i class="fas fa-briefcase-medical" style="color: #1E64C8;"></i></div>
                        <h3 data-i18n="prog_pharma">Pharmacy Salesperson</h3>
                        <p data-i18n="prog_pharma_desc">Prepare for national exams, internships in partner pharmacies. DQP level.</p>
                        <a href="https://wa.me/237677544988?text=Hello%2C%20I%27m%20interested%20in%20the%20Pharmacy%20Salesperson%20program" class="card-link whatsapp-link" target="_blank" data-i18n="learn_more">Learn more &rarr;</a>
                    </div>
                    <div class="program-card fade-up">
                        <div class="card-icon" style="background: rgba(46, 125, 50, 0.1);"><i class="fas fa-hand-holding-heart" style="color: #2E7D32;"></i></div>
                        <h3 data-i18n="prog_avs">Social Care Assistant</h3>
                        <p data-i18n="prog_avs_desc">Elderly care, nursing basics, ethics - 100% employability. BEPC/Prob level.</p>
                        <a href="https://wa.me/237677544988?text=Hello%2C%20I%27m%20interested%20in%20the%20Social%20Care%20Assistant%20program" class="card-link whatsapp-link" target="_blank" data-i18n="learn_more">Learn more &rarr;</a>
                    </div>
                    <div class="program-card fade-up">
                        <div class="card-icon" style="background: rgba(25, 118, 210, 0.1);"><i class="fas fa-paint-brush" style="color: #1976D2;"></i></div>
                        <h3 data-i18n="prog_infograph">Multimedia Infographics</h3>
                        <p data-i18n="prog_infograph_desc">Photoshop, Illustrator, AI tools (DALL·E), web design, portfolio, freelancing.</p>
                        <a href="https://wa.me/237677544988?text=Hello%2C%20I%27m%20interested%20in%20the%20Multimedia%20Infographics%20program" class="card-link whatsapp-link" target="_blank" data-i18n="learn_more">Learn more &rarr;</a>
                    </div>
                    <div class="program-card fade-up">
                        <div class="card-icon" style="background: rgba(211, 47, 47, 0.1);"><i class="fas fa-chart-line" style="color: #D32F2F;"></i></div>
                        <h3 data-i18n="prog_digital">Digital Marketing & CM</h3>
                        <p data-i18n="prog_digital_desc">Social media, content creation, Meta/Google ads, community management.</p>
                        <a href="https://wa.me/237677544988?text=Hello%2C%20I%27m%20interested%20in%20the%20Digital%20Marketing%20program" class="card-link whatsapp-link" target="_blank" data-i18n="learn_more">Learn more &rarr;</a>
                    </div>
                </div>
                <div class="text-center fade-up">
                    <a href="programs.html" class="btn btn-primary" data-i18n="view_all_programs">View All Programs</a>
                </div>
            </div>
        </section>

        <!-- ========== WHY CHOOSE US ========== -->
        <section id="why" class="section why-section">
            <div class="container">
                <div class="section-header fade-up">
                    <h2 data-i18n="why_title">Why choose UNITED ACADEMY-UARD?</h2>
                    <div class="divider"></div>
                </div>
                <div class="why-grid">
                    <div class="why-item fade-up">
                        <i class="fas fa-award" style="color: var(--blue);"></i>
                        <h3 data-i18n="why_accredited">MINEFOP Accredited</h3>
                        <p data-i18n="why_accredited_desc">Official recognition ensures your certification is nationally valid.</p>
                    </div>
                    <div class="why-item fade-up">
                        <i class="fas fa-users" style="color: var(--green);"></i>
                        <h3 data-i18n="why_experts">Expert Trainers</h3>
                        <p data-i18n="why_experts_desc">Learn from industry professionals with real-world experience.</p>
                    </div>
                    <div class="why-item fade-up">
                        <i class="fas fa-briefcase" style="color: var(--red);"></i>
                        <h3 data-i18n="why_jobs">Job Placement</h3>
                        <p data-i18n="why_jobs_desc">98% employment rate through our extensive partner network.</p>
                    </div>
                    <div class="why-item fade-up">
                        <i class="fas fa-flask" style="color: var(--blue);"></i>
                        <h3 data-i18n="why_facilities">Modern Facilities</h3>
                        <p data-i18n="why_facilities_desc">Fully equipped labs, computer rooms, and learning resources.</p>
                    </div>
                    <div class="why-item fade-up">
                        <i class="fas fa-certificate" style="color: var(--green);"></i>
                        <h3 data-i18n="why_certification">Certification Focus</h3>
                        <p data-i18n="why_certification_desc">100% success rate in national DQP/CQP examinations.</p>
                    </div>
                    <div class="why-item fade-up">
                        <i class="fas fa-globe" style="color: var(--red);"></i>
                        <h3 data-i18n="why_global">Global Standards</h3>
                        <p data-i18n="why_global_desc">Curriculum aligned with international best practices.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== TESTIMONIALS SECTION (Dynamic) ========== -->
        <section id="testimonials" class="section testimonials-section bg-light">
            <div class="container">
                <div class="section-header fade-up">
                    <h2 data-i18n="testimonials_title">What our students say</h2>
                    <div class="divider"></div>
                </div>
                <div class="testimonials-grid">
                    <?php if (count($testimonials) > 0): ?>
                        <?php foreach ($testimonials as $testimonial): ?>
                            <div class="testimonial-card fade-up">
                                <i class="fas fa-quote-left quote"></i>
                                <p><?php echo htmlspecialchars($testimonial['testimonial_text']); ?></p>
                                <h4> 
                                    <?php echo htmlspecialchars($testimonial['student_name']); ?> 
                                    <span>
                                        <?php echo htmlspecialchars($testimonial['program_name'] ?: 'Graduate'); ?> 
                                        <?php if ($testimonial['graduation_year']): ?>
                                            - <?php echo $testimonial['graduation_year']; ?>
                                        <?php endif; ?>
                                    </span>
                                </h4>
                                <?php if ($testimonial['rating']): ?>
                                    <div class="testimonial-rating">
                                        <?php 
                                        $rating = (int)$testimonial['rating'];
                                        for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $rating ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback testimonials if no data -->
                        <div class="testimonial-card fade-up">
                            <i class="fas fa-quote-left quote"></i>
                            <p data-i18n="testimonial1">"The pharmacy course gave me real skills. My internship at Zenith Pro Clinic turned into a job offer!"</p>
                            <h4>Marie Claire <span>Pharmacy Salesperson 2025</span></h4>
                        </div>
                        <div class="testimonial-card fade-up">
                            <i class="fas fa-quote-left quote"></i>
                            <p data-i18n="testimonial2">"I learned infographics and AI tools. Now I work as a freelance designer while studying."</p>
                            <h4>Jean Paul <span>Infographics 2025</span></h4>
                        </div>
                        <div class="testimonial-card fade-up">
                            <i class="fas fa-quote-left quote"></i>
                            <p data-i18n="testimonial3">"The trainers are amazing - they really care. I passed my DQP with honors."</p>
                            <h4>Stéphanie A. <span>Social Care Assistant</span></h4>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center fade-up">
                    <a href="testimonials.php" class="btn btn-primary" data-i18n="view_all_testimonials">View All Testimonials</a>
                </div>
            </div>
        </section>

        <!-- ========== BLOG + NEWS SECTION (Dynamic) ========== -->
        <!-- <section id="blog" class="section blog-section">
            <div class="container">
                <div class="section-header fade-up">
                    <h2 data-i18n="blog_title">Blog & News</h2>
                    <div class="divider"></div>
                </div>

                <div class="blog-news-filter fade-up">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" placeholder="Search articles..." data-i18n-placeholder="search_placeholder">
                    </div>
                    <div class="category-pills">
                        <button class="category-btn active" data-category="all" data-i18n="all_items">All</button>
                        <button class="category-btn" data-category="blog" data-i18n="blog_posts">Blog</button>
                        <button class="category-btn" data-category="news" data-i18n="news_articles">News</button>
                    </div>
                </div>

                <div class="blog-grid" id="blogGrid">
                    <?php if (count($blogPosts) > 0): ?>
                        <?php foreach ($blogPosts as $post): ?>
                            <article class="blog-card fade-up">
                                <div class="blog-card-image">
                                    <?php if ($post['media_type'] === 'video' && $post['media_url']): ?>
                                        <video poster="<?php echo htmlspecialchars($post['video_poster'] ?: $post['featured_image']); ?>" preload="metadata" class="blog-video">
                                            <source src="<?php echo htmlspecialchars($post['media_url']); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        <div class="video-overlay">
                                            <button class="play-video-btn" onclick="window.open('blog-single.php?id=<?php echo $post['id']; ?>', '_blank')">
                                                <i class="fas fa-play-circle"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?php echo htmlspecialchars($post['featured_image'] ?: 'assets/images/default-blog.jpg'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-image">
                                    <?php endif; ?>
                                    <div class="post-category"><?php echo ucfirst($post['category']); ?></div>
                                </div>
                                <div class="blog-card-content">
                                    <h3><a href="blog-single.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                    <p><?php echo htmlspecialchars(truncate($post['excerpt'] ?: strip_tags($post['content']), 120)); ?></p>
                                    <div class="post-meta">
                                        <span class="post-author">
                                            <i class="fas fa-user"></i> 
                                            <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>
                                        </span>
                                        <span class="post-date">
                                            <i class="fas fa-calendar"></i> 
                                            <?php echo formatDate($post['published_at']); ?>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>

                        <article class="blog-card fade-up">
                            <div class="blog-card-image">
                                <img src="assets/images/blog1.jpg" alt="Blog post" class="blog-image">
                                <div class="post-category">News</div>
                            </div>
                            <div class="blog-card-content">
                                <h3><a href="blog.php">New Programs for 2025</a></h3>
                                <p>Exciting new programs launching this academic year including AI for Creatives and Advanced Digital Marketing.</p>
                                <div class="post-meta">
                                    <span class="post-author"><i class="fas fa-user"></i> Admin</span>
                                    <span class="post-date"><i class="fas fa-calendar"></i> Jan 15, 2025</span>
                                </div>
                            </div>
                        </article>
                        <article class="blog-card fade-up">
                            <div class="blog-card-image">
                                <img src="assets/images/blog2.jpg" alt="Blog post" class="blog-image">
                                <div class="post-category">Blog</div>
                            </div>
                            <div class="blog-card-content">
                                <h3><a href="blog.php">Student Success Stories</a></h3>
                                <p>Meet our graduates who are making waves in their respective industries across Cameroon.</p>
                                <div class="post-meta">
                                    <span class="post-author"><i class="fas fa-user"></i> Admin</span>
                                    <span class="post-date"><i class="fas fa-calendar"></i> Jan 10, 2025</span>
                                </div>
                            </div>
                        </article>
                        <article class="blog-card fade-up">
                            <div class="blog-card-image">
                                <img src="assets/images/blog3.jpg" alt="Blog post" class="blog-image">
                                <div class="post-category">Events</div>
                            </div>
                            <div class="blog-card-content">
                                <h3><a href="blog.php">Open Day 2025</a></h3>
                                <p>Join us for our annual open day event. Tour our facilities, meet trainers, and discover your future career.</p>
                                <div class="post-meta">
                                    <span class="post-author"><i class="fas fa-user"></i> Admin</span>
                                    <span class="post-date"><i class="fas fa-calendar"></i> Jan 5, 2025</span>
                                </div>
                            </div>
                        </article>
                    <?php endif; ?>
                </div>
                <div id="noResultsMsg" class="no-results" style="display: none;" data-i18n="no_results">No matching posts.</div>
                <div class="text-center fade-up">
                    <a href="blog.php" class="btn btn-primary" data-i18n="view_all_posts">View All Posts</a>
                </div>
            </div>
        </section> -->

        <!-- ========== CONTACT CTA ========== -->
        <section id="contact" class="section cta-section">
            <div class="container">
                <div class="cta-content fade-up">
                    <h2 data-i18n="cta_home">Ready to start your journey?</h2>
                    <p data-i18n="cta_home_sub">Join UNITED ACADEMY-UARD and become part of the school of multiple talents.</p>
                    <div class="cta-buttons">
                        <a href="contact.html" class="btn btn-primary" data-i18n="cta_contact">Contact us today</a>
                        <a href="https://wa.me/237677544988?text=Hello%2C%20I%27m%20interested%20in%20UNITED%20ACADEMY-UARD" class="btn btn-outline" target="_blank" data-i18n="cta_whatsapp">WhatsApp</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== ENHANCED FOOTER ========== -->
        <footer class="main-footer">
            <div class="container">
                <div class="footer-grid">
                    <!-- col 1: logo + about -->
                    <div class="footer-col">
                        <img src="assets/images/logo.jpg" alt="UNITED ACADEMY-UARD" class="footer-logo" width="70">
                        <p data-i18n="footer_about">Vocational Training Institute UNITED ACADEMY-UARD - MINEFOP accredited. Excellence, employability, innovation.</p>
                    </div>
                    <!-- col 2: quick links -->
                    <div class="footer-col">
                        <h4 data-i18n="quick_links">Quick links</h4>
                        <ul>
                            <li><a href="index.php" data-i18n="nav_home">Home</a></li>
                            <li><a href="about.html" data-i18n="nav_about">About</a></li>
                            <li><a href="programs.html" data-i18n="nav_programs">Programs</a></li>
                            <li><a href="testimonials.php" data-i18n="nav_testimonials">Testimonials</a></li>
                            <li><a href="blog.php" data-i18n="nav_blog">Blog</a></li>
                            <li><a href="contact.html" data-i18n="nav_contact">Contact</a></li>
                            <li><a href="#" data-i18n="privacy">Privacy policy</a></li>
                        </ul>
                    </div>
                    <!-- col 3: contact info -->
                    <div class="footer-col">
                        <h4 data-i18n="footer_contact">Contact</h4>
                        <p><i class="fas fa-map-pin"></i> Yaoundé-Simbock, Montée Mechcam, Roseville Complex (Immeuble Bleu)</p>
                        <p><i class="fas fa-phone"></i> +237 683 05 93 55 / +237 658 72 62 37</p>
                        <p><i class="fas fa-envelope"></i> unitedacademyuard@gmail.com</p>
                    </div>
                    <!-- col 4: newsletter -->
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

    <!-- Main JavaScript -->
    <script src="assets/js/main.js"></script>
    <!-- Preloader handling -->
    <script>
        // Hide preloader when page is fully loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                const preloader = document.getElementById('preloader');
                if (preloader) {
                    preloader.style.opacity = '0';
                    preloader.style.visibility = 'hidden';
                    setTimeout(function() {
                        preloader.style.display = 'none';
                    }, 500);
                }
            }, 1000); // Show preloader for at least 1 second
        });

        // Fallback: hide preloader after 5 seconds even if load event fails
        setTimeout(function() {
            const preloader = document.getElementById('preloader');
            if (preloader && preloader.style.display !== 'none') {
                preloader.style.opacity = '0';
                preloader.style.visibility = 'hidden';
                setTimeout(function() {
                    preloader.style.display = 'none';
                }, 500);
            }
        }, 5000);
    </script>
    <!-- Blog filtering JS -->
    <script>
        // Simple blog filtering for homepage
        document.addEventListener('DOMContentLoaded', function() {
            const blogCards = document.querySelectorAll('.blog-card');
            const categoryBtns = document.querySelectorAll('.category-btn');
            const searchInput = document.getElementById('searchInput');
            const noResultsMsg = document.getElementById('noResultsMsg');

            function filterBlog() {
                const activeCategory = document.querySelector('.category-btn.active').dataset.category;
                const searchTerm = searchInput.value.toLowerCase();
                let visibleCount = 0;

                blogCards.forEach(card => {
                    const title = card.querySelector('h3 a').textContent.toLowerCase();
                    const content = card.querySelector('p').textContent.toLowerCase();
                    const category = card.querySelector('.post-category').textContent.toLowerCase();
                    
                    const matchesCategory = activeCategory === 'all' || category === activeCategory;
                    const matchesSearch = title.includes(searchTerm) || content.includes(searchTerm);
                    
                    if (matchesCategory && matchesSearch) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                noResultsMsg.style.display = visibleCount > 0 ? 'none' : 'block';
            }

            categoryBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    categoryBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    filterBlog();
                });
            });

            searchInput.addEventListener('input', filterBlog);
        });
    </script>
</body>
</html>
