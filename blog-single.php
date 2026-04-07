<?php
require_once 'admin/includes/config.php';

// Get database connection
$pdo = getDB();

// Get blog post
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$postId) {
    header('Location: blog.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT bp.*, u.first_name, u.last_name, u.username as author_username, GROUP_CONCAT(t.name) as tags
    FROM blog_posts bp 
    LEFT JOIN users u ON bp.author_id = u.id 
    LEFT JOIN blog_post_tags bpt ON bp.id = bpt.blog_post_id
    LEFT JOIN tags t ON bpt.tag_id = t.id
    WHERE bp.id = ? AND bp.status = 'published'
    GROUP BY bp.id
");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blog.php');
    exit();
}

// Increment view count
$incrementStmt = $pdo->prepare("CALL sp_increment_blog_views(?)");
$incrementStmt->execute([$postId]);

// Get related posts
$relatedStmt = $pdo->prepare("
    SELECT bp.id, bp.title, bp.featured_image, bp.media_type, bp.media_url, bp.video_poster, bp.published_at
    FROM blog_posts bp
    WHERE bp.id != ? AND bp.status = 'published' AND bp.category = ?
    ORDER BY bp.published_at DESC
    LIMIT 3
");
$relatedStmt->execute([$postId, $post['category']]);
$relatedPosts = $relatedStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <!-- META TAGS (SEO) -->
    <title><?php echo htmlspecialchars($post['title']); ?> | UNITED ACADEMY-UARD Blog</title>
    <meta name="description" content="<?php echo htmlspecialchars(truncate(strip_tags($post['content']), 160)); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($post['tags']); ?>, UNITED ACADEMY-UARD, <?php echo htmlspecialchars($post['category']); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(truncate(strip_tags($post['content']), 160)); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($post['featured_image'] ?: 'assets/images/logo.jpg'); ?>">
    <meta property="og:url" content="https://www.unitedacademy-uard.cm/blog-single.php?id=<?php echo $post['id']; ?>">
    <meta property="og:type" content="article">
    <meta property="article:published_time" content="<?php echo $post['published_at']; ?>">
    <meta property="article:author" content="<?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>">
    <meta property="article:section" content="<?php echo htmlspecialchars($post['category']); ?>">
    <link rel="canonical" href="https://www.unitedacademy-uard.cm/blog-single.php?id=<?php echo $post['id']; ?>">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300..700&family=Playfair+Display:wght@400;700;900&display=swap" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .blog-single {
            padding: 4rem 0;
        }
        .blog-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .blog-category {
            display: inline-block;
            background: var(--blue);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }
        .blog-title {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        .blog-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray);
            font-size: 0.9rem;
        }
        .meta-item i {
            color: var(--blue);
        }
        .blog-featured-media {
            margin-bottom: 3rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .blog-featured-media img,
        .blog-featured-media video {
            width: 100%;
            height: auto;
            display: block;
        }
        .blog-content {
            max-width: 800px;
            margin: 0 auto 3rem;
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--dark);
        }
        .blog-content h2,
        .blog-content h3,
        .blog-content h4 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        .blog-content h2 {
            font-size: 1.8rem;
        }
        .blog-content h3 {
            font-size: 1.5rem;
        }
        .blog-content p {
            margin-bottom: 1.5rem;
        }
        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        .blog-content blockquote {
            border-left: 4px solid var(--blue);
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            color: var(--gray);
        }
        .blog-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }
        .tag {
            background: var(--light);
            color: var(--dark);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            text-decoration: none;
            transition: var(--transition);
        }
        .tag:hover {
            background: var(--blue);
            color: white;
        }
        .blog-share {
            text-align: center;
            padding: 2rem 0;
            border-top: 1px solid var(--light);
            border-bottom: 1px solid var(--light);
            margin-bottom: 3rem;
        }
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
        }
        .share-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        .share-facebook {
            background: #1877f2;
            color: white;
        }
        .share-twitter {
            background: #1da1f2;
            color: white;
        }
        .share-linkedin {
            background: #0077b5;
            color: white;
        }
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .related-posts {
            margin-top: 4rem;
        }
        .related-posts h3 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 3rem;
            color: var(--dark);
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        .related-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: var(--transition);
        }
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .related-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        .related-image img,
        .related-image video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .related-content {
            padding: 1.5rem;
        }
        .related-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        .related-title a {
            color: var(--dark);
            text-decoration: none;
        }
        .related-title a:hover {
            color: var(--blue);
        }
        .related-meta {
            color: var(--gray);
            font-size: 0.85rem;
        }
        @media (max-width: 768px) {
            .blog-title {
                font-size: 2rem;
            }
            .blog-meta {
                gap: 1rem;
            }
            .blog-content {
                font-size: 1rem;
                margin: 0 1rem 2rem;
            }
            .related-grid {
                grid-template-columns: 1fr;
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
                        <li><a href="testimonials.php" class="nav-link" data-i18n="nav_testimonials">Testimonials</a></li>
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

        <!-- ========== BLOG SINGLE ========== -->
        <section class="section blog-single">
            <div class="container">
                <!-- Blog Header -->
                <div class="blog-header animate-fade-in">
                    <div class="blog-category"><?php echo ucfirst($post['category']); ?></div>
                    <h1 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="blog-meta">
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo formatDate($post['published_at']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-eye"></i>
                            <span><?php echo number_format($post['views']); ?> views</span>
                        </div>
                    </div>
                </div>

                <!-- Featured Media -->
                <?php if ($post['featured_image'] || $post['media_url']): ?>
                    <div class="blog-featured-media animate-slide-up">
                        <?php if ($post['media_type'] === 'video' && $post['media_url']): ?>
                            <video controls poster="<?php echo htmlspecialchars($post['video_poster'] ?: $post['featured_image']); ?>">
                                <source src="<?php echo htmlspecialchars($post['media_url']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Blog Content -->
                <div class="blog-content animate-fade-in">
                    <?php echo $post['content']; ?>
                </div>

                <!-- Tags -->
                <?php if ($post['tags']): ?>
                    <div class="blog-tags animate-slide-up">
                        <?php $tags = explode(',', $post['tags']); ?>
                        <?php foreach ($tags as $tag): ?>
                            <a href="blog.php?tag=<?php echo urlencode(trim($tag)); ?>" class="tag"><?php echo htmlspecialchars(trim($tag)); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Share -->
                <div class="blog-share animate-slide-up">
                    <h3>Share this article</h3>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://www.unitedacademy-uard.cm/blog-single.php?id=' . $post['id']); ?>" target="_blank" class="share-btn share-facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://www.unitedacademy-uard.cm/blog-single.php?id=' . $post['id']); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="share-btn share-twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://www.unitedacademy-uard.cm/blog-single.php?id=' . $post['id']); ?>" target="_blank" class="share-btn share-linkedin">
                            <i class="fab fa-linkedin-in"></i> LinkedIn
                        </a>
                    </div>
                </div>

                <!-- Related Posts -->
                <?php if (count($relatedPosts) > 0): ?>
                    <div class="related-posts animate-slide-up">
                        <h3>Related Articles</h3>
                        <div class="related-grid">
                            <?php foreach ($relatedPosts as $related): ?>
                                <div class="related-card">
                                    <div class="related-image">
                                        <?php if ($related['media_type'] === 'video' && $related['media_url']): ?>
                                            <video poster="<?php echo htmlspecialchars($related['video_poster'] ?: $related['featured_image']); ?>" preload="metadata">
                                                <source src="<?php echo htmlspecialchars($related['media_url']); ?>" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        <?php else: ?>
                                            <img src="<?php echo htmlspecialchars($related['featured_image'] ?: 'assets/images/default-blog.jpg'); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="related-content">
                                        <h4 class="related-title">
                                            <a href="blog-single.php?id=<?php echo $related['id']; ?>"><?php echo htmlspecialchars($related['title']); ?></a>
                                        </h4>
                                        <div class="related-meta">
                                            <i class="fas fa-calendar"></i> <?php echo formatDate($related['published_at']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
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
                            <li><a href="blog.php" data-i18n="nav_blog">Blog</a></li>
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
</body>
</html>
